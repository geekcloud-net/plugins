<?php
/**
 * WPLA_API_Hooks
 *
 * implements public action hooks for 3rd party developers
 *
 * TODO: document other filter hooks intended for 3rd party developers...
 */

class WPLA_API_Hooks {

	var $dblogger;
	
	public function __construct() {

		// re-apply profile and mark listing item as changed
		add_action( 'wpla_product_has_changed', array( &$this, 'wpla_product_has_changed' ), 10, 2 );

		// create new prepared listing from product and profile
		add_action( 'wpla_prepare_listing', array( &$this, 'wpla_prepare_listing' ), 10, 2 );

		// process inventory changes from WP-Lister for eBay
		add_action('wplister_inventory_status_changed', array( &$this, 'wplister_inventory_status_changed'), 10, 1 );

		// process product updates triggered via the WooCommerce REST API
		add_action( 'woocommerce_api_edit_product', 			array( &$this, 'wpla_product_has_changed' ), 20, 1 ); 			// WC REST API					PUT /wc-api/v2/products/1234 

		// handle ajax requests from third party CSV import plugins
		add_action( 'wp_ajax_woo-product-importer-ajax',      	array( &$this, 'handle_third_party_ajax_csv_import' ), 1, 1 );	// Woo Product Importer 		https://github.com/dgrundel/woo-product-importer
		add_action( 'wp_ajax_woocommerce_csv_import_request', 	array( &$this, 'handle_third_party_ajax_csv_import' ), 1, 1 );	// Product CSV Import Suite 	http://www.woothemes.com/products/product-csv-import-suite/
		add_action( 'wp_ajax_runImport',      					array( &$this, 'handle_third_party_ajax_csv_import' ), 1, 1 );	// WooCommerce CSV importer 2.x	http://wordpress.org/plugins/woocommerce-csvimport/
		add_action( 'wp_ajax_run_import',      					array( &$this, 'handle_third_party_ajax_csv_import' ), 1, 1 );	// WooCommerce CSV importer 3.x	http://wordpress.org/plugins/woocommerce-csvimport/
		// add_action( 'load-all-import_page_pmxi-admin-import', array( &$this, 'handle_third_party_ajax_csv_import' ), 1, 1 );	// WP All Import				
		add_action( 'pmxi_saved_post', 							array( &$this, 'wpla_product_has_changed'           ),20, 1 );  // http://www.wpallimport.com/documentation/advanced/action-reference/

		// trigger 3rd party import mode if called from custom cron implementation
		// example: /wp-content/plugins/wwc-amz-aff/do-cron.php for WooCommerce Amazon Affiliates plugin
		// deactivated as it seems to cause problems with wwc-amz-aff
		// if ( 'do-cron.php' == basename( $_SERVER['SCRIPT_NAME'] ) )
		// 	$this->handle_third_party_ajax_csv_import();

	}
	
	
	// re-apply profile and mark listing item as changed
	function wpla_product_has_changed( $post_id, $skip_updating_feeds = false ) {

		$lm = new WPLA_ListingsModel();
		$lm->markItemAsModified( $post_id, $skip_updating_feeds );

	}

	// create new prepared listing(s) from product(s) and apply profile
	function wpla_prepare_listing( $product_ids, $profile_id ) {

		// accept both single post_id and array of post_ids
		if ( ! is_array( $product_ids ) )
			$product_ids = array( $product_ids );

		// prepare new listing(s) from product(s)
		$lm = new WPLA_ListingsModel();
		$response = $lm->prepareListings( $product_ids, $profile_id );

	} // wpla_prepare_listing()


	// process inventory changes from WP-Lister for eBay
	public function wplister_inventory_status_changed( $post_id ) {
        $this->dblogger = new WPLA_AmazonLogger();

        // log to db - before request
        $this->dblogger->updateLog( array(
            'callname'    => 'wplister_inventory_status_changed',
            'request'     => 'internal action hook',
            'parameters'  => maybe_serialize( $post_id ),
            'request_url' => '',
            'account_id'  => '',
            'market_id'   => '',
            'success'     => 'pending'
        ));
		
		// mark as modified
		$listingsModel = new WPLA_ListingsModel();
		$result = $listingsModel->markItemAsModified( $post_id );
		WPLA()->logger->info('marked item as modified: ' . $post_id . '');

		// create and submit pending feeds immediately - to minimize delay when syncing sales
		// (disabled because WPLA cron job runs immediately after WPLE cron job now)
		// do_action('wpla_submit_pending_feeds');

        // log to db 
        $this->dblogger->updateLog( array(
            'result'    => json_encode( $result ),
            'success'   => 'Success'
        ));

	} // wplister_inventory_status_changed()
	





