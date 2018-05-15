<?php
/**
 * WPLA_ProfilesPage class
 * 
 */

class WPLA_ProfilesPage extends WPLA_Page {

	const slug = 'profiles';

	var $detail_fields = array(
		'price_add_amount',
		'price_add_percentage',
		'variations_mode',
	);

	public function onWpInit() {

		// Add custom screen options
		$load_action = "load-".$this->main_admin_menu_slug."_page_wpla-".self::slug;
		add_action( $load_action, array( &$this, 'addScreenOptions' ) );

		$this->handleSubmitOnInit();
	}

	public function onWpAdminMenu() {
		parent::onWpAdminMenu();

		add_submenu_page( self::ParentMenuId, $this->getSubmenuPageTitle( 'Profiles' ), __('Profiles','wpla'), 
						  self::ParentPermissions, $this->getSubmenuId( 'profiles' ), array( &$this, 'displayProfilesPage' ) );
	}

	function addScreenOptions() {
		
		// render table options
		$option = 'per_page';
		$args = array(
	    	'label' => 'Profiles',
	        'default' => 20,
	        'option' => 'profiles_per_page'
	        );
		add_screen_option( $option, $args );
		$this->profilesTable = new WPLA_ProfilesTable();
	
		// load styles and scripts for this page only
		add_action( 'admin_print_styles', array( &$this, 'onWpPrintStyles' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'onWpEnqueueScripts' ) );		

	    // add_thickbox();
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

	}
	
	public function handleSubmitOnInit() {
		if ( ! current_user_can('manage_amazon_listings') ) return;

		// handle save profile
		if ( $this->requestAction() == 'wpla_save_profile' ) {
		    check_admin_referer( 'wpla_save_profile' );

			$this->saveProfile();
			if ( @$_POST['return_to'] == 'listings' ) {
				$return_url = get_admin_url().'admin.php?page=wpla';
		        if ( isset($_REQUEST['listing_status']) )	$return_url = add_query_arg( 'listing_status', 	$_REQUEST['listing_status'], $return_url );
		        if ( isset($_REQUEST['profile_id']) )		$return_url = add_query_arg( 'profile_id', 		$_REQUEST['profile_id'], 	 $return_url );
		        if ( isset($_REQUEST['account_id']) )		$return_url = add_query_arg( 'account_id', 		$_REQUEST['account_id'], 	 $return_url );
		        if ( isset($_REQUEST['s']) )				$return_url = add_query_arg( 's', 				$_REQUEST['s'], 			 $return_url );
				wp_redirect( $return_url );
			}
		}

		// handle duplicate profile
		if ( $this->requestAction() == 'wpla_duplicate_profile' ) {
		    check_admin_referer( 'wpla_duplicate_profile' );
			$this->duplicateProfile();
		}
		// handle upload profile
		if ( $this->requestAction() == 'wpla_upload_listing_profile' ) {
		    check_admin_referer( 'wpla_upload_listing_profile' );
			$this->uploadProfile();
		}
		// handle download profile
		if ( isset( $_REQUEST['profile'] ) && ( $this->requestAction() == 'wpla_download_listing_profile' ) ) {
		    check_admin_referer( 'wpla_download_listing_profile' );
			$this->downloadProfile( $_REQUEST['profile'] );
		}

	}
	
	public function handleActions() {
		if ( ! current_user_can('manage_amazon_listings') ) return;
	
		// handle delete action
		if ( $this->requestAction() == 'wpla_delete_profile' ) {
		    check_admin_referer( 'bulk-profiles' );
			$this->deleteProfiles( $_REQUEST['amazon_profile'] );
		}

	}

