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
 * Snotr Video SEO Details
 *
 * Snotr does not offer oembed info nor an API, but Embedly can deal real well with Snotr video links.
 *
 * @see http://embed.ly/docs/embed/api/endpoints/1/oembed
 *
 * JSON response format [2014/7/22] for query http://www.snotr.com/video/13751/Terre_des_Hommes_-_Sweetie_Case :
 * {
 *    "provider_url": "http://snotr.com",
 *    "version": "1.0",
 *    "title": "Terre des Hommes - Sweetie Case",
 *    "thumbnail_width": 240,
 *    "height": 420,
 *    "width": 520,
 *    "html": "<iframe class=\"embedly-embed\" src=\"//cdn.embedly.com/widgets/media.html?url=http%3A%2F%2Fwww.snotr.com%2Fvideo%2F13751%2FTerre_des_Hommes_-_Sweetie_Case&src=http%3A%2F%2Fwww.snotr.com%2Fembed%2F13751&image=http%3A%2F%2Fcdn.videos.snotr.com%2F13751-large.jpg&type=text%2Fhtml&schema=snotr\" width=\"520\" height=\"420\" scrolling=\"no\" frameborder=\"0\" allowfullscreen></iframe>",
 *    "provider_name": "Snotr.com",
 *    "thumbnail_url": "http://cdn.videos.snotr.com/13751-large.jpg",
 *    "type": "video",
 *    "thumbnail_height": 135
 * }
 */
if ( ! class_exists( 'WPSEO_Video_Details_Snotr' ) ) {

	/**
	 * Class WPSEO_Video_Details_Snotr
	 *
	 * Retrieve details via the Embedly service.
	 */
	class WPSEO_Video_Details_Snotr extends WPSEO_Video_Details_Embedly {

		/**
		 * @var    string    Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`[/\.]snotr\.com/(?:video|embed)/([0-9]+)(?:$|[/#\?])`i';

		/**
		 * @var	string	Sprintf template to create a url from an id
		 */
		protected $url_template = 'http://snotr.com/video/%s';


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = 'http://www.snotr.com/embed/' . rawurlencode( $this->vid['id'] );
			}
		}

		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = 'http://cdn.videos.snotr.com/' . rawurlencode( $this->vid['id'] ) . '-large.jpg';
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
