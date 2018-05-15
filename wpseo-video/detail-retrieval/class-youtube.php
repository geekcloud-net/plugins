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
 * YouTube Video SEO Details
 *
 * @see https://www.youtube.com/yt/dev/
 *
 * Also available: oembed at http://www.youtube.com/oembed?url=%s
 */
if ( ! class_exists( 'WPSEO_Video_Details_Youtube' ) ) {

	/**
	 * Class WPSEO_Video_Details_Youtube
	 */
	class WPSEO_Video_Details_Youtube extends WPSEO_Video_Details {

		/**
		 * @var    string    Sprintf template to create a url from an id
		 */
		protected $url_template = 'http://www.youtube.com/v/%s';

		/**
		 * @var    array    Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'https://www.googleapis.com/youtube/v3/videos?part=snippet,status,statistics,contentDetails,player&id=%1$s&fields=items&key=%2$s',
			'replace_key'   => 'id',
			'response_type' => 'json',
		);

		/**
		 * @var string Google API access key.
		 */
		protected $api_key = 'AIzaSyAAR2WKu1hRt7lE1HWkiAzGVzoodviCxOI';


		/**
		 * Retrieve the video id from a known video url based on a regex match.
		 * Also change the url based on the new video id.
		 *
		 * @param int $match_nr The captured parenthesized sub-pattern to use from matches. Defaults to 1.
		 *
		 * @return void
		 */
		protected function determine_video_id_from_url( $match_nr = 1 ) {
			if ( isset( $this->vid['url'] ) && is_string( $this->vid['url'] ) && $this->vid['url'] !== '' ) {

				$yt_id = WPSEO_Video_Sitemap::$youtube_id_pattern;

				$patterns = array(
					'`youtube\.(?:com|[a-z]{2})/(?:v/|(?:watch)?(?:\?|#!)(?:.*&)?v=)(' . $yt_id . ')`i',
					'`youtube(?:-nocookie)?\.com/(?:embed|v)/(?!videoseries|playlist)(' . $yt_id . ')`i',
					'`https?://youtu\.be/(' . $yt_id . ')`i',
				);

				foreach ( $patterns as $pattern ) {
					if ( preg_match( $pattern, $this->vid['url'], $match ) ) {
						$this->vid['id'] = $match[ $match_nr ];
						break;
					}
				}

				// @todo [JRF => Yoast] shouldn't this be checked against $youtube_id_pattern as well ?
				if ( ( ! isset( $this->vid['id'] ) || empty( $this->vid['id'] ) ) && ! preg_match( '`^(?:http|//)`', $this->vid['url'] ) ) {
					$this->vid['id'] = $this->vid['url'];
				}
			}
		}


		/**
		 * Check if the response is for a video
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			return ( ! empty( $this->decoded_response ) );
		}


		/**
		 * Set the video duration
		 */
		protected function set_duration() {
			if ( ! empty( $this->decoded_response->contentDetails->duration ) ) {
				if ( version_compare( PHP_VERSION, '5.3.0', '>=' ) ) {
					$date = new DateTime( '@0' );
					$date->add( new DateInterval( $this->decoded_response->contentDetails->duration ) );

					$this->vid['duration'] = (int) $date->format( 'U' );
				}
				else {
					if ( preg_match( '`^(?:P)(?:[^T]*)(?:T)?(?:(?P<hour>\d+)H)?(?:(?P<min>\d+)M)?(?:(?P<sec>\d+)S)?$`', $this->decoded_response->contentDetails->duration, $matches ) > 0 ) {
						$seconds = 0;
						if ( ! empty( $matches['hour'] ) ) {
							$seconds += ( $matches['hour'] * 3600 );
						}
						if ( ! empty( $matches['min'] ) ) {
							$seconds += ( $matches['min'] * 60 );
						}
						if ( ! empty( $matches['sec'] ) ) {
							$seconds += $matches['sec'];
						}

						$this->vid['duration'] = $seconds;
					}
				}
			}
		}


		/**
		 * Set the video height
		 */
		protected function set_height() {
			if ( ! empty( $this->decoded_response->player->embedHtml ) &&
				preg_match( '` height="([^"]+)"`i', $this->decoded_response->player->embedHtml, $match )
			) {
				$this->vid['height'] = (int) $match[1];
			}
			else {
				// Fall back to hard-coded default.
				$this->vid['height'] = 390;
			}
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			// Bow out if video is explicitely not embeddable - falls through if embeddable status not available.
			if ( isset( $this->decoded_response->status->embeddable ) && $this->decoded_response->status->embeddable !== true ) {
				return;
			}

			if ( ! empty( $this->decoded_response->player->embedHtml ) &&
				preg_match( '` src="([^"]+)"`i', $this->decoded_response->player->embedHtml, $match )
			) {
				$player_loc = $match[1];
			}
			else {
				// Fall back to hard-coded default.
				$player_loc = '//www.youtube.com/embed/' . rawurlencode( $this->vid['id'] );
			}

			// Add protocol if the resulting player URL would be protocol-less.
			if ( 0 !== strpos( $player_loc, 'http' ) ) {
				$player_loc = 'https:' . $player_loc;
			}

			$this->vid['player_loc'] = $player_loc;
		}


		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
			$formats = array( 'maxres', 'standard', 'high', 'medium', 'default' );

			foreach ( $formats as $format ) {
				if ( ! empty( $this->decoded_response->snippet->thumbnails->$format ) && is_object( $this->decoded_response->snippet->thumbnails->$format ) ) {
					$thumbnail = $this->decoded_response->snippet->thumbnails->$format;
					if ( ! empty( $thumbnail->url ) ) {
						$image = $this->make_image_local( $thumbnail->url );
						if ( is_string( $image ) && $image !== '' ) {
							$this->vid['thumbnail_loc'] = $image;

							return;
						}
					}
				}
			}
		}


		/**
		 * Set the video view count
		 */
		protected function set_view_count() {
			if ( ! empty( $this->decoded_response->statistics->viewCount ) ) {
				$this->vid['view_count'] = $this->decoded_response->statistics->viewCount;
			}
		}


		/**
		 * Set the video width
		 */
		protected function set_width() {
			if ( ! empty( $this->decoded_response->player->embedHtml ) &&
				preg_match( '` width="([^"]+)"`i', $this->decoded_response->player->embedHtml, $match )
			) {
				$this->vid['width'] = (int) $match[1];
			}
			else {
				// Fall back to hard-coded default.
				$this->vid['width'] = 640;
			}
		}


		/**
		 * Extends the parent method. By letting the parent set the response and get the first item afterwards
		 */
		protected function decode_as_json() {
			parent::decode_as_json();

			if ( ! empty( $this->decoded_response->items[0] ) ) {
				$this->decoded_response = $this->decoded_response->items[0];
			}
			else {
				// Reset if no valid data received.
				$this->decoded_response = null;
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
