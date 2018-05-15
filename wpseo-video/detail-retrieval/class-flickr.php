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
 * Flickr Video SEO Details
 *
 * JSON response format [2014/7/22] - see below class.
 *
 * @todo - maybe add width/height methods ?
 */
if ( ! class_exists( 'WPSEO_Video_Details_Flickr' ) ) {

	/**
	 * Class WPSEO_Video_Details_Flickr
	 */
	class WPSEO_Video_Details_Flickr extends WPSEO_Video_Details {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`/[0-9]+(?:@N[0-9]+)?/([0-9]+)`';

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video short url
		 *              Example url: https://flic.kr/p/mRVcwc
		 */
		protected $short_id_regex = '`[/\.]flic\.kr/p/([a-z0-9_-]+)(?:$|[/#\?])`i';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 *              API call will work with with both full numeric ids as well as short ids
		 */
		protected $remote_url = array(
			'pattern'       => 'https://api.flickr.com/services/rest/?method=flickr.photos.getInfo&api_key=2d2985adb59d21e6933368e41e5ca3b0&photo_id=%s&format=json&nojsoncallback=1',
			'replace_key'   => 'id',
			'response_type' => 'json',
		);


		/**
		 * Deal with potentially wrong ids from short url format and instantiate the class
		 *
		 * @param array $vid     The video array with all the data.
		 * @param array $old_vid The video array with all the data of the previous "fetch", if available.
		 *
		 * @return \WPSEO_Video_Details_Flickr
		 */
		public function __construct( $vid, $old_vid = array() ) {
			// Check for wrongly set short id as id.
			if ( ! empty( $vid['id'] ) && ! preg_match( '`^[0-9]+$`', $vid['id'] ) ) {
				$vid['short_id'] = $vid['id'];
				unset( $vid['id'] );
			}
			// Make sure we use the short id if it's available and there's no id.
			if ( empty( $vid['id'] ) && ! empty( $vid['short_id'] ) ) {
				$this->remote_url['replace_key'] = 'short_id';
			}

			parent::__construct( $vid, $old_vid );
		}


		/**
		 * Retrieve the video id or short id from a known video url based on a regex match
		 *
		 * @uses WPSEO_Video_Details_Flickr::$id_regex
		 * @uses WPSEO_Video_Details_Flickr::$short_id_regex
		 *
		 * @param  int $match_nr  The captured parenthesized sub-pattern to use from matches. Defaults to 1.
		 *
		 * @return void
		 */
		protected function determine_video_id_from_url( $match_nr = 1 ) {
			if ( is_string( $this->vid['url'] ) && $this->vid['url'] !== '' ) {
				if ( preg_match( $this->id_regex, $this->vid['url'], $match ) ) {
					$this->vid['id'] = $match[ $match_nr ];
				}
				elseif ( preg_match( $this->short_id_regex, $this->vid['url'], $match ) ) {
					$this->vid['short_id']           = $match[ $match_nr ];
					$this->remote_url['replace_key'] = 'short_id';
				}
			}
		}


		/**
		 * Check if the response is for a video
		 *
		 * @return bool
		 */
		protected function is_video_response() {
			return ( ! empty( $this->decoded_response ) && isset( $this->decoded_response->photo->media ) && $this->decoded_response->photo->media === 'video' );
		}


		/**
		 * Set the video duration
		 */
		protected function set_duration() {
			if ( ! empty( $this->decoded_response->photo->video->duration ) ) {
				$this->vid['duration'] = $this->decoded_response->photo->video->duration;
			}
		}


		/**
		 * Set the video height
		 */
		protected function set_height() {
			if ( ! empty( $this->decoded_response->photo->video->height ) ) {
				$this->vid['height'] = $this->decoded_response->photo->video->height;
			}
		}


		/**
		 * Set the video id
		 */
		protected function set_id() {
			if ( ! empty( $this->decoded_response->photo->id ) ) {
				$this->vid['id'] = $this->decoded_response->photo->id;
			}
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->decoded_response->photo->secret ) && ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = $this->url_encode( 'http://www.flickr.com/apps/video/stewart.swf?v=109786&intl_lang=en_us&photo_secret=' . $this->decoded_response->photo->secret . '&photo_id=' . $this->vid['id'] );
			}
		}


		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
			if ( ( ! empty( $this->decoded_response->photo->farm ) && ! empty( $this->decoded_response->photo->server ) )
				&& ( ! empty( $this->decoded_response->photo->secret ) && ! empty( $this->vid['id'] ) ) ) {

				$url   = 'http://farm' . $this->decoded_response->photo->farm . '.staticflickr.com/' .
							$this->decoded_response->photo->server . '/' .
							$this->vid['id'] . '_' . $this->decoded_response->photo->secret . '.jpg';
				$url   = $this->url_encode( $url );
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
			if ( ! empty( $this->decoded_response->photo->views ) ) {
				$this->vid['view_count'] = $this->decoded_response->photo->views;
			}
		}


		/**
		 * Set the video width
		 */
		protected function set_width() {
			if ( ! empty( $this->decoded_response->photo->video->width ) ) {
				$this->vid['width'] = $this->decoded_response->photo->video->width;
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */

/**
 * Full JSON response format [2014/7/22]:
 * {
 *    "photo":
 *    {
 *        "id":"3989150138",
 *        "secret":"de7a31ba19",
 *        "server":"2624",
 *        "farm":3,
 *        "dateuploaded":"1254882213",
 *        "isfavorite":0,
 *        "license":"0",
 *        "safety_level":"0",
 *        "rotation":0,
 *        "originalsecret":"eb04f37e5a",
 *        "originalformat":"jpg",
 *        "owner":
 *        {
 *            "nsid":"36587311@N08",
 *            "username":"\u25baCubaGallery",
 *            "realname":"Cuba Gallery",
 *            "location":"Auckland, New Zealand",
 *            "iconserver":"3705",
 *            "iconfarm":4,
 *            "path_alias":"cubagallery"
 *        },
 *        "title":
 *        {
 *            "_content":"Lightroom Tutorial Video"
 *        },
 *        "description":
 *        {
 *            "_content":"<b>Lightroom Tutorial Video: <\/b>Yes there is more on this but I'm not allowed to even whisper where it could be! :) Shh Check out my Lightroom blog for <b>more before &amp; after shots.<\/b> The link is on my profile page.\n\n<a href=\"http:\/\/cubagallery.tumblr.com\" rel=\"nofollow\"> <b>\u25ba Follow me on Tumblr<\/b><\/a>"
 *        },
 *        "visibility":
 *        {
 *            "ispublic":1,
 *            "isfriend":0,
 *            "isfamily":0
 *        },
 *        "dates":
 *        {
 *            "posted":"1254882213",
 *            "taken":"2009-10-06 19:23:33",
 *            "takengranularity":"0",
 *            "lastupdate":"1342897955"
 *        },
 *        "views":"42692",
 *        "editability":
 *        {
 *            "cancomment":0,
 *            "canaddmeta":0
 *        },
 *        "publiceditability":
 *        {
 *            "cancomment":1,
 *            "canaddmeta":0
 *        },
 *        "usage":
 *        {
 *            "candownload":1,
 *            "canblog":0,
 *            "canprint":0,
 *            "canshare":1
 *        },
 *        "comments":
 *        {
 *            "_content":"36"
 *        },
 *        "notes":
 *        {
 *            "note":[]
 *        },
 *        "people":
 *        {
 *            "haspeople":0
 *        },
 *        "tags":
 *        {
 *            "tag":[
 *            {
 *                "id":"36494498-3989150138-152587",
 *                "author":"36587311@N08",
 *                "authorname":"\u25baCubaGallery",
 *                "raw":"Lightroom",
 *                "_content":"lightroom",
 *                "machine_tag":0
 *            },
 *            {
 *                "id":"36494498-3989150138-61264",
 *                "author":"36587311@N08",
 *                "authorname":"\u25baCubaGallery",
 *                "raw":"Tutorial",
 *                "_content":"tutorial",
 *                "machine_tag":0
 *            },
 *            {
 *                "id":"36494498-3989150138-2546",
 *                "author":"36587311@N08",
 *                "authorname":"\u25baCubaGallery",
 *                "raw":"Video",
 *                "_content":"video",
 *                "machine_tag":0
 *            }
 *            ]
 *        },
 *        "location":
 *        {
 *            "latitude":-36.830181,
 *            "longitude":174.428497,
 *            "accuracy":"13",
 *            "context":"0",
 *            "locality":
 *            {
 *             	 "_content":"Muriwai",
 *             	 "place_id":"zMBe8sZUVLqzrrZ.UQ",
 *             	 "woeid":"56023026"
 *            },
 *            "county":
 *            {
 *             	 "_content":"Rodney District",
 *             	 "place_id":"fBUgSc5UV7JuHDMGsA",
 *             	 "woeid":"55875887"
 *            },
 *            "region":
 *            {
 *             	 "_content":"Auckland",
 *             	 "place_id":"XIVOdI5QV7pepC5JCA",
 *             	 "woeid":"15021756"
 *            },
 *            "country":{
 *             	 "_content":"New Zealand",
 *             	 "place_id":"X_2zAGVTUb5..jhXDw",
 *             	 "woeid":"23424916"
 *            },
 *            "place_id":"zMBe8sZUVLqzrrZ.UQ",
 *            "woeid":"56023026"
 *        },
 *        "geoperms":
 *        {
 *            "ispublic":1,
 *            "iscontact":0,
 *            "isfriend":0,
 *            "isfamily":0
 *        },
 *        "urls":
 *        {
 *            "url":[
 *            {
 *                "type":"photopage",
 *                "_content":"https:\/\/www.flickr.com\/photos\/cubagallery\/3989150138\/"
 *            }
 *            ]
 *        },
 *        "media":"video",
 *        "video":
 *        {
 *            "ready":1,
 *            "failed":0,
 *            "pending":0,
 *            "duration":"20",
 *            "width":"600",
 *            "height":"600"
 *        }
 *    },
 *    "stat":"ok"
 * }
 */
