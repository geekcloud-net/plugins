<?php
/**
 * POS API Removed Items Class
 *
 * Handles requests to the /removed endpoint. 
 *
 * @class 	  WC_API_POS_Orders
 * @package   WooCommerce POS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_API_POS_Removed extends WC_API_Resource {

	/** @var string $base the route base */
	protected $base = '/pos_removed';

	/** @var string $post_type the custom post type */
	protected $post_type = 'shop_order';

	/**
	 * Register the routes for this class
	 *
	 * GET/pos_removed
	 *
	 * @since 2.1
	 * @param array $routes
	 * @return array
	 */
	public function register_routes( $routes ) {

		# GET /pos_removed
		$routes[ $this->base ] = array(
			array( array( $this, 'get_removed_items' ),     WC_API_Server::READABLE )
		);

		return $routes;
	}

	/**
	 * Setup class
	 *
	 * @since 2.1
	 * @param WC_API_Server $server
	 * @return WC_API_Resource
	 */
	public function __construct( WC_API_Server $server ) {

		$this->server = $server;

		// automatically register routes for sub-classes
		add_filter( 'woocommerce_api_endpoints', array( $this, 'register_routes' ) );

		// maybe add meta to top-level resource responses
		foreach ( array( 'pos_removed' ) as $resource ) {
			add_filter( "woocommerce_api_{$resource}_response", array( $this, 'maybe_add_meta' ), 15, 2 );
		}

		$response_names = array( 'pos_removed' );

		foreach ( $response_names as $name ) {

			/* remove fields from responses when requests specify certain fields
			 * note these are hooked at a later priority so data added via
			 * filters (e.g. customer data to the order response) still has the
			 * fields filtered properly
			 */
			add_filter( "woocommerce_api_{$name}_response", array( $this, 'filter_response_fields' ), 20, 3 );
		}
	}

	/**
	 * Get all removed items
	 *
	 * @since 2.1
	 * @param string $fields
	 * @param array $filter
	 * @param string $status
	 * @param int $page
	 * @return array
	 */
	public function get_removed_items( $fields = null, $filter = array(), $status = null, $page = 1 ) {

		try {
			if ( ! current_user_can( 'read_private_shop_orders' ) ) {
				throw new WC_API_Exception( 'woocommerce_api_user_cannot_read_orders_count', __( 'You do not have permission to read the orders', 'wc_point_of_sale' ), 401 );
			}

			$post_ids = get_option( 'pos_removed_posts_ids', array() );

			$user_ids = get_option( 'pos_removed_user_ids', array() );

			return array( 'post_ids' => (array) $post_ids, 'user_ids' => (array) $user_ids );

		} catch ( WC_API_Exception $e ) {
			return new WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ) );
		}
	}
}
