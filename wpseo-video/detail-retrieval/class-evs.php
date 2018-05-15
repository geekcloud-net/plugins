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
 * Easy Video Suite (EVS) Video SEO Details
 */
if ( ! class_exists( 'WPSEO_Video_Details_Evs' ) ) {

	/**
	 * @todo Add other evs metadata: player_loc, content_loc, duration, width, height.
	 */
	class WPSEO_Video_Details_Evs extends WPSEO_Video_Details {

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'response_type' => 'json',
		);

		/**
		 * @var string EVS location
		 */
		protected $evs_location;


		/**
		 * Instantiate the class
		 *
		 * Retrieve the EVS location and only pass of to the parent constructor if we find one
		 *
		 * @param array $vid     The video array with all the data.
		 * @param array $old_vid The video array with all the data of the previous "fetch", if available.
		 *
		 * @return \WPSEO_Video_Details_Evs
		 */
		public function __construct( $vid, $old_vid = array() ) {
			$this->evs_location = get_option( 'evs_location' );

			if ( $this->evs_location ) {
				parent::__construct( $vid, $old_vid );
			}
			else {
				/*
				 * @todo [JRF -> Yoast] Why not use (merge with) oldvid data here if available?
				 * The api key might be removed, but old data might still be better than none.
				 */
				$this->vid = $vid;
			}
		}

		/**
		 * Set the video id to a known video url.
		 *
		 * @param  int $match_nr [Not used in this implementation].
		 *
		 * @return void
		 */
		protected function determine_video_id_from_url( $match_nr = 1 ) {
			if ( ! empty( $this->vid['url'] ) ) {
				$this->vid['id'] = $this->vid['url'];
			}
		}


		/**
		 * Retrieve information on a video via a remote API call
		 *
		 * @return void
		 */
		protected function get_remote_video_info() {
			if ( ! empty( $this->vid['id'] ) ) {
				// Retrieve evs thumbnail info.
				$api      = $this->evs_location . '/api.php';
				$response = wp_remote_post(
					$api,
					array(
						'method'      => 'POST',
						'timeout'     => 45,
						'redirection' => 5,
						'httpversion' => '1.0',
						'blocking'    => true,
						'headers'     => array(),
						'cookies'     => array(),
						'body'        => array(
							'page_url' => $this->vid['id'],
							'method'   => 'public-file-images',
						),
					)
				);

				if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
					$this->remote_response = $response['body'];
				}
			}
		}


		/**
		 * Set the content location
		 */
		protected function set_content_loc() {
			if ( ! empty( $this->vid['url'] ) ) {
				$this->vid['content_loc'] = $this->vid['url'];
			}
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['url'] ) ) {
				$this->vid['player_loc'] = $this->vid['url'];
			}
		}


		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
			if ( ( isset( $this->decoded_response->success ) && $this->decoded_response->success === true ) && ! empty( $this->decoded_response->thumbnail ) ) {
				$image = $this->make_image_local( $this->decoded_response->thumbnail );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
