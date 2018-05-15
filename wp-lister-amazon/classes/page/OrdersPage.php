<?php
/**
 * WPLA_OrdersPage class
 * 
 */

class WPLA_OrdersPage extends WPLA_Page {

	const slug = 'orders';

	public function onWpInit() {

		// Add custom screen options
		$load_action = "load-".$this->main_admin_menu_slug."_page_wpla-".self::slug;
		add_action( $load_action, array( &$this, 'addScreenOptions' ) );

		$this->handleSubmitOnInit();
	}

	public function onWpAdminMenu() {
		parent::onWpAdminMenu();

		add_submenu_page( self::ParentMenuId, $this->getSubmenuPageTitle( 'Orders' ), __('Orders','wpla'), 
						  self::ParentPermissions, $this->getSubmenuId( 'orders' ), array( &$this, 'displayOrdersPage' ) );
	}

	function addScreenOptions() {
		
		// render table options
		$option = 'per_page';
		$args = array(
	    	'label' => 'Orders',
	        'default' => 20,
	        'option' => 'orders_per_page'
	        );
		add_screen_option( $option, $args );
		$this->ordersTable = new WPLA_OrdersTable();
	
	    // add_thickbox();
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

	}
	

	public function handleSubmitOnInit() {
		if ( ! current_user_can('manage_amazon_listings') ) return;

		// handle preview action
		if ( $this->requestAction() == 'view_amazon_order_details' ) {
		    check_admin_referer( 'wpla_view_order_details' );
			$this->showOrderDetails( $_REQUEST['amazon_order'] );
			exit();
		}

	}

	public function handleActions() {
		if ( ! current_user_can('manage_amazon_listings') ) return;
	
		// trigger orders update
		if ( $this->requestAction() == 'update_amazon_orders' ) {
		    check_admin_referer( 'wpla_update_orders' );
			do_action( 'wpla_update_orders' );
		}

		// load order items
		if ( $this->requestAction() == 'load_order_items' ) {
		    check_admin_referer( 'wpla_load_order_items' );

			$lm = new WPLA_OrdersModel();
			$order = $lm->getItem( $_REQUEST['amazon_order'] );
			if ( ! $order ) return;

			$account = WPLA_AmazonAccount::getAccount( $order['account_id'] );
			if ( ! $account ) return;

			$api = new WPLA_AmazonAPI( $account->id );

			// get report requests
			$items = $api->getOrderLineItems( $order['order_id'] );
			// echo "<pre>";print_r($items);echo"</pre>";die();

			if ( is_array( $items ) )  {

				// run the import
				$this->importOrderItems( $items, $order['order_id'] );

				$this->showMessage( sprintf( __('%s item(s) were processed for account %s.','wpla'), sizeof($items), $account->title ) );

			} elseif ( $items->Error->Message ) {
				$this->showMessage( sprintf( __('There was a problem downloading items for account %s.','wpla'), $account->title ) .'<br>Error: '. $items->Error->Message, 1 );
			} else {
				$this->showMessage( sprintf( __('There was a problem downloading items for account %s.','wpla'), $account->title ), 1 );
			}

		}

		// handle update from Amazon action
		if ( $this->requestAction() == 'wpla_update_orders' ) {
		    check_admin_referer( 'bulk-orders' );
			$this->updateOrdersfromAmazon( $_REQUEST['amazon_order'] );
			// $this->showMessage( __('Not implemented yet.','wpla') );
		}

		## BEGIN PRO ##
		// create WooCommerce order
		if ( $this->requestAction() == 'wpla_create_order' ) {
		    check_admin_referer( 'wpla_create_order' );
			$this->createOrder( $_REQUEST['amazon_order'] );
			// $this->showMessage( __('Order(s) created.','wpla') );
		}

        // bulk create WooCommerce order
        if ( $this->requestAction() == 'wpla_create_orders' ) {
		    check_admin_referer( 'bulk-orders' );
            $this->bulkCreateOrders( $_REQUEST['amazon_order'] );
            // $this->showMessage( __('Order(s) created.','wpla') );
        }
		## END PRO ##

		// handle delete action
		if ( $this->requestAction() == 'wpla_delete_orders' ) {
		    check_admin_referer( 'bulk-orders' );
			$this->deleteOrders( $_REQUEST['amazon_order'] );
			$this->showMessage( __('Selected items were removed.','wpla') );
		}

	}
	

