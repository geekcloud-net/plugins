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
 * TED Talks Video SEO Details
 *
 * Full API is in limited beta and at this moment (2014-07-22), the page to apply for an API-key is 404-ing.
 *
 * @see http://developer.ted.com/API_Docs
 * @see http://developer.ted.com/io-docs
 *
 * JSON response format [2014/7/22]:
 * {
 *    "type":"video",
 *    "version":"1.0",
 *    "html":"<iframe src=\"http:\/\/embed.ted.com\/talks\/jill_bolte_taylor_s_powerful_stroke_of_insight.html\" width=\"560\" height=\"315\" frameborder=\"0\" scrolling=\"no\" webkitAllowFullScreen mozallowfullscreen allowFullScreen><\/iframe>",
 *    "width":560,
 *    "height":315,
 *    "title":"Jill Bolte Taylor: My stroke of insight",
 *    "url":"http:\/\/www.ted.com\/talks\/jill_bolte_taylor_s_powerful_stroke_of_insight",
 *    "author_name":"Jill Bolte Taylor",
 *    "author_url":"http:\/\/www.ted.com\/speakers\/jill_bolte_taylor",
 *    "provider_name":"TED",
 *    "provider_url":"http:\/\/ted.com",
 *    "thumbnail_url":"http:\/\/images.ted.com\/images\/ted\/e86e4fdeedbff174a70b8e80f6c3ebe12b9e9cfa_480x360.jpg?lang=en",
 *    "thumbnail_width":"480",
 *    "thumbnail_height":"360"
 * }
 */
if ( ! class_exists( 'WPSEO_Video_Details_Ted' ) ) {

	/**
	 * Class WPSEO_Video_Details_Ted
	 */
	class WPSEO_Video_Details_Ted extends WPSEO_Video_Details_Oembed {

		/**
		 * @var    string    Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`[/\.]ted\.com/talks/([a-z0-9_-]+)(?:$|\.html|[/#\?])`i';

		/**
		 * @var    string    Regular expression to retrieve a numeric video id from a known video short url
		 */
		protected $short_id_regex = '`[/\.]ted\.com/talks/view/id/(.+?)(?:$|/)`i';

		/**
		 * @var    string    Sprintf template to create a url from an id
		 */
		protected $url_template = 'http://www.ted.com/talks/%s.html';

		/**
		 * @var    string    Sprintf template to create a url from an id
		 */
		protected $short_id_url_template = 'http://www.ted.com/talks/view/id/%s';

		/**
		 * @var    array    Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'http://www.ted.com/talks/oembed.json?url=%s',
			'replace_key'   => 'url',
			'response_type' => 'json',
		);


		/**
		 * Deal with potentially wrong ids from short id url format and instantiate the class
		 *
		 * @param array $vid     The video array with all the data.
		 * @param array $old_vid The video array with all the data of the previous "fetch", if available.
		 *
		 * @return \WPSEO_Video_Details_Ted
		 */
		public function __construct( $vid, $old_vid = array() ) {
			// Check for wrongly set short id as id.
			if ( ! empty( $vid['id'] ) && preg_match( '`^[0-9]+$`', $vid['id'] ) ) {
				$vid['short_id'] = $vid['id'];
				unset( $vid['id'] );
			}
			parent::__construct( $vid, $old_vid );
		}


		/**
		 * Retrieve the video id or short id from a known video url based on a regex match
		 *
		 * @uses WPSEO_Video_Details_Ted::$id_regex
		 * @uses WPSEO_Video_Details_Ted::$short_id_regex
		 *
		 * @param  int $match_nr The captured parenthesized sub-pattern to use from matches. Defaults to 1.
		 *
		 * @return void
		 */
		protected function determine_video_id_from_url( $match_nr = 1 ) {
			$this->determine_id_from_url( $this->vid['url'] );
		}


		/**
		 * Retrieve the video id or short id from a known video url based on a regex match
		 *
		 * @uses WPSEO_Video_Details_Ted::$id_regex
		 * @uses WPSEO_Video_Details_Ted::$short_id_regex
		 *
		 * @param  string $url      The video url.
		 * @param  int    $match_nr The captured parenthesized sub-pattern to use from matches. Defaults to 1.
		 *
		 * @return void
		 */
		private function determine_id_from_url( $url, $match_nr = 1 ) {
			if ( is_string( $url ) && $url !== '' ) {
				// Check for short id form first as the normal regex would match on the 'view' bit.
				if ( preg_match( $this->short_id_regex, $url, $match ) ) {
					$this->vid['short_id'] = $match[ $match_nr ];
				}
				elseif ( preg_match( $this->id_regex, $url, $match ) ) {
					$this->vid['id'] = $match[ $match_nr ];
				}
			}
		}


		/**
		 * Create a video url based on a known video id and url template
		 *
		 * @uses WPSEO_Video_Details_Ted::$url_template
		 * @uses WPSEO_Video_Details_Ted::$short_id_url_template
		 *
		 * @return void
		 */
		protected function determine_video_url_from_id() {
			if ( ! empty( $this->vid['id'] ) && $this->url_template !== '' ) {
				$this->vid['url'] = sprintf( $this->url_template, $this->vid['id'] );
			}
			elseif ( ! empty( $this->vid['short_id'] ) && $this->short_id_url_template !== '' ) {
				$this->vid['url'] = sprintf( $this->short_id_url_template, $this->vid['short_id'] );
			}
		}


		/**
		 * Set the player location
		 *
		 * @todo - verify if this is the correct setting for content_loc
		 */
		protected function set_content_loc() {
			if ( ! empty( $this->decoded_response->url ) ) {
				$this->vid['content_loc'] = $this->decoded_response->url;
			}
		}


		/**
		 * Set the video id
		 */
		protected function set_id() {
			if ( ! empty( $this->decoded_response->url ) ) {
				$this->determine_id_from_url( $this->decoded_response->url );
			}
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = 'http://embed.ted.com/talks/' . rawurlencode( $this->vid['id'] ) . '.html';
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
