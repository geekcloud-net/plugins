<?php
/**
 * @package Quality_Control
 * @subpackage Changesets
 * @since Quality Control 0.2
 */

require_once( dirname( __FILE__ ) . '/sourcecontrol.php' );

if ( ! defined( 'QC_CHANGESET_PTYPE' ) )
	define( 'QC_CHANGESET_PTYPE', 'changeset' );

add_action( 'init', array( 'QC_Changesets', 'init' ) );

/**
 * Creates the 'changeset' post type.
 *
 * @since Quality Control 0.2
 */
class QC_Changesets {
	private static $apis;

	private static $repo_type;
	private static $repo_class;

	private static $settings_group;

	public static function init() {
		$services = array(
			'beanstalk' => 'QC_Beanstalk',
			'bitbucket' => 'QC_Bitbucket',
			'github' => 'QC_Github',
		);
		$services = apply_filters( 'qc_changesets_classes', $services );

		foreach ( (array) $services as $id => $class ) {
			if ( class_exists( $class ) ) {
				appthemes_add_instance( $class );
				self::$apis[ $id ] = appthemes_get_instance( $class );
			}
		}

		self::register_cpt();
		add_action( 'save_post', array( __CLASS__, 'reference_tickets' ), 10, 2 );

		// Changeset URL
		add_action( 'admin_menu', array( __CLASS__, 'meta_box' ) );
		add_action( 'save_post', array( __CLASS__, 'save_changeset_url' ) );

		// Cron
		$cronjob = new scbCron( '', array(
			'callback' => array( __CLASS__, 'maybe_import_changesets' ),
			'interval' => 300	// 5 minutes
		) );
		add_action( 'appthemes_first_run', array( $cronjob, 'reset' ) );

		// Settings
		add_action( 'tabs_quality-control_page_app-settings', array( __CLASS__, 'init_tabs' ) );

		if ( self::$repo_type = $GLOBALS['qc_options']->repository['type'] ) {
			if ( ! isset( self::$apis[ self::$repo_type ] ) )
				return;

			self::$repo_class = self::$apis[ self::$repo_type ];

			// Import button
			add_action( 'restrict_manage_posts', array( __CLASS__, 'import_changesets_button' ) );
			add_action( 'load-edit.php', array( __CLASS__, 'import_changesets_handler' ) );
			add_action( 'admin_notices', array( __CLASS__, 'import_changesets_message' ) );
		}
	}

	public static function init_tabs( $admin_page ) {
		$admin_page->tab_sections['general']['source-control'] = array(
			'title' => __( 'Source Control', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Repository Type', APP_TD ),
					'type' => 'select',
					'name' => array( 'repository', 'type' ),
					'values' => self::load_repository_types(),
				),
				array(
					'title' => '',
					'type' => 'text',
					'name' => '_blank',
					'extra' => array( 'style' => 'display: none' ),
					'desc' => html_link( add_query_arg( array( 'post_type' => QC_CHANGESET_PTYPE ), admin_url( 'edit.php' ) ), __( 'View Changesets', APP_TD ) ),
				),
			),
		);

		// init repo settings
		foreach ( (array) self::$apis as $instance ) {
			$instance->init_tabs( $admin_page );
		}