	public function updateOrdersfromAmazon( $orders ) {
		$there_were_errors = false;
		
		$om = new WPLA_OrdersModel();
		foreach ($orders as $id) {
			$success = $om->updateFromAmazon( $id );
			if ( $success == 'RequestThrottled' ) {
				$this->showMessage( sprintf( __('Order %s could not be updated because you are sending too many requests per minute to Amazon.<br>Please wait a minute and then try to update a smaller number of orders at the same time.','wpla'), $om->lastOrderID ), 1, 1 );
				$there_were_errors = true;
			}
		}

		if ( $there_were_errors ) {
			$this->showMessage( __('Some orders could not be updated from Amazon.','wpla'), 2 );			
		} else {
			$this->showMessage( __('Selected orders were updated from Amazon.','wpla') );
		}

	}

	public function deleteOrders( $orders ) {
		
		$om = new WPLA_OrdersModel();
		foreach ($orders as $id) {
			$om->deleteItem( $id );
		}

	}

	## BEGIN PRO ##
    public function bulkCreateOrders( $orders ) {
	    if ( !is_array( $orders ) ) {
	        $orders = array( $orders );
        }

        $created = 0;
        foreach ( $orders as $order ) {
	        if ( $this->createOrder( $order, true ) ) {
	            $created++;
            }
        }

        $msg  = _n( '%d WooCommerce order was created from Amazon order', '%d WooCommerce orders were created from Amazon orders', $created, 'wpla' );
        $this->showMessage( sprintf( $msg, $created ) );
    }

	public function createOrder( $order, $quiet = false ) {

		$wob = new WPLA_OrderBuilder();

		$wp_order_id = $wob->createWooOrderFromAmazonOrder( $order );

		if ( $wp_order_id ) {
		    // if the order ID is not getting returned, display the message in the Orders page
		    if ( ! $quiet ) {
                $msg  = __('WooCommerce order was created from Amazon order.','wpla');
                $msg .= '<br><a href="post.php?action=edit&post=' . $wp_order_id .'" class="button button-small" target="_blank">'. __('View order','wpla').'</a>';
                $msg .= '<!br> <span style="color:silver">Order ID: ' . $wp_order_id .'</span>';
                $this->showMessage( $msg );
            }

			// load updated order record from wp_amazon_orders 
			$ordersModel = new WPLA_OrdersModel();
			$wpla_order  = $ordersModel->getItem( $order );

			// add history record
			$history_message = "Order #$wp_order_id was created manually";
			$history_details = array( 'post_id' => $wp_order_id, 'status' => $wpla_order['status'], 'user_id' => get_current_user_id() );
			WPLA_OrdersImporter::addHistory( $wpla_order['order_id'], 'create_order', $history_message, $history_details );
		}

	    return $wp_order_id;

		// $msg  = 'Imported: '.$wob->imported_count.'<br>';
		// $msg .= 'Updated: '.$wob->updated_count.'<br>';
		// $this->showMessage( $msg );
	}
	## END PRO ##
	
	public function importOrderItems( $items, $order_id ) {
	
		$importer = new WPLA_OrdersImporter();
		$success  = $importer->importOrderItems( $items, $order_id );

	}
	

	public function displayOrdersPage() {
		$this->check_wplister_setup();
	
		// handle actions and show notes
		$this->handleActions();

	    // create table and fetch items to show
	    // $this->ordersTable = new WPLA_OrdersTable();
	    $this->ordersTable->prepare_items();

		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			'ordersTable'				=> $this->ordersTable,
		
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-orders'
		);
		$this->display( 'orders_page', $aData );

	}

	public function showOrderDetails( $id ) {
	
		// init model
		$ordersModel = new WPLA_OrdersModel();		

		// get amazon_order record
		$amazon_order = $ordersModel->getItem( $id );
		
		// get WooCommerce order
		$wc_order_notes = $amazon_order['post_id'] ? $this->get_order_notes( $amazon_order['post_id'] ) : false;

		$aData = array(
			'amazon_order'				=> $amazon_order,
			'wc_order_notes'			=> $wc_order_notes,
		);
		$this->display( 'order_details', $aData );
		
	}

	public function get_order_notes( $id ) {

		$notes = array();

		$args = array(
			'post_id' => $id,
			'approve' => 'approve',
			'type' => ''
		);

		remove_filter('comments_clauses', 'woocommerce_exclude_order_comments');

		// fix blank details page if WooCommerce Product Reviews Pro plugin is active (Call to undefined function get_current_screen())
		// since we only render the details page and then exit(), it's safe to remove all problematic filters
		remove_all_filters('comments_clauses');
		remove_all_filters('parse_comment_query');

		$comments = get_comments( $args );

		foreach ($comments as $comment) :
			// $is_customer_note = get_comment_meta($comment->comment_ID, 'is_customer_note', true);
			// $comment->comment_content = make_clickable($comment->comment_content);
			$notes[] = $comment;
		endforeach;

		add_filter('comments_clauses', 'woocommerce_exclude_order_comments');

		return (array) $notes;

	}

}
