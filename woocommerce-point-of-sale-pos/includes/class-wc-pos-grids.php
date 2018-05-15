<?php
/**
 * Grids Page
 *
 * @author    Actuality Extensions
 * @package   WoocommercePointOfSale/Classes/Outlets
 * @category	Class
 * @since     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_Pos_Grids' ) ) :

/**
 * WC_Pos_Grids Class
 */
class WC_Pos_Grids {

	/**
	 * @var WC_Pos_Grids The single instance of the class
	 * @since 1.9
	 */
	protected static $_instance = null;

	/**
	 * Main WC_Pos_Grids Instance
	 *
	 * Ensures only one instance of WC_Pos_Grids is loaded or can be loaded.
	 *
	 * @since 1.9
	 * @static
	 * @return WC_Pos_Grids Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.9
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '1.9' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.9
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '1.9' );
	}

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );
	}
	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {
    $post_types = array('product');     //limit meta box to certain post types
    if ( in_array( $post_type, $post_types )) {
			add_meta_box(
				'product_grid_category',
				__( 'Product grid', 'wc_point_of_sale' ),
				array( $this, 'render_product_grid_category' ),
				$post_type,
				'side',
				'core'
			);
    }
	}
	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save_meta_box($post_id){

		global $wpdb;
		/*
		 * Verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['_ajax_nonce-add-product_grid'] ) )
			return $post_id;

		$nonce = $_POST['_ajax_nonce-add-product_grid'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'add-product_grid' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
                //     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		// Check the user's permissions.
		if ( 'product' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;

			if($_REQUEST['_pos_visibility']){
			    update_post_meta($post_id,'_pos_visibility',$_REQUEST['_pos_visibility']);
            }

			if(isset($_POST['pos_input']['product_grid'])){

				$product_grids = wc_point_of_sale_get_grids_for_product($post_id);

				if(!empty($_POST['pos_input']['product_grid'])){
					foreach ($_POST['pos_input']['product_grid'] as $grid_id) {
						if(in_array($grid_id, $product_grids)){
							$pos = array_search($grid_id, $product_grids);
							unset($product_grids[$pos]);
							continue;
						}
						$order_position = 1;
						$position = get_last_position_of_tile($grid_id);
						if(!empty($position->max)) $order_position = $position->max + 1;
						$data = array(
							'grid_id'           => $grid_id,
							'product_id'        => $post_id,
							'colour'            => 'ffffff',
							'background'        => '8E8E8E',
							'default_selection' => 0,
							'order_position'    => $order_position,
							'style'             => 'image'
						);
						$wpdb->insert( $wpdb->prefix.'wc_poin_of_sale_tiles', $data );
					}
				}
				if(!empty($product_grids)){
					$ids = implode(',', $product_grids);
					$remove_sql = "DELETE FROM {$wpdb->prefix}wc_poin_of_sale_tiles WHERE product_id = $post_id AND grid_id IN($ids)";
					$wpdb->query($remove_sql);
				}
			}else{
				$remove_sql = "DELETE FROM {$wpdb->prefix}wc_poin_of_sale_tiles WHERE product_id = $post_id";
				$wpdb->query($remove_sql);
			}
		}
		return $post_id;
	}

	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_product_grid_category( $post ){
		// Add an nonce field so we can check for it later.

		$grids         = wc_point_of_sale_get_grids();
		$product_grids = wc_point_of_sale_get_grids_for_product($post->ID);
		?>
			<div class="gridcategorydiv">
				<?php if (!empty($grids)){ ?>
				<div class="tabs-panel">
					<ul class="categorychecklist form-no-clear">
					<?php
					foreach ($grids as $grid) {
						$ch = '';
						if(in_array((int)$grid->ID, $product_grids))
							$ch = 'checked="checked"';
						echo '<li id="product_grid-'.$grid->ID.'"><label class="selectit"><input '.$ch.' value="'.$grid->ID.'" name="pos_input[product_grid][]" id="in-product_grid-'.$grid->ID.'" type="checkbox"> '.$grid->name.'</label></li>';
					}
					?>
					</ul>
				</div>
				<?php } ?>
				<div class="wp-hidden-children" id="product_grid-adder">
					<h4>
						<a class="hide-if-no-js" href="#product_grid-add" id="product_grid-add-toggle">+ <?php _e( 'Add new product grid ', 'wc_point_of_sale' ); ?></a>
					</h4>
					<p class="product_grid-add wp-hidden-child" id="product_grid-add">
						<label for="newproduct_grid" class="screen-reader-text"><?php _e( 'Add New Product Grid ', 'wc_point_of_sale' ); ?></label>
						<input type="text" aria-required="true" value="" class="form-required form-input-tip" id="newproduct_grid" name="newproduct_grid">
						<input type="button" value="<?php _e( 'Add new product grid ', 'wc_point_of_sale' ); ?>" class="button product_grid-add-submit" id="product_grid-add-submit">
						<input type="hidden" value="<?php echo wp_create_nonce("add-product_grid"); ?>" name="_ajax_nonce-add-product_grid" id="_ajax_nonce-add-product_grid">
						<span id="product_grid-ajax-response"></span>
					</p>
				</div>
			</div>
			<?php
	}

	/**
	 * Handles output of the grids page in admin.
	 *
	 * Shows the created grids and lets you add new ones or edit existing ones.
 	 * The added grids are stored in the database and can be used for layered navigation.
	 */
	public function output() {
		global $wpdb;


		// Action to perform: add, edit, delete or none
		$action = '';
		if ( ! empty( $_POST['add_new_grid'] ) ) {
			$action = 'add';
		} elseif ( ! empty( $_POST['save_grid'] ) && ! empty( $_GET['edit'] ) ) {
			$action = 'edit';
		} elseif ( ! empty( $_GET['delete'] ) ) {
			$action = 'delete';
		}

		// Add or edit an grid
		if ( 'add' === $action || 'edit' === $action ) {

			if ( 'edit' === $action ) {
				$grid_id = absint( $_GET['edit'] );
				//check_admin_referer( 'woocommerce-save-grid_' . $grid_id );
			}

			$grid_name   = ( isset( $_POST['grid_name'] ) )   ? (string) stripslashes( $_POST['grid_name'] ) : '';
			$grid_label  = ( isset( $_POST['grid_label'] ) )    ? wc_sanitize_taxonomy_name( stripslashes( (string) $_POST['grid_label'] ) ) : '';
			$sort_order  = ( isset( $_POST['grid_sort'] ) ) ? (string) stripslashes( $_POST['grid_sort'] ) : 'name';

			// Auto-generate the label or slug if only one of both was provided

			if ( ! $grid_name ) {
				$grid_name = ucfirst( $grid_label );
			}
			if ( ! $grid_label ) {
				$grid_label = wc_sanitize_taxonomy_name( stripslashes( $grid_name ) );
			}

			// Forbidden grid names
			// http://codex.wordpress.org/Function_Reference/register_taxonomy#Reserved_Terms
			$reserved_terms = array(
				'attachment', 'attachment_id', 'author', 'author_name', 'calendar', 'cat', 'category', 'category__and',
				'category__in', 'category__not_in', 'category_name', 'comments_per_page', 'comments_popup', 'cpage', 'day',
				'debug', 'error', 'exact', 'feed', 'hour', 'link_category', 'm', 'minute', 'monthnum', 'more', 'name',
				'nav_menu', 'nopaging', 'offset', 'order', 'orderby', 'p', 'page', 'page_id', 'paged', 'pagename', 'pb', 'perm',
				'post', 'post__in', 'post__not_in', 'post_format', 'post_mime_type', 'post_status', 'post_tag', 'post_type',
				'posts', 'posts_per_archive_page', 'posts_per_page', 'preview', 'robots', 's', 'search', 'second', 'sentence',
				'showposts', 'static', 'subpost', 'subpost_id', 'tag', 'tag__and', 'tag__in', 'tag__not_in', 'tag_id',
				'tag_slug__and', 'tag_slug__in', 'taxonomy', 'tb', 'term', 'type', 'w', 'withcomments', 'withoutcomments', 'year',
			);

			// Error checking
			if ( ! $grid_name || ! $grid_label ) {
				$error = __( 'Please, provide an grid name, slug and type.', 'woocommerce' );
			} elseif ( strlen( $grid_label ) >= 28 ) {
				$error = sprintf( __( 'Slug “%s” is too long (28 characters max). Shorten it, please.', 'woocommerce' ), sanitize_title( $grid_label ) );
			} elseif ( in_array( $grid_label, $reserved_terms ) ) {
				$error = sprintf( __( 'Slug “%s” is not allowed because it is a reserved term. Change it, please.', 'woocommerce' ), sanitize_title( $grid_label ) );
			} else {
				$grid_exists = wc_point_of_sale_grid_exists( wc_point_of_sale_grid_name( $grid_label ) );

				if ( 'add' === $action && $grid_exists ) {
					$error = sprintf( __( 'Slug “%s” is already in use. Change it, please.', 'woocommerce' ), sanitize_title( $grid_label ) );
				}
				if ( 'edit' === $action ) {
					$old_grid_label = $wpdb->get_var( "SELECT label FROM {$wpdb->prefix}wc_poin_of_sale_grids WHERE ID = $grid_id" );
					$old_grid_name = $wpdb->get_var( "SELECT name FROM {$wpdb->prefix}wc_poin_of_sale_grids WHERE ID = $grid_id" );
					if ( $old_grid_label != $grid_label && $old_grid_name != $grid_name && $grid_exists ) {
						$error = sprintf( __( 'Slug “%s” is already in use. Change it, please.', 'woocommerce' ), sanitize_title( $grid_name ) );
					}
				}
			}

			// Show the error message if any
			if ( ! empty( $error ) ) {
				echo '<div id="woocommerce_errors" class="error fade"><p>' . $error . '</p></div>';
			} else {

				// Add new grid
				if ( 'add' === $action ) {

					$grid = array(
						'label'      => $grid_label,
						'name'       => $grid_name,
						'sort_order' => $sort_order,
					);
					// insert gird layout data  its table "wp_wc_poin_of_sale_grids"
					$wpdb->insert( $wpdb->prefix . 'wc_poin_of_sale_grids', $grid );
					do_action( 'woocommerce_grid_added', $wpdb->insert_id, $grid );
					echo '<div id="message" class="updated"><p>Product Grid added successfully.</p></div>';
					$action_completed = true;
				}

				// Edit existing grid
				if ( 'edit' === $action ) {
					$grid = array(
						'label'      => $grid_label,
						'name'       => $grid_name,
						'sort_order' => $sort_order,
					);
					$wpdb->update( $wpdb->prefix . 'wc_poin_of_sale_grids', $grid, array( 'ID' => $grid_id ) );

					echo '<div id="message" class="updated"><p>Product Grid updated successfully.</p></div>';
					$action_completed = true;
				}
				flush_rewrite_rules();
			}
		}else	 if(isset($_GET['message']) && !empty($_GET['message']) && $_GET['message'] == '4'){
		    	echo '<div id="message" class="updated"><p>Product Grid deleted successfully.</p></div>';
	    	}

		// Delete an grid
		if ( 'delete' === $action ) {
			// Security check
			$grid_id = absint( $_GET['delete'] );
			 $wpdb->query("DELETE FROM " .$wpdb->prefix. "wc_poin_of_sale_grids WHERE ID =".$grid_id." ");
			 $wpdb->query("DELETE FROM " .$wpdb->prefix. "wc_poin_of_sale_tiles WHERE grid_id =".$grid_id." ");
			echo '<div id="woocommerce_errors" class="error fade"><p>Product Grid deleted successfully.</p></div>';
			$action_completed = true;

		}
		// Show admin interface
		if ( ! empty( $_GET['edit'] ) )
			$this->edit_grid();
		else
			$this->add_grid();
	}

