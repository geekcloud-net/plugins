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
 * Viddler Video SEO Details
 *
 * Full remote response (serialized) format [2014/7/22] - see below class.
 * Also offers oembed support at http://www.viddler.com/oembed/?format=json&url=
 *
 * @see http://developers.viddler.com/documentation/api-v2/
 * @see http://developers.viddler.com/documentation/oembed/
 */
if ( ! class_exists( 'WPSEO_Video_Details_Viddler' ) ) {

	/**
	 * Class WPSEO_Video_Details_Viddler
	 */
	class WPSEO_Video_Details_Viddler extends WPSEO_Video_Details {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`[/\.]viddler\.com/(?:embed|v|player|file)/([a-z0-9]+)(?:$|[/#\?])`i';

		/**
		 * @var	string	Sprintf template to create a url from an id
		 */
		protected $url_template = 'http://www.viddler.com/v/%s';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'http://api.viddler.com/api/v2/viddler.videos.getDetails.php?key=0118093f713643444556524f452f&add_embed_code=1&video_id=%s',
			'replace_key'   => 'id',
			'response_type' => 'serial',
		);

		/**
		 * @var array  The file from the files array which contains the data we need
		 */
		private $video_file = array();


		/**
		 * Use the "new" post data with the old video data, to prevent the need for an external video
		 * API call when the video hasn't changed.
		 *
		 * Match whether old data can be used on video id or on video url if id is not available
		 *
		 * @param string $match_on  Array key to use in the $vid array to determine whether or not to use the old data
		 *                          Defaults to 'url' for this implementation.
		 *
		 * @return bool  Whether or not valid old data was found (and used)
		 */
		protected function maybe_use_old_video_data( $match_on = 'url' ) {
			if ( ( isset( $this->old_vid['id'] ) && isset( $this->vid['id'] ) ) && $this->old_vid['id'] == $this->vid['id'] ) {
				$match_on = 'id';
			}
			return parent::maybe_use_old_video_data( $match_on );
		}


		/**
		 * Retrieve information on a video via a remote API call
		 *
		 * Change the $remote_url parameters if id is not available, before passing off to the parent
		 *
		 * @return void
		 */
		protected function get_remote_video_info() {
			if ( empty( $this->vid['id'] ) && ! empty( $this->vid['url'] ) ) {
				$this->remote_url['pattern']     = str_replace( '&video_id=%s', '&url=%s', $this->remote_url['pattern'] );
				$this->remote_url['replace_key'] = 'url';

			}
			parent::get_remote_video_info();
		}


		/**
		 * Check if the response is for a valid video
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			return ( ! empty( $this->decoded_response['video'] ) && is_array( $this->decoded_response['video'] ) );
		}


		/**
		 * Set video details to their new values (mostly by passing off to the parent method)
		 */
		protected function put_video_details() {
			$this->get_video_file_data();
			parent::put_video_details();
		}


		/**
		 * Retrieve the file we want to use from the remote response
		 */
		protected function get_video_file_data() {
			if ( isset( $this->decoded_response['video']['files'] ) && is_array( $this->decoded_response['video']['files'] ) && $this->decoded_response['video']['files'] !== array() ) {
				foreach ( $this->decoded_response['video']['files'] as $file ) {
					if ( ( isset( $file['ext'] ) && $file['ext'] === 'mp4' ) && ( isset( $file['status'] ) && $file['status'] === 'ready' && isset( $file['url'] ) ) && ( is_string( $file['url'] ) && $file['url'] !== '' ) ) {
						$this->video_file = $file;
						break;
					}
				}
			}
		}


		/**
		 * Set the content location
		 */
		protected function set_content_loc() {
			if ( isset( $this->video_file['url'] ) && ( is_string( $this->video_file['url'] ) && $this->video_file['url'] !== '' ) ) {
				$this->vid['content_loc'] = $this->video_file['url'];
			}
			// @todo needs checking if this gives a valid return value, but don't have enough sample data
			elseif ( ! empty( $this->decoded_response['video']['html5_video_source'] ) ) {
				$this->vid['content_loc'] = $this->decoded_response['video']['html5_video_source'];
			}
		}


		/**
		 * Set the video duration
		 */
		protected function set_duration() {
			if ( ! empty( $this->decoded_response['video']['length'] ) ) {
				$this->vid['duration'] = $this->decoded_response['video']['length'];
			}
		}


		/**
		 * Set the video height
		 */
		protected function set_height() {
			if ( ! empty( $this->video_file['height'] ) ) {
				$this->vid['height'] = $this->video_file['height'];
			}
			elseif ( ! empty( $this->decoded_response['video']['embed_code'] ) && preg_match( '`<(?:iframe|video).*? height="([0-9]+)"`', $this->decoded_response['video']['embed_code'], $match ) ) {
				$this->vid['height'] = $match[1];
			}
		}


		/**
		 * (Re-)Set the video id
		 */
		protected function set_id() {
			if ( ! empty( $this->decoded_response['video']['id'] ) ) {
				$this->vid['id'] = $this->decoded_response['video']['id'];
			}
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = 'http://www.viddler.com/player/' . rawurlencode( $this->vid['id'] ) . '/';
			}
		}


		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
			if ( isset( $this->decoded_response['video']['thumbnail_url'] ) && is_string( $this->decoded_response['video']['thumbnail_url'] ) && $this->decoded_response['video']['thumbnail_url'] !== '' ) {
				$image = $this->make_image_local( $this->decoded_response['video']['thumbnail_url'] );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
			}
		}


		/**
		 * Set the video view count
		 */
		protected function set_view_count() {
			if ( ! empty( $this->decoded_response['video']['view_count'] ) ) {
				$this->vid['view_count'] = $this->decoded_response['video']['view_count'];
			}
		}


		/**
		 * Set the video width
		 */
		protected function set_width() {
			if ( ! empty( $this->video_file['width'] ) ) {
				$this->vid['width'] = $this->video_file['width'];
			}
			elseif ( ! empty( $this->decoded_response['video']['embed_code'] ) && preg_match( '`<(?:iframe|video).*? width="([0-9]+)"`', $this->decoded_response['video']['embed_code'], $match ) ) {
				$this->vid['width'] = $match[1];
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */


/**
 * Remote response (unserialized version of serialized response) format [2014/7/22]:
 *
Array
(
	[video] => Array
		(
			[id] => 1646c55
			[status] => ready
			[author] => cdevroe
			[title] => iPhone macro lens demonstration
			[upload_time] => 1206114091
			[updated_at] => 1206103291
			[made_public_time] => 1206104030
			[length] => 245
			[description] => This video has a better description <a href="http://cdevroe.com/videos/iphone-macrolens-demo/">on my site</a>.
			[age_limit] =>
			[url] => http://www.viddler.com/v/1646c55
			[thumbnail_url] => http://thumbs.cdn-ec.viddler.com/thumbnail_2_1646c55_v1.jpg
			[thumbnail_version] => v1
			[permalink] => http://www.viddler.com/v/1646c55
			[html5_video_source] => http://www.viddler.com/file/1646c55/html5
			[view_count] => 8482
			[impression_count] => 24042
			[favorite] => 0
			[comment_count] => 21
			[tags] => Array
				(
					[0] => Array
						(
							[type] => global
							[text] => Colin Devroe
						)

					[1] => Array
						(
							[type] => global
							[text] => handmade
						)

					[2] => Array
						(
							[type] => global
							[text] => demo
						)

					[3] => Array
						(
							[type] => global
							[text] => apple
						)

					[4] => Array
						(
							[type] => global
							[text] => mobile
						)

					[5] => Array
						(
							[type] => global
							[text] => diy
						)

					[6] => Array
						(
							[type] => global
							[text] => iphone
						)

					[7] => Array
						(
							[type] => global
							[text] => timed
						)

					[8] => Array
						(
							[type] => global
							[text] => macro
						)

					[9] => Array
						(
							[type] => global
							[text] => lens
						)

					[10] => Array
						(
							[type] => global
							[text] => photography
						)

					[11] => Array
						(
							[type] => timed
							[text] => lens
							[offset] => 11390
							[thumbnail_url] => http://thumbs.cdn-ec.viddler.com/tagthumbnail_2_718102594727e1ee.jpg
						)

					[12] => Array
						(
							[type] => timed
							[text] => 66
							[offset] => 1870
							[thumbnail_url] => http://thumbs.cdn-ec.viddler.com/tagthumbnail_2_7186005b4123e1ee.jpg
						)

					[13] => Array
						(
							[type] => timed
							[text] => iphone
							[offset] => 11390
							[thumbnail_url] => http://thumbs.cdn-ec.viddler.com/tagthumbnail_2_718102594725e1ee.jpg
						)

				)

			[embed_code] => <!--[if IE]><object width="545" height="349" id="viddlerOuter-1646c55" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><param name="movie" value="//www.viddler.com/player/1646c55/"><param name="allowScriptAccess" value="always"><param name="allowNetworking" value="all"><param name="allowFullScreen" value="true"><param name="flashVars" value="f=1"><object id="viddlerInner-1646c55"><video id="viddlerVideo-1646c55" src="//www.viddler.com/file/1646c55/html5mobile/" type="video/mp4" width="545" height="307" poster="//www.viddler.com/thumbnail/1646c55/" controls="controls" x-webkit-airplay="allow"></video></object></object><![endif]--> <!--[if !IE]> <!--> <object width="545" height="349" id="viddlerOuter-1646c55" type="application/x-shockwave-flash" data="//www.viddler.com/player/1646c55/"> <param name="movie" value="//www.viddler.com/player/1646c55/"> <param name="allowScriptAccess" value="always"><param name="allowNetworking" value="all"><param name="allowFullScreen" value="true"><param name="flashVars" value="f=1"><object id="viddlerInner-1646c55"> <video id="viddlerVideo-1646c55" src="//www.viddler.com/file/1646c55/html5mobile/" type="video/mp4" width="545" height="307" poster="//www.viddler.com/thumbnail/1646c55/" controls="controls" x-webkit-airplay="allow"></video> </object></object> <!--<![endif]-->
			[player_type] => Array
				(
					[player_type_id] => 1
					[player_type] => full
				)

			[display_aspect_ratio] => 16:9
			[closed_captioning_list] => Array
				(
				)

		)

)
 */
