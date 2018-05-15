<?php
/**
 * WPLA_LogPage class
 * 
 */

class WPLA_LogPage extends WPLA_Page {

	const slug = 'log';

	public function onWpInit() {
		// parent::onWpInit();

		// Add custom screen options
		$load_action = "load-".$this->main_admin_menu_slug."_page_wpla-".self::slug;
		add_action( $load_action, array( &$this, 'addScreenOptions' ) );

	}

	public function onWpAdminMenu() {
		parent::onWpAdminMenu();

		if ( current_user_can('manage_amazon_options') && ( self::getOption( 'log_to_db' ) == '1' ) ) {
			add_submenu_page( self::ParentMenuId, $this->getSubmenuPageTitle( 'Logs' ), __('Logs','wpla'), 
							  self::ParentPermissions, $this->getSubmenuId( 'log' ), array( &$this, 'displayLogPage' ) );
		}
	}

	public function handleSubmit() {
		if ( ! current_user_can('manage_amazon_options') ) return;

		if ( $this->requestAction() == 'wpla_display_log_entry' ) {
		    check_admin_referer( 'wpla_display_log_entry' );
			$this->displayLogEntry( $_REQUEST['log_id'] );
			exit();
		}

		// handle delete action
		if ( $this->requestAction() == 'wpla_delete_logs' ) {
		    check_admin_referer( 'bulk-logs' );

			$log_ids = @$_REQUEST['log'];
			if ( is_array($log_ids)) {
				foreach ($log_ids as $id) {
					$this->deleteLogEntry( $id );
				}
				$this->showMessage( __('Selected items were removed.','wpla') );
			}
		}

		if ( $this->requestAction() == 'wpla_clear_amazon_log' ) {
		    check_admin_referer( 'wpla_clear_amazon_log' );

			$this->clearLog();
			$this->showMessage( __('Database log has been cleared.','wpla') );
		}
		if ( $this->requestAction() == 'wpla_optimize_amazon_log' ) {
		    check_admin_referer( 'wpla_optimize_amazon_log' );
			$count = $this->optimizeLog();
			$this->showMessage( $count . ' ' . __('expired records have been removed and the database table has been optimized.','wpla') );
		}

	}

	function addScreenOptions() {
		$option = 'per_page';
		$args = array(
	    	'label' => 'Log entries',
	        'default' => 20,
	        'option' => 'logs_per_page'
	        );
		add_screen_option( $option, $args );
		$this->logsTable = new WPLA_LogTable();

	    // add_thickbox();
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );
	}
	

	public function displayLogPage() {

		// get all items
		#$logs = $logModel->getAll();

	    //Create an instance of WPLA_LogTable
	    $logTable = new WPLA_LogTable();
	    $logTable->prepare_items();

		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			// 'logs'						=> $logs,
			'logTable'					=> $logTable,
			'tableSize'					=> $this->getTableSize(),
	
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-log'
		);
		$this->display( 'log_page', $aData );
	}


	public function displayLogEntry( $id ) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}amazon_log WHERE id = %d", $id ) );
		if ( ! $row ) die('invalid record id');

		// send log entry to support
		if ( @$_REQUEST['send_to_support'] == 'yes' ) {

			$this->sendRecordToSupport( $id, $row );

		} else {

			$this->display( 'log_details', array( 'row' => $row, 'version' => WPLA_VERSION ) );

		}
		exit();		
	}

	public function sendRecordToSupport( $id, $row ) {

		// check nonce
		if ( ! check_admin_referer( 'wpla_send_to_support' ) ) return;

		// get html content
		$content = $this->display( 'log_details', array( 'row' => $row, 'version' => WPLA_VERSION ), false );

		// build email
		$to          = 'support@wplab.com';
		$subject     = 'WP-Lister for Amazon log record #'.$id.' - '. str_replace( 'http://','', get_bloginfo('wpurl') );

		$user_name   = $_REQUEST['user_name'] ? $_REQUEST['user_name'] : 'unknown user';
		$user_email  = sanitize_email( $_REQUEST['user_email'] );
		$user_msg    = stripslashes( $_REQUEST['user_msg'] );
		$headers     = 'From: '.$user_name.' <'.$user_email.'>' . "\r\n";
		$attachments = array();

		$message  = '';
		$message .= 'Name:  '.$user_name.'<br>';
		$message .= 'Email: '.$user_email.'<br>';
		$message .= 'Message: <br><br>'.nl2br($user_msg).'<br>';
		$message .= '<hr>';
		$message .= $content;
		$message .= '<hr>';

		// send email as html
		add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));
		wp_mail($to, $subject, $message, $headers, $attachments);
		
		echo '<br><div style="text-align:center;font-family:sans-serif;">';
		echo 'Your log entry was sent to WP Lab support.';
		echo '<br><br>';
		echo 'Thank you for helping us improve WP-Lister for Amazon.</div>';

	}


	public function getTableSize() {
		global $wpdb;
		$dbname = $wpdb->dbname;
		$table  = $wpdb->prefix.'amazon_log';

		// check if MySQL server has gone away and reconnect if required - WP 3.9+
		if ( method_exists( $wpdb, 'check_connection') ) $wpdb->check_connection();

		$sql = "
			SELECT round(((data_length + index_length) / 1024 / 1024), 1) AS 'size' 
			FROM information_schema.TABLES 
			WHERE table_schema = '$dbname'
			  AND table_name = '$table' ";
		// echo "<pre>";print_r($sql);echo"</pre>";#die();

		$size = $wpdb->get_var($sql);
		if ( $wpdb->last_error ) echo 'Error in getTableSize(): '.$wpdb->last_error;

		return $size;
	}

	public function deleteLogEntry( $id ) {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix.'amazon_log',  array( 'id' => $id ) );
		if ( $wpdb->last_error ) echo 'Error in deleteLogEntry(): '.$wpdb->last_error;
	}

	public function clearLog() {
		global $wpdb;
		$table = $wpdb->prefix.'amazon_log';

		$wpdb->query("DELETE FROM $table");
		if ( $wpdb->last_error ) echo 'Error in clearLog(): '.$wpdb->last_error;

		$wpdb->query("OPTIMIZE TABLE $table");
		if ( $wpdb->last_error ) echo 'Error in clearLog(): '.$wpdb->last_error;
	}

	public function optimizeLog() {
		global $wpdb;
		$table = $wpdb->prefix.'amazon_log';

		$days_to_keep = self::getOption( 'log_days_limit', 30 );		
		$delete_count = $wpdb->get_var('SELECT count(id) FROM '.$wpdb->prefix.'amazon_log WHERE timestamp < DATE_SUB(NOW(), INTERVAL '.$days_to_keep.' DAY )');
		if ( $delete_count ) {
			$wpdb->query('DELETE FROM '.$wpdb->prefix.'amazon_log WHERE timestamp < DATE_SUB(NOW(), INTERVAL '.$days_to_keep.' DAY )');
			// $this->showMessage( 'Log entries removed: ' . $delete_count );
		}
		if ( $wpdb->last_error ) echo 'Error in optimizeLog(): '.$wpdb->last_error;

		$wpdb->query("OPTIMIZE TABLE $table");
		if ( $wpdb->last_error ) echo 'Error in optimizeLog(): '.$wpdb->last_error;

		return $delete_count;
	}



}
