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
 * The Internet Archive - Archive.org Video SEO Details
 *
 * @see https://archive.org/help/video.php
 * @see http://archive.org/help/json.php
 * @see http://api-portal.anypoint.mulesoft.com/internet-archive/api/internet-archive-json-api/docs/reference
 *
 * JSON response format [2014/7/27] - see below class.
 */
if ( ! class_exists( 'WPSEO_Video_Details_Archiveorg' ) ) {

	/**
	 * Class WPSEO_Video_Details_Archiveorg
	 *
	 * {@internal No $id_regex has been set as both plugins which support archive.org should return an id anyway.
	 * If this changes, this may need looking into.}}
	 */
	class WPSEO_Video_Details_Archiveorg extends WPSEO_Video_Details {

		/**
		 * @var    string    Sprintf template to create a url from an id
		 */
		protected $url_template = 'https://archive.org/details/%s';

		/**
		 * @var    array    Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'https://archive.org/details/%s?output=json',
			'replace_key'   => 'id',
			'response_type' => 'json',
		);

		/**
		 * @var object  The file from the files array which contains most data we need
		 */
		private $video_file;


		/**
		 * Check if the response is for a video
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			return ( ! empty( $this->decoded_response ) && ( ( isset( $this->decoded_response->metadata->mediatype[0] ) && $this->decoded_response->metadata->mediatype[0] === 'movies' ) || ( isset( $this->decoded_response->misc->css ) && $this->decoded_response->misc->css === 'movies' ) ) );
		}


		/**
		 * Set video details to their new values
		 */
		protected function put_video_details() {
			$this->get_video_file_data();
			parent::put_video_details();
		}


		/**
		 * Determine which file in the files array contains the information we need.
		 */
		protected function get_video_file_data() {
			$video_files = array();

			// Get the video files from the files object.
			if ( ! empty( $this->decoded_response->files ) ) {
				foreach ( $this->decoded_response->files as $key => $value ) {
					if ( preg_match( '`\.(' . WPSEO_Video_Sitemap::$video_ext_pattern . ')$`', $key, $match ) ) {
						$video_files[ $match[1] ] = $value;

						// Strip off the '/' at the start.
						$video_files[ $match[1] ]->file_name = substr( $key, 1 );
					}
				}
			}

			// Find a file with enriched data.
			if ( $video_files !== array() ) {
				// Preferred extensions (sort of) in order of preference.
				$video_exts = explode( '|', WPSEO_Video_Sitemap::$video_ext_pattern );

				foreach ( $video_exts as $ext ) {
					if ( isset( $video_files[ $ext ] ) ) {
						if ( ! isset( $this->video_file ) ) {
							// Set to the file with the first matched (most preferred) extension.
							$this->video_file = $video_files[ $ext ];

							if ( ( ! empty( $video_files[ $ext ]->length ) || ! empty( $this->decoded_response->metadata->runtime[0] ) ) && ( ! empty( $video_files[ $ext ]->height ) || ! empty( $this->decoded_response->metadata->width[0] ) ) && ( ! empty( $video_files[ $ext ]->width ) || ! empty( $this->decoded_response->metadata->height[0] ) ) ) {
								// We got all the data we need.
								break;
							}
						}
						else {
							if ( empty( $this->video_file->length ) && ! empty( $video_files[ $ext ]->length ) ) {
								$this->video_file->length = $video_files[ $ext ]->length;
							}

							if ( empty( $this->video_file->width ) && empty( $this->video_file->height ) ) {
								if ( ! empty( $video_files[ $ext ]->width ) ) {
									$this->video_file->width = $video_files[ $ext ]->width;
								}
								if ( ! empty( $video_files[ $ext ]->height ) ) {
									$this->video_file->height = $video_files[ $ext ]->height;
								}
							}

							if ( ! empty( $this->video_file->length ) && ( ! empty( $this->video_file->width ) || ! empty( $this->video_file->height ) ) ) {
								// We have as much data as we can have.
								break;
							}
						}
					}
				}
			}
		}


		/**
		 * Set the content location
		 */
		protected function set_content_loc() {
			if ( ! empty( $this->vid['id'] ) && ! empty( $this->video_file->file_name ) ) {
				$this->vid['content_loc'] = sprintf(
					'https://archive.org/download/%s/%s',
					rawurlencode( $this->vid['id'] ),
					rawurlencode( $this->video_file->file_name )
				);
			}
		}


		/**
		 * Set the video duration
		 */
		protected function set_duration() {
			if ( ! empty( $this->decoded_response->metadata->runtime[0] ) ) {
				// 31 seconds
				$this->vid['duration'] = str_replace( ' seconds', '', $this->decoded_response->metadata->runtime[0] );
			}
			elseif ( ! empty( $this->video_file->length ) ) {
				$this->vid['duration'] = $this->video_file->length;
			}
		}


		/**
		 * Set the video height
		 */
		protected function set_height() {
			if ( ! empty( $this->decoded_response->metadata->height[0] ) ) {
				$this->vid['height'] = $this->decoded_response->metadata->height[0];
			}
			elseif ( ! empty( $this->video_file->height ) ) {
				$this->vid['height'] = $this->video_file->height;
			}
		}


		/**
		 * Set the video id
		 */
		protected function set_id() {
			if ( ! empty( $this->decoded_response->metadata->identifier[0] ) ) {
				$this->vid['id'] = $this->decoded_response->metadata->identifier[0];
			}
		}

		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = 'https://archive.org/embed/' . rawurlencode( $this->vid['id'] );
			}
		}


		/**
		 * Set the thumbnail location
		 *
		 * @todo decide whether the order is correct - should the thumb permalink be tried first or the misc image ?
		 */
		protected function set_thumbnail_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$image_url = sprintf( 'https://archive.org/download/%s/format=Thumbnail', $this->vid['id'] );
				$image     = $this->make_image_local( $image_url );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
				elseif ( isset( $this->decoded_response->misc->image ) && ( is_string( $this->decoded_response->misc->image ) && $this->decoded_response->misc->image !== '' ) ) {
					$image = $this->make_image_local( $this->decoded_response->misc->image );
					if ( is_string( $image ) && $image !== '' ) {
						$this->vid['thumbnail_loc'] = $image;
					}
				}
			}
		}


		/**
		 * Set the video view count
		 *
		 * @todo [JRF -> Yoast] is using the download count acceptable here ?
		 */
		protected function set_view_count() {
			if ( ! empty( $this->decoded_response->item->downloads ) ) {
				$this->vid['view_count'] = $this->decoded_response->item->downloads;
			}
		}


		/**
		 * Set the video width
		 */
		protected function set_width() {
			if ( ! empty( $this->decoded_response->metadata->width[0] ) ) {
				$this->vid['width'] = $this->decoded_response->metadata->width[0];
			}
			elseif ( ! empty( $this->video_file->width ) ) {
				$this->vid['width'] = $this->video_file->width;
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
