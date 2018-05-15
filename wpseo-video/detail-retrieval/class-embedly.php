<?php
/**
 * @package    Internals
 * @since      1.8.0
 * @version    1.8.0
 */

// Avoid direct calls to this file.
if ( ! class_exists( 'WPSEO_Video_Sitemap' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/**
 *****************************************************************
 * Embedly Video SEO Details
 *
 * Embedly can also often retrieve details about videos hosted elsewhere if passed a url, see
 * the response example for instance.
 *
 * @see http://embed.ly/docs/embed/api/endpoints/1/oembed
 * @see http://embed.ly/embed/features/providers
 *
 * JSON response format [2014/7/22] for query http://api.embed.ly/1/oembed?url=http://bit.ly/cXVifg&format=json :
 * {
 *    "provider_url": "http://www.youtube.com/",
 *    "description": "On Twitter, @Alyssa_Milano wrote \"GENIUS. Shirtless Old Spice guy replies on Twitter w/ hilarious personalized videos http://tnw.to/16XQ3 /via @Zee\"",
 *    "title": "Re: @Alyssa_Milano 1 | Old Spice",
 *    "url": "http://www.youtube.com/watch?v=-oElH6M_5i4",
 *    "author_name": "Old Spice",
 *    "height": 480,
 *    "thumbnail_width": 480,
 *    "width": 854,
 *    "html": "<iframe class=\"embedly-embed\" src=\"//cdn.embedly.com/widgets/media.html?url=http%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3D-oElH6M_5i4&src=http%3A%2F%2Fwww.youtube.com%2Fembed%2F-oElH6M_5i4%3Ffeature%3Doembed&image=http%3A%2F%2Fi.ytimg.com%2Fvi%2F-oElH6M_5i4%2Fhqdefault.jpg&type=text%2Fhtml&schema=youtube\" width=\"854\" height=\"480\" scrolling=\"no\" frameborder=\"0\" allowfullscreen></iframe>",
 *    "author_url": "http://www.youtube.com/user/OldSpice",
 *    "version": "1.0",
 *    "provider_name": "YouTube",
 *    "thumbnail_url": "http://i.ytimg.com/vi/-oElH6M_5i4/hqdefault.jpg",
 *    "type": "video",
 *    "thumbnail_height": 360
 * }
 */
if ( ! class_exists( 'WPSEO_Video_Details_Embedly' ) ) {

	/**
	 * Class WPSEO_Video_Details_Embedly
	 *
	 * Retrieve details via the embedly service. Can be used for nearly services.
	 */
	class WPSEO_Video_Details_Embedly extends WPSEO_Video_Details_Oembed {

		/**
		 * @var    array    Information on the remote url to use for retrieving the video details
		 * Alternative url: http://api.embed.ly/v1/api/oembed
		 */
		protected $remote_url = array(
			'pattern'       => 'https://api.embed.ly/1/oembed?&url=%s&format=json',
			'replace_key'   => 'url',
			'response_type' => 'json',
		);

		/**
		 * @static
		 * @var string  Embedly API key from saved options
		 */
		protected $api_key;

		/**
		 * @static
		 * @var bool Whether or not we're still getting a response when retrieval is done without API key.
		 *           After a few calls the IP address is cut off for a limited period if you're not using an API key.
		 */
		public static $functional = true;


		/**
		 * Instantiate the class
		 *
		 * Retrieve the Embedly API key if there is any and prevent unnecessary API calls if there
		 * isn't one and Embedly has started to cut off the ip address
		 *
		 * @param array $vid     The video array with all the data.
		 * @param array $old_vid The video array with all the data of the previous "fetch", if available.
		 *
		 * @return \WPSEO_Video_Details_Embedly
		 */
		public function __construct( $vid, $old_vid = array() ) {
			if ( empty( $this->api_key ) ) {
				// Grab Embedly api key if it's set.
				$options = get_option( 'wpseo_video' );
				if ( $options['embedly_api_key'] !== '' ) {
					$this->api_key = $options['embedly_api_key'];
				}
			}
			if ( ! empty( $this->api_key ) ) {
				$this->remote_url['pattern'] .= '&key=' . $this->api_key;
			}

			// Prevent further API calls if the user has been cut off by Embedly.
			if ( self::$functional === true ) {
				parent::__construct( $vid, $old_vid );
			}
			else {
				// @todo [JRF -> Yoast] Why not use (merge with) oldvid data here if available?
				$this->vid = $vid;
			}
		}


		/**
		 * Retrieve information on a video via a remote API call and prevent further API calls
		 * if the user has been cut off.
		 */
		protected function get_remote_video_info() {
			$response = parent::get_remote_video_info();
			if ( ! isset( $this->remote_response ) && intval( wp_remote_retrieve_response_code( $response ) ) === 403 ) {
				// User has been cut off, prevent further calls from this class instance and other instances.
				self::$functional = false;
			}
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->decoded_response->html ) ) {
				$this->decoded_response->html = stripslashes( $this->decoded_response->html );
				if ( preg_match( '` src="([^"]+)"`i', $this->decoded_response->html, $match ) ) {
					$this->vid['player_loc'] = $match[1];
				}
			}
		}


		/**
		 * Set the video type
		 */
		protected function set_type() {
			if ( ! empty( $this->decoded_response->provider_name ) ) {
				// When needed, change service.com to service.
				$provider          = explode( '.', $this->decoded_response->provider_name );
				$this->vid['type'] = strtolower( $provider[0] );
			}
			else {
				parent::set_type();
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
