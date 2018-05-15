<?php

class ADBC_Options_List extends WP_List_Table {

	/** Holds the message to be displayed if any */
	private $aDBc_message = "";

	/** Holds the class for the message : updated or error. Default is updated */
	private $aDBc_class_message = "updated";

	/** Holds options that will be displayed */
	private $aDBc_options_to_display = array();

	/** Holds counts + info of options categories */
	private $aDBc_options_categories_info	= array();

	/** Should we display "run search" or "continue search" button (after a timeout failed). Default is "run search" */
	private $aDBc_which_button_to_show = "new_search";

    function __construct(){
        parent::__construct(array(
            'singular'  => __('Option', 'advanced-db-cleaner'),		//singular name of the listed records
            'plural'    => __('Options', 'advanced-db-cleaner'),	//plural name of the listed records
            'ajax'      => false	//does this table support ajax?
		));
		if(isset($_POST['aDBc_new_search_button'])){
			$this->aDBc_run_new_search();
		}
		$this->aDBc_prepare_and_count_options();
		$this->aDBc_print_page_content();
    }

	/** Prepare options to display and count options for each category */
	function aDBc_prepare_and_count_options(){

		// Process bulk action if any before preparing options to display
		$deleted_options = array();
		if(!isset($_POST['aDBc_new_search_button'])){
			$deleted_options = $this->process_bulk_action();
		}

		// Prepare data
		aDBc_prepare_items_to_display(
			$deleted_options,
			$this->aDBc_options_to_display,
			$this->aDBc_options_categories_info,
			$this->aDBc_which_button_to_show,
			$this->aDBc_message,
			$this->aDBc_class_message,
			"options"
		);

		// Call WP prepare_items function
		$this->prepare_items();
	}

