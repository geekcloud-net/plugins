<?php
/**
 * Views API
 *
 * @package Framework\Views
 */

/**
 * Helper class for controlling all aspects of a view.
 *
 * Supported methods (automatically hooked):
 * - `init()`                    - for registering post types, taxonomies, rewrite rules etc.
 * - `parse_query()`             - for correcting query flags
 * - `pre_get_posts()`           - for altering the query, without affecting the query flags
 * - `posts_search()`,
 * - `posts_clauses()`,
 * - `posts_request()`           - for direct SQL manipulation
 * - `the_posts()`               - for various other manipulations
 * - `template_redirect()`       - for enqueuing scripts etc.
 * - `template_include( $path )` - for loading a different template file
 * - `title_parts( $parts )`     - for changing the title
 * - `breadcrumbs( $trail )`     - for changing the breadcrumbs
 * - `notices()`                 - for displaying notices
 */
abstract class APP_View {

	/**
	 * Test if this class should handle the current view.
	 *
	 * Use is_*() conditional tags and get_query_var()
	 *
	 * @return bool
	 */
	abstract function condition();


	function __construct() {
		// 'init' hook (always ran)
		if ( method_exists( $this, 'init' ) )
			add_action( 'init', array( $this, 'init' ) );

		// $wp_query hooks
		$actions = array( 'parse_query', 'pre_get_posts' );
		$filters = array( 'posts_search', 'posts_clauses', 'posts_request', 'the_posts' );

		foreach ( $actions as $method ) {
			if ( method_exists( $this, $method ) )
				add_action( $method, array( $this, '_action' ) );
		}

		foreach ( $filters as $method ) {
			if ( method_exists( $this, $method ) )
				add_filter( $method, array( $this, '_filter' ), 10, 2 );
		}

		// other hooks
		add_action( 'template_redirect', array( $this, '_template_redirect' ), 9 );
	}

	final function _action( $wp_query ) {
		if ( $wp_query->is_main_query() && $this->condition() ) {
			$method = current_filter();

//			debug( get_class( $this ) . '->' . $method . '()' );

			$this->$method( $wp_query );
		}
	}

	final function _filter( $value, $wp_query ) {
		if ( $wp_query->is_main_query() && $this->condition() ) {
			$method = current_filter();

//			debug( get_class( $this ) . '->' . $method . '()' );

			$value = $this->$method( $value, $wp_query );
		}

		return $value;
	}

	final function _template_redirect() {
		if ( !$this->condition() )
			return;

		if ( method_exists( $this, 'template_redirect' ) )
			$this->template_redirect();

		$filters = array(
			'template_include' => 'template_include',
			'appthemes_title_parts' => 'title_parts',
			'appthemes_notices' => 'notices',
			'breadcrumb_trail_items' => 'breadcrumbs',
		);

		// register any vars that need to be passed to loaded template
		if ( method_exists( $this, 'template_vars' ) )
			appthemes_add_template_var( $this->template_vars() );

		foreach ( $filters as $filter => $method ) {
			if ( method_exists( $this, $method ) )
				add_filter( $filter, array( $this, $method ) );
		}
	}

	function notices() {
		appthemes_display_notices();
	}
}


/**
 * Class for handling special pages that have a specific template file.
 */
class APP_View_Page extends APP_View {

	private $template;
	private $default_title;
	private $hidden_features;

	// List of templates only available for internal use, not for use in page templates dropdown
	private static $internal_templates = array();

	// List of instances
	private static $instances = array();

	// Page ID cache
	private static $page_ids = array();

	function __construct( $template, $default_title, $hidden_features = array() ) {
		$this->template = $template;
		$this->default_title = $default_title;

		$this->hidden_features = wp_parse_args( $hidden_features, array(
			'hide_description' => false,
			'hide_post_image' => false,
			'hide_comment_status' => false,
			'hide_comments' => false,
			'hide_post_custom' => false,
			'hide_slug' => false,
			'hide_author' => false,
			'hide_page_parent' => false,
			'hide_page_template' => false,
			'hide_page_order' => false,
			'internal_use_only' => false,
		) );

		self::$instances[ get_class( $this ) ] = $this;

		parent::__construct();
	}

	function condition() {
		if ( is_page_template( $this->template ) )
			return true;

		$page_id = (int) get_query_var( 'page_id' );

		return $page_id && $page_id == self::_get_id( get_class( $this ) ); // for 'page_on_front'
	}

