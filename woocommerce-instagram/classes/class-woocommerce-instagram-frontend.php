<?php
/**
 * Frontend handler.
 *
 * @package WooCommerce_Instagram
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WooSlider Instagram Frontend Class
 *
 * @package WordPress
 * @subpackage Woocommerce_Instagram
 * @category Admin
 * @author WooThemes
 * @since 1.0.0
 */
class Woocommerce_Instagram_Frontend {
	/**
	 * Token as plugin identification.
	 *
	 * @var string
	 */
	private $_token;

	/**
	 * Plugin's main file.
	 *
	 * @var string
	 */
	private $_file;

	/**
	 * API instance.
	 *
	 * @var Woocommerce_Instagram_API
	 */
	private $_api;

	/**
	 * Constructor function.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file    Plugin's main file.
	 * @param object $api_obj Instance of API class.
	 */
	public function __construct( $file, $api_obj ) {
		$this->_file  = $file;
		$this->_token = 'woocommerce-instagram';
		$this->_api   = $api_obj;

		add_action( 'woocommerce_after_single_product', array( $this, 'maybe_display_instagrams' ) );
	}

	/**
	 * If a hash tag has been specified, maybe display images with that tag.
	 *
	 * @since 1.0.0
	 *
	 * @todo Move portion of this into template file?
	 *
	 * @param int $id Product ID.
	 *
	 * @return  void
	 */
	public function maybe_display_instagrams( $id = 0 ) {
		if ( 0 == $id ) {
			$id = get_the_ID();
		}

		$html = '';
		$tag = $this->_get_hashtag_by_product( intval( $id ) );

		if ( '' != $tag ) {

			$html .= '<div class="woocommerce-instagram columns-' . intval( apply_filters( 'woocommerce_instagram_columns', 4 ) ) . '">' . "\n";
			$html .= '<h2>' . sprintf( apply_filters( 'woocommerce_instagram_section_title', __( '%s on Instagram', 'woocommerce-instagram' ) ), get_the_title( get_the_ID() ) ) . '</h2>' . "\n";

			$images = $this->get_instagrams_by_tag( $tag );

			if ( isset( $images->data ) && 0 < count( $images->data ) ) {
				// Make sure we use the most appropriate Instagram image, based on the "shop_catalog" image size.
				$size_array = wc_get_image_size( apply_filters( 'woocommerce_instagram_image_size', 'shop_catalog' ) );
				$size = 150;
				if ( isset( $size_array['width'] ) ) {
					$size = intval( $size_array['width'] );
				}
				$size_token = $this->_determine_image_by_size( $size );

				$html .= '<ul class="products">' . "\n";
				// Loop through the images.
				$count = 1;
				foreach ( $images->data as $k => $v ) {
					$class = 'product instagram';

					if ( 1 == ( $count % apply_filters( 'woocommerce_instagram_columns', 4 ) ) ) {
						$class .= ' first';
					}
					if ( 0 == ( $count % apply_filters( 'woocommerce_instagram_columns', 4 ) ) ) {
						$class .= ' last';
					}

					if ( isset( $v->caption->text ) && '' != $v->caption->text ) {
						$caption = $v->caption->text;
					} else {
						/* translators: 1) post title 2) post author */
						$caption = sprintf( __( 'Instagram of %1$s by %2$s', 'woocommerce-instagram' ), get_the_title( get_the_ID() ), $v->user->username );
					}
					$html .= '<li class="' . esc_attr( $class ) . '">' . '<a href="' . esc_url( $v->link ) . '" title="' . esc_attr( $caption ) . '"><img src="' . esc_url( $this->_get_image_url_by_size( $v, $size_token ) ) . '" /></a>' . '</li>' . "\n";
					$count++;
				}
				$html .= '</ul>' . "\n";
			}

			if ( apply_filters( 'woocommerce_instagram_display_action_note', true ) ) {
				$html .= '<p class="woocommerce-instagram-call-to-action">' . sprintf( __( 'Want to share your instagrams of you with your %1$s? Use the %2$s hash tag.', 'woocommerce-instagram' ), '<strong>' . get_the_title( get_the_ID() ) . '</strong>', '<strong>#' . esc_attr( $tag ) . '</strong>' ) . '</p>' . "\n";
			}
			$html .= '</div><!--/.woocommerce-instagram-->' . "\n";

			if ( '' != $html ) {
				echo $html;
			}
		}
	}

	/**
	 * Return the hash tag value.
	 *
	 * @since 1.0.0
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return string
	 */
	private function _get_hashtag_by_product( $product_id ) {
		$value = '';
		$tag = esc_html( get_post_meta( intval( $product_id ), '_instagram_hashtag', true ) );
		if ( '' != $tag ) {
			$value = $tag;
		}
		return $value;
	}

	/**
	 * Get instagrams by tag.
	 *
	 * @since   1.0.0
	 * @param   string $tag Tag to check against.
	 * @return  array       Instagrams.
	 */
	public function get_instagrams_by_tag( $tag ) {
		if ( null == $tag || '' == $tag || false == $tag ) {
			return array();
		}

		$images = $this->_api->get_tag_media_recent( $tag, array( 'count' => apply_filters( 'woocommerce_instagram_images', $images = 8 ) ) );

		return $images;
	}

	/**
	 * Determine which of the 3 image sizes should be used, based on a specified custom image size.
	 *
	 * @since   1.0.0
	 * @param   string $size The size to be used.
	 * @return  string The token of the image to be used.
	 */
	private function _determine_image_by_size( $size ) {
		$token = 'thumbnail';

		if ( $size <= 150 ) { $token = 'thumbnail'; }
		if ( $size <= 306 && $size > 150 ) { $token = 'low_resolution'; }
		if ( ( $size <= 612 || $size > 612 ) && $size > 306 ) { $token = 'standard_resolution'; }

		return $token;
	}

	/**
	 * Get image based on provided size token.
	 *
	 * @since   1.0.0
	 * @param   object $obj  Instagram image object.
	 * @param   string $size The size token to be used.
	 * @return  string The token of the image to be used.
	 */
	private function _get_image_url_by_size( $obj, $size ) {
		$url = '';
		if ( isset( $obj->images->$size->url ) ) {
			$url = esc_url( $obj->images->$size->url );
		}
		return $url;
	}
}
