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
 * FunnyorDie Video SEO Details
 *
 * JSON response format [2014/7/22]:
 * {
 *    "type":"video",
 *    "version":"1.0",
 *    "provider_name":"Funny or Die",
 *    "provider_url":"http://www.funnyordie.com",
 *    "author_name":"That Happened!",
 *    "author_url":"http://www.funnyordie.com/thathappened",
 *    "title":"Old Guy Dancing is the Best",
 *    "html":"\u003Ciframe src=\"http://www.funnyordie.com/embed/e3ef08d14f\" width=\"960\" height=\"580\" frameborder=\"0\" allowfullscreen webkitallowfullscreen mozallowfullscreen\u003E\u003C/iframe\u003E\u003Cdiv style=\"text-align:left;font-size:x-small;margin-top:0;width:960px;\"\u003E\u003Ca href=\"http://www.funnyordie.com/videos/e3ef08d14f/old-guy-dancing-is-the-best\" title=\"'from That Happened!\"\u003EOld Guy Dancing is the Best\u003C/a\u003E - watch more \u003Ca href=\"http://www.funnyordie.com/\" title=\"on Funny or Die\"\u003Efunny videos\u003C/a\u003E      \u003Ciframe src=\"http://www.facebook.com/plugins/like.php?app_id=138711277798\u0026amp;href=http%3A%2F%2Fwww.funnyordie.com%2Fvideos%2Fe3ef08d14f%2Fold-guy-dancing-is-the-best\u0026amp;send=false\u0026amp;layout=button_count\u0026amp;width=150\u0026amp;show_faces=false\u0026amp;action=like\u0026amp;height=21\" scrolling=\"no\" frameborder=\"0\" style=\"border:none; overflow:hidden; width:90px; height:21px; vertical-align:middle;\" allowTransparency=\"true\"\u003E\u003C/iframe\u003E\n\u003C/div\u003E",
 *    "width":960,
 *    "height":580,
 *    "thumbnail_width":464,
 *    "thumbnail_height":348,
 *    "thumbnail_url":"http://t.fod4.com/t/e3ef08d14f/c480x270_3.jpg",
 *    "duration":181.0
 * }
 */
if ( ! class_exists( 'WPSEO_Video_Details_Funnyordie' ) ) {

	/**
	 * Class WPSEO_Video_Details_Funnyordie
	 */
	class WPSEO_Video_Details_Funnyordie extends WPSEO_Video_Details_Oembed {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`[/\.]funnyordie\.com/(?:videos|embed)/([a-z0-9]+)(?:$|[/#\?])`i';

		/**
		 * @var	string	Sprintf template to create a url from an id
		 */
		protected $url_template = 'http://www.funnyordie.com/videos/%s/';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'http://www.funnyordie.com/oembed.json?url=%s',
			'replace_key'   => 'url',
			'response_type' => 'json',
		);


		/**
		 * Instantiate the class and determine which remote retrieval method we can use before
		 * passing of to the parent constructor.
		 *
		 * @param array $vid     The video array with all the data.
		 * @param array $old_vid The video array with all the data of the previous "fetch", if available.
		 *
		 * @return \WPSEO_Video_Details_Funnyordie
		 */
		public function __construct( $vid, $old_vid = array() ) {
			if ( isset( $vid['url'] ) ) {
				// Fix it as FoD oembed does not work with embed urls.
				$vid['url'] = str_replace( 'funnyordie.com/embed/', 'funnyordie.com/videos/', $vid['url'] );
			}

			parent::__construct( $vid, $old_vid );
		}


		/**
		 * Create a video url based on a known video id and url template
		 */
		protected function determine_video_url_from_id() {
			if ( ( ! empty( $this->vid['id'] ) && strlen( $this->vid['id'] ) > 4 ) && $this->url_template !== '' ) {
				$this->vid['url'] = sprintf( $this->url_template, $this->vid['id'] );
			}
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = 'http://www.funnyordie.com/embed/' . rawurlencode( $this->vid['id'] );
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
