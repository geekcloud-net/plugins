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
 * Local_File Video SEO Details
 *
 * I.e. try and retrieve details from a locally saved file or attachment
 *
 * Example response format [WP 3.9.2 / GetID3 v 1.9.7-20130705]:
 *
 * Array:
 * (
 *     [lossless (string)] => bool : ( = false )
 *     [bitrate (string)] => int : 76266
 *     [bitrate_mode (string)] => string[3] : �cbr�
 *     [filesize (string)] => int : 388042
 *     [mime_type (string)] => string[14] : �video/x-ms-wmv�
 *     [length (string)] => int : 34
 *     [length_formatted (string)] => string[4] : �0:33�
 *     [width (string)] => int : 320
 *     [height (string)] => int : 240
 *     [fileformat (string)] => string[3] : �asf�
 *     [dataformat (string)] => string[3] : �wmv�
 *     [encoder (string)] => string[21] : �Windows Media Video 9�
 *     [audio (string)] => Array:
 *     (
 *         [codec (string)] => string[21] : �Windows Media Audio 9�
 *         [channels (string)] => int : 1
 *         [sample_rate (string)] => int : 16000
 *         [bitrate (string)] => int : 17396
 *         [bits_per_sample (string)] => int : 16
 *         [dataformat (string)] => string[3] : �wma�
 *         [bitrate_mode (string)] => string[3] : �cbr�
 *         [lossless (string)] => bool : ( = false )
 *         [encoder (string)] => string[21] : �Windows Media Audio 9�
 *         [encoder_options (string)] => string[32] : �16 kbps, 16 kHz, mono 1-pass CBR�
 *         [channelmode (string)] => string[4] : �mono�
 *         [compression_ratio (string)] => float : 0.067953125
 *     )
 * )
 */
