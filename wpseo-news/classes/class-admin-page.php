<?php
/**
 * Yoast SEO: News plugin file.
 *
 * @package WPSEO_News\Admin
 */

/**
 * Represents the admin page.
 */
class WPSEO_News_Admin_Page {

	/**
	 * Options.
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->options = WPSEO_News::get_options();

		if ( $this->is_news_page( filter_input( INPUT_GET, 'page' ) ) ) {
			$this->register_i18n_promo_class();
		}

		// When the timezone is an empty string.
		$this->add_timezone_notice();
	}

	/**
	 * Display admin page.
	 */
	public function display() {
		// Admin header.
		WPSEO_News_Wrappers::admin_header( true, 'yoast_wpseo_news_options', 'wpseo_news' );

		// Introduction.
		echo '<p>' . esc_html__( 'You will generally only need a News Sitemap when your website is included in Google News.', 'wordpress-seo-news' ) . '</p>';
		echo '<p>';
		printf(
			/* translators: %1$s opening tag of the link to the News Sitemap, %2$s closing tag for the link. */
			esc_html__( '%1$sView your News Sitemap%2$s.', 'wordpress-seo-news' ),
			'<a target="_blank" href="' . esc_url( WPSEO_News_Sitemap::get_sitemap_name() ) . '">',
			'</a>'
		);
		echo '</p>';

		echo '<h2>' . esc_html__( 'General settings', 'wordpress-seo-news' ) . '</h2>';

		echo '<fieldset><legend class="screen-reader-text">' . esc_html__( 'News Sitemap settings', 'wordpress-seo-news' ) . '</legend>';

		// Google News Publication Name.
		echo WPSEO_News_Wrappers::textinput( 'name', __( 'Google News Publication Name', 'wordpress-seo-news' ) );

		// Default Genre.
		echo WPSEO_News_Wrappers::select(
			'default_genre',
			__( 'Default Genre', 'wordpress-seo-news' ),
			WPSEO_News::list_genres()
		);

		echo '</fieldset>';

		// Post Types to include in News Sitemap.
		$this->include_post_types();

		// Post categories to exclude.
		$this->excluded_post_categories();

		// Editors' Picks.
		$this->editors_pick();

		// Admin footer.
		WPSEO_News_Wrappers::admin_footer( true, false );
	}

	/**
	 * Register the promotion class for our GlotPress instance.
	 *
	 * @link https://github.com/Yoast/i18n-module
	 */
	protected function register_i18n_promo_class() {
		new Yoast_I18n_v3(
			array(
				'textdomain'     => 'wordpress_seo_news',
				'project_slug'   => 'news-seo',
				'plugin_name'    => 'WordPress SEO News',
				'hook'           => 'wpseo_admin_promo_footer',
				'glotpress_url'  => 'http://translate.yoast.com/gp/',
				'glotpress_name' => 'Yoast Translate',
				'glotpress_logo' => 'http://translate.yoast.com/gp-templates/images/Yoast_Translate.svg',
				'register_url'   => 'http://translate.yoast.com/gp/projects#utm_source=plugin&utm_medium=promo-box&utm_campaign=wpseo-news-i18n-promo',
			)
		);
	}

	/**
	 * Generate HTML for the post types which should be included in the sitemap.
	 */
	private function include_post_types() {
		// Post Types to include in News Sitemap.
		echo '<h2>' . esc_html__( 'Post Types to include in News Sitemap and Editors&#39; Picks RSS', 'wordpress-seo-news' ) . '</h2>';
		echo '<fieldset><legend class="screen-reader-text">' . esc_html__( 'Post Types to include:', 'wordpress-seo-news' ) . '</legend>';
		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $posttype ) {
			echo WPSEO_News_Wrappers::checkbox( 'newssitemap_include_' . $posttype->name, $posttype->labels->name . ' (<code>' . $posttype->name . '</code>)', false );
		}
		echo '</fieldset><br>';
	}

	/**
	 * Generate HTML for excluding post categories.
	 */
	private function excluded_post_categories() {
		if ( isset( $this->options['newssitemap_include_post'] ) ) {
			echo '<h2>' . esc_html__( 'Post categories to exclude', 'wordpress-seo-news' ) . '</h2>';
			echo '<fieldset><legend class="screen-reader-text">' . esc_html__( 'Post categories to exclude', 'wordpress-seo-news' ) . '</legend>';
			foreach ( get_categories() as $cat ) {
				echo WPSEO_News_Wrappers::checkbox( 'catexclude_' . $cat->slug, $cat->name . ' (' . $cat->count . ' posts)', false );
			}
			echo '</fieldset><br>';
		}
	}

	/**
	 * Part with HTML for editors picks.
	 */
	private function editors_pick() {
		echo '<h2>' . esc_html__( "Editors' Picks", 'wordpress-seo-news' ) . '</h2>';

		echo '<label class="select" for="ep_image_src">' . esc_html__( "Editors' Picks Image", 'wordpress-seo-news' ) . ':</label>';
		echo '<input id="ep_image_src" type="text" size="36" name="wpseo_news[ep_image_src]" value="' . esc_attr( $this->options['ep_image_src'] ) . '" />';
		echo '<input id="ep_image_src_button" class="wpseo_image_upload_button button" type="button" value="' . esc_attr__( 'Upload Image', 'wordpress-seo-news' ) . '" />';
		echo '<br class="clear"/>';

		echo '<p>';
		printf(
			/* translators: %1$s opening tag of the link to the Editors Picks RSS, %2$s closing tag for the link. */
			esc_html__( '%1$sView your Editors\' Picks RSS Feed%2$s.', 'wordpress-seo-news' ),
			'<a target="_blank" href="' . esc_url( home_url( 'editors-pick.rss' ) ) . '">',
			'</a>'
		);
		echo '</p>';

		echo '<p>';
		printf(
			/* translators: %1$s opening tag of the link to the Google Editors Picks submit page, %2$s closing tag for the link. */
			esc_html__( '%1$sSubmit your Editors\' Picks RSS Feed to Google News%2$s.', 'wordpress-seo-news' ),
			'<a href="https://support.google.com/news/publisher/contact/editors_picks" target="_blank">',
			'</a>'
		);
		echo '</p>';
	}

	/**
	 * Checks if the current page is a news seo plugin page.
	 *
	 * @param string $page The page to check.
	 *
	 * @return bool True when currently on a new page.
	 */
	protected function is_news_page( $page ) {
		$news_pages = array( 'wpseo_news' );

		return in_array( $page, $news_pages, true );
	}

	/**
	 * Shows a notice when the timezone is in UTC format.
	 */
	private function add_timezone_notice() {
		if ( ! class_exists( 'Yoast_Notification_Center' ) ) {
			return;
		}

		$notification_message = sprintf(
			/* translators: %1$s resolves to the opening tag of the link to the general settings page, %1$s resolves to the closing tag for the link */
			__( 'Your timezone settings should reflect your real timezone, not a UTC offset, please change this on the %1$sGeneral Settings page%2$s.', 'wordpress-seo-news' ),
			'<a href="' . esc_url( admin_url( 'options-general.php' ) ) . '">',
			'</a>'
		);

		$notification_options = array(
			'type'         => Yoast_Notification::ERROR,
			'id'           => 'wpseo-news_timezone_format_empty',
		);

		$timezone_notification = new Yoast_Notification( $notification_message, $notification_options );

		$notification_center = Yoast_Notification_Center::get();

		if ( get_option( 'timezone_string' ) === '' ) {
			$notification_center->add_notification( $timezone_notification );
		}
		else {
			$notification_center->remove_notification( $timezone_notification );
		}
	}
}