	/**
	 *  support for Woo Product Importer plugin
	 *  https://github.com/dgrundel/woo-product-importer
	 *  
	 *  support for WooCommerce Product CSV Import Suite
	 *  http://www.woothemes.com/products/product-csv-import-suite/
	 *
	 *  Third party CSV import plugins usually call wp_update_post() before update_post_meta() so WP will trigger the save_post action before price and stock have been updated.
	 *  We need to disable the original save_post hook and collect post IDs to mark them as modified at shutdown (including further processing for locked items)
	 *  TODO: this can be simplified for WPLA as Amazon will be updated asynchronously - it should be okay to mark an item as changed right before its product is being updated.
	 */

	function handle_third_party_ajax_csv_import() {
		WPLA()->logger->info("CSV import mode ENABLED");

		// disable default action for save_post
		remove_action( 'save_post', array( WPLA()->woo_backend, 'wpla_on_woocommerce_product_quick_edit_save' ), 20, 2 );
		remove_action( 'save_post', array( WPLA()->woo_backend, 'wpla_on_woocommerce_product_bulk_edit_save'  ), 20, 2 );

		// add new save_post action to collect changed post IDs
		add_action( 'save_post', array( &$this, 'collect_updated_products' ), 10, 2 );

		// collect changed post IDs from WooCommerce Product CSV Import Suite (PCSVIS)
		add_action( 'import_end', array( &$this, 'collect_updated_products_from_pcsvis' ) );

		// add shutdown handler
		register_shutdown_function( array( &$this, 'update_products_on_shutdown' ) );

	}

	// collect changed product IDs
	function collect_updated_products_from_pcsvis() {
		WPLA()->logger->info("CSV: collect_updated_products_from_pcsvis()");

		if ( ! isset( $GLOBALS['WC_CSV_Product_Import']                  ) ) return;
		if ( ! isset( $GLOBALS['WC_CSV_Product_Import']->processed_posts ) ) return;
		if (   isset( $_GET['step'] ) && $_GET['step'] == 4                ) return; // step 4 is cleaning up after the actual import
		WPLA()->logger->info("CSV: processed_posts: ".print_r($GLOBALS['WC_CSV_Product_Import']->processed_posts,1));

		// get queue
		$collected_products = get_option( 'wpla_updated_products_queue', array() );
		if ( ! is_array( $collected_products ) ) $collected_products = array();

		// add processed posts to queue - and remove duplicates
		$collected_products = array_unique( array_merge( $collected_products, $GLOBALS['WC_CSV_Product_Import']->processed_posts ) );

		// update queue
		update_option( 'wpla_updated_products_queue', $collected_products );		
	}

	// collect changed product IDs
	function collect_updated_products( $post_id, $post ) {
		WPLA()->logger->info("CSV: collect_updated_products( $post_id )");

		if ( !$_POST ) return $post_id;
		// if ( is_int( wp_is_post_revision( $post_id ) ) ) return;
		// if( is_int( wp_is_post_autosave( $post_id ) ) ) return;
		// if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
		if ( ! current_user_can( 'edit_post', $post_id )) return $post_id;
		if ( ! in_array( $post->post_type, array( 'product', 'product_variation' ) ) ) return $post_id;

		// if this is a single variation use parent_id 
		// if ( $parent_id = WPLA_ProductWrapper::getVariationParent( $post_id ) ) {
		if ( $post->post_type == 'product_variation' ) {
			$parent_id = WPLA_ProductWrapper::getVariationParent( $post_id );
			// WPLA()->logger->info("single variation found - use parent $parent_id for $post_id");
			$post_id = $parent_id;
		}

		// get queue
		$collected_products = get_option( 'wpla_updated_products_queue', array() );
		if ( ! is_array( $collected_products ) ) $collected_products = array();

		// add product_id to queue - if it doesn't exist
		if ( ! in_array( $post_id, $collected_products ) )
			$collected_products[] = $post_id;

		// WPLA()->logger->info("collected products: ".print_r($collected_products,1));

		// update queue
		update_option( 'wpla_updated_products_queue', $collected_products );
	}

	function update_products_on_shutdown() {

		// get queue
		$collected_products = get_option( 'wpla_updated_products_queue', array() );
		if ( ! is_array( $collected_products ) ) $collected_products = array();

		// DEBUG
		WPLA()->logger->info("CSV: update_products_on_shutdown() - collected_products: ".print_r($collected_products,1));

		// mark each queued product as modified
		$lm = new WPLA_ListingsModel();
		foreach ($collected_products as $post_id ) {
			// do_action( 'wpla_product_has_changed', $post_id );
			$lm->markItemAsModified( $post_id, true ); // set $skip_updating_feeds = true
		}

		// clear queue
		delete_option( 'wpla_updated_products_queue' );

		// update pending feeds - after all items have been updated
		if ( ! empty($collected_products) ) WPLA_AmazonFeed::updatePendingFeeds();
	}

}

// global $wpla_api;
// $wpla_api = new WPLA_API_Hooks();
