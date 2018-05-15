<?php
/**
 * @package    Internals
 * @since      1.7.0
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
 * Animoto Video SEO Details
 *
 * @todo Add support for video214.com domain which is also used by Animoto
 *
 * JSON response format [2014/7/22]:
 * {
 *    "provider_url":"http://animoto.com/",
 *    "title":"Juno Groove",
 *    "thumbnail_height":360,
 *    "html":"<iframe id=\"vp1JzwsB\" title=\"Video Player\" width=\"640\" height=\"360\" frameborder=\"0\" src=\"https://s3.amazonaws.com/embed.animoto.com/play.html?w=swf/production/vp1&e=1406063431&f=JzwsBn5FRVxS0qoqcBP5zA&d=0&m=p&r=240p&i=m&ct=&cu=&asset_domain=s3-p.animoto.com&animoto_domain=animoto.com&options=\" allowfullscreen></iframe>",
 *    "provider_name":"Animoto",
 *    "thumbnail_width":648,
 *    "icon_url":"https://s3.amazonaws.com/s3-p.animoto.com/Video/JzwsBn5FRVxS0qoqcBP5zA/cover_432x240.jpg",
 *    "author_name":"Chris Korhonen",
 *    "type":"video",
 *    "width":640,
 *    "video_url":"https://d150hyw1dtprld.cloudfront.net/swf/w.swf?w=swf/production/vp1&e=1406063431&f=JzwsBn5FRVxS0qoqcBP5zA&d=0&m=p&r=240p&i=m&ct=&cu=&asset_domain=s3-p.animoto.com&animoto_domain=animoto.com&options=",
 *    "version":1.0,
 *    "thumbnail_url":"https://s3.amazonaws.com/s3-p.animoto.com/Video/JzwsBn5FRVxS0qoqcBP5zA/cover_432x240.jpg",
 *    "icon_height":54,
 *    "description":"",
 *    "height":360,
 *    "cache_age":{},
 *    "icon_width":54
 * }
 */
if ( ! class_exists( 'WPSEO_Video_Details_Animoto' ) ) {

	/**
	 * Class WPSEO_Video_Details_Animoto
	 *
	 * {@internal Animoto doesn't provide duration in the oembed API, unfortunately.}}
	 */
	class WPSEO_Video_Details_Animoto extends WPSEO_Video_Details_Oembed {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`animoto\.com/play/(.+)$`i';

		/**
		 * @var	string	Sprintf template to create a url from an id
		 */
		protected $url_template = 'https://animoto.com/play/%s';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'http://animoto.com/services/oembed?format=json&url=%s',
			'replace_key'   => 'url',
			'response_type' => 'json',
		);


		/**
		 * Instantiate the class
		 *
		 * Adjust the video url before passing off to the parent constructor
		 *
		 * @param array $vid     The video array with all the data.
		 * @param array $old_vid The video array with all the data of the previous "fetch", if available.
		 *
		 * @return \WPSEO_Video_Details_Animoto
		 */
		public function __construct( $vid, $old_vid = array() ) {
			if ( ! empty( $vid['url'] ) ) {
				if ( preg_match( '`http://static\.animoto\.com/swf/.*?&f=([^&]+)`', $vid['url'], $match ) ) {
					$vid['url'] = sprintf( $this->url_template, rawurlencode( $match[1] ) );
				}
			}
			parent::__construct( $vid, $old_vid );
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->decoded_response->video_url ) ) {
				$this->vid['player_loc'] = $this->decoded_response->video_url;
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
