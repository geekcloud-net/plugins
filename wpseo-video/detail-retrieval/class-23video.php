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
 * 23video Video SEO Details
 *
 * @see http://www.23video.com/api/photo-list
 *
 * Oembed info is also available on https://yoast.23video.com/oembed?format=json&url=%s
 *
 * JSON response format [2014/08/02] - see below class.
 */
if ( ! class_exists( 'WPSEO_Video_Details_23video' ) ) {

	/**
	 * Class WPSEO_Video_Details_23video
	 */
	class WPSEO_Video_Details_23video extends WPSEO_Video_Details {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`://([a-z0-9]+)\.23video\.com/(?:[^\?]+\?.*?photo(?:_|%5f)id=|(?:[a-z]+/)*)([0-9]+)(?:$|[/#\?])`i';

		/**
		 * @var string  Regular expression to retrieve the permalink part from a known video url
		 */
		protected $permalink_regex = '`://([a-z0-9]+)\.23video\.com/([a-z0-9_-]+)$`i';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'http://videos.23video.com/api/photo/list?format=json&photo_id=%s&video_p=1',
			'replace_key'   => 'id',
			'response_type' => 'json',
		);

		/**
		 * @var	array	Alternate remote url for when the video id is unknown
		 */
		protected $alternate_remote = array(
			'pattern'       => 'http://videos.23video.com/api/photo/list?format=json&search=%s&video_p=1',
			'replace_key'   => 'permalink',
			'response_type' => 'json',
		);


		/**
		 * Instantiate the class, main routine.
		 *
		 * @param array $vid     The video array with all the data.
		 * @param array $old_vid The video array with all the data of the previous "fetch", if available.
		 *
		 * @return \WPSEO_Video_Details_23video
		 */
		public function __construct( $vid, $old_vid = array() ) {
			// @todo Deal with custom domains.
			parent::__construct( $vid, $old_vid );
		}


		/**
		 * Retrieve the video id or the permalink from a known video url based on a regex match
		 *
		 * @param  int $match_nr  The captured parenthesized sub-pattern to use from matches.
		 *
		 * @return void
		 */
		protected function determine_video_id_from_url( $match_nr = 2 ) {
			if ( ( is_string( $this->vid['url'] ) && $this->vid['url'] !== '' ) && $this->id_regex !== '' ) {
				if ( preg_match( $this->id_regex, $this->vid['url'], $match ) ) {
					$this->vid['id']        = $match[ $match_nr ];
					$this->vid['subdomain'] = $match[1];
				}
				elseif ( preg_match( $this->permalink_regex, $this->vid['url'], $match ) ) {
					$this->vid['permalink'] = $match[ $match_nr ];
					$this->vid['subdomain'] = $match[1];
				}
			}
		}


		/**
		 * Retrieve information on a video via a remote API call
		 */
		protected function get_remote_video_info() {
			if ( empty( $this->vid['id'] ) && ! empty( $this->vid['permalink'] ) ) {
				$this->remote_url = $this->alternate_remote;
			}
			if ( ! empty( $this->vid['subdomain'] ) ) {
				$replace                     = '://' . $this->vid['subdomain'] . '.';
				$this->remote_url['pattern'] = str_replace( '://videos.', $replace, $this->remote_url['pattern'] );
			}
			parent::get_remote_video_info();
		}


		/**
		 * Decode a remote response for a number of typical response types
		 */
		protected function decode_remote_video_info() {
			if ( ! empty( $this->remote_response ) ) {
				// Get rid of the 'var visual = ' string before the actual json output.
				$this->remote_response = substr( $this->remote_response, strpos( $this->remote_response, '{' ) );
			}
			parent::decode_remote_video_info();
		}


		/**
		 * Check to see if this is really a video and for the right item.
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			$valid = false;

			if ( ! empty( $this->decoded_response ) ) {

				// Check whether we received a valid response based on how we retrieved it.
				switch ( $this->remote_url['replace_key'] ) {

					case 'id':
						if ( ! empty( $this->decoded_response->photo->photo_id ) && $this->decoded_response->photo->photo_id === $this->vid['id'] ) {
							$valid = true;
						}
						break;

					case 'permalink':
						if ( ! empty( $this->decoded_response->photo->one ) && $this->decoded_response->photo->one === '/' . $this->vid['permalink'] ) {
							$valid = true;
						}
						elseif ( isset( $this->decoded_response->photos ) && is_array( $this->decoded_response->photos ) && $this->decoded_response->photos !== array() ) {
							// Walk through the (first page of the) search results and see if we can find a match.
							foreach ( $this->decoded_response->photos as $photo ) {
								if ( $photo->one === '/' . $this->vid['permalink'] ) {
									$this->decoded_response->photo = $photo;
									$valid                         = true;
									break;
								}
							}
						}
						break;
				}
			}
			return $valid;
		}


		/**
		 * Set video details to their new values
		 */
		protected function put_video_details() {
			$this->set_subdomain();
			parent::put_video_details();
		}


		/**
		 * Set the content location
		 *
		 * {@internal If this is changed to another property, the width/height properties need to change too.
		 * Alternative set could be video_medium_download / video_medium_width / video_medium_height.}}
		 */
		protected function set_content_loc() {
			if ( ! empty( $this->decoded_response->photo->standard_download ) && ! empty( $this->vid['subdomain'] ) ) {
				// Extension-less, could add .mp4, should work most of the time.
				$this->vid['content_loc'] = 'http://' . rawurlencode( $this->vid['subdomain'] ) . '.23video.com' . $this->decoded_response->photo->standard_download;
			}
		}


		/**
		 * Set the video duration
		 */
		protected function set_duration() {
			if ( ! empty( $this->decoded_response->photo->video_length ) ) {
				$this->vid['duration'] = ( $this->decoded_response->photo->video_length );
			}
		}


		/**
		 * Set the video height
		 */
		protected function set_height() {
			if ( ! empty( $this->decoded_response->photo->standard_height ) ) {
				$this->vid['height'] = $this->decoded_response->photo->standard_height;
			}
		}


		/**
		 * Set the video id (as it might not be set - permalink based retrieval)
		 */
		protected function set_id() {
			if ( ! empty( $this->decoded_response->photo->photo_id ) ) {
				$this->vid['id'] = $this->decoded_response->photo->photo_id;
			}
		}


		/**
		 * Set the player location
		 *
		 * {@internal Alternative options:
		 * https://[subdomain].23video.com/v.swf?photo_id=[photo->photo_id]&autoPlay=1
		 * https://[subdomain].23video.com/[photo->tree_id].ihtml?photo_id=[photo->photo_id]&token=[photo->token]&autoPlay=1&defaultQuality=high }}
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['id'] ) && ! empty( $this->vid['subdomain'] ) ) {
				$this->vid['player_loc'] = 'http://' . rawurlencode( $this->vid['subdomain'] ) . '.23video.com/v.ihtml/player.html?source=share&photo_id=' . urlencode( $this->vid['id'] ) . '&autoPlay=0';
			}
		}


		/**
		 * Verify and set the subdomain
		 */
		protected function set_subdomain() {
			if ( ! empty( $this->decoded_response->site->domain ) && $this->decoded_response->site->domain !== $this->vid['subdomain'] . '.23video.com' ) {
				$this->vid['subdomain'] = str_replace( '.23video.com', '', $this->decoded_response->site->domain );
			}
		}


		/**
		 * Set the thumbnail location
		 *
		 * {@internal Possible alternative:
		 * https://[subdomain].23video.com/[photo->tree_id]/[photo->photo_id]/[photo->token]/large }}
		 */
		protected function set_thumbnail_loc() {
			if ( isset( $this->decoded_response->photo->video_frames_download ) && ( is_string( $this->decoded_response->photo->video_frames_download ) && $this->decoded_response->photo->video_frames_download !== '' ) && ! empty( $this->vid['subdomain'] ) ) {
				$url   = 'http://' . rawurlencode( $this->vid['subdomain'] ) . '.23video.com' . $this->decoded_response->photo->video_frames_download;
				$image = $this->make_image_local( $url );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
			}
		}


		/**
		 * Set the video view count
		 */
		protected function set_view_count() {
			if ( ! empty( $this->decoded_response->photo->view_count ) ) {
				$this->vid['view_count'] = $this->decoded_response->photo->view_count;
			}
		}


		/**
		 * Set the video width
		 */
		protected function set_width() {
			if ( ! empty( $this->decoded_response->photo->standard_width ) ) {
				$this->vid['width'] = $this->decoded_response->photo->standard_width;
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