	/** Run new search for options */
	function aDBc_run_new_search(){

		/*
		* xxx: WP core options
		* Found in wp-admin/includes/schema.php
		* After each release of WP, this list should be updated to add new options if necessary (to minimize searches in files).
		*/
		$aDBc_wp_core_options = array(
			'siteurl',
			'home',
			'blogname',
			'blogdescription',
			'users_can_register',
			'admin_email',
			'start_of_week',
			'use_balanceTags',
			'use_smilies',
			'require_name_email',
			'comments_notify',
			'posts_per_rss',
			'rss_use_excerpt',
			'mailserver_url',
			'mailserver_login',
			'mailserver_pass',
			'mailserver_port',
			'default_category',
			'default_comment_status',
			'default_ping_status',
			'default_pingback_flag',
			'posts_per_page',
			'date_format',
			'time_format',
			'links_updated_date_format',
			'comment_moderation',
			'moderation_notify',
			'permalink_structure',
			'gzipcompression',
			'hack_file',
			'blog_charset',
			'moderation_keys',
			'active_plugins',
			'category_base',
			'ping_sites',
			'advanced_edit',
			'comment_max_links',
			'gmt_offset',
			// 1.5
			'default_email_category',
			'recently_edited',
			'template',
			'stylesheet',
			'comment_whitelist',
			'blacklist_keys',
			'comment_registration',
			'html_type',
			// 1.5.1
			'use_trackback',
			// 2.0
			'default_role',
			'db_version',
			// 2.0.1
			'uploads_use_yearmonth_folders',
			'upload_path',
			// 2.1
			'blog_public',
			'default_link_category',
			'show_on_front',
			// 2.2
			'tag_base',
			// 2.5
			'show_avatars',
			'avatar_rating',
			'upload_url_path',
			'thumbnail_size_w',
			'thumbnail_size_h',
			'thumbnail_crop',
			'medium_size_w',
			'medium_size_h',
			// 2.6
			'avatar_default',
			// 2.7
			'large_size_w',
			'large_size_h',
			'image_default_link_type',
			'image_default_size',
			'image_default_align',
			'close_comments_for_old_posts',
			'close_comments_days_old',
			'thread_comments',
			'thread_comments_depth',
			'page_comments',
			'comments_per_page',
			'default_comments_page',
			'comment_order',
			'sticky_posts',
			'widget_categories',
			'widget_text',
			'widget_rss',
			'uninstall_plugins',
			// 2.8
			'timezone_string',
			// 3.0
			'page_for_posts',
			'page_on_front',
			// 3.1
			'default_post_format',
			// 3.5
			'link_manager_enabled',
			// 4.3.0
			'finished_splitting_shared_terms',
			// Deleted from new versions
			'blodotgsping_url', 'bodyterminator', 'emailtestonly', 'phoneemail_separator', 'smilies_directory',
			'subjectprefix', 'use_bbcode', 'use_blodotgsping', 'use_phoneemail', 'use_quicktags', 'use_weblogsping',
			'weblogs_cache_file', 'use_preview', 'use_htmltrans', 'smilies_directory', 'fileupload_allowedusers',
			'use_phoneemail', 'default_post_status', 'default_post_category', 'archive_mode', 'time_difference',
			'links_minadminlevel', 'links_use_adminlevels', 'links_rating_type', 'links_rating_char',
			'links_rating_ignore_zero', 'links_rating_single_image', 'links_rating_image0', 'links_rating_image1',
			'links_rating_image2', 'links_rating_image3', 'links_rating_image4', 'links_rating_image5',
			'links_rating_image6', 'links_rating_image7', 'links_rating_image8', 'links_rating_image9',
			'links_recently_updated_time', 'links_recently_updated_prepend', 'links_recently_updated_append',
			'weblogs_cacheminutes', 'comment_allowed_tags', 'search_engine_friendly_urls', 'default_geourl_lat',
			'default_geourl_lon', 'use_default_geourl', 'weblogs_xml_url', 'new_users_can_blog', '_wpnonce',
			'_wp_http_referer', 'Update', 'action', 'rich_editing', 'autosave_interval', 'deactivated_plugins',
			'can_compress_scripts', 'page_uris', 'update_core', 'update_plugins', 'update_themes', 'doing_cron',
			'random_seed', 'rss_excerpt_length', 'secret', 'use_linksupdate', 'default_comment_status_page',
			'wporg_popular_tags', 'what_to_show', 'rss_language', 'language', 'enable_xmlrpc', 'enable_app',
			'embed_autourls', 'default_post_edit_rows',
			//Found in wp-admin/includes/upgrade.php
			'widget_search',
			'widget_recent-posts',
			'widget_recent-comments',
			'widget_archives',
			'widget_meta',
			'sidebars_widgets',
			// Found in wp-admin/includes/schema.php but not with the above list
			'initial_db_version',
			'WPLANG',
			// Found in wp-admin/includes/class-wp-plugins-list-table.php
			'recently_activated',
			// Found in wp-admin/network/site-info.php
			'rewrite_rules',
			// Found in wp-admin/network.php
			'auth_key',
			'auth_salt',
			'logged_in_key',
			'logged_in_salt',
			'nonce_key',
			'nonce_salt',
			// Found in wp-includes/theme.php
			'theme_switched',
			// Found in wp-includes/class-wp-customize-manager.php
			'current_theme',
			// Found in wp-includes/cron.php
			'cron',
			// Unknown : To verify
			'user_roles',
			'widget_nav_menu',
		);
		
		// Before doing anything, we add some special options to the WP core options array
		// The 'user_roles' option is added in Multi-site as $prefix.'user_roles'. So for each site we should add this options in that format
		if(function_exists('is_multisite') && is_multisite()){
			global $wpdb;
			$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
			foreach($blogs_ids as $blog_id){
				array_push($aDBc_wp_core_options, $wpdb->get_blog_prefix($blog_id).'user_roles');
			}
		}

		// Run search in all files
		aDBc_run_search_for_items($aDBc_wp_core_options, $this->aDBc_message, "options");

	}

	/** WP: Get columns */
	function get_columns(){
		$aDBc_belongs_to_toolip = "<a class='aDBc-tooltips'>
									<img class='aDBc-margin-l-3' src='".  ADBC_PLUGIN_DIR_PATH . '/images/notice.png' . "'/>
									<span>" . __('Indicates the creator of the option: either a plugin, a theme or WordPress itself. If not sure about the creator, an estimation (%) will be displayed and it is up to you to decide what to do','advanced-db-cleaner') ." </span>
								  </a>";	
		$columns = array(
			'cb'          		=> '<input type="checkbox" />',
			'option_name' 		=> __('Option name','advanced-db-cleaner'),
			'option_value' 		=> __('Value','advanced-db-cleaner'),
			'option_autoload' 	=> __('Autoload','advanced-db-cleaner'),
			'site_id'   		=> __('Site id','advanced-db-cleaner'),
			'option_belongs_to' => __('Belongs to','advanced-db-cleaner') . $aDBc_belongs_to_toolip
		);
		return $columns;
	}

