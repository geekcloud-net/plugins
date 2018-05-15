<?php
/**
 * Yoast SEO: News plugin file.
 *
 * @package WPSEO_News
 */

/**
 * Represents the frontend head.
 */
class WPSEO_News_Head {

	/**
	 * Holder for post-data.
	 *
	 * @var object
	 */
	private $post;

	/**
	 * WPSEO_News_Head Constructor.
	 */
	public function __construct() {
		do_action( 'wpseo_news_head' );

		add_action( 'wpseo_head', array( $this, 'add_head_tags' ) );
	}

	/**
	 * Display the optional sources link elements in the <code>&lt;head&gt;</code>.
	 */
	public function add_head_tags() {
		if ( is_singular() ) {
			global $post;

			$this->post = $post;

			$this->display_original_source();
			$this->display_standout();
			$this->display_noindex();
		}
	}

	/**
	 * Displays the original-source as link-tag in head.
	 */
	private function display_original_source() {
		/**
		 * Filter: 'wpseo_news_head_display_original' - Allow preventing of outputting original source tag.
		 *
		 * @api bool unsigned Whether or not to show the original source.
		 *
		 * @param object $post The post.
		 */
		if ( apply_filters( 'wpseo_news_head_display_original', true, $this->post ) ) {
			$original_source = trim( WPSEO_Meta::get_value( 'newssitemap-original', $this->post->ID ) );
			if ( empty( $original_source ) ) {
				echo '<meta name="original-source" content="' . esc_url( get_permalink( $this->post->ID ) ) . '" />' . "\n";
			}
			else {
				$sources = explode( '|', $original_source );
				foreach ( $sources as $source ) {
					echo '<meta name="original-source" content="' . esc_url( $source ) . '" />' . "\n";
				}
			}
		}
	}

	/**
	 * Displays the standout as meta tag in head.
	 */
	private function display_standout() {
		/**
		 * Filter: 'wpseo_news_head_display_standout' - Allow preventing of outputting standout tag.
		 *
		 * @api bool unsigned Whether or not to show the standout tag.
		 *
		 * @param object $post The post.
		 */
		if ( apply_filters( 'wpseo_news_head_display_standout', true, $this->post ) ) {
			$meta_standout = WPSEO_Meta::get_value( 'newssitemap-standout', $this->post->ID );
			if ( 'on' === $meta_standout && strtotime( $this->post->post_date ) >= strtotime( '-7 days' ) ) {
				echo '<meta name="standout" content="' . esc_url( get_permalink( $this->post->ID ) ) . '" />' . "\n";
			}
		}
	}

	/**
	 * Shows the meta-tag with noindex when it has been decided to exclude the post from Google News.
	 *
	 * @see: https://support.google.com/news/publisher/answer/93977?hl=en
	 */
	private function display_noindex() {
		/**
		 * Filter: 'wpseo_news_head_display_noindex' - Allow preventing of outputting noindex tag.
		 *
		 * @api string $meta_standout The noindex tag.
		 *
		 * @param object $post The post.
		 */
		if ( apply_filters( 'wpseo_news_head_display_noindex', true, $this->post ) ) {
			$robots_index = WPSEO_Meta::get_value( 'newssitemap-robots-index', $this->post->ID );
			if ( ! empty( $robots_index ) ) {
				echo '<meta name="Googlebot-News" content="noindex" />' . "\n";
			}
		}
	}
}
