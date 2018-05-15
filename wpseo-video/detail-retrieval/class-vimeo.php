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
 * Vimeo Video SEO Details
 *
 * @see https://developer.vimeo.com/
 *
 * JSON response format [2014/7/22]:
 * {
 *    "id":81276708,
 *    "title":"MotoX off-road training by Frans Verhoeven",
 *    "description":"MotoX off-road training door Frans Verhoeven - 5 juli 2013",
 *    "url":"http:\/\/vimeo.com\/81276708",
 *    "upload_date":"2013-12-07 12:25:34",
 *    "mobile_url":"http:\/\/vimeo.com\/m\/81276708",
 *    "thumbnail_small":"http:\/\/i.vimeocdn.com\/video\/457375046_100x75.jpg",
 *    "thumbnail_medium":"http:\/\/i.vimeocdn.com\/video\/457375046_200x150.jpg",
 *    "thumbnail_large":"http:\/\/i.vimeocdn.com\/video\/457375046_640.jpg",
 *    "user_id":3329492,"user_name":"Mars Publishers",
 *    "user_url":"http:\/\/vimeo.com\/marspublishers",
 *    "user_portrait_small":"http:\/\/i.vimeocdn.com\/portrait\/7555976_30x30.jpg",
 *    "user_portrait_medium":"http:\/\/i.vimeocdn.com\/portrait\/7555976_75x75.jpg",
 *    "user_portrait_large":"http:\/\/i.vimeocdn.com\/portrait\/7555976_100x100.jpg",
 *    "user_portrait_huge":"http:\/\/i.vimeocdn.com\/portrait\/7555976_300x300.jpg",
 *    "stats_number_of_likes":0,
 *    "stats_number_of_plays":85,
 *    "stats_number_of_comments":0,
 *    "duration":220,
 *    "width":1280,
 *    "height":720,
 *    "tags":"motox, frans verhoeven, enduro, offroad, off-road, ktm, yamaha",
 *    "embed_privacy":"approved"
 * }
 *
 * Oembed response format [2016/11/01]:
 * {
 *     "type":"video",
 *     "version":"1.0",
 *     "provider_name":"Vimeo",
 *     "provider_url":"https:\/\/vimeo.com\/",
 *     "title":"Pro 110 Industrial FTS",
 *     "author_name":"Gas Trailer",
 *     "author_url":"https:\/\/vimeo.com\/gastrailer",
 *     "is_plus":"0",
 *     "html":"<iframe src=\"https:\/\/player.vimeo.com\/video\/101410103\" width=\"480\" height=\"270\" frameborder=\"0\" title=\"Pro 110 Industrial FTS\" webkitallowfullscreen mozallowfullscreen allowfullscreen><\/iframe>",
 *     "width":480,
 *     "height":270,
 *     "duration":332,
 *     "description":"Built off ...",
 *     "thumbnail_url":"https:\/\/i.vimeocdn.com\/video\/487915832_295x166.jpg",
 *     "thumbnail_width":295,
 *     "thumbnail_height":166,
 *     "thumbnail_url_with_play_button":"https:\/\/i.vimeocdn.com\/filter\/overlay?src0=https%3A%2F%2Fi.vimeocdn.com%2Fvideo%2F487915832_295x166.jpg&src1=http%3A%2F%2Ff.vimeocdn.com%2Fp%2Fimages%2Fcrawler_play.png",
 *     "upload_date":"2014-07-22 10:32:07",
 *     "video_id":101410103,
 *     "uri":"\/videos\/101410103"
 * }
 */
if ( ! class_exists( 'WPSEO_Video_Details_Vimeo' ) ) {

	/**
	 * Class WPSEO_Video_Details_Vimeo
	 */
	class WPSEO_Video_Details_Vimeo extends WPSEO_Video_Details {

		/**
		 * @var	string	Sprintf template to create a url from an id
		 */
		protected $url_template = 'https://vimeo.com/%s';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'https://vimeo.com/api/v2/video/%s.json',
			'replace_key'   => 'id',
			'response_type' => 'json',
		);

		/**
		 * @var	array	Alternate remote url (oembed) to use for private videos where the
		 *              API will refuse to provide information.
		 */
		protected $alternate_remote = array(
			'pattern'       => 'https://vimeo.com/api/oembed.json?url=http://vimeo.com/%s',
			'replace_key'   => 'id',
			'response_type' => 'json',
		);


		/**
		 * Retrieve the video id from a known video url based on a regex match.
		 *
		 * @param int $match_nr The captured parenthesized sub-pattern to use from matches. Defaults to 1.
		 *
		 * @return void
		 */
		protected function determine_video_id_from_url( $match_nr = 1 ) {
			if ( isset( $this->vid['url'] ) && is_string( $this->vid['url'] ) && $this->vid['url'] !== '' ) {
				if ( preg_match( '`vimeo\.com/(?:(?:m|video|channels|groups)/(?:[a-z0-9]+/)*)?([0-9]+)(?:$|[/#\?])`i', $this->vid['url'], $match ) ) {
					$this->vid['id'] = $match[ $match_nr ];
				}
				elseif ( preg_match( '`vimeo\.com/moogaloop\.swf\?clip_id=([^&]+)`i', $this->vid['url'], $match ) ) {
					$this->vid['id'] = $match[ $match_nr ];
				}
				elseif ( preg_match( '`player\.vimeo\.com/(?:video|external)/([0-9]+)`i', $this->vid['url'], $match ) ) {
					$this->vid['id'] = $match[ $match_nr ];
				}
			}
		}


		/**
		 * Retrieve information on a video via a remote API call.
		 *
		 * Deal with private videos separately.
		 *
		 * @since 3.9.0
		 */
		protected function get_remote_video_info() {
			parent::get_remote_video_info();

			if ( ! isset( $this->remote_response ) ) {
				// If no valid response was received, this may be a private video.
				// Try again using oembed as that will still yield most of the needed information.
				$this->remote_url = $this->alternate_remote;
				parent::get_remote_video_info();
			}
		}


		/**
		 * Decode a remote response as json
		 */
		protected function decode_as_json() {
			$response = json_decode( $this->remote_response );
			// API response.
			if ( is_array( $response ) && ! empty( $response[0] ) && is_object( $response[0] ) ) {
				$this->decoded_response = $response[0];
			}
			// Oembed response.
			elseif ( is_object( $response ) ) {
				$this->decoded_response = $response;
			}
		}


		/**
		 * Check to see if this is really a video.
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			// All valid video responses will have a duration.
			return ( ! empty( $this->decoded_response->duration ) );
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
				$this->vid['player_loc'] = 'https://player.vimeo.com/video/' . rawurlencode( $this->vid['id'] );
			}
		}


		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
			if ( isset( $this->decoded_response->thumbnail_large ) && is_string( $this->decoded_response->thumbnail_large ) && $this->decoded_response->thumbnail_large !== '' ) {
				$image = $this->make_image_local( $this->decoded_response->thumbnail_large );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
			}
			else {
				// Oembed fallback.
				$this->set_thumbnail_loc_from_json_object();
			}
		}


		/**
		 * Set the video width
		 */
		protected function set_width() {
			$this->set_width_from_json_object();
		}


		/**
		 * Set the video view count
		 *
		 * Property only available via full API call.
		 */
		protected function set_view_count() {
			if ( ! empty( $this->decoded_response->stats_number_of_plays ) ) {
				$this->vid['view_count'] = $this->decoded_response->stats_number_of_plays;
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