if ( ! class_exists( 'WPSEO_Video_Details_Localfile' ) ) {

	/**
	 * Class WPSEO_Video_Details_Local_File
	 *
	 * {@internal This class works slightly different from the other detail retrieval service classes
	 * in that no remote call is done, but that the details are retrieved are file metadata.
	 *
	 * This also means that this class uses a few extra $vid keys to a) pass things to this class and
	 * b) retain the information found.
	 *
	 * The 'maybe_local' (bool) key is used to determine whether to call this class. It would be nicer to
	 * use 'type' = 'localfile' for this to be in line with the other detail retrieval classes, but that
	 * could break existing filters on types 'jwplayer', 'mediaelementjs' etc which have been used up to
	 * now for local files from various sources.
	 *
	 * The 'attachment_id' (int) key is used to refer to local attachment posts.
	 * The 'file_path' (string) key is used to remember the path to the file we determined exists.
	 *
	 * The local property file_url always *has* to be set, of the local properties file_path and
	 * attachment_id only one or the other is expected.}}
	 */
	class WPSEO_Video_Details_Localfile extends WPSEO_Video_Details {

		/**
		 * @var string $file_path File path to a local file, which *may be* a video file (unconfirmed)
		 */
		protected $file_path = '';

		/**
		 * @var string $file_url URL for a local file, which *may be* a video file (unconfirmed)
		 */
		protected $file_url = '';

		/**
		 * @var string $attachment_id Attachment id, which *may be* a video file (unconfirmed)
		 */
		protected $attachment_id = 0;


		/**
		 * Instantiate the class, main routine.
		 *
		 * @param array $vid     The video array with all the data.
		 * @param array $old_vid The video array with all the data of the previous "fetch", if available.
		 *
		 * @return \WPSEO_Video_Details_Localfile
		 */
		public function __construct( $vid, $old_vid = array() ) {
			if ( $this->could_be_local_video_file( $vid ) === true ) {
				parent::__construct( $vid, $old_vid );
			}
			else {
				// @todo [JRF -> Yoast] Why not use (merge with) oldvid data here if available ? The api key might be removed, but old data might still be better than none.
				$this->vid = $vid;
			}
		}


		/**
		 * Determine whether an absolute or relative url is a local file and possibly a video file.
		 *
		 * @param  array $vid  Currently available video info.
		 *
		 * @return bool
		 */
		protected function could_be_local_video_file( $vid ) {
			$is_local = false;

			if ( ! empty( $vid['file_path'] ) ) {
				$this->file_path = $vid['file_path'];
				if ( isset( $vid['url'] ) ) {
					$this->file_url = $vid['url'];
				}
				else {
					$this->file_url = str_replace( ABSPATH, site_url( '/' ), $this->file_path );
				}
				$is_local = true;
			}
			elseif ( ! empty( $vid['attachment_id'] ) ) {
				$this->attachment_id = $vid['attachment_id'];
				$is_local            = true;
			}
			elseif ( isset( $vid['url'] ) && is_string( $vid['url'] ) && $vid['url'] !== '' ) {
				$is_local = $this->is_attachment_or_local_file( $vid['url'] );
			}

			return $is_local;
		}


		/**
		 * Try and determine if a url refers to a local file.
		 *
		 * For relative urls, this method recurses onto itself while trying to find the file with a variety
		 * of absolute versions of the relative url.
		 *
		 * @todo This one could do with some refactoring, but at least got it working ;-)
		 *
		 * @param  string $url The url to test.
		 *
		 * @return bool
		 */
		private function is_attachment_or_local_file( $url ) {
			static $uploads;
			static $site_url;
			static $network_url;
			static $search;
			static $extensions;

			// Set statics.
			if ( ! isset( $uploads ) ) {
				$uploads = wp_upload_dir();
			}
			if ( ! isset( $site_url ) ) {
				$site_url = preg_replace( '`^http[s]?:`', '', site_url() );
			}
			if ( ! isset( $network_url ) ) {
				if ( is_multisite() ) {
					$network_url = preg_replace( '`^http[s]?:`', '', network_site_url() );
				}
				else {
					$network_url = false;
				}
			}
			if ( ! isset( $search ) ) {
				$search = array( $site_url . '/' );
				if ( ! empty( $network_url ) ) {
					$search[] = $network_url . '/';
				}
			}
			if ( ! isset( $extensions ) ) {
				$extensions = explode( '|', WPSEO_Video_Sitemap::$video_ext_pattern );
			}


			/**
			 * Absolute url
			 */
			if ( strpos( $url, 'http' ) === 0 || strpos( $url, '//' ) === 0 ) {

				$is_local = false;

				// Make it protocol relative so we don't have to worry about that.
				$url = preg_replace( '`^http[s]?:`', '', $url );
				$url = rtrim( $url, '\/' );

				// Is this a url on our site/network ?
				if ( strpos( $url, $site_url ) === 0 || ( ! empty( $network_url ) && strpos( $url, $network_url ) === 0 ) ) {
					$parsed_url = WPSEO_Video_Analyse_Post::parse_url( $url );

					if ( $parsed_url['file'] !== '' ) {
						$ext = strrchr( $parsed_url['file'], '.' );

						if ( $ext !== false && in_array( substr( $ext, 1 ), $extensions, true ) ) {
							$base_url = preg_replace( '`^http[s]?:`', '', $uploads['baseurl'] );
							if ( strpos( $url, $base_url ) === 0 ) {
								$this->file_path = str_replace( $base_url, $uploads['basedir'], $url );
							}
							else {
								$this->file_path = str_replace( $search, ABSPATH, $url );
							}

							if ( file_exists( $this->file_path ) ) {
								$this->file_url = 'http:' . $url;
								$is_local       = true;
							}
						}
						elseif ( $ext === false ) {
							/*
							 * {@internal At some point in the future we may want to switch this over to the
							 * attachment_url_to_postid( $url ) function which is introduced in WP 4.0.}}
							 */
							$path_parts = explode( '/', trim( $parsed_url['path'], '\/' ) );
							$last_bit   = array_pop( $path_parts );
							$query_arg  = array(
								'post_status' => 'any',
								'post_type'   => 'attachment',
								'name'        => $last_bit,
							);
							$query      = new WP_Query( $query_arg );

							if ( $query->post_count === 1 ) {
								$this->attachment_id = $query->post->ID;
								$is_local            = true;
							}
							else {
								// Last ditch effort - can we find the file if we add an extension?
								$base_url = preg_replace( '`^http[s]?:`', '', $uploads['baseurl'] );
								if ( strpos( $url, $base_url ) === 0 ) {
									$file_path = str_replace( $base_url, $uploads['basedir'], $url );
								}
								else {
									$file_path = str_replace( $search, ABSPATH, $url );
								}

								foreach ( $extensions as $extension ) {
									if ( file_exists( $file_path . '.' . $extension ) ) {
										$this->file_path = $file_path . '.' . $extension;
										$this->file_url  = 'http:' . $url . '.' . $extension;
										$is_local        = true;
										break;
									}
								}
							}
						}
					}
					elseif ( $parsed_url['query'] !== '' ) {
						parse_str( $parsed_url['query'], $query );
						if ( ! empty( $query['attachment_id'] ) ) {
							$post_id = $query['attachment_id'];
						}
						elseif ( ! empty( $query['p'] ) ) {
							$post_id = $query['p'];
						}

						if ( isset( $post_id ) && get_post_type( $post_id ) === 'attachment' ) {
							$this->attachment_id = $post_id;
							$is_local            = true;
						}
					}
				}
				return $is_local;
			}
			else {
				/**
				 * Relative path - try and see if we can find the absolute url
				 */
				if ( $this->is_attachment_or_local_file( site_url( $url ) ) === true ) {
					return true;
				}
				elseif ( is_multisite() && $this->is_attachment_or_local_file( network_site_url( $url ) ) === true ) {
					return true;
				}
				elseif ( $this->is_attachment_or_local_file( $uploads['baseurl'] . '/' . ltrim( $url, '\/' ) ) === true ) {
					return true;
				}
				elseif ( $this->is_attachment_or_local_file( $uploads['url'] . '/' . ltrim( $url, '\/' ) ) === true ) {
					return true;
				}
				elseif ( $this->is_attachment_or_local_file( content_url( $url ) ) === true ) {
					return true;
				}
				elseif ( $this->is_attachment_or_local_file( get_stylesheet_directory_uri() . '/' . ltrim( $url, '\/' ) ) === true ) {
					return true;
				}
				elseif ( $this->is_attachment_or_local_file( get_template_directory_uri() . '/' . ltrim( $url, '\/' ) ) === true ) {
					return true;
				}
				elseif ( $this->is_attachment_or_local_file( plugins_url( $url ) ) === true ) {
					return true;
				}

				return false;
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


		/**
		 * Retrieve information on a local video via GetID3.
		 *
		 * @uses WPSEO_Video_Details::$remote_url
		 *
		 * @return void|string
		 */
		protected function get_remote_video_info() {
			$response = null;

			if ( ! empty( $this->attachment_id ) ) {
				$response = wp_get_attachment_metadata( $this->attachment_id );
				if ( is_array( $response ) && $response !== array() ) {
					$this->remote_response = $response;
				}
			}
			elseif ( ! empty( $this->file_path ) ) {
				if ( ! function_exists( 'wp_read_video_metadata' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/media.php' );
				}

				$response = wp_read_video_metadata( $this->file_path );

				if ( is_array( $response ) && $response !== array() ) {
					$this->remote_response = $response;
				}
			}
			return $response;
		}


		/**
		 * Check to see if this is really a video.
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			if ( isset( $this->decoded_response['mime_type'] ) && strpos( $this->decoded_response['mime_type'], 'video' ) !== false ) {
				if ( ! empty( $this->attachment_id ) ) {
					if ( empty( $this->file_url ) ) {
						$this->file_url = wp_get_attachment_url( $this->attachment_id );
					}
					if ( empty( $this->file_path ) ) {
						$this->file_path = get_attached_file( $this->attachment_id );
					}
				}
				return true;
			}
			else {
				unset( $this->vid['attachment_id'], $this->vid['file_path'] );
				return false;
			}
		}


		/**
		 * Set video details to their new values
		 */
		protected function put_video_details() {
			// Only save the determined details to the vid array if we're sure it's a video.
			$this->set_file_path();
			$this->set_file_url();
			$this->set_attachment_id();

			parent::put_video_details();
		}



		/**
		 * Set the attachment id
		 */
		protected function set_attachment_id() {
			if ( ! empty( $this->attachment_id ) ) {
				$this->vid['attachment_id'] = $this->attachment_id;
			}
		}


		/**
		 * Set the content location
		 */
		protected function set_content_loc() {
			if ( ! empty( $this->file_url ) ) {
				$this->vid['content_loc'] = $this->file_url;
			}
		}


		/**
		 * Set the video duration
		 *
		 * {@internal In some rare cases this may result in a video time * 1000. This is a GetID3 bug.
		 * The value will in that case be a string, which is why we use length_formatted in that case.}}
		 *
		 * @see https://core.trac.wordpress.org/ticket/29176
		 */
		protected function set_duration() {
			if ( ! empty( $this->decoded_response['length'] ) && ! is_string( $this->decoded_response['length'] ) && $this->decoded_response['length'] > 0 ) {
				$this->vid['duration'] = $this->decoded_response['length'];
			}
			elseif ( ! empty( $this->decoded_response['length_formatted'] ) && $this->decoded_response['length_formatted'] > 0 ) {
				// The presumption is made that no videos longer than 24 hours will be posted.
				$duration = 0;
				$time     = explode( ':', $this->decoded_response['length_formatted'] );
				if ( count( $time ) === 2 ) {
					$duration += $time[1];
					$duration += ( $time[0] * MINUTE_IN_SECONDS );
				}
				elseif ( count( $time ) === 3 ) {
					$duration += $time[2];
					$duration += ( $time[1] * MINUTE_IN_SECONDS );
					$duration += ( $time[0] * HOUR_IN_SECONDS );
				}

				if ( $duration > 0 ) {
					$this->vid['duration'] = $duration;
				}
			}
		}


		/**
		 * Set the file path
		 */
		protected function set_file_path() {
			if ( ! empty( $this->file_path ) ) {
				$this->vid['file_path'] = $this->file_path;
			}
		}


		/**
		 * Set the file url
		 */
		protected function set_file_url() {
			if ( ! empty( $this->file_url ) ) {
				$this->vid['file_url'] = $this->file_url;
			}
		}


		/**
		 * Set the video height
		 */
		protected function set_height() {
			if ( ! empty( $this->decoded_response['height'] ) ) {
				$this->vid['height'] = $this->decoded_response['height'];
			}
		}


		/**
		 * (Don't) Set the player location
		 */
		protected function set_player_loc() {
			return;
		}


		/**
		 * Set the thumbnail location - try and find a local image file for the video
		 */
		protected function set_thumbnail_loc() {
			if ( ! empty( $this->file_path ) && ! empty( $this->file_url ) ) {

				// @todo transform from path to url.
				$img_file = preg_replace( '`\.(' . WPSEO_Video_Sitemap::$video_ext_pattern . ')$`', '', $this->file_path );
				$img_url  = preg_replace( '`\.(' . WPSEO_Video_Sitemap::$video_ext_pattern . ')$`', '', $this->file_url );

				if ( file_exists( $img_file . '.jpg' ) ) {
					$this->vid['thumbnail_loc'] = $img_url . '.jpg';
				}
				elseif ( file_exists( $img_file . '.jpeg' ) ) {
					$this->vid['thumbnail_loc'] = $img_url . '.jpeg';
				}
				elseif ( file_exists( $img_file . '.png' ) ) {
					$this->vid['thumbnail_loc'] = $img_url . '.png';
				}
				elseif ( file_exists( $img_file . '.gif' ) ) {
					$this->vid['thumbnail_loc'] = $img_url . '.gif';
				}
			}
		}


		/**
		 * (Don't) Set the video type - leave as is to prevent filters failing
		 */
		protected function set_type() {
			return;
		}


		/**
		 * Set the video width
		 */
		protected function set_width() {
			if ( ! empty( $this->decoded_response['width'] ) ) {
				$this->vid['width'] = $this->decoded_response['width'];
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
