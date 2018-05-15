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
 * Veoh Video SEO Details
 *
 * @todo Maybe look into the API: http://www.veoh.com/rest/v2/doc.html
 * API-key: E97FCECD-875D-D5EB-035C-8EF241F184E2
 * @see http://jlorek.wordpress.com/tag/veoh/ ;-)
 *
 * Example of full remote SPI response (XML) format [2014/7/22] - see below class.
 */
if ( ! class_exists( 'WPSEO_Video_Details_Veoh' ) ) {

	/**
	 * Class WPSEO_Video_Details_Veoh
	 */
	class WPSEO_Video_Details_Veoh extends WPSEO_Video_Details {

		/**
		 * @var	string	Regular expression to retrieve a video id from a known video url
		 */
		protected $id_regex = '`[/\.]veoh\.com/(?:videos|watch)/([^/]+)[/]?$`i';

		/**
		 * Set the player location
		 */
		protected function set_player_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$this->vid['player_loc'] = 'http://www.veoh.com/veohplayer.swf?permalinkId=' . urlencode( $this->vid['id'] );
			}
		}

		/**
		 * Set the thumbnail location
		 */
		protected function set_thumbnail_loc() {
			if ( ! empty( $this->vid['id'] ) ) {
				$url   = $this->url_encode( 'http://ll-images.veoh.com/media/w300/thumb-' . $this->vid['id'] . '-1.jpg' );
				$image = $this->make_image_local( $url );
				if ( is_string( $image ) && $image !== '' ) {
					$this->vid['thumbnail_loc'] = $image;
				}
			}
		}
	} /* End of class */

} /* End of class-exists wrapper */