	public function displayProfilesPage() {
		$this->check_wplister_setup();
	
		// handle actions and show notes
		$this->handleActions();

		// edit profile
		if ( ( $this->requestAction() == 'edit' ) || ( $this->requestAction() == 'add_new_profile' ) ) {
			return $this->displayEditPage();			
		} 

	    // create table and fetch items to show
	    $this->profilesTable->prepare_items();

		// process errors 		
		// if ($this->IC->message) $this->showMessage( $this->IC->message,1 );
		
		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			'profilesTable'				=> $this->profilesTable,
		
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-profiles'
		);
		$this->display( 'profiles_page', $aData );

	}


	public function displayEditPage() {
	
		// init model

		// get item
		if ( $this->requestAction() == 'add_new_profile' ) {
			$profile = new WPLA_AmazonProfile();
		} else {
			$profile = new WPLA_AmazonProfile( $_REQUEST['profile'] );
		}
		
		// $listingsModel = new ListingsModel();
		// $prepared_listings  = $listingsModel->getAllPreparedWithProfile( $item['profile_id'] );
		// $verified_listings  = $listingsModel->getAllVerifiedWithProfile( $item['profile_id'] );
		// $published_listings = $listingsModel->getAllPublishedWithProfile( $item['profile_id'] );
		// $ended_listings     = $listingsModel->getAllEndedWithProfile( $item['profile_id'] );

		$lm = new WPLA_ListingsModel();
		$listings  = $profile->profile_id ? $lm->findAllListingsByColumn( $profile->profile_id, 'profile_id' ) : array();

		$accounts  = WPLA_AmazonAccount::getAll();
		$templates = WPLA_AmazonFeedTemplate::getAll();

		// separate ListingLoader templates
		$category_templates = array();
		$liloader_templates = array();
		foreach ($templates as $tpl) {
			if ( $tpl->title == 'Offer' ) {
				$tpl->title = "Listing Loader";
				$liloader_templates[] = $tpl;
			} else {
				$category_templates[] = $tpl;
			}
		}

		$aData = array(
			'plugin_url'				=> self::$PLUGIN_URL,
			'message'					=> $this->message,

			'profile'                   => $profile,
			'accounts'                  => $accounts,
			// 'templates'                 => $templates,
			'category_templates'        => $category_templates,
			'liloader_templates'        => $liloader_templates,
			'profile_listings'          => $listings,
			'profile_details'           => maybe_unserialize( $profile->details ),

			// 'prepared_listings'         => $prepared_listings,
			// 'verified_listings'         => $verified_listings,
			// 'published_listings'        => $published_listings,
			// 'ended_listings'            => $ended_listings,
			
			'form_action'				=> 'admin.php?page='.self::ParentMenuId.'-profiles'
		);
		// $this->display( 'profiles_edit_page', array_merge( $aData, $profile ) );
		$this->display( 'profiles_edit_page', $aData );
		
	}

	private function saveProfile() {
		if ( ! current_user_can('manage_amazon_listings') ) return;

		// init profile
		$profile_id = $this->getValueFromPost( 'profile_id' );
		$profile = new WPLA_AmazonProfile( $profile_id );

		// fill in post data
		$post_data = $this->getPreprocessedPostData();
		$profile->fillFromArray( $post_data );

		// add field data
		$profile->fields = maybe_serialize( $this->getPreprocessedPostData( 'tpl_col_', true ) );

		// insert or update
		if ( $profile_id ) {
			$profile->update();
			$this->showMessage( __('Profile updated.','wpla') );
		} else {
			$profile->add();
			$this->showMessage( __('Profile added.','wpla') );
		}

		// error handling
		// if ($result===false) {
		// 	$this->showMessage( "There was a problem saving your profile.<br>SQL:<pre>".$wpdb->last_query.'</pre>'.$wpdb->last_error, true );	
		// } else {
		// }

		// prepare for updating items
		// $profile    = new WPLA_AmazonProfile( $profile_id );
		$listingsModel = new WPLA_ListingsModel();

		// re-apply profile to all published
		if ( ! $profile_id ) return;
		$items = $listingsModel->getWhere( 'profile_id', $profile_id );
        $listingsModel->applyProfileToListings( $profile, $items );
		$this->showMessage( sprintf( __('%s items updated.','wplister'), count($items) ) );			

	} // saveProfile()

	public function getPreprocessedPostData( $prefix = 'wpla_', $skip_empty = false ) {
		$data 	 = array();
		$details = array();
		// echo "<pre>";print_r($_POST);echo"</pre>";die();

		foreach ( $_POST as $key => $val ) {
			if ( empty($val) && !is_numeric($val) && $skip_empty ) continue;
			if ( substr( $key, 0, strlen($prefix) ) == $prefix ) {
				$field = substr( $key, strlen($prefix) );
				$val   = stripslashes( $val );
				
				if ( in_array($field, $this->detail_fields) ) {
					// store in details column
					$details[$field] = trim($val);
				} else {
					// store as sql column
					$data[$field] = $val;	
				}
			}
		}

		// serialize details column
		$data['details'] = serialize($details);

		return $data;
	}

	private function duplicateProfile() {
				
		// duplicate profile
		$new_profile_id = WPLA_AmazonProfile::duplicateProfile( $_REQUEST['profile'] );
		
		// redirect to edit new profile
		wp_redirect( get_admin_url().'admin.php?page=wpla-profiles&action=edit&profile='.$new_profile_id );

	}


	private function downloadProfile( $profile_id ) {

		// load profile
		$profile_id = intval( $profile_id );
		$data = WPLA_AmazonProfile::getProfile( $profile_id );
		$data = get_object_vars( $data ); // cast object into an array

		// preprocess data
		unset( $data['profile_id'] );			// profile id will be generated on upload
		$data['details'] = maybe_unserialize( $data['details'] );
		$data['fields']  = maybe_unserialize( $data['fields'] );
		$profile_name = str_replace( '_', ' ', sanitize_file_name( str_replace( ' ', '_', $data['profile_name'] ) ) );

    	// send as json
    	$filename = "WPLA profile $profile_id - $profile_name"; 
        header('Content-Disposition: attachment; filename='.$filename.'.json');
        echo json_encode( $data );
        exit;	
	}


    private function uploadProfile() {

        $uploaded_file = $this->process_upload();
        if ( ! $uploaded_file ) return;

        $result = $this->import_json( $uploaded_file );

        if ( $result ) {
            wpla_show_message( 'Profile "' . $result . '" was uploaded and restored successfully.');
        } else {
            wpla_show_message( 'The uploaded file could not be imported. Please make sure you use a JSON backup file exported from this plugin.','warn');                
        }

        // clean up
        if ( file_exists($uploaded_file) ) unlink($uploaded_file);
    }

    // process content of JSON file
    private function import_json( $uploaded_file ) {
        global $wpdb;

        $json = file_get_contents( $uploaded_file );
        $data = json_decode( $json, true );

        // prepare data
        $profile_name = $data['profile_name'];
        $data['profile_name'] .= ' (restored)';
        $data['details'] = maybe_serialize( $data['details'] );
        $data['fields']  = maybe_serialize( $data['fields'] );
		if ( ! $profile_name ) return false;

        // insert into db
		$result = $wpdb->insert( $wpdb->prefix.'amazon_profiles', $data );
		if ( ! $result ) return false;

		return $profile_name;
    }

    // process file upload
    private function process_upload() {

        if ( isset( $_FILES['wpla_file_upload_profile'] ) ) {

			// set target path
			$upload_dir  = wp_upload_dir(); // Array of key => value pairs
            $target_path = $upload_dir['basedir'].'/wpla-tmp-import-file.json';

            // delete last import
            if ( file_exists($target_path) ) unlink($target_path);

            if ( move_uploaded_file( $_FILES['wpla_file_upload_profile']['tmp_name'], $target_path ) ) {
                return $target_path;
            } else {
                echo "There was an error uploading the file, please try again!";
            }
            return false;
        }
        echo "no file_upload set";
        return false;
    }


	public function deleteProfiles( $profiles ) {
		if ( ! is_array($profiles) ) $profiles = array( $profiles );
		$count = 0;

		foreach ($profiles as $id) {
			if ( ! $id ) continue;
			
			// check if there are listings using this profile
			$lm = new WPLA_ListingsModel();
			$listings = $lm->findAllListingsByColumn( $id, 'profile_id' );
			if ( ! empty($listings) ) {
				$this->showMessage('This profile is applied to '.count($listings).' listings and can not be deleted.',1,1);
				continue;
			}

			$profile = new WPLA_AmazonProfile( $id );
			$profile->delete();
			$count++;
		}

		if ( $count )
			$this->showMessage( sprintf( __('%s profile(s) were removed.','wpla'), $count ) );
	}
	
	public function onWpPrintStyles() {

		// jqueryFileTree
		wp_register_style('jqueryFileTree_style', self::$PLUGIN_URL.'js/jqueryFileTree/jqueryFileTree.css' );
		wp_enqueue_style('jqueryFileTree_style'); 

	}

	public function onWpEnqueueScripts() {

		// jqueryFileTree
		wp_register_script( 'jqueryFileTree', self::$PLUGIN_URL.'js/jqueryFileTree/jqueryFileTree.js', array( 'jquery' ) );
		wp_enqueue_script( 'jqueryFileTree' );

	}	

}
