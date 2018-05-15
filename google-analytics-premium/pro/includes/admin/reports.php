<?php
/**
 * Pro Admin features.
 *
 * Adds Pro Reporting features.
 *
 * @since 6.0.0
 *
 * @package MonsterInsights Dimensions
 * @subpackage Reports
 * @author  Chris Christoff
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MonsterInsights_Admin_Pro_Reports {

	/**
	 * Primary class constructor.
	 *
	 * @access public
	 * @since 6.0.0
	 */
	public function __construct() {
		add_action( 'admin_print_scripts', array( $this, 'enqueue_js' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_css' ) );
		add_action( 'monsterinsights_tab_reports_actions', array( $this, 'dashboard_refresh_button' ) );

		$this->load_reports();
	}

	public function load_reports() {
		$overview_report = new MonsterInsights_Report_Overview();
		MonsterInsights()->reporting->add_report( $overview_report );

		require_once MONSTERINSIGHTS_PLUGIN_DIR . 'pro/includes/admin/reports/report-publisher.php';
		$publisher_report = new MonsterInsights_Report_Publisher();
		MonsterInsights()->reporting->add_report( $publisher_report );

		require_once MONSTERINSIGHTS_PLUGIN_DIR . 'pro/includes/admin/reports/report-ecommerce.php';
		$ecommerce_report = new MonsterInsights_Report_eCommerce();
		MonsterInsights()->reporting->add_report( $ecommerce_report );

		require_once MONSTERINSIGHTS_PLUGIN_DIR . 'pro/includes/admin/reports/report-queries.php';
		$queries_report = new MonsterInsights_Report_Queries();
		MonsterInsights()->reporting->add_report( $queries_report );

		require_once MONSTERINSIGHTS_PLUGIN_DIR . 'pro/includes/admin/reports/report-dimensions.php';
		$dimensions_report = new MonsterInsights_Report_Dimensions();
		MonsterInsights()->reporting->add_report( $dimensions_report );

		//require_once MONSTERINSIGHTS_PLUGIN_DIR . 'pro/includes/admin/reports/report-forms.php';
		//$forms_report = new MonsterInsights_Report_Forms();
		//MonsterInsights()->reporting->add_report( $forms_report );

	}

	public function enqueue_js() {
		if ( ( filter_input( INPUT_GET, 'page' ) === 'monsterinsights_reports' || filter_input( INPUT_GET, 'page' ) === 'monsterinsights_dashboard' ) && current_user_can( 'monsterinsights_view_dashboard' ) ) {
			wp_enqueue_script( 'monsterinsights_pro_admin_script', MONSTERINSIGHTS_PLUGIN_URL . 'pro/assets/js/admin.js', array('monsterinsights-vendors-script'), monsterinsights_get_asset_version() );
		}
	}

	public function enqueue_css() {
		if ( ( filter_input( INPUT_GET, 'page' ) === 'monsterinsights_reports' || filter_input( INPUT_GET, 'page' ) === 'monsterinsights_dashboard' ) && current_user_can( 'monsterinsights_view_dashboard' ) ) {
			wp_register_style( 'monsterinsights_pro_admin_style',  MONSTERINSIGHTS_PLUGIN_URL . 'pro/assets/css/admin.css', array( 'monsterinsights-vendors-style' ), monsterinsights_get_asset_version() );
			wp_enqueue_style( 'monsterinsights_pro_admin_style' );
		}
	}

	public function dashboard_refresh_button() {
		echo '<div class="monsterinsights-pro-report-date-control-group btn-group" role="group" aria-label="' . esc_html__( 'MonsterInsights Pro Report Date Controls', 'ga-premium' ) . '">
			  <button type="button" class="monsterinsights-pro-report-7-days btn btn-default">' . esc_html__( 'Last 7 days', 'ga-premium' ) . '</button>
			  <button type="button" class="monsterinsights-pro-report-30-days btn btn-default active" disabled="disabled">' . esc_html__( 'Last 30 days', 'ga-premium' ) . '</button>
			  <input class="monsterinsights-pro-datepicker flatpickr flatpickr-input" type="text" placeholder="' . esc_html__( 'Select Custom Date Range', 'ga-premium' ) . '" data-id="monsterinsights-pro-reports-date-range" readonly="readonly">
			</div>';
	}
}