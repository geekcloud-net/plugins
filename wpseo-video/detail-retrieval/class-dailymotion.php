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
 * Dailymotion Video SEO Details
 *
 * @see http://www.dailymotion.com/doc/api/obj-video.html
 *
 * JSON response format [2014/7/22]:
 * {
 *    "duration":23,
 *    "embed_url":"http:\/\/www.dailymotion.com\/embed\/video\/x1mifuz",
 *    "thumbnail_large_url":"http:\/\/s2.dmcdn.net\/EI2-J\/x240-mFs.jpg",
 *    "views_total":6
 * }
 */
if ( ! class_exists( 'WPSEO_Video_Details_Dailymotion' ) ) {

	/**
	 * Class WPSEO_Video_Details_Dailymotion
	 */
	class WPSEO_Video_Details_Dailymotion extends WPSEO_Video_Details {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`[/\.](?:dai\.ly|dailymotion\.com/(?:embed/)?video)/([^_\?]+)`i';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'https://api.dailymotion.com/video/%s?fields=duration,embed_url,thumbnail_large_url,views_total',
			'replace_key'   => 'id',
			'response_type' => 'json',
		);


		/**
		 * Check if the response is for a video
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			return ( isset( $this->decoded_response ) && ( is_object( $this->decoded_response ) && ! isset( $this->decoded_response->error ) ) );
		}


		/**
		 * Set the video duration
		 */
		protected function set_duration() {
			$this->set_duration_from_json_object();
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->decoded_response->embed_url ) ) {
				$this->vid['player_loc'] = $this->decoded_response->embed_url;
			}
		}


		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
			if ( isset( $this->decoded_response->thumbnail_large_url ) && is_string( $this->decoded_response->thumbnail_large_url ) && $this->decoded_response->thumbnail_large_url !== '' ) {
				$image = $this->make_image_local( $this->decoded_response->thumbnail_large_url );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
			}
		}


		/**
		 * Set the video view count
		 */
		protected function set_view_count() {
			if ( ! empty( $this->decoded_response->views_total ) ) {
				$this->vid['view_count'] = $this->decoded_response->views_total;
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
