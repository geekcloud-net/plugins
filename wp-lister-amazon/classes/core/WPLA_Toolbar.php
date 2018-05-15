<?php

class WPLA_Toolbar  {
	
	public function __construct() {

		// custom toolbar
		add_action( 'admin_bar_menu', array( &$this, 'customize_toolbar' ), 999 );

		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

	}


	// custom toolbar bar
	function customize_toolbar( $wp_admin_bar ) {

		// check if current user can manage listings
		if ( ! current_user_can('manage_amazon_listings') ) return;

		// get stats about active and scheduled jobs
		$feeds_in_progress   = get_option( 'wpla_feeds_in_progress', 0 );
		$reports_in_progress = get_option( 'wpla_reports_in_progress', 0 );
		$pending_feeds       = get_option( 'wpla_db_version' ) > 0 ? WPLA_AmazonFeed::getAllPendingFeeds() : array();
		$total_active_jobs   = $feeds_in_progress + $reports_in_progress + sizeof( $pending_feeds );

		if ( $total_active_jobs ) {
			add_action( 'admin_footer', array( &$this, 'print_admin_toolbar_styles' ) );
		}

		// top level 'Amazon'
		$extra_class = $total_active_jobs ? '-spinner' : '';
		$args = array(
			'id'    => 'wpla_top',
			'title' => __('Amazon', 'wpla'),
			'href'  => admin_url( 'admin.php?page=wpla' ),
			'meta'  => array('class' => 'wpla-toolbar-top'.$extra_class)
		);
		$wp_admin_bar->add_node($args);

		
		// Activity Monitor
		$activity_title = sprintf( __('%s active tasks', 'wpla'), $total_active_jobs);
		$activity_title = $total_active_jobs ? $activity_title : __('No active tasks', 'wpla');
		$args = array(
			'id'     => 'wpla_current_activity',
			'title'  => $activity_title,
			'href'   => '#',
			'parent' => 'wpla_top',
			'meta'   => array('class' => 'wpla-toolbar-page wpla-activity-monitor')
		);	
		$wp_admin_bar->add_node($args);

		// Activity: Reports
		$args = array(
			'id'     => 'wpla_current_reports',
			'title'  => __('Reports in progress', 'wpla') .': '. $reports_in_progress,
			'href'   => admin_url( 'admin.php?page=wpla-reports' ),
			'parent' => 'wpla_current_activity',
			'meta'   => array('class' => 'wpla-toolbar-page wpla-activity-monitor')
		);	
		if ( $reports_in_progress )
			$wp_admin_bar->add_node($args);

		// Activity: Feeds (submitted)
		$args = array(
			'id'     => 'wpla_current_feeds_submitted',
			'title'  => __('Feeds in progress', 'wpla') .': '. $feeds_in_progress,
			'href'   => admin_url( 'admin.php?page=wpla-feeds&feed_status=submitted' ),
			'parent' => 'wpla_current_activity',
			'meta'   => array('class' => 'wpla-toolbar-page wpla-activity-monitor')
		);	
		if ( $feeds_in_progress )
			$wp_admin_bar->add_node($args);

		// Activity: Feeds (pending)
		$args = array(
			'id'     => 'wpla_current_feeds_pending',
			'title'  => __('Scheduled feeds', 'wpla') .': '. sizeof( $pending_feeds ),
			'href'   => admin_url( 'admin.php?page=wpla-feeds&feed_status=pending' ),
			'parent' => 'wpla_current_activity',
			'meta'   => array('class' => 'wpla-toolbar-page wpla-activity-monitor')
		);	
		if ( ! empty($pending_feeds) )
			$wp_admin_bar->add_node($args);


		// Listings page	
		$args = array(
			'id'    => 'wpla_listings',
			'title' => __('Listings', 'wpla'),
			'href'  => admin_url( 'admin.php?page=wpla' ),
			'parent'  => 'wpla_top',
			'meta'  => array('class' => 'wpla-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		// Orders page
		$args = array(
			'id'    => 'wpla_orders',
			'title' => __('Orders', 'wpla'),
			'href'  => admin_url( 'admin.php?page=wpla-orders' ),
			'parent'  => 'wpla_top',
			'meta'  => array('class' => 'wpla-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		// Reports page
		$args = array(
			'id'    => 'wpla_reports',
			'title' => __('Reports', 'wpla'),
			'href'  => admin_url( 'admin.php?page=wpla-reports' ),
			'parent'  => 'wpla_top',
			'meta'  => array('class' => 'wpla-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		// Feeds page
		$args = array(
			'id'    => 'wpla_feeds',
			'title' => __('Feeds', 'wpla'),
			'href'  => admin_url( 'admin.php?page=wpla-feeds' ),
			'parent'  => 'wpla_top',
			'meta'  => array('class' => 'wpla-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		// Profiles page
		$args = array(
			'id'    => 'wpla_profiles',
			'title' => __('Profiles', 'wpla'),
			'href'  => admin_url( 'admin.php?page=wpla-profiles' ),
			'parent'  => 'wpla_top',
			'meta'  => array('class' => 'wpla-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		// Import page
		$args = array(
			'id'    => 'wpla_import',
			'title' => __('Import', 'wpla'),
			'href'  => admin_url( 'admin.php?page=wpla-import' ),
			'parent'  => 'wpla_top',
			'meta'  => array('class' => 'wpla-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		// Tools page
		$args = array(
			'id'    => 'wpla_tools',
			'title' => __('Tools', 'wpla'),
			'href'  => admin_url( 'admin.php?page=wpla-tools' ),
			'parent'  => 'wpla_top',
			'meta'  => array('class' => 'wpla-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		// Repricing Tool
		$args = array(
			'id'    => 'wpla_tools_repricing',
			'title' => __('Repricing Tool', 'wpla'),
			'href'  => admin_url( 'admin.php?page=wpla-tools&tab=repricing' ),
			'parent'  => 'wpla_tools',
			'meta'  => array('class' => 'wpla-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		// Inventory Check
		$args = array(
			'id'    => 'wpla_tools_inventory',
			'title' => __('Inventory Check', 'wpla'),
			'href'  => admin_url( 'admin.php?page=wpla-tools&tab=inventory' ),
			'parent'  => 'wpla_tools',
			'meta'  => array('class' => 'wpla-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		// SKU Generator
		$args = array(
			'id'    => 'wpla_tools_skugen',
			'title' => __('SKU Generator', 'wpla'),
			'href'  => admin_url( 'admin.php?page=wpla-tools&tab=skugen' ),
			'parent'  => 'wpla_tools',
			'meta'  => array('class' => 'wpla-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		// Stock Log
		$args = array(
			'id'    => 'wpla_tools_stock_log',
			'title' => __('Stock Log', 'wpla'),
			'href'  => admin_url( 'admin.php?page=wpla-tools&tab=stock_log' ),
			'parent'  => 'wpla_tools',
			'meta'  => array('class' => 'wpla-toolbar-page')
		);
		$wp_admin_bar->add_node($args);

		// Developer Tools
		$args = array(
			'id'    => 'wpla_tools_developer',
			'title' => __('Developer', 'wpla'),
			'href'  => admin_url( 'admin.php?page=wpla-tools&tab=developer' ),
			'parent'  => 'wpla_tools',
			'meta'  => array('class' => 'wpla-toolbar-page')
		);
		$wp_admin_bar->add_node($args);


		if ( current_user_can('manage_amazon_options') ) {

			// Settings page
			$args = array(
				'id'    => 'wpla_settings',
				'title' => __('Settings', 'wpla'),
				'href'  => admin_url( 'admin.php?page=wpla-settings' ),
				'parent'  => 'wpla_top',
				'meta'  => array('class' => 'wpla-toolbar-page')
			);
			$wp_admin_bar->add_node($args);

			// Settings - General tab
			$args = array(
				'id'    => 'wpla_settings_general',
				'title' => __('General Settings', 'wpla'),
				'href'  => admin_url( 'admin.php?page=wpla-settings&tab=settings' ),
				'parent'  => 'wpla_settings',
				'meta'  => array('class' => 'wpla-toolbar-page')
			);
			$wp_admin_bar->add_node($args);

			// Settings - Accounts tab
			$args = array(
				'id'    => 'wpla_settings_accounts',
				'title' => __('Accounts', 'wpla'),
				'href'  => admin_url( 'admin.php?page=wpla-settings&tab=accounts' ),
				'parent'  => 'wpla_settings',
				'meta'  => array('class' => 'wpla-toolbar-page')
			);
			$wp_admin_bar->add_node($args);

			// Settings - Categories tab
			$args = array(
				'id'    => 'wpla_settings_categories',
				'title' => __('Categories', 'wpla'),
				'href'  => admin_url( 'admin.php?page=wpla-settings&tab=categories' ),
				'parent'  => 'wpla_settings',
				'meta'  => array('class' => 'wpla-toolbar-page')
			);
			$wp_admin_bar->add_node($args);

			// Settings - Advanced tab
			$args = array(
				'id'    => 'wpla_settings_advanced',
				'title' => __('Advanced', 'wpla'),
				'href'  => admin_url( 'admin.php?page=wpla-settings&tab=advanced' ),
				'parent'  => 'wpla_settings',
				'meta'  => array('class' => 'wpla-toolbar-page')
			);
			$wp_admin_bar->add_node($args);

			// Settings - Developer tab
			$args = array(
				'id'    => 'wpla_settings_developer',
				'title' => __('Developer', 'wpla'),
				'href'  => admin_url( 'admin.php?page=wpla-settings&tab=developer' ),
				'parent'  => 'wpla_settings',
				'meta'  => array('class' => 'wpla-toolbar-page')
			);
			$wp_admin_bar->add_node($args);

			## BEGIN PRO ##
			// Settings - Updates tab
			$args = array(
				'id'    => 'wpla_settings_license',
				'title' => __('Updates', 'wpla'),
				'href'  => admin_url( 'admin.php?page=wpla-settings&tab=license' ),
				'parent'  => 'wpla_settings',
				'meta'  => array('class' => 'wpla-toolbar-page')
			);
			$wp_admin_bar->add_node($args);
			## END PRO ##

		} // if current_user_can('manage_amazon_options')


		if ( current_user_can('manage_amazon_options') && ( get_option( 'wpla_log_to_db' ) == '1' ) ) {
		
			// Logs page
			$args = array(
				'id'    => 'wpla_log',
				'title' => __('Logs', 'wpla'),
				'href'  => admin_url( 'admin.php?page=wpla-log' ),
				'parent'  => 'wpla_top',
				'meta'  => array('class' => 'wpla-toolbar-page')
			);
			$wp_admin_bar->add_node($args);

		}





		// product page
		global $post;
		global $wp_query;
		global $pagenow;
		$post_id = false;

		if ( $wp_query->in_the_loop && isset( $wp_query->post->post_type ) && ( $wp_query->post->post_type == 'product' ) ) {
			$post_id = $wp_query->post->ID;
		} elseif ( is_object( $post ) && isset( $post->post_type ) && ( $post->post_type == 'product' ) ) {
			$post_id = $post->ID;
		}

		// skip product links on the main products page
		if ( $pagenow == 'edit.php' ) return;

		// do we have a single product page?
		if ( empty($post_id) ) return;


		// get all items
		$lm = new WPLA_ListingsModel();
		$listings = $lm->getAllItemsByPostOrParentID( $post_id );

		if ( sizeof($listings) > 0 ) {

			$asin = $lm->getASINFromPostID( $post_id );
			// $url = $lm->getViewItemURLFromPostID( $post_id );

			// View on Amazon link
			$args = array(
				'id'    => 'wpla_view_on_amazon',
				'title' => __('View item on Amazon', 'wpla'), # ." ($asin)",
				// 'href'  => $url,
				'parent'  => 'wpla_top',
				'meta'  => array('target' => '_blank', 'class' => 'wpla-toolbar-link')
			);
			if ( $asin ) $wp_admin_bar->add_node($args);

			foreach ($listings as $listing) {

		        $listing_url = 'http://www.amazon.com/dp/'.$listing->asin.'/';
		        if ( $listing->account_id ) {
		            $account = new WPLA_AmazonAccount( $listing->account_id );
		            $market  = new WPLA_AmazonMarket( $account->market_id );
		            $listing_url = 'http://www.'.$market->url.'/dp/'.$listing->asin.'/';
		        }

				$args = array(
					'id'    => 'wpla_view_on_amazon_'.$listing->id,
					'title' => '#'.$listing->asin . ': ' . $listing->listing_title,
					'href'  => $listing_url,
					'parent'  => 'wpla_view_on_amazon',
					'meta'  => array('target' => '_blank', 'class' => 'wpla-toolbar-link')
				);
				if ( $listing_url ) $wp_admin_bar->add_node($args);

			}

			// View in WP-Lister
			$url = admin_url( 'admin.php?page=wpla&s='.$post_id );
			$args = array(
				'id'    => 'wpla_view_on_listings_page',
				'title' => __('View item in WP-Lister', 'wpla'),
				'href'  => $url,
				'parent'  => 'wpla_top',
				'meta'  => array('target' => '_blank', 'class' => 'wpla-toolbar-link')
			);
			$wp_admin_bar->add_node($args);

		} else { // no listings

			// match product option
			$tb_url = admin_url( 'admin-ajax.php?action=wpla_show_product_matches&id='.$post_id.'&width=640&height=420' );
			// echo '<a href="'.$tb_url.'" class="thickbox" title="Match product on Amazon"><img src="'.WPLA_URL.'/img/search3.png" alt="match" /></a>';
			$onclick = 'tb_show("'.__('Match on Amazon','wpla').'", "'.$tb_url.'");return false;';

			$args = array(
				'id'    => 'wpla_match_on_amazon',
				'title' => __('Match on Amazon','wpla'),
				'href'  => $tb_url,
				'parent'=> 'wpla_top',					
				'meta'  => array('onclick' => $onclick, 'class' => 'wpla-toolbar-link')
			);
			$wp_admin_bar->add_node($args);

			// $args = $this->addPrepareActions( $args );
		}

		// if ( current_user_can('prepare_amazon_listings') )
		$this->addPrepareActions( $wp_admin_bar, $post_id );



	} // customize_toolbar()

    public function load_scripts() {
        // enqueue ProductMatcher.js
        if ( current_user_can( 'manage_amazon_listings' ) ) {
            wp_register_script( 'wpla_product_matcher', WPLA_URL.'js/classes/ProductMatcher.js?ver='.time(), array( 'jquery' ) );
            wp_enqueue_script( 'wpla_product_matcher' );

            wp_localize_script('wpla_product_matcher', 'wpla_ProductMatcher_i18n', array(
                    'WPLA_URL' 	=> WPLA_URL
                )
            );
        }
    }


	function addPrepareActions( $wp_admin_bar, $post_id ) {

		// Prepare listing link
		$url = '';
		$args = array(
			'id'    => 'wpla_prepare_listing',
			'title' => __('Prepare listing', 'wpla'),
			'href'  => $url,
			'parent'  => 'wpla_top'
		);
		$wp_admin_bar->add_node( $args );

		$pm = new WPLA_AmazonProfile();
		$profiles = $pm->getAll();

		foreach ($profiles as $profile) {

			// echo "<pre>";print_r($profile);echo"</pre>";#die();
			$profile_id = $profile->profile_id;
			$url = admin_url( 'admin.php?page=wpla&action=wpla_prepare_single_listing&product_id='.$post_id.'&profile_id='.$profile_id .'&_wpnonce='. wp_create_nonce( 'wpla_prepare_single_listing' ) );
			$args = array(
				'id'    => 'wpla_list_on_amazon_'.$profile->profile_id,
				'title' => $profile->profile_name,
				'href'  => $url,
				'parent'  => 'wpla_prepare_listing'
			);
			$wp_admin_bar->add_node($args);

		}

		return $args;
	} // addPrepareActions()
	
	public function print_admin_toolbar_styles() {
		// 	wp_register_style( 'wpla_style', self::$PLUGIN_URL.'css/style.css' );
		// 	wp_enqueue_style( 'wpla_style' );
		?>
		<style>
			#wpadminbar .ab-top-menu > li.wpla-toolbar-top-spinner > a.ab-item,
			#wpadminbar .ab-top-menu > li.wpla-toolbar-top-spinner:hover > a.ab-item {
				padding-left: 30px;
				background-position: 5px center;
				background-repeat: no-repeat;
				background-image: url(<?php echo WPLA_URL ?>/img/spinner-16px.gif);
			}
			#wpadminbar .ab-top-menu > li.wpla-toolbar-top-spinner #wp-admin-bar-wpla_current_activity {
				background-color: #444;
			}
		</style>
		<?php
	}

} // class WPLA_Toolbar

// instantiate object
// $oWPLA_Toolbar = new WPLA_Toolbar();