	static function _get_id( $class ) {
		$template = self::$instances[ $class ]->template;

		if ( isset( self::$page_ids[ $template ] ) )
			return self::$page_ids[ $template ];

		// don't use 'fields' => 'ids' because it skips caching
		$page_q = new WP_Query( array(
			'post_type' => 'page',
			'meta_key' => '_wp_page_template',
			'meta_value' => $template,
			'posts_per_page' => 1,
			'no_found_rows' => true,
			'suppress_filters' => true,
		) );

		if ( empty( $page_q->posts ) )
			$page_id = 0;
		else
			$page_id = $page_q->posts[0]->ID;

		$page_id = apply_filters( 'appthemes_page_id_for_template', $page_id, $template );

		self::$page_ids[$template] = $page_id;

		return $page_id;
	}

	static function install() {
		foreach ( self::$instances as $class => $instance ) {
			if ( self::_get_id( $class ) )
				continue;

			$page_id = wp_insert_post( array(
				'post_type' => 'page',
				'post_status' => 'publish',
				'post_title' => $instance->default_title
			) );

			// Cache will have been set to 0, so update it
			self::$page_ids[ $instance->template ] = $page_id;

			add_post_meta( $page_id, '_wp_page_template', $instance->template );
		}
	}

	static function uninstall() {
		foreach ( self::$instances as $class => $instance ) {
			$page_id = self::_get_id( $class );

			if ( !$page_id )
				continue;

			wp_delete_post( $page_id, true );

			self::$page_ids[ $instance->template ] = 0;
		}
	}

	static function manage_internal_use_page_templates() {
		foreach( self::$instances as $class => $instance ) {
			if ( $instance->hidden_features['internal_use_only'] ) {
				$page_id = self::_get_id( $class );
				// Add to internal templates only if page already exists
				if ( $page_id ) {
					self::$internal_templates[] = $instance->template;
				}
			}
		}

		add_post_type_support( 'page', 'appthemes_internal_use_page_templates', apply_filters( 'appthemes_internal_use_page_templates', array_unique( self::$internal_templates ) ) );
	}

	static function manage_edit_page_features() {
		global $pagenow, $post;

		if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) )
			return;

		if ( empty( $post ) || 'page' != $post->post_type )
			return;

		$page_template = get_page_template_slug( $post );

		$current_instance = '';
		foreach( self::$instances as $instance ) {
			if ( $instance->template == $page_template ) {
				$current_instance = $instance;
				break;
			}
		}

		if ( empty( $current_instance ) ) {
			$hidden_features = array(
				'hide_page_parent' => false,
				'hide_page_template' => false,
				'hide_page_order' => false,
				'internal_use_only' => false,
			);
		} else {
			$hidden_features = $current_instance->hidden_features;
		}

		remove_post_type_support( 'page', 'page-attributes' );
		remove_meta_box( 'pageparentdiv', 'page', 'side' );

		add_post_type_support( 'page', 'appthemes_page_attributes', $hidden_features );
		add_meta_box( 'appthemespageparentdiv', __( 'Page Attributes', APP_TD ), 'appthemes_page_attributes_meta_box', null, 'side', 'core' );

		if ( empty( $current_instance ) )
			return;

		$removable = array(
			'hide_comment_status',
			'hide_comments',
			'hide_slug',
			'hide_author',
		);

		foreach( $removable as $remove ) {
			if ( $current_instance->hidden_features[ $remove ] ){
				$meta_box = str_ireplace( array( 'hide_', '_' ), '', $remove);
				$meta_box = $meta_box . 'div';
				remove_meta_box( $meta_box, 'page', 'normal'  );
			}
		}

		if( $current_instance->hidden_features['hide_post_image'] ) {
			remove_meta_box( 'postimagediv', 'page', 'side' );
		}

		if( $current_instance->hidden_features['hide_post_custom'] ) {
			remove_meta_box( 'postcustom', 'page', 'normal' );
		}

		if( $current_instance->hidden_features['hide_description'] ) {
			remove_post_type_support( 'page', 'editor' );
		}

		if( $current_instance->hidden_features['hide_page_parent'] && $current_instance->hidden_features['hide_page_template'] && $current_instance->hidden_features['hide_page_order'] && $current_instance->hidden_features['internal_use_only'] ) {
			// This is an internal use page with no page attributes at all, need to drop in hidden input for page_template
			remove_meta_box( 'appthemespageparentdiv', 'page', 'side' );
			add_action( 'edit_form_after_editor', 'appthemes_fixed_page_template');
		} else {
			$hidden_features = array(
				'hide_page_parent' => $current_instance->hidden_features['hide_page_parent'],
				'hide_page_template' => $current_instance->hidden_features['hide_page_template'],
				'hide_page_order' => $current_instance->hidden_features['hide_page_order'],
				'internal_use_only' => $current_instance->hidden_features['internal_use_only'],
			);
			add_post_type_support( 'page', 'appthemes_page_attributes', $hidden_features );
		}

	}
}

