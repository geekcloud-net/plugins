<?php
/**
 * WPLA_AccountsPage class
 * 
 */

class WPLA_AccountsPage extends WPLA_Page {

	const slug = 'accounts';

	public function onWpInit() {

		// Add custom screen options
		if ( ! isset($_GET['tab']) || $_GET['tab'] == 'accounts' ) {
			// $load_action = "load-".$this->main_admin_menu_slug."_page_wpla-".self::slug;
			$load_action = "load-".$this->main_admin_menu_slug."_page_wpla-".'settings';
			add_action( $load_action, array( &$this, 'addScreenOptions' ) );
		}

		if ( get_option( 'wpla_enable_accounts_page' ) ) {
			$load_action = "load-".$this->main_admin_menu_slug."_page_wpla-".'settings';
			add_action( $load_action.'-accounts', array( &$this, 'addScreenOptions' ) );
		}

	}

	
	public function handleActions() {
		if ( ! current_user_can('manage_amazon_listings') ) return;

		// save accounts
		if ( $this->requestAction() == 'wpla_add_account' ) {
		    check_admin_referer( 'wpla_add_account' );
			$this->newAccount();
		}

		// update account details from amazon
		if ( $this->requestAction() == 'wpla_update_account' ) {
		    check_admin_referer( 'wpla_update_account' );
			$this->updateAccount( $_REQUEST['amazon_account'] );
		}

		// delete account
		if ( $this->requestAction() == 'wpla_delete_account' ) {
		    check_admin_referer( 'wpla_delete_account' );
			$account = new WPLA_AmazonAccount( $_REQUEST['amazon_account'] );
			$account->delete();
			$this->showMessage( __('Account has been deleted.','wpla') );
		}

		// enable account
		if ( $this->requestAction() == 'wpla_enable_account' ) {
		    check_admin_referer( 'wpla_enable_account' );
			$account = new WPLA_AmazonAccount( $_REQUEST['amazon_account'] );
			$account->active = 1;
			$account->update();
			$this->showMessage( __('Account has been enabled.','wpla') );
		}

		// disable account
		if ( $this->requestAction() == 'wpla_disable_account' ) {
		    check_admin_referer( 'wpla_disable_account' );
			$account = new WPLA_AmazonAccount( $_REQUEST['amazon_account'] );
			$account->active = 0;
			$account->update();
			$this->showMessage( __('Account has been disabled.','wpla') );
		}

		// set default account
		if ( $this->requestAction() == 'wpla_make_default' ) {
		    check_admin_referer( 'wpla_make_default_account' );
			update_option( 'wpla_default_account_id', $_REQUEST['amazon_account'] );
		}

		// assign invalid data to default account
		if ( $this->requestAction() == 'wpla_assign_invalid_data_to_default_account' ) {
		    check_admin_referer( 'wpla_assign_invalid_data_to_default_account' );
			WPLA_Setup::fixItemsUsingInvalidAccounts();
		}

	} // handleActions()
	

	public function updateAccount( $id ) {

		$account = new WPLA_AmazonAccount( $id );
		if ( ! $account ) return;

		// update allowed markets
		$account->updateMarketplaceParticipations();

		$this->showMessage( __('Account details have been updated.','wpla') );
	}

	function addScreenOptions() {
		
		// render table options
		$option = 'per_page';
		$args = array(
	    	'label' => 'Accounts',
	        'default' => 20,
	        'option' => 'accounts_per_page'
	        );
		add_screen_option( $option, $args );
		$this->accountsTable = new WPLA_AccountsTable();
	
	    // add_thickbox();
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

	}
	