	public function delete_grids()
	{
		global $wpdb;
		if(isset($_POST['id']) && !empty($_POST['id'])){
			$ids = $_POST['id'];
		}
		elseif (isset($_GET['id']) && !empty($_GET['id']) ) {
			$ids = $_GET['id'];
		}
		$filter  = '';
		$filter2 = '';
		if($ids)
			$ids = wc_pos_check_can_delete('grid', $ids);

		if( $ids ){
          if(is_array($ids)){
            $ids = implode(',', array_map('intval', $ids));
            $filter  .= "WHERE ID IN ($ids)";
            $filter2 .= "WHERE grid_id IN ($ids)";
          }else{
            $filter  .= "WHERE ID = $ids";
            $filter2 .= "WHERE grid_id = $ids";
          }
      $table_name  = $wpdb->prefix . "wc_poin_of_sale_grids";
      $table_name2 = $wpdb->prefix . "wc_poin_of_sale_tiles";
    	$query  = "DELETE FROM $table_name $filter";
    	$query2 = "DELETE FROM $table_name2 $filter2";
    	if( $wpdb->query( $query )  && $wpdb->query( $query2 ) ) {
				return wp_redirect( add_query_arg( array( "page" => WC_POS()->id_grids, "message" => 4 ), 'admin.php' ) );
			}else{
				return wp_redirect( add_query_arg( array( "page" => WC_POS()->id_grids ), 'admin.php' ) );
			}
    }
			return wp_redirect( add_query_arg( array( "page" => WC_POS()->id_grids ), 'admin.php' ) );
	}