	/** WP: Prepare items to display */
	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$per_page = 50;
		$current_page = $this->get_pagenum();
		// Prepare sequence of options to display
		$display_data = array_slice($this->aDBc_options_to_display,(($current_page-1) * $per_page), $per_page);
		$this->set_pagination_args( array(
			'total_items' => count($this->aDBc_options_to_display),
			'per_page'    => $per_page
		));
		$this->items = $display_data;
	}

	/** WP: Get columns that should be hidden */
    function get_hidden_columns(){
		// If MU, nothing to hide, else hide Side ID column
		if(function_exists('is_multisite') && is_multisite()){
			return array();
		}else{
			return array('site_id');
		}
    }	

	/** WP: Column default */
	function column_default($item, $column_name){
		switch($column_name){
			case 'option_name':
			case 'option_value':
			case 'option_autoload':
			case 'site_id':
			case 'option_belongs_to':
			  return $item[$column_name];
			default:
			  return print_r($item, true) ; //Show the whole array for troubleshooting purposes
		}
	}

	/** WP: Column cb for check box */
	function column_cb($item) {
		return sprintf('<input type="checkbox" name="aDBc_options_to_delete[]" value="%s" />', $item['site_id']."|".$item['option_name']);
	}

	/** WP: Get bulk actions */
	function get_bulk_actions() {
		$actions = array(
			'delete'    => __('Delete','advanced-db-cleaner')
		);
		return $actions;
	}

	/** WP: Message to display when no items found */
	function no_items() {
		_e('No options found!','advanced-db-cleaner');
	}

	/** WP: Process bulk actions */
    public function process_bulk_action() {
        // security check!
        if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])){
            $nonce  = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];
            if (!wp_verify_nonce( $nonce, $action))
                wp_die('Security check failed!');
        }
        $action = $this->current_action();
		// Prepare an array containing numbers of options deleted
		$options_deleted = array();
        if($action == 'delete'){
			// If the user wants to clean the options he/she selected
			if(isset($_POST['aDBc_options_to_delete'])){
				if(function_exists('is_multisite') && is_multisite()){
					// Prepare options to delete in organized array to minimize switching from blogs
					$options_to_delete = array();
					foreach($_POST['aDBc_options_to_delete'] as $option){
						$option_info = explode("|", $option);
						if(empty($options_to_delete[$option_info[0]])){
							$options_to_delete[$option_info[0]] = array();
						}
						if(empty($options_deleted[$option_info[1]])){
							$options_deleted[$option_info[1]] = 0;
						}
						array_push($options_to_delete[$option_info[0]], $option_info[1]);
						$options_deleted[$option_info[1]]++;
					}
					// Delete options
					foreach($options_to_delete as $site_id => $options){
						switch_to_blog($site_id);
						foreach($options as $option) {
							delete_option($option);
						}
						restore_current_blog();
					}
				}else{
					foreach($_POST['aDBc_options_to_delete'] as $option) {
						$aDBc_option_info = explode("|", $option);
						delete_option($aDBc_option_info[1]);
						$options_deleted[$aDBc_option_info[1]] = 1;
					}
				}
				// Update the message to show to the user
				$this->aDBc_message = __('Selected options cleaned successfully!', 'advanced-db-cleaner');
			}
        }
		return $options_deleted;
    }

	/** Print the page content */
	function aDBc_print_page_content(){
		// Print a message if any
		if($this->aDBc_message != ""){
			echo '<div id="aDBc_message" class="' . $this->aDBc_class_message . ' notice is-dismissible"><p>' . $this->aDBc_message . '</p></div>';
		}
		?>
		<div class="aDBc-content-max-width">
			<form id="aDBc_form" action="" method="post">
				<?php
				$aDBc_new_URI = $_SERVER['REQUEST_URI'];
				// Remove the paged parameter to start always from the first page when selecting a new category of options
				$aDBc_new_URI = remove_query_arg('paged', $aDBc_new_URI);
				?>
				<!-- Print numbers of options found in each category -->
				<div class="aDBc-category-counts">
					<?php
					$iterations = 0;
					foreach($this->aDBc_options_categories_info as $abreviation => $category_info){
						$iterations++;
						$aDBc_new_URI = add_query_arg('aDBc_cat', $abreviation, $aDBc_new_URI);?>
						<span class="<?php echo $abreviation == $_GET['aDBc_cat'] ? 'aDBc-selected-category' : ''?>" style="<?php echo $abreviation == $_GET['aDBc_cat'] ? 'border-bottom: 1px solid ' . $category_info['color'] : '' ?> ">
							<a href="<?php echo $aDBc_new_URI; ?>" class="aDBc-category-counts-links" style="color:<?php echo $category_info['color']; ?>">
								<span class="aDBc-category-color" style="background: <?php echo $category_info['color']; ?>"></span>
								<span><?php echo $category_info['name']; ?> : </span>
								<span><?php echo $category_info['count'];?></span>
							</a>	
						</span>
						<?php
						if($iterations < 5){
							echo '<span class="aDBc-category-separator"></span>';
						}
					}?>
				</div>

				<div class="aDBc-clear-both"></div>

				<!-- Code for "run new search" button + Show loading image -->
				<div class="aDBc-margin-t-20">
					<?php 
					if($this->aDBc_which_button_to_show == "new_search" ){
						$aDBc_search_class = "aDBc-run-new-search";
						$aDBc_search_text  = __('Detect orphan options','advanced-db-cleaner');
					}else{
						$aDBc_search_class = "aDBc-continue-new-search";
						$aDBc_search_text  = __('Continue searching','advanced-db-cleaner');
					}
					?>
					<input id="aDBc_new_search_button" type="submit" class="button-primary <?php echo $aDBc_search_class; ?>" value="<?php echo $aDBc_search_text; ?>"  name="aDBc_new_search_button"/>

					<div id="aDBc-please-wait">
						<div class="aDBc-loading-gif"></div>
						<?php 
						//_e('Searching...Please wait! If your browser stops loading without refreshing, please refresh this page.','advanced-db-cleaner');
						_e('Please wait!','advanced-db-cleaner');
						?>
					</div>
				</div>

				<div class="aDBc-clear-both aDBc-margin-b-20"></div>

				<!-- Print a notice/warning according to each type of options -->
				<?php
				if($_GET['aDBc_cat'] == 'all' && $this->aDBc_options_categories_info['all']['count'] > 0){
					echo '<div class="aDBc-box-warning">' . __('Below the list of all your options. Please do not delete any option unless you really know what you are doing!','advanced-db-cleaner') . '</div>';
				}else if($_GET['aDBc_cat'] == 'o' && $this->aDBc_options_categories_info['o']['count'] > 0){
					echo '<div class="aDBc-box-info">' . __('The options below seem to be orphan. However, please delete only those you are sure to be orphan.','advanced-db-cleaner') . '</div>';
				}else if($_GET['aDBc_cat'] == 'p' && $this->aDBc_options_categories_info['p']['count'] > 0){
					echo '<div class="aDBc-box-warning">' . __('The options below belong to your plugins. DO NOT delete them unless you really know what you are doing!','advanced-db-cleaner') . '</div>';
				}else if($_GET['aDBc_cat'] == 't' && $this->aDBc_options_categories_info['t']['count'] > 0){
					echo '<div class="aDBc-box-warning">' . __('The options below belong to your themes. DO NOT delete them unless you really know what you are doing!','advanced-db-cleaner') . '</div>';
				}else if($_GET['aDBc_cat'] == 'w' && $this->aDBc_options_categories_info['w']['count'] > 0){
					echo '<div class="aDBc-box-warning">' . __('The options below belong to WordPress core. DO NOT delete them unless you really know what you are doing!','advanced-db-cleaner') . '</div>';
				}

				// Print the options
				$this->display();

				?>
			</form>
		</div>
		<div id="aDBc_dialog1" title="<?php _e('Cleaning...','advanced-db-cleaner'); ?>" class="aDBc-jquery-dialog">
			<p class="aDBc-box-warning">
				<?php _e('You are about to clean some of your options. This operation is irreversible. Don\'t forget to make a backup first.','advanced-db-cleaner'); ?>
			</p>
			<p>
				<?php _e('Are you sure to continue?','advanced-db-cleaner'); ?>
			</p>
		</div>
		<div id="aDBc_dialog2" title="<?php _e('Action required','advanced-db-cleaner'); ?>" class="aDBc-jquery-dialog">
			<p class="aDBc-box-info">
				<?php _e('Please select an action!','advanced-db-cleaner'); ?>
			</p>
		</div>
	<?php
	}
}

new ADBC_Options_List();

?>