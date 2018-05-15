<?php
/**
 * @package    Internals
 * @since      1.7.0
 * @version    1.7.0
 */

// Avoid direct calls to this file.
if ( ! class_exists( 'WPSEO_Video_Sitemap' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/**
 *****************************************************************
 * Video SEO Details
 */
if ( ! class_exists( 'WPSEO_Video_Details' ) ) {

	/**
	 * @package    WordPress\Plugins\Video-seo
	 * @subpackage Internals
	 * @since      1.7.0
	 * @version    1.7.0
	 *
	 * This abstract class and it's concrete classes implement the retrieval of details about videos
	 * from various video hosting services.
	 *
	 * {@internal Variable testing before passing values to $this->vid is currently done with empty().
	 * This is fine as long as the defaults in the $vid array are all either empty strings,
	 * 0 integers or null values. If at any point those defaults would change, checking with empty()
	 * will start to cause serious issues and all those checks will need to be rewritten.}}
	 *
	 * {@internal If you add a service, don't forget to add the class to the autoload list and
	 * the associated urls to the verify_service_type() method in class-analyse-post.php.
	 * Oh, and adding some tests would not go amiss either ;-) }}
	 *
	 * {@internal If you remove a service, make sure you check that no other services where extending its class.}}
	 */
	abstract class WPSEO_Video_Details {

		/**
		 * @abstract    This property must be set in the concrete implementation class
		 *              Leaving it empty will disable the standard determine_video_id_from_url() functionality
		 *              You can still implement your own version of this functionality by adding a
		 *              determine_video_id_from_url() method to the concrete class.
		 *
		 * @see         WPSEO_Video_Details::determine_video_id_from_url()
		 *
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '';

		/**
		 * @abstract    This property must be set in the concrete implementation class
		 *              Leaving it empty will disable the standard determine_video_url_from_id() functionality
		 *              You can still implement your own version of this functionality by adding a
		 *              determine_video_url_from_id() method to the concrete class.
		 *
		 * @see         WPSEO_Video_Details::determine_video_url_from_id()
		 *
		 * @var	string	Sprintf template to create a url from an id
		 */
		protected $url_template = '';

		/**
		 * @abstract    This property must be set in the concrete implementation class
		 *              Leaving 'pattern' and 'replace_key' empty will disable the get_remote_video_info()
		 *              functionality
		 *              You can still implement you own version of this functionality by adding this method to
		 *              the concrete class.
		 *              Similarly leaving 'response_type' empty will disable the decode_remote_video_info()
		 *              functionality.
		 *
		 * @see         WPSEO_Video_Details::get_remote_video_info()
		 * @see         WPSEO_Video_Details::decode_remote_video_info()
		 *
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => '', // Remote url pattern with one (!) %s placeholder.
			'replace_key'   => '', // Key in the $vid array with which to replace the placeholder in the url.

			/*
			 * expected response type for use in decoding the response
			   - should be one of the following: 'json', 'serial' or 'simplexml'
			   - if you need another type of decoding, implement your own version of
			     decode_remote_video_info() in the concrete class
			   - leaving it empty will disable decoding and pass the received response
			     unchanged to decoded_response
			*/
			'response_type' => '',
		);

		/**
		 * In some cases there is a need for an API key
		 *
		 * @var string
		 */
		protected $api_key = '';

		/**
		 * @var array   The details retrieved for this video
		 */
		protected $vid = array(
			// Should/will always be set after retrieving the details.
			'id'               => null,
			'url'              => '',
			'type'             => '',
			'player_loc'       => '',
			'thumbnail_loc'    => '',

			// Might be set after retrieving the details.
			'content_loc'      => '',
			'duration'         => 0,
			'view_count'       => 0,
			'width'            => 0,
			'height'           => 0,

			/*
			// Might come in via old_vid / update_meta method
			'title'            => '',
			'description'      => '',
			'publication_date' => null,
			'post_ID'          => null,
			'category'         => null,
			'tag'              => null,
			*/
		);

		/**
		 * @var array  The video array with all the data of the previous "fetch", if available.
		 */
		protected $old_vid = array();

		/**
		 * @var string  Storage for response retrieved from external server upon video detail request
		 */
		protected $remote_response;

		/**
		 * @var mixed  Storage for the decoded version of the remote response
		 */
		protected $decoded_response;

		/**
		 * @var object  Storage for a SimpleXML object created from response
		 *              Only used when $remote_response['type'] has been set to 'simpleXML'
		 */
		protected $xml;


		/**
		 * Instantiate the class, main routine.
		 *
		 * @param array $vid     The video array with all the data.
		 * @param array $old_vid The video array with all the data of the previous "fetch", if available.
		 *
		 * @return \WPSEO_Video_Details
		 */
		public function __construct( $vid, $old_vid = array() ) {
			$vid       = (array) $vid;
			$this->vid = array_merge( $this->vid, array_filter( $vid ) );

			if ( is_array( $old_vid ) && $old_vid !== array() ) {
				$this->old_vid = $old_vid;
			}

			if ( ! isset( $this->vid['id'] ) || empty( $this->vid['id'] ) ) {
				$this->determine_video_id_from_url();
			}

			if ( ! isset( $this->vid['url'] ) || empty( $this->vid['url'] ) ) {
				$this->determine_video_url_from_id();
			}

			if ( $this->maybe_use_old_video_data() === false ) {

				$this->get_remote_video_info();

				if ( isset( $this->remote_response ) ) {
					$this->decode_remote_video_info();
				}

				if ( $this->is_video_response() ) {
					$this->put_video_details();
				}

				/**
				 * @todo - if it's not a video - should we reset the $vid array ? or maybe add a key
				 * 'video' => false, so we can avoid checking the item again?
				 */
			}
		}


		/**
		 * Get the enriched video details without empties
		 *
		 * @return array
		 */
		public function get_details() {
			return array_filter( $this->vid );
		}


		/**
		 * Retrieve the video id from a known video url based on a regex match
		 *
		 * @uses WPSEO_Video_Details::$id_regex
		 *
		 * @param  int $match_nr  The captured parenthesized sub-pattern to use from matches. Defaults to 1.
		 *
		 * @return void
		 */
		protected function determine_video_id_from_url( $match_nr = 1 ) {
			if ( ( is_string( $this->vid['url'] ) && $this->vid['url'] !== '' ) && $this->id_regex !== '' ) {
				if ( preg_match( $this->id_regex, $this->vid['url'], $match ) ) {
					$this->vid['id'] = $match[ $match_nr ];
				}
			}
		}


		/**
		 * Create a video url based on a known video id and url template
		 *
		 * @uses WPSEO_Video_Details::$url_template
		 *
		 * @return void
		 */
		protected function determine_video_url_from_id() {
			if ( ! empty( $this->vid['id'] ) && $this->url_template !== '' ) {
				$this->vid['url'] = sprintf( $this->url_template, rawurlencode( $this->vid['id'] ) );
			}
		}


		/**
		 * Use the "new" post data with the old video data, to prevent the need for an external video
		 * API call when the video hasn't changed.
		 *
		 * @since 0.1
		 *
		 * @todo Big check on whether this works properly in this new implementation !!!
		 * What about overwritting a title with an empty title ? Probably can't be done this way, so probably
		 * needs alternative solution!
		 *
		 * @todo [JRF -> Yoast] The remote info may change over time (most notably view count), should we maybe
		 * set a cache time for this kind of data ? Only retrieve once a month ? Once every six months ?
		 * Cache check could be done by adding a $this->vid['remote_retrieve_date'] key and checking against that
		 *
		 * @todo [JRF/Yoast] Re-visit this method - what about if we have improved the retrieval methods ?
		 * (like we have) - shouldn't we also check that either a content_loc or player_loc has been set and if not,
		 * try a remote call anyway ?
		 * After all, it's not as if video retrieval is done *that* often, only on post/page/term save when a
		 * video has been found in that item and on manual request for re-index.
		 * So it shouldn't really slow people down.
		 *
		 * @param string $match_on  Array key to use in the $vid array to determine whether or not to use the old data
		 *                          Defaults to 'id'.
		 *
		 * @return bool             Whether or not valid old data was found (and used)
		 */
		protected function maybe_use_old_video_data( $match_on = 'id' ) {
			if ( ( is_array( $this->old_vid ) && $this->old_vid !== array() ) && ( ( isset( $this->old_vid[ $match_on ] ) && isset( $this->vid[ $match_on ] ) ) && $this->vid[ $match_on ] === $this->old_vid[ $match_on ] ) ) {

				// Filter out any empty values so as to not overwrite a real value with an empty.
				$this->vid = array_merge( array_filter( $this->old_vid ), array_filter( $this->vid ) );

				return true;
			}

			return false;
		}


		/**
		 * Retrieve information on a video via a remote API call
		 *
		 * @uses WPSEO_Video_Details::$remote_url
		 *
		 * @return void|string
		 */
		protected function get_remote_video_info() {
			if ( ( is_string( $this->remote_url['pattern'] ) && $this->remote_url['pattern'] !== '' ) &&
				( ( is_string( $this->vid[ $this->remote_url['replace_key'] ] ) || is_int( $this->vid[ $this->remote_url['replace_key'] ] ) ) && ! empty( $this->vid[ $this->remote_url['replace_key'] ] ) ) ) {

				$replace_key = $this->vid[ $this->remote_url['replace_key'] ];
				// Fix protocol-less urls in parameters as the remote get call most often will not work with them.
				if ( $this->remote_url['replace_key'] === 'url' && strpos( $this->vid['url'], '//' ) === 0 ) {
					$replace_key = 'http:' . $this->vid['url'];
				}

				$url = sprintf( $this->remote_url['pattern'], $replace_key, $this->api_key );
				$url = $this->url_encode( $url );

				$response = $this->remote_get( $url );
				if ( is_string( $response ) && $response !== '' && $response !== 'null' ) {
					$this->remote_response = $response;
				}

				// Only needed for child classes to catch the response and handle it differently.
				return $response;
			}
		}


		/**
		 * Wrapper for the WordPress internal wp_remote_get function, making sure a proper user-agent is sent along.
		 *
		 * @since 0.1
		 *
		 * @param string $url     The URL to retrieve.
		 * @param array  $headers Optional headers to send.
		 *
		 * @return array|boolean $body Returns the body of the post when successful, false when unsuccessful
		 */
		protected function remote_get( $url, $headers = array() ) {
			// Fix protocol-less urls as the remote get call will not work with them (mainly needed for wistia frame source).
			if ( strpos( $url, '//' ) === 0 ) {
				$url = 'http:' . $url;
			}

			$response = wp_remote_get(
				$url,
				array(
					'redirection' => 1,
					'httpversion' => '1.1',
					'user-agent'  => 'WordPress Video SEO plugin ' . WPSEO_VERSION . '; WordPress (' . home_url( '/' ) . ')',
					'timeout'     => 15,
					'headers'     => $headers,
				)
			);

			if ( ! is_wp_error( $response ) && $response['response']['code'] === 200 && isset( $response['body'] ) ) {
				return $response['body'];
			}

			return false;
		}


		/**
		 * Decode a remote response for a number of typical response types
		 *
		 * @uses WPSEO_Video_Details::$remote_url
		 *
		 * @return void
		 */
		protected function decode_remote_video_info() {
			if ( ( ! empty( $this->remote_url['response_type'] ) && is_string( $this->remote_url['response_type'] ) ) && ! empty( $this->remote_response ) ) {

				switch ( $this->remote_url['response_type'] ) {

					case 'json':
						$this->decode_as_json();
						break;

					case 'serial':
						$this->decode_as_serialized();
						break;

					case 'simplexml':
						$this->decode_as_simplexml();
						break;
				}
			}
			else {
				$this->decoded_response = $this->remote_response;
			}
		}


		/**
		 * Decode a remote response as json
		 */
		protected function decode_as_json() {
			$response = json_decode( $this->remote_response );
			if ( is_object( $response ) ) {
				$this->decoded_response = $response;
			}
		}


		/**
		 * Decode a remote response as serialized
		 */
		protected function decode_as_serialized() {
			$response = unserialize( $this->remote_response );
			if ( $response !== false ) {
				$this->decoded_response = $response;
			}
		}


		/**
		 * Decode a remote response as simpleXML
		 */
		protected function decode_as_simplexml() {
			$this->xml = new SimpleXMLElement( $this->remote_response );
			$response  = $this->xml->channel->item->children( 'http://search.yahoo.com/mrss/' );
			if ( is_object( $response ) && ! empty( $response ) ) {
				$this->decoded_response = $response;
			}
		}


		/**
		 * Check to see if this is really a video.
		 * Meant to be overloaded from child classes. Defaults to true.
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			return true;
		}


		/**
		 * Set video details to their new values
		 *
		 * The actual setting is done via methods in the concrete classes.
		 *
		 * @return void
		 */
		protected function put_video_details() {
			// {@internal: keep set_id() first, if it does need changing, it needs to be done before anything else.}}
			if ( method_exists( $this, 'set_id' ) ) {
				$this->set_id();
			}
			$this->set_type();
			$this->set_player_loc();

			if ( method_exists( $this, 'set_duration' ) ) {
				$this->set_duration();
			}
			if ( method_exists( $this, 'set_view_count' ) ) {
				$this->set_view_count();
			}
			if ( method_exists( $this, 'set_content_loc' ) ) {
				$this->set_content_loc();
			}
			if ( method_exists( $this, 'set_width' ) ) {
				$this->set_width();
			}
			if ( method_exists( $this, 'set_height' ) ) {
				$this->set_height();
			}

			/*
			    Only override the thumbnail if it hasn't been set already.
				This is in contrast to all the other methods, where the info retrieved from the remote
				service is leading. For the thumbnail, the user preference is leading.
			*/
			if ( empty( $this->vid['thumbnail_loc'] ) ) {
				$this->set_thumbnail_loc();
			}

			/*
			   Add protocol if the resulting player_loc URL would be protocol-less to prevent invalid sitemaps.
			   Default to http as not all video services support https.
			*/
			if ( isset( $this->vid['player_loc'] ) && strpos( $this->vid['player_loc'], '//' ) === 0 ) {
				$this->vid['player_loc'] = 'http:' . $this->vid['player_loc'];
			}
		}


		/**
		 * Set the player location
		 *
		 * @abstract
		 */
		abstract protected function set_player_loc();


		/**
		 * Set the thumbnail location
		 *
		 * @abstract
		 */
		abstract protected function set_thumbnail_loc();


		/**
		 * Set the video type based on the concrete class name
		 */
		protected function set_type() {
			$type = str_ireplace( 'WPSEO_Video_Details_', '', get_class( $this ), $count );
			if ( $count === 1 ) {
				$this->vid['type'] = strtolower( $type );
			}
		}


		/* ********* HELPER METHODS ******** */


		/**
		 * Set the video duration based on a typical json response
		 *
		 * @uses WPSEO_Video_Details::$decoded_response
		 */
		protected function set_duration_from_json_object() {
			if ( ! empty( $this->decoded_response->duration ) ) {
				$this->vid['duration'] = $this->decoded_response->duration;
			}
		}


		/**
		 * Set the video height based on a typical json response
		 *
		 * @uses WPSEO_Video_Details::$decoded_response
		 */
		protected function set_height_from_json_object() {
			if ( ! empty( $this->decoded_response->height ) ) {
				$this->vid['height'] = $this->decoded_response->height;
			}
		}


		/**
		 * Set the video thumbnail url based on a typical json response
		 *
		 * @uses WPSEO_Video_Details::$decoded_response
		 */
		protected function set_thumbnail_loc_from_json_object() {
			if ( ! empty( $this->decoded_response->thumbnail_url ) ) {
				$image = $this->make_image_local( $this->decoded_response->thumbnail_url );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
			}
		}


		/**
		 * Set the video width based on a typical json response
		 *
		 * @uses WPSEO_Video_Details::$decoded_response
		 */
		protected function set_width_from_json_object() {
			if ( ! empty( $this->decoded_response->width ) ) {
				$this->vid['width'] = $this->decoded_response->width;
			}
		}


		/**
		 * Downloads an externally hosted thumbnail image to the local server
		 *
		 * @since 0.1
		 *
		 * @todo - revisit this whole function, a lot depends on $vid['id'] being set, while it might very well not be
		 *
		 * @todo - also: why not check whether the url is already local (not just an attachment) before doing anything else ?
		 *
		 * @param string $url The remote URL of the image.
		 * @param string $ext Extension to use for the image, optional.
		 *
		 * @return bool|string $img[0] The link to the now locally hosted image.
		 */
		protected function make_image_local( $url, $ext = '' ) {

			$vid = $this->vid;

			// Remove query parameters from the URL.
			$url = strtok( $url, '?' );

			if ( isset( $vid['post_id'] ) ) {
				$att = get_posts(
					array(
						'numberposts' => 1,
						'post_type'   => 'attachment',
						'meta_key'    => 'wpseo_video_id',
						'meta_value'  => isset( $vid['id'] ) ? $vid['id'] : '',
						'post_parent' => $vid['post_id'],
						'fields'      => 'ids',
					)
				);

				if ( is_array( $att ) && count( $att ) > 0 ) {
					$img = wp_get_attachment_image_src( $att[0], 'full' );

					if ( $img ) {
						if ( strpos( $img[0], 'http' ) !== 0 ) {
							return get_site_url( null, $img[0] );
						}

						return $img[0];
					}
				}
			}

			/*
			 * Disable wp smush.it to speed up the process.
			 * @todo - should this filter maybe be added back at the end ? If so we need to test whether it existed and
			 * only add it back if it did.
			 */
			remove_filter( 'wp_generate_attachment_metadata', 'wp_smushit_resize_from_meta_data' );

			$tmp = download_url( $url );

			if ( ! is_wp_error( $tmp ) ) {

				if ( preg_match( '`[^\?]+\.(' . WPSEO_Video_Sitemap::$image_ext_pattern . ')$`i', $url, $matches ) ) {
					$ext = $matches[1];
				}

				if ( ( ! isset( $vid['title'] ) || empty( $vid['title'] ) ) && isset( $vid['post_id'] ) ) {
					$vid['title'] = get_the_title( $vid['post_id'] );
				}
				else {
					$vid['title'] = strtolower( $vid['id'] );
				}
				$title = sanitize_title( strtolower( $vid['title'] ) );

				$file_array = array(
					'name'     => sanitize_file_name( preg_replace( '`[^a-z0-9\s_-]`i', '', $title ) ) . '.' . $ext,
					'tmp_name' => $tmp,
				);

				if ( isset( $vid['post_id'] ) && ! defined( 'WPSEO_VIDEO_NO_ATTACHMENTS' ) ) {

					$ret = media_handle_sideload( $file_array, $vid['post_id'], 'Video thumbnail for ' . $vid['type'] . ' video ' . $vid['title'] );
					if ( is_wp_error( $ret ) ) {
						@unlink( $tmp );
						return false;
					}

					if ( isset( $vid['id'] ) ) {
						update_post_meta( $ret, 'wpseo_video_id', $vid['id'] );
					}

					$img = wp_get_attachment_image_src( $ret, 'full' );

					if ( $img ) {
						// Try and prevent relative paths to images.
						if ( strpos( $img[0], 'http' ) !== 0 ) {
							$img = get_site_url( null, $img[0] );
						}
						else {
							$img = $img[0];
						}

						return $img;
					}
				}
				else {
					$file = wp_handle_sideload( $file_array, array( 'test_form' => false ) );

					if ( ! isset( $file['error'] ) ) {
						return $file['url'];
					}
					else {
						@unlink( $file );
					}
				}

				return false;
			}
		}


		/**
		 * Encode a url according to the specs
		 *
		 * Based on a function by Lucas Gonze -- lucas@gonze.com
		 * 07-Jan-2005 07:01
		 * http://nl2.php.net/manual/nl/function.rawurlencode.php
		 *
		 * @param string $url (Part of) url to be encoded.
		 *
		 * @return string     Correctly encoded url
		 */
		protected function url_encode( $url ) {

			$defaults = array(
				'scheme'   => '',
				'pass'     => '',
				'user'     => '',
				'port'     => '',
				'host'     => '',
				'path'     => '',
				'query'    => '',
				'fragment' => '',
			);

			$parsed_url = WPSEO_Video_Analyse_Post::wp_parse_url( $url );
			$parsed_url = array_merge( $defaults, $parsed_url );

			if ( empty( $parsed_url['scheme'] ) === false ) {
				$parsed_url['scheme'] .= '://';
			}

			if ( empty( $parsed_url['pass'] ) === false && empty( $parsed_url['user'] ) === false ) {
				$parsed_url['user'] = rawurlencode( $parsed_url['user'] ) . ':';
				$parsed_url['pass'] = rawurlencode( $parsed_url['pass'] ) . '@';
			}
			elseif ( empty( $parsed_url['user'] ) === false ) {
				$parsed_url['user'] .= '@';
			}

			if ( empty( $parsed_url['port'] ) === false && empty( $parsed_url['host'] ) === false ) {
				$parsed_url['host'] = $parsed_url['host'] . ':';
			}

			if ( empty( $parsed_url['path'] ) === false ) {
				$arr  = preg_split( '`([/;=])`', $parsed_url['path'], -1, PREG_SPLIT_DELIM_CAPTURE );
				$path = '';
				foreach ( $arr as $var ) {
					switch ( $var ) {
						case '/':
						case ';':
						case '=':
							$path .= $var;
							break;

						default:
							$path .= rawurlencode( $var );
							break;
					}
				}
				// Legacy patch for servers that need a literal /~username.
				$parsed_url['path'] = str_replace( '/%7E', '/~', $path );
				unset( $path );
			}

			if ( empty( $parsed_url['query'] ) === false ) {
				$arr   = preg_split( '`([&=])`', $parsed_url['query'], -1, PREG_SPLIT_DELIM_CAPTURE );
				$query = '?';
				foreach ( $arr as $var ) {
					if ( '&' === $var || '=' === $var ) {
						$query .= $var;
					}
					else {
						$query .= urlencode( $var );
					}
				}
				$parsed_url['query'] = $query;
				unset( $query );
			}

			if ( empty( $parsed_url['fragment'] ) === false ) {
				$parsed_url['fragment'] = '#' . urlencode( $parsed_url['fragment'] );
			}

			return implode( '', array(
				$parsed_url['scheme'],
				$parsed_url['user'],
				$parsed_url['pass'],
				$parsed_url['host'],
				$parsed_url['port'],
				$parsed_url['path'],
				$parsed_url['query'],
				$parsed_url['fragment'],
			) );
		}
	} /* End of class */

} /* End of class-exists wrapper */
