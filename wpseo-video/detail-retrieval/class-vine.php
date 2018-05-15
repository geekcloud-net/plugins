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
 * Vine Video SEO Details
 *
 * Vine does not seem to offer oembed info nor an API, but Embedly can deal real well with vine video links.
 *
 * @see http://embed.ly/docs/embed/api/endpoints/1/oembed
 *
 * There does seem to be a native oembed endpoint, but I haven't been able to get any useful response.
 * Does not work: https://vine.co/api/oembed?url=https://vine.co/v/M0FzIbhZa36
 * Does not work: https://vine.co/api/oembed.json?url=https://vine.co/v/M0FzIbhZa36
 * Does not work: https://vine.co/api/oembed.json?id=M0FzIbhZa36
 *
 * @see https://api.vineapp.com - example endpoint (not useful result though): https://api.vineapp.com/timelines/posts/MExV5BVrKJE
 * @see https://github.com/starlock/vino/wiki/API-Reference
 * @see http://en.support.wordpress.com/videos/vine/
 *
 *
 * JSON response format [2014/7/22] for query http://api.embed.ly/v1/api/oembed?url=https://vine.co/v/M0iA6AAFOqM :
 * {
 *    "provider_url": "https://vine.co",
 *    "version": "1.0",
 *    "title": "The Approach. The Take. The Decision. The Nom. #Tabula #lion #Carerescuetexas",
 *    "thumbnail_width": 480,
 *    "height": 435,
 *    "width": 435,
 *    "html": "<iframe class=\"embedly-embed\" src=\"//cdn.embedly.com/widgets/media.html?src=https%3A%2F%2Fmtc.cdn.vine.co%2Fr%2Fvideos%2F93FB32DA041106985423173230592_259db060369.1.2.10930682652597924920.mp4%3FversionId%3D2uuA8S5BlYYoW9sG_COulYoyWoxk2Awi&src_secure=1&url=https%3A%2F%2Fvine.co%2Fv%2FMExV5BVrKJE&image=https%3A%2F%2Fv.cdn.vine.co%2Fr%2Fthumbs%2F690FA3F8111106985425564004352_2.1.2.10930682652597924920.mp4.jpg%3FversionId%3Ddmw3H1Tpl_E71k7ggMm9TEZZGFWPfsNh&type=video%2Fmp4&schema=vine\" width=\"435\" height=\"435\" scrolling=\"no\" frameborder=\"0\" allowfullscreen></iframe>",
 *    "provider_name": "Vine",
 *    "thumbnail_url": "https://v.cdn.vine.co/r/thumbs/690FA3F8111106985425564004352_2.1.2.10930682652597924920.mp4.jpg?versionId=dmw3H1Tpl_E71k7ggMm9TEZZGFWPfsNh",
 *    "type": "video",
 *    "thumbnail_height": 480
 * }
 */
if ( ! class_exists( 'WPSEO_Video_Details_Vine' ) ) {

	/**
	 * Class WPSEO_Video_Details_Vine
	 *
	 * Retrieve details via the Embedly service.
	 */
	class WPSEO_Video_Details_Vine extends WPSEO_Video_Details_Embedly {

		/**
		 * @var    string    Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`[/\.]vine\.co/v/([a-z0-9]+)(?:$|[/#\?])`i';

		/**
		 * @var	string	Sprintf template to create a url from an id
		 */
		protected $url_template = 'https://vine.co/v/%s';


		/**
		 * Set the content location
		 */
		protected function set_content_loc() {
			if ( ! empty( $this->decoded_response->html ) ) {
				$this->decoded_response->html = stripslashes( $this->decoded_response->html );
				if ( preg_match( '` src="[^\?]+\?(?:[^&]+&)*?src=([^&]+\.mp4)`i', $this->decoded_response->html, $match ) ) {
					$this->vid['content_loc'] = rawurldecode( $match[1] );
				}
			}
		}


		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = 'https://vine.co/v/' . rawurlencode( $this->vid['id'] ) . '/embed/simple';
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
