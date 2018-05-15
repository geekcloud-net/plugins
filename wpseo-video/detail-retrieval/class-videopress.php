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
 * VideoPress Video SEO Details
 *
 * JSON response format from this class' own call [2014/8/16]:
 * {
 *    "manifest_version":"1.5",
 *    "blog_id":5089392,
 *    "post_id":37132,
 *    "title":"Brennen Byrne: Passwords - The Weakest Link in WordPress Security",
 *    "text_direction":"ltr",
 *    "duration":1491,
 *    "language":"en",
 *    "ogv":{
 *        "url":"https:\/\/videos.files.wordpress.com\/zHKcIQfo\/video-4a4789c1c2_fmt1.ogv",
 *        "codecs":"theora, vorbis"
 *    },
 *    "swf":{
 *        "url":"https:\/\/v0.wordpress.com\/player.swf?v=1.03",
 *        "version":"10.0.0",
 *        "vars":{
 *            "guid":"zHKcIQfo",
 *            "isDynamicSeeking":"true"
 *        },
 *        "params":{
 *            "wmode":"direct",
 *            "seamlesstabbing":"true",
 *            "allowfullscreen":"true",
 *            "allowscriptaccess":"always",
 *            "overstretch":"true"
 *        }
 *    },
 *    "js":{
 *        "url":"https:\/\/s0.wp.com\/wp-content\/plugins\/video\/assets\/js\/videopress.js",
 *        "version":"1.11"
 *    },
 *    "skin":{
 *        "background_color":"#000000",
 *        "watermark":"https:\/\/wptv.files.wordpress.com\/2010\/07\/wptv.png"
 *    },
 *    "width":400,
 *    "height":224,
 *    "posterframe":"https:\/\/videos.files.wordpress.com\/zHKcIQfo\/video-4a4789c1c2_scruberthumbnail_1.jpg",
 *    "mp4":{
 *        "url":"https:\/\/videos.files.wordpress.com\/zHKcIQfo\/video-4a4789c1c2_std.mp4",
 *        "codecs":"avc1.64001E, mp4a.40.2",
 *        "format":"std"
 *    }
 * }
 */
if ( ! class_exists( 'WPSEO_Video_Details_Videopress' ) ) {

	/**
	 * Class WPSEO_Video_Details_Videopress
	 */
	class WPSEO_Video_Details_Videopress extends WPSEO_Video_Details {

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'https://v.wordpress.com/data/wordpress.json?guid=%s&domain=',
			'replace_key'   => 'id',
			'response_type' => 'json',
		);


		/**
		 * Instantiate the class
		 *
		 * @param array $vid     The video array with all the data.
		 * @param array $old_vid The video array with all the data of the previous "fetch", if available.
		 *
		 * @return \WPSEO_Video_Details_Videopress
		 */
		public function __construct( $vid, $old_vid = array() ) {
			// Pre-adjust the remote url.
			$host                        = WPSEO_Video_Analyse_Post::wp_parse_url( home_url(), PHP_URL_HOST );
			$this->remote_url['pattern'] = $this->remote_url['pattern'] . $host;

			parent::__construct( $vid, $old_vid );
		}

		/**
		 * Check if the response is for a valid video
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			return ( ! empty( $this->decoded_response->mp4 ) );
		}


		/**
		 * Set the content location
		 */
		protected function set_content_loc() {
			if ( ! empty( $this->decoded_response->mp4->url ) ) {
				$this->vid['content_loc'] = $this->decoded_response->mp4->url;
			}
		}


		/**
		 * Set the video duration
		 */
		protected function set_duration() {
			$this->set_duration_from_json_object();
		}


		/**
		 * Set the video height
		 */
		protected function set_height() {
			$this->set_height_from_json_object();
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				// @todo: check - original had & encoded as &amp; - is this necessary ? Shouldn't we only encode in output context ?
				$this->vid['player_loc'] = $this->url_encode( 'https://v0.wordpress.com/player.swf?v=1.03&guid=' . $this->vid['id'] . '&isDynamicSeeking=true' );
			}
		}


		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
			if ( isset( $this->decoded_response->posterframe ) && is_string( $this->decoded_response->posterframe ) && $this->decoded_response->posterframe !== '' ) {
				$image = $this->make_image_local( $this->decoded_response->posterframe );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
			}
		}


		/**
		 * Set the video width
		 */
		protected function set_width() {
			$this->set_width_from_json_object();
		}
	} /* End of class */

} /* End of class-exists wrapper */
