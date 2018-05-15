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
 * Spike (formally iFilm) Video SEO Details
 *
 * Spike does not offer oembed info nor an API, but Embedly can deal real well with spike video links.
 *
 * @see http://embed.ly/docs/embed/api/endpoints/1/oembed
 *
 * JSON response format [2014/7/22] for query http://api.embed.ly/v1/api/oembed?url=http://www.spike.com/video-clips/95p1wb/cops-highly-suspicious-meth-bust :
 * {
 *    "provider_url": "http://www.spike.com",
 *    "description": "When officers stop to question a man \"waiting for a cab\" in the middle of a gentrified suburb, they stop to ask some questions. The clearly nervous man turns up warrants and methamphetamine.",
 *    "title": "Highly Suspicious Meth Bust",
 *    "thumbnail_width": 600,
 *    "height": 360,
 *    "width": 640,
 *    "html": "<iframe class=\"embedly-embed\" src=\"//cdn.embedly.com/widgets/media.html?url=http%3A%2F%2Fwww.spike.com%2Fvideo-clips%2F95p1wb%2Fcops-highly-suspicious-meth-bust&src=http%3A%2F%2Fmedia.mtvnservices.com%2Ffb%2Fmgid%3Aarc%3Avideo%3Aspike.com%3A27d5de4f-35f8-4565-8267-255ca62e3534.swf&image=http%3A%2F%2F2.images.spike.com%2Fimages%2Fshows%2Fcops%2Fmethbust600072114.jpg%3Fquality%3D0.91&type=application%2Fx-shockwave-flash&schema=spike\" width=\"640\" height=\"360\" scrolling=\"no\" frameborder=\"0\" allowfullscreen></iframe>",
 *    "version": "1.0",
 *    "provider_name": "Spike",
 *    "thumbnail_url": "http://2.images.spike.com/images/shows/cops/methbust600072114.jpg?quality=0.91",
 *    "type": "video",
 *    "thumbnail_height": 347
 * }
 */
if ( ! class_exists( 'WPSEO_Video_Details_Spike' ) ) {

	/**
	 * Class WPSEO_Video_Details_Spike
	 *
	 * Retrieve details via the Embedly service.
	 */
	class WPSEO_Video_Details_Spike extends WPSEO_Video_Details_Embedly {

		/**
		 * @var	string	Sprintf template to create a url from an id
		 */
		protected $url_template = 'http://www.spike.com/video-clips/%s/';


		/**
		 * Set the player location
		 * http://media.mtvnservices.com/embed/mgid:arc:video:spike.com:27d5de4f-35f8-4565-8267-255ca62e3534.swf
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->decoded_response->html ) ) {
				$this->decoded_response->html = stripslashes( $this->decoded_response->html );
				if ( preg_match( '` src="[^\?]+\?(?:[^&]+&)*?src=([^&]+)&`i', $this->decoded_response->html, $match ) ) {
					$this->vid['player_loc'] = rawurldecode( $match[1] );
				}
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