		add_action( 'admin_notices', array( __CLASS__, 'disabled_repository_warning' ) );
	}

	/**
	 * Displays notices if a repository is disabled
	 * @return void
	 */
	public static function disabled_repository_warning() {
		global $qc_options;

		if ( isset( $_GET['tab'] ) ) {
			$repo_id = $_GET['tab'];

			if ( isset( self::$apis[ $repo_id ] ) && $repo_id != $qc_options->repository['type'] ) {
				echo scb_admin_notice( __( 'This repository is currently <strong>disabled</strong>. You can go to the <a href="?page=app-settings">General</a> tab to enable it.', APP_TD ), 'updated' );
			}
		}
	}

	public static function register_cpt() {
		$labels = array(
			'name' => _x( 'Changesets', 'changeset', APP_TD ),
			'singular_name' => _x( 'Changeset', 'changeset', APP_TD ),
			'add_new' => _x( 'Add New', 'changeset', APP_TD ),
			'add_new_item' => _x( 'Add New Changeset', 'changeset', APP_TD ),
			'edit_item' => _x( 'Edit Changeset', 'changeset', APP_TD ),
			'new_item' => _x( 'New Changeset', 'changeset', APP_TD ),
			'view_item' => _x( 'View Changeset', 'changeset', APP_TD ),
			'search_items' => _x( 'Search Changesets', 'changeset', APP_TD ),
			'not_found' => _x( 'No changesets found', 'changeset', APP_TD ),
			'not_found_in_trash' => _x( 'No changesets found in Trash', 'changeset', APP_TD ),
			'parent_item_colon' => _x( 'Parent Changeset:', 'changeset', APP_TD ),
			'menu_name' => _x( 'Changesets', 'changeset', APP_TD ),
		);

		$args = array(
			'labels' => $labels,
			'hierarchical' => false,

			'supports' => array( 'title', 'excerpt', 'custom-fields' ),

			'public' => false,
			'show_ui' => true,
			'menu_position' => 5,
			'menu_icon' => appthemes_locate_template_uri( 'images/admin-menu.png' ),
		);

		register_post_type( QC_CHANGESET_PTYPE, $args );
	}

	public static function reference_tickets( $changeset_id, $changeset ) {
		global $wpdb;

		if ( QC_CHANGESET_PTYPE != $changeset->post_type )
			return;

		// see http://core.trac.wordpress.org/ticket/24248
		$changeset_url = html_entity_decode( $changeset->guid );

		$comment_text = <<<EOB
In <a class="changeset-link" href="$changeset_url">[$changeset->post_title]</a>:

<div class="changeset-message">$changeset->post_excerpt</div>
EOB;

		$results = self::parse_commit_message( $changeset->post_excerpt );

		foreach ( $results as $ticket_id => $actions ) {
			if ( ! $ticket = get_post( $ticket_id ) ) {
				continue;
			}

			// Check for duplicates
			$sql = "SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = $ticket_id AND comment_content LIKE '%" . $changeset->guid . "%'";
			if ( $wpdb->get_var( $sql ) ) {
				continue;
			}

			$data = array(
				'comment_post_ID' => $ticket_id,
				'comment_author' => get_post_meta( $changeset_id, 'author', true ),
				'comment_author_email' => get_post_meta( $changeset_id, 'email', true ),
				'user_id' => $changeset->post_author,
				'comment_content' => addslashes( $comment_text )
			);

			// update ticket, prepare messages
			self::process_commit_message_actions( $actions, $ticket_id );

			$comment_id = wp_insert_comment( $data );


			// TODO: call wp_notify_post_author() ?
		}
	}


	// Apply changes to ticket specified in commit message
	private static function process_commit_message_actions( $actions, $ticket_id ) {
		global $qc_options;

		foreach ( $actions as $name => $data ) {

			switch ( $name ) {
				case 'ticket_status':
				case 'ticket_priority':
				case 'ticket_milestone':
				case 'ticket_category':
				case 'ticket_tags':
					$append = ( $name == 'ticket_tags' );
					$tax_instance = qc_get_tax_instance( $name );

					if ( ! $tax_instance ) {
						break;
					}

					if ( $data == 'closes' && $name == 'ticket_status' ) {
						$tax_instance->update_ticket( $ticket_id, $qc_options->ticket_status_closed );
					} else {
						$tax_instance->update_ticket( $ticket_id, $data, $append );
					}
					break;
				case 'ticket_assigned':
					$users = implode( ',', $data );
					QC_Assignment::update_ticket_owners( $ticket_id, $users );
					break;
			}

		}
	}


	// Looks for ticket references in a commit message
	public static function parse_commit_message( $msg ) {
		$results = array();

		// Parse multiple commands groups wrapped with square bracelets
		if ( preg_match_all( "/\[(.*?)\]/", $msg, $matches ) ) {
			foreach ( $matches[1] as $parted_msg ) {
				$result = self::parse_commit_message( $parted_msg );
				$results = $results + $result;
			}
			return $results;
		}

		// Parse commands for each ticket
		if ( preg_match_all( "/#([0-9]+)/", $msg, $matches ) ) {
			foreach ( $matches[1] as $ticket_id ) {
				$results[ $ticket_id ] = array();

				// GH Formatting Style
				// Status
				if ( preg_match( '/(Closes|Fixes)/i', $msg ) )
					$results[ $ticket_id ]['ticket_status'] = 'closes';

				// LH Formatting Style
				// Status
				if ( preg_match( "/status:([-a-z0-9]+)/i", $msg, $match ) )
					$results[ $ticket_id ]['ticket_status'] = $match[1];

				// Priority
				if ( preg_match( "/priority:([-a-z0-9]+)/i", $msg, $match ) )
					$results[ $ticket_id ]['ticket_priority'] = $match[1];

				// Milestone
				if ( preg_match( "/milestone:([-a-z0-9]+)/i", $msg, $match ) )
					$results[ $ticket_id ]['ticket_milestone'] = $match[1];

				// Category
				if ( preg_match( "/category:([-a-z0-9]+)/i", $msg, $match ) )
					$results[ $ticket_id ]['ticket_category'] = $match[1];

				// Tag - can be multiple
				if ( preg_match_all( "/tagged:([-a-z0-9]+)/i", $msg, $match ) )
					$results[ $ticket_id ]['ticket_tags'] = $match[1];

				// Assignment - can be multiple
				if ( preg_match_all( "/assigned:([-a-z0-9]+)/i", $msg, $match ) )
					$results[ $ticket_id ]['ticket_assigned'] = $match[1];

			}
		}

		return $results;
	}


	public static function maybe_import_changesets() {
		if ( ! self::$repo_class ) {
			return false;
		}

		return self::$repo_class->import_changesets( $GLOBALS['qc_options']->repository['details'][ self::$repo_class->id ] );
	}


	public static function load_repository_types() {
		$options = array( __( '&mdash; Select &mdash;', APP_TD ) );

		foreach ( self::$apis as $id => $repo_obj )
			$options[ $id ] = $repo_obj->get_title();

		return $options;
	}


	public static function import_changesets_button() {
		if ( QC_CHANGESET_PTYPE != $GLOBALS['post_type'] )
			return;

?>
<input style="display: block; float: right; margin-left: 1em;" class="button" type="submit" name="update_repo" value="<?php _e( 'Import changesets now', APP_TD ); ?>" />
<?php
	}

	public static function import_changesets_handler() {
		if ( !isset( $_REQUEST['update_repo'] ) || QC_CHANGESET_PTYPE != $_REQUEST['post_type'] )
			return;

		if ( !current_user_can( 'manage_options' ) )
			return;

		$data = self::maybe_import_changesets();

		if ( is_wp_error( $data ) ) {
			set_transient( 'qc_repo_update', $data->get_error_message() );
			$str = 'error';
		} else {
			set_transient( 'qc_repo_update', $data );
			$str = 'success';
		}

		wp_redirect( admin_url( add_query_arg( array( 'post_type' => QC_CHANGESET_PTYPE, 'repo_update' => $str ), 'edit.php' ) ) );
		die;
	}

	public static function import_changesets_message() {
		if ( !isset( $_REQUEST['repo_update'] ) || 'edit.php' != $GLOBALS['pagenow'] || QC_CHANGESET_PTYPE != $_REQUEST['post_type'] )
			return;

		$data = get_transient( 'qc_repo_update' );
		if ( false === $data )
			return;
		delete_transient( 'qc_repo_update' );

		if ( 'error' == $_REQUEST['repo_update'] ) {
			echo html( 'div class="error"', html( 'p', sprintf( __( 'Error while fetching changesets: %s.', APP_TD ), $data ) ) );
		} else {
			echo html( 'div class="updated"', html( 'p', sprintf( __( 'Imported %d changests.', APP_TD ), $data ) ) );
		}
	}

	public static function meta_box() {
		add_meta_box( 'changeset_url', __( 'Changeset URL', APP_TD ), array( __CLASS__, 'create_changeset_url_meta_box' ), QC_CHANGESET_PTYPE, 'normal', 'low' );
	}

	public static function create_changeset_url_meta_box() {
		global $post;
	?>
		<input type="text" name="quality[guid]" id="quality-guid-field" value="<?php echo get_post_field( 'guid', $post->ID, 'raw' ); ?>" style="width:100%;" />
	<?php
	}

	public static function save_changeset_url( $post_id ) {
		global $pagenow, $wpdb;

		if ( $pagenow != 'post.php' )
			return $post_id;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;

		if ( isset( $_POST['quality']['guid'] ) )
			$wpdb->update( $wpdb->posts, array( 'guid' => $_POST['quality']['guid'] ), array( 'ID' => $post_id ), array( '%s' ), array( '%d' ) );

		return $post_id;
	}


}

