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
 * WordPress.tv Video SEO Details
 *
 * JSON response format from this class' own call in the determine_id... method [2014/8/16]:
 * {
 *    "type":"video",
 *    "version":"1.0",
 *    "title":null,
 *    "width":400,
 *    "height":224,
 *    "html":"<embed src=\"\/\/v.wordpress.com\/zHKcIQfo\" type=\"application\/x-shockwave-flash\" width=\"400\" height=\"224\" allowscriptaccess=\"always\" allowfullscreen=\"true\" wmode=\"transparent\"><\/embed>"
 * }
 */
if ( ! class_exists( 'WPSEO_Video_Details_Wordpresstv' ) ) {

	/**
	 * Class WPSEO_Video_Details_Wordpresstv
	 *
	 * Retrieve video details from WordPress.tv (well grab the ID and then use the VideoPress API)
	 */
	class WPSEO_Video_Details_Wordpresstv extends WPSEO_Video_Details_Videopress {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 *
		 * {@internal Is used in a slightly different way than in the other classes - uses a remote call first
		 * to get the url to use this against.}}
		 */
		protected $id_regex = '`v\.wordpress\.com/([^"]+)`i';

		/**
		 * @var	string	Sprintf template to create a url from an id
		 */
		protected $url_template = '//v.wordpress.com/%s';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 * /
		protected $remote_url = array(
			'pattern'       => '',
			'replace_key'   => '',
			'response_type' => '',
		);
		 */


		/**
		 * Retrieve the video id from a known video url via a remote call, then match it based on a regex
		 *
		 * @param  int $match_nr  The captured parenthesized sub-pattern to use from matches. Defaults to 1.
		 */
		protected function determine_video_id_from_url( $match_nr = 1 ) {
			if ( ( is_string( $this->vid['url'] ) && $this->vid['url'] !== '' ) && $this->id_regex !== '' ) {

				$replace_key = $this->vid['url'];
				// Fix protocol-less urls in parameters as the remote get call most often will not work with them.
				if ( strpos( $this->vid['url'], '//' ) === 0 ) {
					$replace_key = 'http:' . $this->vid['url'];
				}

				$url = sprintf( 'http://wordpress.tv/oembed/?url=%s', $replace_key );
				$url = $this->url_encode( $url );

				$response = $this->remote_get( $url );
				if ( is_string( $response ) && $response !== '' && $response !== 'null' ) {

					$response = json_decode( $response );
					if ( is_object( $response ) ) {
						if ( preg_match( $this->id_regex, $response->html, $match ) ) {
							$this->vid['id'] = $match[ $match_nr ];
						}
					}
				}
			}
		}


		/**
		 * Use the "new" post data with the old video data, to prevent the need for an external video
		 * API call when the video hasn't changed.
		 *
		 * Match whether old data can be used on url rather than video id
		 *
		 * @param string $match_on  Array key to use in the $vid array to determine whether or not to use the old data
		 *                          Defaults to 'url' for this implementation.
		 *
		 * @return bool  Whether or not valid old data was found (and used)
		 */
		protected function maybe_use_old_video_data( $match_on = 'url' ) {
			return parent::maybe_use_old_video_data( $match_on );
		}
	} /* End of class */

} /* End of class-exists wrapper */