	/**
	 * Edit Grid admin panel
	 *
	 * Shows the interface for changing an grids type between select and text
	 */
	public function edit_grid() {
		global $wpdb;

		$edit = absint( $_GET['edit'] );
		$grid_to_edit = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "wc_poin_of_sale_grids WHERE ID = '$edit'");

		//$att_type 	= $grid_to_edit->grid_type;
		$att_label 	= $grid_to_edit->label;
		$att_name 	= $grid_to_edit->name;
		$grid_sort 	= !empty($grid_to_edit->sort_order) ? $grid_to_edit->sort_order : 'name';
		//$att_orderby 	= $grid_to_edit->grid_orderby;
		?>
		<div class="wrap woocommerce">
			<div class="icon32 icon32-grids" id="icon-woocommerce"><br/></div>
		    <h2><?php _e( 'Edit Product Grid', 'woocommerce' ) ?></h2>
			<form action="admin.php?page=wc_pos_grids&amp;edit=<?php echo absint( $edit ); ?>" method="post">
				<table class="form-table">
					<tbody>
						<tr class="form-field form-required">
							<th scope="row" valign="top">
								<label for="grid_label"><?php _e( 'Name', 'woocommerce' ); ?></label>
							</th>
							<td>
								<input name="grid_name" id="grid_name" type="text" value="<?php echo esc_attr( $att_name ); ?>" maxlength="28" />
								<p class="description"><?php _e( 'Name for the grid (shown on the front-end).', 'wc_point_of_sale' ); ?></p>

