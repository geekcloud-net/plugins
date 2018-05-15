<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * AmazonS3 API
 *
 * Acts as a replacement for the Amazon SDK (same method names/parameters), only implementing the functions we need
 * And using "Signature Version 4" for requests so it works in all buckets
 *
 * @see http://docs.aws.amazon.com/AmazonS3/latest/API/sigv4-query-string-auth.html
 */
class AmazonS3 {

	//Amazon S3 Key
	private $key;
	// Amazon S3 Secret
	private $secret;

	// Unix time stamp for the current request
	private $time;

	// If we need to append a single extra query string (ie 'location')
	private $extra_query_string = '';
	// Tells if we need to make an extra remote request to get a region
	private $get_remote_region;
	// $region passed in get_object_url
	private $passed_region = '';

	// The bucket we are trying to acces
	private $bucket;
	// The object (file) we are trying to access
	private $object;
	// How long our signatures should last (admin setting)
	private $period;

	// Transient prefix
	private $transient_prefix = 's3-region-';

	// WC_Logger
	public $log = false;

	// Use path style? Only fall back if the case of the bucket is not completely lowercase
	public $use_path_style = false;

	/**
	 * Passes our credentials in so we can use them in the requests
	 * @param array $credentials key & secret
	 */
	public function __construct( $credentials ) {
		$this->key = trim( $credentials['key'] );
		$this->secret = trim( $credentials['secret'] );
	}

	/**
	 * Returns a presigned Amazon S3 URL for an object/file
	 */
	public function get_object_url( $bucket, $object, $period, $region = '' ) {
		$this->time = time();

		$this->bucket = $bucket;
		$this->object = str_replace( array( '+', ' ' ), '%20', $object );
		$this->period = $period;
		$this->path   = $this->object;

		if ( strtolower( $this->bucket ) !== $this->bucket ) {
			$this->use_path_style = true;
		}

		if ( ! empty( $region ) ) {
			$this->passed_region = $region;
		}

		if ( $this->use_path_style ) {
			$url = 'https://' . $this->get_host() . '/' . $bucket . '/' . $object . '?';
		} else {
			$url = 'https://' . $bucket . '.' . $this->get_host() . '/' . $object . '?';
		}

		$url .= $this->get_query_strings();
		$url .= '&X-Amz-Signature=' . $this->generate_signature();

		// If we don't know if we have the correct region,
		// try to figure out the correct one and try again
		if ( $this->get_remote_region && empty( $this->passed_region ) ) {
			$region = $this->get_remote_region();
			$this->get_remote_region = false;
			return $this->get_object_url( $bucket, $object, $period, $region );
		}

		return $url;
	}

	/**
	 * Return the "canonical request" for our current S3 API request
	 */
	private function get_canonical_request() {
		$request = "GET\n";

		if ( $this->use_path_style ) {
			$request .= '/' . $this->bucket . '/' . $this->path . "\n";
		} else {
			$request .= '/' . $this->path . "\n";
		}

		$request .= $this->get_query_strings() . "\n";

		if ( $this->use_path_style ) {
			$request .= 'host:' . $this->get_host() . "\n\n";
		} else {
			$request .= 'host:' . $this->bucket . '.' . $this->get_host() . "\n\n";
		}

		$request .= "host\n";
		$request .= 'UNSIGNED-PAYLOAD';

		return $request;
	}

	/**
	 * Some regions expect a different host
	 */
	private function get_host() {
		$region = $this->get_region();
		switch ( $region ) {
			case 'us-east-1' :
				return 's3.amazonaws.com';
				break;
			case 'us-east-2':
				return 's3.us-east-2.amazonaws.com';
				break;
			case 'us-west-1' :
				return 's3-us-west-1.amazonaws.com';
				break;
			case 'us-west-2' :
				return 's3-us-west-2.amazonaws.com';
				break;
			case 'eu-central-1' :
				return 's3.eu-central-1.amazonaws.com';
				break;
			case 'eu-west-1' :
				return 's3-eu-west-1.amazonaws.com';
				break;
			case 'eu-west-2' :
				return 's3-eu-west-2.amazonaws.com';
				break;
			case 'ap-southeast-1' :
				return 's3-ap-southeast-1.amazonaws.com';
				break;
			case 'ap-southeast-2' :
				return 's3-ap-southeast-2.amazonaws.com';
				break;
			case 'ap-northeast-1' :
				return 's3-ap-northeast-1.amazonaws.com';
				break;
			case 'ap-northeast-2':
				return 's3.ap-northeast-2.amazonaws.com';
				break;
			case 'sa-east-1' :
				return 's3-sa-east-1.amazonaws.com';
				break;
			case 'ca-central-1':
				return 's3.ca-central-1.amazonaws.com';
				break;
			case 'ap-south-1':
				return 's3.ap-south-1.amazonaws.com';
				break;
			default:
				// Unrecognized region
				$this->log( sprintf( esc_html__( 'An unrecognized S3 region (%s) was provided.', 'wc_amazon_s3' ), $region ) );
				return 's3.amazonaws.com';
				break;
		}
	}

