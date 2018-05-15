<?php
/**
 * Instagram API class.
 *
 * @package WooCommerce_Instagram
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WooCommerce Instagram API Class
 *
 * @package WordPress
 * @subpackage Woocommerce_Instagram
 * @category API
 * @author WooThemes
 * @since 1.0.0
 */
class Woocommerce_Instagram_API {

	/**
	 * API URL.
	 *
	 * @var string
	 */
	private $_api_url = 'https://api.instagram.com/';

	/**
	 * Class token.
	 *
	 * @var string
	 */
	private $_token = 'woocommerce-instagram';

	/**
	 * Instagram Username.
	 *
	 * @deprecated
	 *
	 * @var string
	 */
	private $_username;

	/**
	 * Constructor function.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file Plugin's main file.
	 *
	 * @return void
	 */
	public function __construct( $file ) {
		$this->_file = $file;
	}

	/**
	 * Retrieve stored tag/XXXXX/media/recent images.
	 *
	 * @since 1.0.0
	 *
	 * @param string $tag  Instagram tag.
	 * @param array  $args Request args.
	 *
	 * @return array
	 */
	public function get_tag_media_recent( $tag, $args ) {
		$data = '';
		$transient_key = esc_attr( $tag ) . '-tag-media-recent';

		if ( isset( $args['count'] ) ) {
			// Unique transient each time we change the count.
			$transient_key .= '-' . intval( $args['count'] );
		}

		if ( false === ( $data = get_transient( $transient_key ) ) ) {
			$response = $this->request_tag_media_recent( $tag, $args );

			if ( isset( $response->data ) ) {
				$data = json_encode( $response );
				set_transient( $transient_key, $data, $this->get_transient_expire_time() );
			}
		}

		return json_decode( $data );
	}

	/**
	 * Retrieve recent photos for the specified tag.
	 *
	 * @since 1.0.0
	 *
	 * @param string $tag  Instagram tag.
	 * @param array  $args Request args.
	 *
	 * @return  array
	 */
	public function request_tag_media_recent( $tag, $args ) {
		$data     = array();
		$settings = $this->_get_settings();
		if ( ! isset( $settings['access_token'] ) || '' == $settings['access_token'] ) {
			return false;
		}

		$args['access_token'] = $settings['access_token'];
		$response = $this->_request( 'v1/tags/' . urlencode( $tag ) . '/media/recent/', $args, 'get' );

		if ( is_wp_error( $response ) ) {
			$data = new StdClass;
		} else {
			if ( isset( $response->meta->code ) && ( 200 == absint( $response->meta->code ) ) ) {
				$data = $response;
			}
		}

		return $data;
	}

	/**
	 * Make a request to the API.
	 *
	 * @since 1.0.0
	 *
	 * @param string $endpoint The endpoint of the API to be called.
	 * @param array  $params   Array of parameters to pass to the API.
	 * @param string $method   Request method. Default to 'post'.
	 *
	 * @return object The response from the API.
	 */
	private function _request( $endpoint, $params = array(), $method = 'post' ) {
		$return = '';
		$method = strtolower( $method );

		if ( 'get' === $method ) {
			$url = $this->_api_url . $endpoint;

			if ( count( $params ) > 0 ) {
				$url .= '?';
				$count = 0;
				foreach ( $params as $k => $v ) {
					$count++;

					if ( $count > 1 ) {
						$url .= '&';
					}

					$url .= $k . '=' . $v;
				}
			}

			$response = wp_remote_get( $url,
				array(
					'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
				)
			);
		} else {
			$response = wp_remote_post( $this->_api_url . $endpoint,
				array(
					'body' => $params,
					'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
				)
			);
		}

		if ( ! is_wp_error( $response ) ) {
			$return = json_decode( $response['body'] );
		}

		return $return;
	}

	/**
	 * Request an access token from the API.
	 *
	 * @since 1.0.0
	 * @deprecated since 1.0.9
	 *
	 * @param   string $username The username.
	 * @param   string $password The password.
	 */
	public function get_access_token( $username, $password ) {
		_deprecated_function( __FUNCTION__, 'Use connect.wooocommerce.com/login/instagram to get the access token.' );
	}

	/**
	 * Get transient expire time.
	 *
	 * @since 1.0.9
	 *
	 * @return int Transient expire time in second. Default to one day
	 */
	public function get_transient_expire_time() {
		return apply_filters( 'woocommerce_instagram_transient_expire_time', 60 * 60 * 24 );
	}

	/**
	 * If the parameter is an object with our expected properties, display an error notice.
	 *
	 * @since 1.0.0
	 *
	 * @param   object/string $obj Object if an error, empty string if not.
	 * @return  boolean/string     String if an error, boolean if not.
	 */
	private function _maybe_display_error( $obj ) {
		if ( ! is_object( $obj ) || ! isset( $obj->code ) || ! isset( $obj->error_message ) ) {
			return;
		}
		return '<p class="woocommerce-instagram-error error">' . esc_html( $obj->error_message ) . '</p>' . "\n";
	}

	/**
	 * Retrieve stored settings.
	 *
	 * @since 1.0.0
	 *
	 * @return array Stored settings.
	 */
	private function _get_settings() {
		return wp_parse_args( (array) get_option( $this->_token . '-settings', array( 'access_token' => '', 'username' => '' ) ), array( 'access_token' => '', 'username' => '' ) );
	}
}