							</td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row" valign="top">
								<label for="grid_name"><?php _e( 'Slug', 'woocommerce' ); ?></label>
							</th>
							<td>
								<input name="grid_label" id="grid_label" type="text" value="<?php echo esc_attr( $att_label ); ?>" />
								<p class="description"><?php _e( 'Unique slug/reference for the grid; must be shorter than 28 characters.', 'wc_point_of_sale' ); ?></p>

							</td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row" valign="top">
								<label for="grid_sort"><?php _e( 'Default sort order', 'woocommerce' ); ?></label>
							</th>
							<td>
								<select name="grid_sort" id="grid_sort">
									<option value="name" <?php selected('name', $grid_sort, true ); ?> ><?php _e( 'Name', 'woocommerce' ); ?></option>
									<option value="menu_order" <?php selected('menu_order', $grid_sort, true ); ?> ><?php _e( 'Custom ordering', 'woocommerce' ); ?></option>
								</select>
								<p class="description"><?php _e( 'Determines the sort order of the products on the POS page. If using custom ordering, you can drag and drop the products in this grid.', 'wc_point_of_sale' ); ?></p>
							</td>
						</tr>
					</tbody>
				</table>
				<p class="submit"><input type="submit" name="save_grid" id="submit" class="button-primary" value="<?php _e( 'Update', 'woocommerce' ); ?>"></p>
				<?php wp_nonce_field( 'woocommerce-save-grid_' . $edit ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Add Grid admin panel
	 *
	 * Shows the interface for adding new grids
	 */
	public function add_grid() {
		?>
		<div class="wrap nosubsub">
		    <h2><?php _e( 'Product Grids', 'woocommerce' ) ?></h2>


		    <br class="clear" />
		    <div id="col-container">
		    	<div id="col-right">
		    		<div class="col-wrap">
		    				<form id="wc_pos_grids_table" action="" method="post">
								<?php
									$grids_table = WC_POS()->grids_table();
									$grids_table->prepare_items();
									$grids_table->display();
								?>
							</form>
		    		</div>
		    	</div>
		    	<div id="col-left">
		    		<div class="col-wrap">
		    			<div class="form-wrap">
		    				<h3><?php _e( 'Add New Product Grid', 'wc_point_of_sale' ) ?></h3>
		    				<p><?php _e( 'Product grids lets you define the products that you want to show on each register. Using tiles which represent a produce, you can customise the product grid layout.', 'wc_point_of_sale' ) ?></p>
		    				<form action="" method="post" class="addGirdLayout">
								<div class="form-field">
									<label for="grid_name"><?php _e( 'Name', 'woocommerce' ); ?></label>
									<input name="grid_name" id="grid_name" type="text" value="" />
									<p class="description"><?php _e( 'Name for the grid (shown when configuring and viewing the register)', 'wc_point_of_sale' ); ?></p>
								</div>

								<div class="form-field">
									<label for="grid_label"><?php _e( 'Slug', 'woocommerce' ); ?></label>
									<input name="grid_label" id="grid_label" type="text" value="" maxlength="28" />
									<p class="description"><?php _e( 'Unique slug/reference for the grid; must be shorter than 28 characters.', 'woocommerce' ); ?></p>
								</div>

								<div class="form-field">
									<label for="grid_sort"><?php _e( 'Default sort order', 'woocommerce' ); ?></label>
									<select name="grid_sort" id="grid_sort">
										<option value="name"><?php _e( 'Name', 'woocommerce' ); ?></option>
										<option value="menu_order"><?php _e( 'Custom ordering', 'woocommerce' ); ?></option>
									</select>
									<p class="description"><?php _e( 'Determines the sort order of the products on the POS page. If using custom ordering, you can drag and drop the products in this grid.', 'woocommerce' ); ?></p>
								</div>

								<p class="submit"><input type="submit" name="add_new_grid" id="submit" class="button" value="<?php _e( 'Add Product Grid', 'wc_point_of_sale' ); ?>"></p>
								<?php wp_nonce_field( 'woocommerce-add-new_grid' ); ?>
		    				</form>
		    			</div>
		    		</div>
		    	</div>
		    </div>
		    <script type="text/javascript">
			/* <![CDATA[ */

				jQuery('a.delete').click(function(e){
		    		var answer = confirm ("<?php _e( 'Are you sure you want to delete this grid?', 'wc_point_of_sale' ); ?>");
					e.preventDefault();
					if (answer){
						var obj = this;
						var currentURL = document.URL;
						jQuery.ajax({
							type: "GET",
							url: jQuery(obj).attr('href'),
							async:false,
							cache : false,
							success: function(data)
							{
								window.location.href=currentURL;
							}
						});

					}else{
						return false;
					}

		    	});


			/* ]]> */
			</script>
		</div>
		<?php
	}

	public function get_data_names($ids = ''){
    global $wpdb;
        $filter = '';
        if( !empty($ids) ){
          if(is_array($ids)){
            $ids = implode(',', array_map('intval', $ids));
            $filter .= "WHERE ID IN  == ($ids)";
          }else{
            $filter .= "WHERE ID = $ids";
          }
        }
        $table_name = $wpdb->prefix . "wc_poin_of_sale_grids";
        $db_data = $wpdb->get_results("SELECT * FROM $table_name $filter");
        $data = array();

        foreach ($db_data as $value) {
          $data[] = get_object_vars($value);
        }
        $names_list = array();
    foreach ($data as $value) {
      $names_list[$value['ID']] = $value['name'];
    }
    $names_list['all'] = __('All Products', 'wc_point_of_sale');
    $names_list['categories'] = __('Category Taxonomy', 'wc_point_of_sale');
    return $names_list;
  }

}
return new WC_Pos_Grids;
endif;