add_action( 'appthemes_first_run', array( 'APP_View_Page', 'install' ), 9 );

if ( is_admin() ) {
	add_action( 'admin_init', 'appthemes_manage_internal_use_page_templates');
	add_action( 'add_meta_boxes_page', 'appthemes_manage_edit_page_features' );
}

function appthemes_manage_internal_use_page_templates(){
	APP_View_Page::manage_internal_use_page_templates();
}

function appthemes_manage_edit_page_features() {
	APP_View_Page::manage_edit_page_features();
}

function appthemes_fixed_page_template( $post ) {

	$current_template = !empty($post->page_template) ? $post->page_template : false;
	?>
	<input type="hidden" name="page_template" value="<?php echo esc_attr( $current_template ); ?>" />
	<?php
}

function appthemes_page_attributes_meta_box($post) {

	$post_type_object = get_post_type_object($post->post_type);
	$post_type_supports = get_all_post_type_supports( $post->post_type );

	if ( $post_type_object->hierarchical && !$post_type_supports['appthemes_page_attributes'][0]['hide_page_parent'] ) {
		$dropdown_args = array(
			'post_type'        => $post->post_type,
			'exclude_tree'     => $post->ID,
			'selected'         => $post->post_parent,
			'name'             => 'parent_id',
			'show_option_none' => __( '(no parent)', APP_TD ),
			'sort_column'      => 'menu_order, post_title',
			'echo'             => 0,
		);

		$dropdown_args = apply_filters( 'page_attributes_dropdown_pages_args', $dropdown_args, $post );
		$pages = wp_dropdown_pages( $dropdown_args );
		if ( ! empty($pages) ) {
			?>
			<p><strong><?php _e( 'Parent', APP_TD ); ?></strong></p>
			<label class="screen-reader-text" for="parent_id"><?php _e( 'Parent', APP_TD ); ?></label>
			<?php echo $pages; ?>
		<?php
		} // end empty pages check
	} // end hierarchical check.

	if ( 'page' == $post->post_type && 0 != count( get_page_templates() ) ) {

		$current_template = !empty($post->page_template) ? $post->page_template : false;

		if ( $post_type_supports['appthemes_page_attributes'][0]['internal_use_only'] ) {
			// This is an internal use page, need to keep the page template locked and don't even offer dropdown
			appthemes_fixed_page_template($post);
		} else {
			?>
			<p><strong><?php _e( 'Template', APP_TD ); ?></strong></p>
			<label class="screen-reader-text" for="page_template"><?php _e( 'Page Template', APP_TD ); ?></label>
			<select name="page_template" id="page_template">
			<option value='default'><?php _e( 'Default Template', APP_TD ); ?></option>
			<?php
				$templates = get_page_templates();
				ksort( $templates );

				// Take out appthemes internal use templates
				foreach( $templates as $name => $template ) {
					if ( in_array( $template, $post_type_supports['appthemes_internal_use_page_templates'][0] ) )
						unset( $templates[$name] );
				}

				foreach (array_keys( $templates ) as $template )
					: if ( $current_template == $templates[$template] )
						$selected = " selected='selected'";
					else
						$selected = '';
				echo "\n\t<option value='".$templates[$template]."' $selected>$template</option>";
				endforeach;
			?>
			</select>
			<?php
		}

	}

	if ( !$post_type_supports['appthemes_page_attributes'][0]['hide_page_order'] ) {
	?>
	<p><strong><?php _e( 'Order', APP_TD ); ?></strong></p>
	<p><label class="screen-reader-text" for="menu_order"><?php _e( 'Order', APP_TD ); ?></label><input name="menu_order" type="text" size="4" id="menu_order" value="<?php echo esc_attr( $post->menu_order ); ?>" /></p>
	<p><?php if ( 'page' == $post->post_type ) _e( 'Need help? Use the Help tab in the upper right of your screen.', APP_TD ); ?></p>
	<?php
	}

}