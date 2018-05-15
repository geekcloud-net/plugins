<?php
/**
 * Admin Dashboard
 *
 * @package Framework\Dashboard
 */
class APP_Dashboard extends scbBoxesPage {

	const NEWS_FEED = 'http://feeds2.feedburner.com/appthemes';
	const TUTORIALS_FEED = 'http://feeds.feedburner.com/AppThemesTutorials/';
	const MARKETPLACE_FEED = 'http://feeds.feedburner.com/AppThemesMarketplace/';

	function __construct( $args ) {
		$this->args = wp_parse_args( $args, array(
			'submenu_title' => __( 'Dashboard', APP_TD ),
			'page_slug' => 'app-dashboard',
			'toplevel' => 'menu',
			'position' => 3,
			'screen_icon' => 'themes',
		) );

		$this->boxes = array(
			array( 'news', $this->box_icon( 'newspaper.png' ) . __( 'Latest News', APP_TD ), 'normal' ),
			array( 'tutorials', $this->box_icon( 'book-open.png' ) . __( 'Tutorials', APP_TD ), 'side' ),
			array( 'marketplace', $this->box_icon( 'store.png' ) . __( 'From the Marketplace', APP_TD ), 'side' ),
		);

		scbAdminPage::__construct();
	}

	function news_box() {
		echo '<div class="rss-widget">';
		wp_widget_rss_output( self::NEWS_FEED, array( 'items' => 3, 'show_author' => 0, 'show_date' => 1, 'show_summary' => 1 ) );
		echo '</div>';
	}

	function tutorials_box() {
		echo '<div class="rss-widget">';
		wp_widget_rss_output( self::TUTORIALS_FEED, array( 'items' => 3, 'show_author' => 0, 'show_date' => 1, 'show_summary' => 1 ) );
		echo '</div>';
	}

	function marketplace_box() {
		echo '<div class="rss-widget">';
		wp_widget_rss_output( self::MARKETPLACE_FEED, array( 'items' => 3, 'show_author' => 0, 'show_date' => 1, 'show_summary' => 1 ) );
		echo '</div>';
	}

	function page_init() {
		// This will be enqueued on all admin pages
		wp_enqueue_style( 'app-admin', get_template_directory_uri() . '/includes/admin/admin.css' );

		parent::page_init();
	}

	protected function box_icon( $name ) {
		return html( 'img', array(
			'class' => 'box-icon',
			'src' => appthemes_framework_image( $name )
		) );
	}

	function page_head() {
		wp_enqueue_style( 'dashboard' );

?>
<style type="text/css">
.postbox {
	position: relative;
}

.postbox .hndle span {
	padding-left: 21px;
}

.box-icon {
	position: absolute;
	top: 7px;
	left: 10px;
}
</style>
<?php
	}
}

