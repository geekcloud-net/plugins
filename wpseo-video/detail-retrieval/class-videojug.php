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
 * Videojug Video SEO Details
 *
 * {@internal The video id has no relation to the 'normal' urls. Normal urls can be used to retrieve oembed
 * information, however if all we have is a video id, we're lost as oembed does not return any info in that
 * case - no matter how the url is build up (tried with /embed/#id#, &id=, &embedid= etc) }}
 *
 * JSON response format [2014/7/22]:
 * {
 *    "version":"1.0",
 *    "type":"video",
 *    "provider_name":"Videojug",
 *    "provider_url":"http://www.videojug.com/",
 *    "width":640,
 *    "height":382,
 *    "title":"How To Tie A Knot Braid",
 *    "html":"\r\n\u003ciframe width=\"640\" height=\"382\" src=\"http://www.videojug.com/embed/a5885506-146c-0304-32e4-ff0008d05cb5\" frameborder=\"0\" allowfullscreen\u003e\u003c/iframe\u003e\u003cp class=\"vj-videolink\"\u003e\u003ca href=\"http://www.videojug.com/film/how-to-tie-a-knot-braid\" target=\"_blank\"\u003eHow To Tie A Knot Braid\u003c/a\u003e\u003c/p\u003e\r\n",
 *    "thumbnail_url":"http://www.videojug.com/a5/a5885506-146c-0304-32e4-ff0008d05cb5/hair-with-hollie-knot-braid-hair.WidePromo.jpg?v3",
 *    "thumbnail_width":382,
 *    "thumbnail_height":215,
 *    "author_url":"",
 *    "author_name":"DFS: Keyword: Broad: All Comp:",
 *    "id":"a5885506-146c-0304-32e4-ff0008d05cb5"
 * }
 */
if ( ! class_exists( 'WPSEO_Video_Details_Videojug' ) ) {

	/**
	 * Class WPSEO_Video_Details_Videojug
	 */
	class WPSEO_Video_Details_Videojug extends WPSEO_Video_Details_Oembed {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`[/\.]videojug\.com/embed/([a-z0-9-]+)(?:$|[/#\?])`i';

		/**
		 * @var	string	Sprintf template to create a url from an id
		 */
		protected $url_template = 'http://www.videojug.com/film/%s';

		/**
		 * @var	array	Information on the remote url to use for retrieving the video details
		 */
		protected $remote_url = array(
			'pattern'       => 'http://www.videojug.com/oembed.json?url=%s',
			'replace_key'   => 'url',
			'response_type' => 'json',
		);

		/**
		 * (Re-)Set the video id
		 */
		protected function set_id() {
			if ( ! empty( $this->decoded_response->id ) ) {
				$this->vid['id'] = $this->decoded_response->id;
			}
		}

		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = 'http://www.videojug.com/embed/' . rawurlencode( $this->vid['id'] );
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
