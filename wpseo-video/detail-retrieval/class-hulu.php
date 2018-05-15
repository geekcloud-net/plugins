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
 * Hulu Video SEO Details
 *
 * {@internal Hulu apparently has a hidden API, but this may disappear at any time.
 * Also, I haven't been able to figure out how to reliably get info on a specific video via
 * the API. Anyways, oembed actually give plenty of info, so we're ok.}}
 *
 * @see http://adammagana.com/2012/09/hulu-hidden-api/
 *
 * JSON response format [2014/7/22]:
 * {
 *    "thumbnail_width":145,
 *    "type":"video",
 *    "provider_url":"http://www.hulu.com/",
 *    "title":"Mon, Jul 21, 2014 (The Daily Show With Jon Stewart)",
 *    "width":512,
 *    "thumbnail_height":80,
 *    "cache_age":3600,
 *    "large_thumbnail_url":"http://ib.huluim.com/video/60420730?size=512x288&caller=h1o&img=i",
 *    "height":296,
 *    "duration":1301.78,
 *    "embed_url":"http://www.hulu.com/embed.html?eid=shSDO1uPk1pslbO5PMUG-A",
 *    "html":"<iframe width=\"512\" height=\"296\" src=\"http://www.hulu.com/embed.html?eid=shSDO1uPk1pslbO5PMUG-A\" frameborder=\"0\" scrolling=\"no\" webkitAllowFullScreen mozallowfullscreen allowfullscreen> </iframe>",
 *    "version":"1.0",
 *    "large_thumbnail_width":512,
 *    "thumbnail_url":"http://ib.huluim.com/video/60420730?size=145x80&caller=h1o&img=i",
 *    "air_date":"Mon Jul 21 00:00:00 UTC 2014",
 *    "large_thumbnail_height":288,
 *    "author_name":"Comedy Central",
 *    "provider_name":"Hulu"
 * }
 */
if ( ! class_exists( 'WPSEO_Video_Details_Hulu' ) ) {

	/**
	 * Class WPSEO_Video_Details_Hulu
	 */
	class WPSEO_Video_Details_Hulu extends WPSEO_Video_Details_Oembed {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`[/\.]hulu\.com/watch/([0-9]+)(?:$|[/#\?])`i';

		/**
		 * @var	string	Sprintf template to create a url from an id
		 */
		protected $url_template = 'http://hulu.com/watch/%s';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'http://www.hulu.com/api/oembed.json?url=%s',
			'replace_key'   => 'url',
			'response_type' => 'json',
		);


		/**
		 * Check if the response is for a valid video - Filter out "404s"
		 *
		 * Hulu does not provide proper 404s, but just returns the details of the first video in
		 * their library if passed in invalid video id in the url.
		 * Test for this known first video to filter out 404s. This will give an issue for someone
		 * embedding this very first video, but that outweights the alternative.
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			$title_404     = 'Cop in a Cage (Kojak)';
			$embed_url_404 = 'http://www.hulu.com/embed.html?eid=VY_l7Yi0kCop3y-NtMAFaA';

			return ( ! empty( $this->decoded_response ) && ( ( ! isset( $this->decoded_response->title ) || $this->decoded_response->title !== $title_404 ) && ( ! isset( $this->decoded_response->embed_url ) || $this->decoded_response->embed_url !== $embed_url_404 ) ) );
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->decoded_response->embed_url ) ) {
				$this->vid['player_loc'] = $this->decoded_response->embed_url;
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
