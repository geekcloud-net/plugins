<?php
/**
 * Forms Report  
 *
 * Ensures all of the reports have a uniform class with helper functions.
 *
 * @since 7.0.0
 *
 * @package MonsterInsights
 * @subpackage Reports
 * @author  Chris Christoff
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MonsterInsights_Report_Forms extends MonsterInsights_Report {

	public $title;
	public $class   = 'MonsterInsights_Report_Forms';
	public $name    = 'forms';
	public $version = '1.0.0';
	public $level   = 'pro';

	/**
	 * Primary class constructor.
	 *
	 * @access public
	 * @since 6.0.0
	 */
	public function __construct() {
		$this->title = __( 'Forms', 'ga-premium' );
		parent::__construct();
	}

	public function requirements( $error = false, $args = array(), $name = '' ) {
		if ( ! empty( $error ) || $name !== 'forms' ) {
			return $error;
		}

		if ( ! class_exists('MonsterInsights_Forms' ) ) {
			return __( 'Please activate the forms addon.', 'ga-premium' );
		}

		if ( version_compare( MonsterInsights_Forms::get_instance()->version, '1.1.0', '<' ) ) {
			return __( 'Please update the forms addon.', 'ga-premium' );
		}

		return $error;
	}

	// Outputs the report.
	protected function get_report_html( $data = array() ){
		ob_start();
		if ( ! empty( $data['forms'] ) ) { ?>
			<div class="monsterinsights-reports-1-column-row panel row container-fluid nopadding" style="position: relative;">
				<div class="monsterinsights-reports-panel-title">
					<?php echo esc_html__( 'Forms', 'ga-premium' );?>
				</div>
				
				<div class="monsterinsights-reports-uright-tooltip monsterinsights-help-tip" style="top:15px;" data-tooltip-title="<?php echo esc_attr( __( 'Forms', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'This list shows the number of conversions, impressions and conversion rate for each form on your website with at least 1 impression during the selected time range.', 'ga-premium' ) ); ?>"></div>
				<div class="monsterinsights-reports-list">
					<table class="table monsterinsights-reports-data-table">
						<thead class="monsterinsights-reports-data-table-thead"> 
							<tr>
								<th><?php echo esc_html__( 'Form Name or ID', 'ga-premium' );?></th>
								<th><?php echo esc_html__( 'Impressions', 'ga-premium' );?></th>
								<th><?php echo esc_html__( 'Conversions', 'ga-premium' );?></th>
								<th><?php echo esc_html__( 'Conversion Rate', 'ga-premium' );?></th>
							</tr>
						</thead> 


						<tbody class="monsterinsights-reports-data-table-tbody">
							<?php $i = 1;
							foreach( $data['forms'] as $iqueries => $formsdata ) { 
							   	$title = $formsdata['id'];
							   	// WPForms
							   	if ( class_exists( 'WPForms' ) ) {
							   		if ( substr( $title, 0, strlen( 'wpforms-submit-' ) ) ) {
							   			$title = str_replace( 'wpforms-submit-', '', $title );
							   			$title = get_the_title( $title );
							   		}
							   	}

							   	// Gravity Forms
							   	if ( class_exists( 'GFAPI' ) ) {
							   		if ( substr( $title, 0, strlen( 'gform_' ) ) ) {
							   			$title = str_replace( 'gform_', '', $title );
							   			$title = GFAPI::get_form( $title );
							   			$title = $title['title'];
							   		}
							   	}

							   	// Contact Form 7
							   	if ( defined( 'WPCF7_VERSION' ) ) {
							   		if ( substr( $title, 0, strlen( 'wpcf7-f' ) ) ) {
							   			$title = str_replace( 'wpcf7-f', '', $title );
							   			$title = get_the_title( $title ); // Example: wpcf7-f1203
							   		}
							   	}
								?>
							<tr class="monsterinsights-reports-data-table-tr">
								<td><?php echo '<span class="monsterinsights-reports-list-count">'. $i .'.</span>&nbsp;<span class="monsterinsights-reports-list-text">' . esc_html( $title ) . '</span>';?></td>
								<td><?php echo esc_html( $formsdata['impressions'] );?></td>
								<td><?php echo esc_html( $formsdata['conversions'] );?></td>
								<td><?php echo number_format( $formsdata['conversionrate'], 2 ) . '%';?></td>
							</tr>
							<?php
							}
							?>
						</tbody>
					</table>
				</div>
				<?php 
				$referral_url = 'https://analytics.google.com/analytics/web/#report/content-event-events/' .  MonsterInsights()->auth->get_referral_url() . $this->get_ga_report_range( $data ) .'%3Fexplorer-table.plotKeys%3D%5B%5D%26_r.drilldown%3Danalytics.eventCategory%3Aform/';
				?>
				<div class="monsterinsights-reports-panel-footer">
					<a href="<?php echo $referral_url; ?>" target="_blank" title="<?php echo esc_html__( 'View Full Forms Report', 'ga-premium' );?>" class="monsterinsights-reports-panel-footer-button"><?php echo esc_html__( 'View Full Forms Report', 'ga-premium' );?></a>
				</div>
			</div>
		<?php } ?>
		<?php
		$html = ob_get_clean();
		return $html;
	}
}