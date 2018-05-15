<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

if ( ! class_exists( 'TCB_Post' ) ) {


	/**
	 * Class TCB_Post
	 *
	 * @property $tcb_content
	 * @property $post_content
	 * @property $ID
	 */
	class TCB_Post {
		/**
		 * @var WP_Post
		 */
		public $post;

		public function __construct( $post_id = null ) {
			$this->post = get_post( $post_id );
		}

		/**
		 * Setter or getter for a post_meta field
		 *
		 * @param string $key
		 * @param null|mixed $value if $value is null, the function acts as a getter
		 * @param bool $use_lp_key whether or not to get / set the value in the LandingPage meta space
		 * @param mixed $default default value to return if meta value is empty
		 *
		 * @return mixed|TCB_Post
		 */
		public function meta( $key, $value = null, $use_lp_key = false, $default = null ) {
			if ( ! $this->post ) {
				return null;
			}

			if ( null === $value ) {
				$fn    = $use_lp_key ? 'tve_get_post_meta' : 'get_post_meta';
				$value = $fn( $this->post->ID, $key, true );

				if ( empty( $value ) && null !== $default ) {
					$value = $default;
				}

				return $value;
			}

			$fn = $use_lp_key ? 'tve_update_post_meta' : 'update_post_meta';
			$fn( $this->post->ID, $key, $value );

			return $this;
		}

		/**
		 * Deletes post meta field
		 *
		 * @param string $key
		 */
		public function meta_delete( $key ) {
			delete_post_meta( $this->post->ID, $key );

			return $this;
		}

		/**
		 * @param $name
		 *
		 * @return mixed|null
		 */
		public function __get( $name ) {
			switch ( $name ) {
				case 'tcb_content':
					return $this->meta( 'tve_updated_post', null, true );
			}

			return $this->post ? $this->post->{$name} : null;
		}

		/**
		 * Get the HTML for a single WP content element containing everything from the post_content field
		 */
		public function get_wp_element() {
			$html       = '';
			$wp_content = wpautop( $this->post->post_content );
			$wp_content = shortcode_unautop( $wp_content );
			if ( trim( $wp_content ) ) {
				$html = '<div class="thrv_wrapper tve_wp_shortcode"><div class="tve_shortcode_raw" style="display: none">___TVE_SHORTCODE_RAW__' . htmlentities( $wp_content ) . '__TVE_SHORTCODE_RAW___</div></div>';
			}

			return $html;
		}

		/**
		 * Migrates the post to TCB2
		 *
		 * @param boolean $update_plain_text whether or not to also update the plain text version of the post content
		 */
		public function migrate( $update_plain_text = true ) {
			$wp_content  = $this->post->post_content;
			$tcb_content = $this->tcb_content;
			if ( ! empty( $wp_content ) ) {
				$tcb_content .= $this->get_wp_element();
			}
			$this->meta( 'tve_updated_post', $tcb_content, true )
			     ->meta( 'tcb2_ready', 1 )
			     ->meta( 'tcb_editor_enabled', 1 )
			     ->meta_delete( 'tcb_editor_disabled' );

			if ( $update_plain_text ) {
				$this->update_plain_text_content( $tcb_content );
			}
		}

		/**
		 * Generates the text version of a TCB-saved post
		 *
		 * @param string|int $post_id
		 * @param string $tcb_content
		 *
		 * @return TCB_Post
		 */
		public function update_plain_text_content( $tcb_content = null ) {
			if ( ! $this->editor_enabled() ) {
				return $this;
			}

			if ( null === $tcb_content ) {
				$tcb_content = $this->tcb_content;
			}
			$tcb_content = tcb_clean_frontend_content( $tcb_content );

			/* Make sure WP shortcode element is decoded before saving it */
			$tcb_content = preg_replace_callback( '#___TVE_SHORTCODE_RAW__(.+?)__TVE_SHORTCODE_RAW___#s', array( $this, 'plain_text_decode_content' ), $tcb_content );

			$tcb_content = tve_thrive_shortcodes( $tcb_content );
			$tcb_content = preg_replace( '/<script(.*?)>(.*?)<\/script>/is', '', $tcb_content );
			$tcb_content = preg_replace( '/<style(.*?)>(.*?)<\/style>/is', '', $tcb_content );

			$tcb_content = strip_tags( $tcb_content, '<h1><h2><h3><h4><h5><h6><p><ul><ol><li><span><a><img><strong><b><u><em><sup><sub><blockquote><address><table><tbody><thead><tr><th><td>' );
			$tcb_content = str_replace( array( "\n", "\r", "\t" ), '', $tcb_content );
			/* re-add the <!--more--> tag to the text, if it was present before */
			$tcb_content = str_replace( 'TCB_WP_MORE_TAG', '<!--more-->', $tcb_content );

			$tcb_content = preg_replace( '/(\s+)?class="([^"]*)?"/is', '', $tcb_content );
			$tcb_content = preg_replace( '/(\s+)?data-css="([^"]+)"/is', '', $tcb_content );
			$tcb_content = preg_replace( '/(\s+)?data-tcb-events="__TCB_EVENT_(.+?)_TNEVE_BCT__"/is', '', $tcb_content );

			wp_update_post( array(
				'ID'           => $this->post->ID,
				'post_content' => $tcb_content,
			) );

			return $this;
		}

		/**
		 * Decode html entities inside WP Content elemnt
		 *
		 * @param array $matches
		 *
		 * @return string
		 */
		public function plain_text_decode_content( $matches ) {
			$html = html_entity_decode( $matches[1] );
			/* replace the MORE tag from WP with a placeholder so it does not get stripped by strip_tags */
			if ( preg_match( '#<!--more(.*?)?-->#', $html, $m ) ) {
				$html = str_replace( $m[0], 'TCB_WP_MORE_TAG', $html );
			}

			return $html;
		}

		/**
		 * Enables the TCB-only editor for this post
		 *
		 * @return TCB_Post
		 */
		public function enable_editor() {
			if ( ! $this->post || ! $this->meta( 'tcb2_ready' ) ) {
				return $this;
			}

			return $this->meta_delete( 'tcb_editor_disabled' )
			            ->meta( 'tcb_editor_enabled', 1 );
		}

		/**
		 * Disables the TCB-only editor for this post
		 *
		 * @return TCB_Post
		 */
		public function disable_editor() {
			if ( ! $this->post || ! $this->meta( 'tcb2_ready' ) ) {
				return $this;
			}

			return $this->meta_delete( 'tcb_editor_enabled' )
			            ->meta( 'tcb_editor_disabled', 1 );
		}

		/**
		 * Checks if this post has the TCB-only editor enabled ( version 2.0 ).
		 */
		public function editor_enabled() {
			if ( ! $this->post ) {
				return false;
			}
			if ( ! $this->meta( 'tcb2_ready' ) ) {
				return false;
			}

			return (int) $this->meta( 'tcb_editor_enabled' );
		}

		/**
		 * Check if the TCB editor is specifically disabled for this post
		 */
		public function editor_disabled() {
			if ( ! $this->post ) {
				return false;
			}

			if ( ! $this->meta( 'tcb2_ready' ) ) {
				/* acts as previously */
				return false;
			}

			return (int) $this->meta( 'tcb_editor_disabled' );
		}

		/**
		 * Check if the conditions are met to auto-migrate the post to TCB2.0, and enable TCB-editor only mode.
		 * This can only happen if there is no WP editor content in the post, and also, the post has tcb-content
		 *
		 * @param boolean $update_plain_text whether or not to update the plain text content
		 *
		 * @return TCB_Post allows chained calls
		 */
		public function maybe_auto_migrate( $update_plain_text = true ) {
			if ( ! $this->post ) {
				return $this;
			}

			if ( $this->meta( 'tcb2_ready' ) ) {
				return $this;
			}
			$wp_content = trim( $this->post->post_content );
			if ( empty( $wp_content ) && $this->meta( 'tve_globals', null, true ) ) {
				$this->migrate( $update_plain_text );
			}

			return $this;
		}

		/**
		 * Checks whether or not this page is a landing page
		 *
		 * @return false|string The name of the template if this is a landing page, boolean false otherwise
		 */
		public function is_landing_page() {

			$is_landing_page = $this->post instanceof WP_Post;
			$is_landing_page = $is_landing_page && in_array( $this->post->post_type, apply_filters( 'tve_landing_page_post_types', array( 'page' ) ) );

			if ( $is_landing_page ) {
				$is_landing_page = $this->meta( 'tve_landing_page' );
			}

			return $is_landing_page;
		}

		/**
		 * Checks if the current post is a Thrive Lightbox
		 */
		public function is_lightbox() {
			return $this->post && $this->post->post_type === 'tcb_lightbox';
		}
	}

	/**
	 * Instantiates a new TCB_Post helper class
	 *
	 * @param null|mixed $post_id
	 *
	 * @return TCB_Post
	 */
	function tcb_post( $post_id = null ) {
		return new TCB_Post( $post_id );
	}
}