	/**
	 * If we don't have a remote region stored, then we will default to
	 * us-east-1 which will work for a LARGE NUMBER of requests. However,
	 * we do need to test that assumption before returning the download URL,
	 * so we let get_object_url know we need to do an extra test request
	 */
	private function get_region() {
		if ( ! empty( $this->passed_region ) ) {
			return $this->passed_region;
		}

		$region = get_transient( $this->transient_prefix . $this->bucket );

		if ( false === $region ) {
			$this->get_remote_region = true;
			return 'us-east-1';
		}
		return $region;
	}

	/**
	 * Returns the "credential" line for signed Amazon S3 requests
	 */
	private function get_credential() {
		$credential = date( 'Ymd', $this->time ) . '/';
		$credential .= $this->get_region() . '/s3/aws4_request';
		return $credential;
	}

	/**
	 * Generates our list of Amazon query strings to generate a signature from
	 */
	private function get_query_strings() {
		$url = '';
		$url .= 'X-Amz-Algorithm=AWS4-HMAC-SHA256';
		$url .= '&X-Amz-Credential=' . urlencode( $this->key . '/' . $this->get_credential() );
		$url .= '&X-Amz-Date=' . gmdate( 'Ymd\THis\Z', $this->time );
		$url .= '&X-Amz-Expires=' . $this->period;
		$url .= '&X-Amz-SignedHeaders=host';
		if ( ! empty( $this->extra_query_string ) ) {
			$url .= '&' . $this->extra_query_string . '=';
		}
		return $url;
	}

	/**
	 * Generates the actual string/data we are signing
	 */
	private function get_string_to_sign() {
		$string = 'AWS4-HMAC-SHA256' . "\n";
		$string .= gmdate( 'Ymd\THis\Z', $this->time ) . "\n";
		$string .= $this->get_credential() . "\n";
		$string .= $this->hex16( hash( 'sha256', $this->get_canonical_request(), true ) );
		return $string;
	}

	/**
	 * Base16 Hex
	 */
	private function hex16( $value ) {
		$result = unpack( 'H*', $value );
		return reset( $result );
	}

	/**
	 * Generates our final signature using a signing key and get_string_to_sign
	 */
	private function generate_signature() {
		$date_key = hash_hmac( 'sha256', date( 'Ymd', $this->time ), 'AWS4' . $this->secret, true );
		$date_region_key = hash_hmac( 'sha256', $this->get_region(), $date_key, true );
		$date_region_service_key = hash_hmac( 'sha256', 's3', $date_region_key, true );
		$signing_key = hash_hmac( 'sha256', 'aws4_request', $date_region_service_key, true );

		$string_to_sign = $this->get_string_to_sign();

		return $this->hex16( hash_hmac( 'sha256', $string_to_sign, $signing_key, true ) );
	}

	/**
	 * If we need to figure out what the correct region is, this does an additional get_request to test
	 * and if it finds the correct one, it'll store it for later
	 */
	private function get_remote_region() {
		$this->path = '';
		$this->extra_query_string = 'location';
		$region = 'us-east-1'; // default

		if ( $this->use_path_style ) {
			$url = 'https://s3.amazonaws.com/' . $this->bucket . '?';
		} else {
			$url = 'https://' . $this->bucket . '.s3.amazonaws.com/?';
		}

		$url .= $this->get_query_strings();
		$url .= '&X-Amz-Signature=' . $this->generate_signature();

		$this->path = $this->object;
		$this->extra_query_string = '';

		$request = wp_remote_get( $url );

		if ( ! is_wp_error( $request ) ) {
			if ( 400 === $request['response']['code'] && strpos( $request['body'], '<Region>' ) !== false ) {
				if ( preg_match( '/<Region>(.+?)<\/Region>/i', $request['body'], $region_match ) ) {
					$region = $region_match[1];
				}
			} elseif ( 200 === $request['response']['code'] && strpos( $request['body'], '</LocationConstraint>' ) !== false ) {
				if ( preg_match( '/<LocationConstraint xmlns="(.+?)">(.+?)<\/LocationConstraint>/i', $request['body'], $region_match ) ) {
					$region = $region_match[2];
				}
			}
		}

		set_transient( $this->transient_prefix . $this->bucket, $region, 1 * HOUR_IN_SECONDS );
		return $region;
	}

	// We don't need to do anything here, for these presigned requests - still need a stub function
	public function disable_ssl_verification() { }


	/**
	 * Logging method
	 * @param string $message
	 */
	public function log( $message ) {
		if ( empty( $this->log ) ) {
			$this->log = new WC_Logger();
		}
		$this->log->add( 'amazon-s3', $message );
	}

}
