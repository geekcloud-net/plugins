<?php
/**
 * Queries Report  
 *
 * Ensures all of the reports have a uniform class with helper functions.
 *
 * @since 6.0.0
 *
 * @package MonsterInsights
 * @subpackage Reports
 * @author  Chris Christoff
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class MonsterInsights_Report_Queries extends MonsterInsights_Report {

	public $title;
	public $class   = 'MonsterInsights_Report_Queries';
	public $name    = 'queries';
	public $version = '1.0.0';
	public $level   = 'plus';

	/**
	 * Primary class constructor.
	 *
	 * @access public
	 * @since 6.0.0
	 */
	public function __construct() {
		$this->title = __( 'Search Console', 'ga-premium' );
		parent::__construct();
	}

	// Outputs the report.
	protected function get_report_html( $data = array() ){
		ob_start();
		if ( ! empty( $data['queries'] ) ) { ?>
			<div class="monsterinsights-reports-1-column-row panel row container-fluid nopadding" style="position: relative;">
				<div class="monsterinsights-reports-panel-title">
					<?php echo esc_html__( 'Top 50 Google Search Terms', 'ga-premium' );?>
				</div>
				
				<div class="monsterinsights-reports-uright-tooltip monsterinsights-help-tip" style="top:15px;" data-tooltip-title="<?php echo esc_attr( __( 'Top 50 Queries', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'This list shows the top 50 Google search queries for your website.', 'ga-premium' ) ); ?>"></div>
				<div class="monsterinsights-reports-list">
					<table class="table monsterinsights-reports-data-table">
						<thead class="monsterinsights-reports-data-table-thead"> 
							<tr>
								<th><?php echo esc_html__( 'Terms', 'ga-premium' );?></th>
								<th><?php echo esc_html__( 'Clicks', 'ga-premium' );?></th>
								<th><?php echo esc_html__( 'Impressions', 'ga-premium' );?></th>
								<th><?php echo esc_html__( 'CTR', 'ga-premium' );?></th>
								<th><?php echo esc_html__( 'Avg. Position', 'ga-premium' );?></th>
							</tr>
						</thead> 


						<tbody class="monsterinsights-reports-data-table-tbody">
							<?php $i = 1;
							foreach( $data['queries'] as $iqueries => $queriesdata ) { ?>
							<tr class="monsterinsights-reports-data-table-tr">
								<td><?php echo '<span class="monsterinsights-reports-list-count">'. $i .'.</span>&nbsp;<span class="monsterinsights-reports-list-text">' . esc_html( $queriesdata['term'] ) . '</span>';?></td>
								<td><?php echo esc_html( $queriesdata['clicks'] );?></td>
								<td><?php echo esc_html( $queriesdata['impressions'] );?></td>
								<td><?php echo number_format( $queriesdata['ctr'], 2 ) . '%';?></td>
								<td><?php echo number_format( $queriesdata['position'], 1 );?></td>
							</tr>
							<?php
								if ( $i == 25 && false ) {
									?>
									<tr><td colspan="5" style="padding:0px"><a href="https://goo.gl/NQzFmx" title="<?php esc_attr_e('Click to learn more about your competitors with SEMRush', 'ga-premium');?>"><img width="100%" height="auto" title="<?php esc_attr_e('Click to learn more about your competitors with SEMRush', 'ga-premium');?>" src="<?php echo MONSTERINSIGHTS_PLUGIN_URL . 'pro/assets/img/semrush-banner.jpg';?>"></a></td></tr>
									<?php
								}

								$i++;
							}
							?>
						</tbody>
					</table>
				</div>
				<?php 
				$referral_url = 'https://analytics.google.com/analytics/web/#report/content-site-search-search-terms/'. MonsterInsights()->auth->get_referral_url() . $this->get_ga_report_range( $data );
				?>
				<div class="monsterinsights-reports-panel-footer">
					<a href="<?php echo $referral_url; ?>" target="_blank" title="<?php echo esc_html__( 'View Full Queries Report', 'ga-premium' );?>" class="monsterinsights-reports-panel-footer-button"><?php echo esc_html__( 'View Full Queries Report', 'ga-premium' );?></a>
				</div>
			</div>
		<?php } ?>
		<?php
		$html = ob_get_clean();
		return $html;
	}

	
}