	public function displayAccountsPage() {

		// handle actions and show notes
		$this->handleActions();

		if ( $this->requestAction() == 'wpla_save_account' ) {
		    check_admin_referer( 'wpla_save_account' );
			$this->saveAccount();
		}
		if ( $this->requestAction() == 'edit_account' ) {
			return $this->displayEditAccountsPage();
		}

		if ( $default_account_id = get_option( 'wpla_default_account_id' ) ) {
			$default_account = WPLA_AmazonAccount::getAccount( $default_account_id );
			if ( ! $default_account ) {
				$this->showMessage( __('Your default account does not exist anymore. Please select a new default account.','wpla'),1);
			}
		}

		// check for data linked to deleted accounts
		WPLA_Setup::checkDbForInvalidAccounts();

		## BEGIN PRO ##
		// check if there are active accounts using the same MerchantID
		WPLA_Setup::checkForAccountsWithSameMerchantID();
		## END PRO ##

	    // create table and fetch items to show
	    $this->accountsTable = new WPLA_AccountsTable();
	    $this->accountsTable->prepare_items();

		$active_tab = 'accounts';
		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			'accountsTable'				=> $this->accountsTable,
			'amazon_markets'			=> WPLA_AmazonMarket::getAll(),
			'amazon_accounts'			=> WPLA_AmazonAccount::getAll( true ),
			'default_account'			=> get_option( 'wpla_default_account_id' ),

			'settings_url'				=> 'admin.php?page='.self::ParentMenuId.'-settings',
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-settings'.'&tab='.$active_tab
		);
		$this->display( 'settings_accounts', $aData );
	}


	public function displayEditAccountsPage() {

	    // get account
	    $account_id = $_REQUEST['amazon_account'];
	    $account = new WPLA_AmazonAccount( $account_id );
	    if ( ! $account ) die('wrong account');

	    $account->allowed_markets = maybe_unserialize( $account->allowed_markets );

		$active_tab = 'accounts';
		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			'account'					=> $account,
			'amazon_markets'			=> WPLA_AmazonMarket::getAll(),
			'default_account'			=> get_option( 'wpla_default_account_id' ),

			'settings_url'				=> 'admin.php?page='.self::ParentMenuId.'-settings',
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-settings'.'&tab='.$active_tab
		);
		$this->display( 'account_edit_page', $aData );
	}



	protected function saveAccount() {
		if ( ! current_user_can('manage_amazon_options') ) return;

		// TODO: check nonce
		if ( isset( $_POST['wpla_account_id'] ) ) {

			// create new account
			$account = new WPLA_AmazonAccount( $_POST['wpla_account_id'] );
			$account->title          = stripslashes( $_POST['wpla_title'] );
			$account->market_id      = trim( $_POST['wpla_market_id'] );
			// $account->market_code    = trim( $_POST['wpla_market_code'] );
			$account->merchant_id    = trim( $_POST['wpla_merchant_id'] );
			$account->marketplace_id = trim( $_POST['wpla_marketplace_id'] );
			$account->access_key_id  = trim( $_POST['wpla_access_key_id'] );
			// $account->secret_key     = trim( $_POST['wpla_secret_key'] );
			$account->active         = trim( $_POST['wpla_account_is_active'] );
			$account->is_reg_brand   = trim( $_POST['wpla_account_is_reg_brand'] );
			$account->update();

			// update allowed markets
			// $account->updateMarketplaceParticipations();
			
			$this->showMessage( __('Account was updated.','wpla') );
		}
	}


	protected function newAccount() {

		// make sure all required fields are populated
		if ( empty( $_POST['wpla_secret_key'] ) ) {
			$this->showMessage( __('No secret key was provided.','wpla'), 1 );
			return;
		}
		if ( empty( $_POST['wpla_access_key_id'] ) ) {
			$this->showMessage( __('No AWS Access Key ID was provided.','wpla'), 1 );
			return;
		}
		if ( empty( $_POST['wpla_merchant_id'] ) ) {
			$this->showMessage( __('No Merchant ID was provided.','wpla'), 1 );
			return;
		}
		if ( empty( $_POST['wpla_marketplace_id'] ) ) {
			$this->showMessage( __('No Marketplace ID was provided.','wpla'), 1 );
			return;
		}

		// TODO: check nonce
		if ( isset( $_POST['wpla_merchant_id'] ) ) {

			// create new account
			$account = new WPLA_AmazonAccount();
			$account->title          = stripslashes( $_POST['wpla_account_title'] );
			$account->market_id      = trim( $_POST['wpla_amazon_market_id'] );
			$account->market_code    = trim( $_POST['wpla_amazon_market_code'] );
			$account->merchant_id    = trim( $_POST['wpla_merchant_id'] );
			$account->marketplace_id = trim( $_POST['wpla_marketplace_id'] );
			$account->access_key_id  = trim( $_POST['wpla_access_key_id'] );
			$account->secret_key     = trim( $_POST['wpla_secret_key'] );
			$account->active         = 1;
			$account->add();

			// update allowed markets
			$account->updateMarketplaceParticipations();
			
			$this->showMessage( __('New account was added.','wpla') );
		}
	}


}
