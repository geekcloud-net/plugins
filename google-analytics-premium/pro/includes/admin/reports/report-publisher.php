<?php
/**
 * Publisher Report  
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

final class MonsterInsights_Report_Publisher extends MonsterInsights_Report {

	public $title;
	public $class   = 'MonsterInsights_Report_Publisher';
	public $name    = 'publisher';
	public $version = '1.0.0';
	public $level   = 'plus';

	/**
	 * Primary class constructor.
	 *
	 * @access public
	 * @since 6.0.0
	 */
	public function __construct() {
		$this->title = __( 'Publishers', 'ga-premium' );
		parent::__construct();
	}

	// Outputs the report.
	protected function get_report_html( $data = array() ){
		ob_start(); ?>
			<div class="monsterinsights-reports-1-column-row panel row container-fluid nopadding list-no-icons" style="position: relative;">
				<div class="monsterinsights-reports-panel-title">
					<?php echo esc_html__( 'Top Landing Pages', 'ga-premium' );?>
				</div>
				
				<div class="monsterinsights-reports-uright-tooltip monsterinsights-help-tip" style="top:15px;" data-tooltip-title="<?php echo esc_attr( __( 'Top Landing Pages', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'This list shows the top pages users first land on when visiting your website.', 'ga-premium' ) ); ?>"></div>
				<div class="monsterinsights-reports-list">
					<table id="monsterinsights-report-landing-page-list" class="table monsterinsights-reports-data-table">
						<thead class="monsterinsights-reports-data-table-thead"> 
							<tr>
								<th><?php echo esc_html__( 'Links', 'ga-premium' );?></th>
								<th><?php echo esc_html__( 'Visits', 'ga-premium' );?></th>
								<th><?php echo esc_html__( 'Avg. Duration', 'ga-premium' );?></th>
								<th><?php echo esc_html__( 'Bounce Rate', 'ga-premium' );?></th>
							</tr>
						</thead> 


						<tbody class="monsterinsights-reports-data-table-tbody monsterinsights-reports-pages-list">
							<?php $i = 1;
							if ( ! empty( $data['landingpages'] ) ) { 
								foreach( $data['landingpages'] as $iqueries => $queriesdata ) { 
									$hide = $i > 10 ? ' style="display: none;" ': ''; ?>
								<tr class="monsterinsights-reports-data-table-tr monsterinsights-listing-table-row" <?php echo $hide;?>>
									<td><?php echo '<span class="monsterinsights-reports-list-count">'. $i .'.</span>&nbsp;<span class="monsterinsights-reports-list-text">' . esc_html( $queriesdata['title'] ) . '</span>';?></td>
									<td><?php echo esc_html( $queriesdata['visits'] );?></td>
									<td><?php echo esc_html( $queriesdata['duration'] );?></td>
									<td><?php echo number_format( $queriesdata['bounce'], 2 ) . '%';?></td>
								</tr>
								<?php
									$i++;
								}
							} else { ?>
									<tr class="monsterinsights-reports-data-table-tr monsterinsights-listing-table-row" colspan="4">
										<td><?php echo esc_html__( 'No landing pages tracked during this time period', 'ga-premium') ;?></td>
									</tr>
								<?php }
							?>
						</tbody>
					</table>
				</div>
				<?php 
				$referral_url = 'https://analytics.google.com/analytics/web/#report/content-landing-pages/'. MonsterInsights()->auth->get_referral_url() . $this->get_ga_report_range( $data );
				?>
				<div class="monsterinsights-reports-panel-footer monsterinsights-reports-panel-footer-large">
					<?php echo esc_html__( 'Show', 'ga-premium' );?>&nbsp;
					<div class="monsterinsights-reports-show-selector-group btn-group" role="group" aria-label="<?php echo esc_html__( 'How many to show', 'ga-premium' );?>">
						 <button type="button" data-tid="monsterinsights-report-landing-page-list" class="monsterinsights-reports-show-selector-button ten btn btn-default active" disabled="disabled">10</button>
						 <button type="button" data-tid="monsterinsights-report-landing-page-list" class="monsterinsights-reports-show-selector-button twentyfive btn btn-default">25</button>
						 <button type="button" data-tid="monsterinsights-report-landing-page-list" class="monsterinsights-reports-show-selector-button fifty btn btn-default">50</button>
					</div>
					<a href="<?php echo $referral_url; ?>" target="_blank"  title="<?php echo esc_html__( 'View Full Top Landing Pages Report', 'ga-premium' );?>" class="monsterinsights-reports-panel-footer-button alignright" style="margin-right: 20px;"><?php echo esc_html__( 'View Full Top Landing Pages Report', 'ga-premium' );?></a>
				</div>
			</div>
			<div class="monsterinsights-reports-1-column-row panel row container-fluid nopadding list-no-icons" style="position: relative;">
				<div class="monsterinsights-reports-panel-title">
					<?php echo esc_html__( 'Top Exit Pages', 'ga-premium' );?>
				</div>
				
				<div class="monsterinsights-reports-uright-tooltip monsterinsights-help-tip" style="top:15px;" data-tooltip-title="<?php echo esc_attr( __( 'Top Exit Pages', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'This list shows the top pages users exit your website from.', 'ga-premium' ) ); ?>"></div>
				<div class="monsterinsights-reports-list">
					<table id="monsterinsights-report-exit-page-list" class="table monsterinsights-reports-data-table">
						<thead class="monsterinsights-reports-data-table-thead"> 
							<tr>
								<th><?php echo esc_html__( 'Links', 'ga-premium' );?></th>
								<th><?php echo esc_html__( 'Exits', 'ga-premium' );?></th>
								<th><?php echo esc_html__( 'Page Views', 'ga-premium' );?></th>
								<th><?php echo esc_html__( '% of Exits', 'ga-premium' );?></th>
							</tr>
						</thead> 


						<tbody class="monsterinsights-reports-data-table-tbody monsterinsights-reports-pages-list">
							<?php 
								if ( ! empty( $data['exitpages'] ) ) {
									$i = 1;
									foreach( $data['exitpages'] as $iqueries => $queriesdata ) { 
										$hide = $i > 10 ? ' style="display: none;" ': ''; ?>
									<tr class="monsterinsights-reports-data-table-tr monsterinsights-listing-table-row" <?php echo $hide;?>>
										<td><?php echo '<span class="monsterinsights-reports-list-count">'. $i .'.</span>&nbsp;<span class="monsterinsights-reports-list-text">' . esc_html( $queriesdata['title'] ) . '</span>';?></td>
										<td><?php echo esc_html( $queriesdata['exits'] );?></td>
										<td><?php echo esc_html( $queriesdata['pageviews'] );?></td>
										<td><?php echo number_format( $queriesdata['exitrate'], 2 ) . '%';?></td>
									</tr>
									<?php
										$i++;
									}
								} else { ?>
									<tr class="monsterinsights-reports-data-table-tr monsterinsights-listing-table-row" colspan="4">
										<td><?php echo esc_html__( 'No exit pages tracked during this time period', 'ga-premium') ;?></td>
									</tr>
								<?php }
							?>
						</tbody>
					</table>
				</div>
				<?php 
				$referral_url = 'https://analytics.google.com/analytics/web/#report/content-exit-pages/' . MonsterInsights()->auth->get_referral_url() . $this->get_ga_report_range( $data );
				?>
				<div class="monsterinsights-reports-panel-footer monsterinsights-reports-panel-footer-large">
					<?php echo esc_html__( 'Show', 'ga-premium' );?>&nbsp;
					<div class="monsterinsights-reports-show-selector-group btn-group" role="group" aria-label="<?php echo esc_html__( 'How many to show', 'ga-premium' );?>">
						 <button type="button" data-tid="monsterinsights-report-exit-page-list" class="monsterinsights-reports-show-selector-button ten btn btn-default active" disabled="disabled">10</button>
						 <button type="button" data-tid="monsterinsights-report-exit-page-list" class="monsterinsights-reports-show-selector-button twentyfive btn btn-default">25</button>
						 <button type="button" data-tid="monsterinsights-report-exit-page-list" class="monsterinsights-reports-show-selector-button fifty btn btn-default">50</button>
					</div>
					<a href="<?php echo $referral_url; ?>" target="_blank" title="<?php echo esc_html__( 'View Full Top Exit Pages Report', 'ga-premium' );?>" class="monsterinsights-reports-panel-footer-button alignright" style="margin-right: 20px;"><?php echo esc_html__( 'View Full Top Exit Pages Report', 'ga-premium' );?></a>
				</div>
			</div>

			<div class="monsterinsights-reports-2-column-container row">
			  <div class="monsterinsights-reports-2-column-item col-md-6">
				<div class="monsterinsights-reports-2-column-panel monsterinsights-white-bg-panel panel nopadding list-no-icons">
					<div class="monsterinsights-reports-panel-title">
						<?php echo esc_html__( 'Top Outbound Links', 'ga-premium' );?>
					</div>
					<div class="monsterinsights-reports-uright-tooltip" data-tooltip-title="<?php echo esc_attr( __( 'Top Outbound Links', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'This list shows the top links clicked on your website that go to another website.', 'ga-premium' ) ); ?>"></div>
					<div class="monsterinsights-reports-list">
						<table class="table monsterinsights-reports-data-table">
							<thead class="monsterinsights-reports-data-table-thead"> 
								<tr>
									<th><?php echo esc_html__( 'Links', 'ga-premium' );?></th>
									<th><?php echo esc_html__( 'Total Clicks', 'ga-premium' );?></th>
								</tr>
							</thead> 
							<tbody class="monsterinsights-reports-data-table-tbody">
								<?php $i = 1;
								if ( ! empty( $data['outboundlinks'] ) && is_array( $data['outboundlinks'] ) ) {
									foreach( $data['outboundlinks'] as $iqueries => $queriesdata ) { ?>
										<tr class="monsterinsights-reports-data-table-tr">
											<td><?php echo '<span class="monsterinsights-reports-list-count">'. $i .'.</span>&nbsp;<span class="monsterinsights-reports-list-text">' . esc_html( $queriesdata['title'] ) . '</span>';?></td>
											<td><?php echo number_format_i18n( $queriesdata['clicks'] );?></td>
										</tr>
										<?php
										$i++;
									}
								}  else {
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2" style="text-align:center">' . esc_html__( 'No outbound link clicks detected for this time period.','ga-premium' ) . '</td></tr>';
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">&nbsp;</td></tr>';
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">&nbsp;</td></tr>';
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">&nbsp;</td></tr>';
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">&nbsp;</td></tr>';
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">&nbsp;</td></tr>';
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">&nbsp;</td></tr>';
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">&nbsp;</td></tr>';
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">&nbsp;</td></tr>';
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">&nbsp;</td></tr>';
								}
								?>
							</tbody>
						</table>
					</div>
					<?php 
					$referral_url = 'https://analytics.google.com/analytics/web/#report/content-event-events/' . MonsterInsights()->auth->get_referral_url() . $this->get_ga_report_range( $data ) . '%3Fexplorer-table.plotKeys%3D%5B%5D%26_r.drilldown%3Danalytics.eventCategory%3Aoutbound-link/';
					?>
					<div class="monsterinsights-reports-panel-footer monsterinsights-reports-panel-footer-large">
						<a href="<?php echo $referral_url; ?>" target="_blank" title="<?php echo esc_html__( 'View All Outbound Links Report', 'ga-premium' );?>" class="monsterinsights-reports-panel-footer-button"><?php echo esc_html__( 'View All Outbound Links Report', 'ga-premium' );?></a>
					</div>
				</div>
			  </div>
			  <div class="monsterinsights-reports-2-column-item col-md-6">
				<div class="monsterinsights-reports-2-column-panel monsterinsights-white-bg-panel panel nopadding list-no-icons">
					<div class="monsterinsights-reports-panel-title">
						<?php echo esc_html__( 'Top Affiliate Links', 'ga-premium' );?>
					</div>
					<div class="monsterinsights-reports-uright-tooltip" data-tooltip-title="<?php echo esc_attr( __( 'Top Affiliate Links', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'This list shows the top affiliate links your visitors clicked on.', 'ga-premium' ) ); ?>"></div>
					<div class="monsterinsights-reports-list">
						<table class="table monsterinsights-reports-data-table">
							<thead class="monsterinsights-reports-data-table-thead"> 
								<tr>
									<th><?php echo esc_html__( 'Links', 'ga-premium' );?></th>
									<th><?php echo esc_html__( 'Total Clicks', 'ga-premium' );?></th>
								</tr>
							</thead> 
							<tbody class="monsterinsights-reports-data-table-tbody">
								<?php $i = 1;
								if ( ! empty( $data['affiliatelinks'] ) && is_array( $data['affiliatelinks'] ) ) {
									foreach( $data['affiliatelinks'] as $iqueries => $queriesdata ) { ?>
										<tr class="monsterinsights-reports-data-table-tr">
											<td><?php echo '<span class="monsterinsights-reports-list-count">'. $i .'.</span>&nbsp;<span class="monsterinsights-reports-list-text">' . esc_html( $queriesdata['title'] ) . '</span>';?></td>
											<td><?php echo number_format_i18n( $queriesdata['clicks'] );?></td>
										</tr>
										<?php
										$i++;
									}
								}  else {
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2" style="text-align:center">' . esc_html__( 'No affiliate link clicks detected for this time period.','ga-premium' ) . '</td></tr>';
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">&nbsp;</td></tr>';
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">&nbsp;</td></tr>';
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">&nbsp;</td></tr>';
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">&nbsp;</td></tr>';
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">&nbsp;</td></tr>';
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">&nbsp;</td></tr>';
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">&nbsp;</td></tr>';
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">&nbsp;</td></tr>';
									echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">&nbsp;</td></tr>';
								}
								?>
							</tbody>
						</table>
					</div>
					<?php 
					$referral_url = 'https://analytics.google.com/analytics/web/#report/content-event-events/'.  MonsterInsights()->auth->get_referral_url() . $this->get_ga_report_range( $data ) . '%3Fexplorer-table.advFilter%3D%5B%5B0%2C%22analytics.eventCategory%22%2C%22BW%22%2C%22outbound-link-%22%2C0%5D%5D%26explorer-table.plotKeys%3D%5B%5D/';
					?>
					<div class="monsterinsights-reports-panel-footer monsterinsights-reports-panel-footer-large">
						<a href="<?php echo $referral_url; ?>" target="_blank" title="<?php echo esc_html__( 'View All Affiliate Links Report', 'ga-premium' );?>" class="monsterinsights-reports-panel-footer-button"><?php echo esc_html__( 'View All Affiliate Links Report', 'ga-premium' );?></a>
					</div>
				</div>
			  </div>
			</div>

			<div class="monsterinsights-reports-1-column-row panel row container-fluid nopadding list-no-icons" style="position: relative;">
				<div class="monsterinsights-reports-panel-title">
					<?php echo esc_html__( 'Top Download Links', 'ga-premium' );?>
				</div>
				
				<div class="monsterinsights-reports-uright-tooltip monsterinsights-help-tip" style="top:15px;" data-tooltip-title="<?php echo esc_attr( __( 'Top Download Links', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'This list shows the download links your visitors clicked the most.', 'ga-premium' ) ); ?>"></div>
				<div class="monsterinsights-reports-list">
					<table class="table monsterinsights-reports-data-table">
						<thead class="monsterinsights-reports-data-table-thead"> 
							<tr>
								<th><?php echo esc_html__( 'Link Label', 'ga-premium' );?></th>
								<th><?php echo esc_html__( 'Clicks', 'ga-premium' );?></th>
							</tr>
						</thead> 


						<tbody class="monsterinsights-reports-data-table-tbody">
							<?php $i = 1;
							if ( ! empty( $data['downloadlinks'] ) && is_array( $data['downloadlinks'] ) ) {
								foreach( $data['downloadlinks'] as $iqueries => $queriesdata ) { ?>
									<tr class="monsterinsights-reports-data-table-tr">
										<td><?php echo '<span class="monsterinsights-reports-list-count"' . $hide . '>'. $i .'.</span>&nbsp;<span class="monsterinsights-reports-list-text">' . esc_html( $queriesdata['title'] ) . '</span>';?></td>
										<td><?php echo number_format_i18n( $queriesdata['clicks'] );?></td>
									</tr>
									<?php
									$i++;
								} 
							} else {
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">' . esc_html__( 'No download link clicks detected for this time period.','ga-premium' ) . '</td></tr>';
							}
							?>
						</tbody>
					</table>
				</div>
				<?php 
				$referral_url = 'https://analytics.google.com/analytics/web/#report/content-event-events/' .  MonsterInsights()->auth->get_referral_url() . $this->get_ga_report_range( $data ) .'%3Fexplorer-table.plotKeys%3D%5B%5D%26_r.drilldown%3Danalytics.eventCategory%3Adownload/';
				?>
				<div class="monsterinsights-reports-panel-footer monsterinsights-reports-panel-footer-large">
					<a href="<?php echo $referral_url; ?>" target="_blank" title="<?php echo esc_html__( 'View All Download Links Report', 'ga-premium' );?>" class="monsterinsights-reports-panel-footer-button"><?php echo esc_html__( 'View All Download Links Report', 'ga-premium' );?></a>
				</div>
			</div>
		<?php if ( ! empty( $data['gender'] ) &&  ! empty( $data['age'] ) ) { ?>
			<div class="monsterinsights-reports-2-column-container row">
			  <div class="monsterinsights-reports-2-column-item col-md-6">
				<div class="monsterinsights-reports-2-column-panel panel monsterinsights-pie-chart-panel chart-panel">
					<div class="monsterinsights-reports-panel-title">
						<?php echo esc_html__( 'Age', 'ga-premium' );?>
					</div>
					<div class="monsterinsights-reports-uright-tooltip" data-tooltip-title="<?php echo esc_attr( __( 'Age', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'This graph shows what percent of your users are in a particular age group.', 'ga-premium' ) ); ?>"></div>
					<div class="monsterinsights-reports-pie-graph monsterinsights-clear">
						<div id="monsterinsights-chartjs-pie-age-tooltip" style="opacity: 0;position:absolute;"></div>
						<canvas id="monsterinsights-reports-age-chart" width="250px" height="250px" style="max-width:500px;max-height:250px"></canvas>
						<script type="text/javascript">
						jQuery(document).ready(function() {
							if ( window.uorigindetected != null){

							var pieTooltips = function(tooltip) {
							  // Tooltip Element
							  var tooltipEl = jQuery('#monsterinsights-chartjs-pie-age-tooltip');
							  if (!tooltipEl[0]) {
								jQuery('body').append('<div id="monsterinsights-chartjs-pie-age-tooltip" style="padding:10px;"></div>');
								tooltipEl = jQuery('#monsterinsights-chartjs-pie-age-tooltip');
							  }
							  // Hide if no tooltip
							  if (!tooltip.opacity) {
								tooltipEl.css({
								  opacity: 0
								});
								jQuery('.chartjs-wrap canvas').each(function(index, el) {
								  jQuery(el).css('cursor', 'default');
								});
								return;
							  }
							  jQuery(this._chart.canvas).css('cursor', 'pointer');

							  // Set caret Position
							  tooltipEl.removeClass('above below no-transform');
							  if (tooltip.yAlign) {
								tooltipEl.addClass(tooltip.yAlign);
							  } else {
								tooltipEl.addClass('no-transform');
							  }
						
							var label  = tooltip.title[0];
							var value  = tooltip.title[1];

							var html  = '<div class="monsterinsights-reports-overview-datagraph-tooltip-container">';
								html += '<div class="monsterinsights-reports-overview-datagraph-tooltip-title">' + label + '</div>';
								html += '<div class="monsterinsights-reports-overview-datagraph-tooltip-number">' + value + '%</div>';
								html += '</div>';

							tooltipEl.html(html);
							  
							  // Find Y Location on page
							  var top = 0;
							  
							  if (tooltip.yAlign) {
								var ch = 0;
								if (tooltip.caretHeight) {
								  ch = tooltip.caretHeight;
								}
								if (tooltip.yAlign == 'above') {
								  top = tooltip.y - ch - tooltip.caretPadding;
								} else {
								  top = tooltip.y + ch + tooltip.caretPadding;
								}
							  }
							  // Display, position, and set styles for font
							  tooltipEl.css({
								opacity: 1,
								width: tooltip.width ? (tooltip.width + 'px') : 'auto',
								left: tooltip.x - 50 + 'px',
								top: top - 40 +'px',
								fontFamily: tooltip._fontFamily,
								fontSize: tooltip.fontSize,
								fontStyle: tooltip._fontStyle,
								padding: tooltip.yPadding + 'px ' + tooltip.xPadding + 'px',
								'z-index': 99999,
							  });
							};

								var config = {
									type: 'bar',
									data: {
										datasets: [{
											data: [<?php echo implode( ', ', $data['age']['graph']['data'] ); ?>],
											backgroundColor: [<?php echo implode( ', ', $data['age']['graph']['colors'] ); ?>],
										}],
										values: [<?php echo implode( ', ', $data['age']['graph']['data'] ); ?>],
										labels: [<?php echo implode( ', ', $data['age']['graph']['labels'] ); ?>],
									},
									options: {
										responsive: true,
										maintainAspectRatio: false,
										scales: {
											yAxes: [{
												ticks: {
													min: 0,
													max: 100
												}
											}]
										},
										tooltips: {
											enabled: false,
											yAlign: 'top',
											xAlign: 'top',
											intersect: true,
											custom: pieTooltips,
											callbacks: {
												  title: function(tooltipItem, data) {
													  tooltipItem    = tooltipItem[0];
													  var label      = data.labels[tooltipItem.index];
													  var value      = data.datasets[0].data[tooltipItem.index];
													  return [label,value];
												  },
												  label: function(tooltipItem, data) {
													 return '';
												  }
											}
										},
										animation: false,
										legendCallback: function (chart) {
											var text = [];
											text.push('<ul class="' + chart.id + '-legend" style="list-style:none">');
											for (var i = 0; i < chart.data.datasets[0].data.length; i++) {
												text.push('<li style="color: #23282d;font-size: 16px;font-weight: 400;"><div style="color: #23282d;width:12px;height:12px;display:inline-block;background:' + chart.data.datasets[0].backgroundColor[i] + '" />&nbsp;');
												if ( typeof(chart) != 'undefined' && typeof(chart.data) != 'undefined' && typeof(chart.data.labels) != 'undefined' && typeof(chart.data.labels[i] ) != 'undefined' ) {
													text.push(chart.data.labels[i]);
												} 

												if (  typeof(chart) != 'undefined' && typeof(chart.data) != 'undefined' && typeof(chart.data.values) != 'undefined' && typeof(chart.data.values[i] ) != 'undefined' ) {
													text.push('<span class="monsterinsights-pie-chart-legend-number">' + chart.data.values[i] + '%</span>');
												}
												text.push('</li>');
											}
											text.push('</ul>');

											return text.join('');
										},
										legend: {display: false},
									}
								};
								var publisherage = new Chart( document.getElementById( "monsterinsights-reports-age-chart").getContext("2d"), config);
							}
						});
						</script>
					</div>
				</div>
			</div>
			<div class="monsterinsights-reports-2-column-item col-md-6">
				<div class="monsterinsights-reports-2-column-panel panel monsterinsights-pie-chart-panel chart-panel">
					<div class="monsterinsights-reports-panel-title">
						<?php echo esc_html__( 'Gender', 'ga-premium' );?>
					</div>
					<div class="monsterinsights-reports-uright-tooltip" data-tooltip-title="<?php echo esc_attr( __( 'Gender', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'This graph shows the gender breakdown of your website visitors.', 'ga-premium' ) ); ?>"></div>
					<div class="monsterinsights-reports-pie-graph monsterinsights-clear">
						<div id="monsterinsights-chartjs-pie-gender-tooltip" style="opacity: 0;position:absolute;"></div>
						<canvas id="monsterinsights-reports-gender-chart" width="250px" height="250px" style="max-width:250px;max-height:250px"></canvas>
						<script type="text/javascript">
						jQuery(document).ready(function() {
							if ( window.uorigindetected != null){

							var pieTooltips = function(tooltip) {
							  // Tooltip Element
							  var tooltipEl = jQuery('#monsterinsights-chartjs-pie-gender-tooltip');
							  if (!tooltipEl[0]) {
								jQuery('body').append('<div id="monsterinsights-chartjs-pie-gender-tooltip" style="padding:10px;"></div>');
								tooltipEl = jQuery('#monsterinsights-chartjs-pie-gender-tooltip');
							  }
							  // Hide if no tooltip
							  if (!tooltip.opacity) {
								tooltipEl.css({
								  opacity: 0
								});
								jQuery('.chartjs-wrap canvas').each(function(index, el) {
								  jQuery(el).css('cursor', 'default');
								});
								return;
							  }
							  jQuery(this._chart.canvas).css('cursor', 'pointer');

							  // Set caret Position
							  tooltipEl.removeClass('above below no-transform');
							  if (tooltip.yAlign) {
								tooltipEl.addClass(tooltip.yAlign);
							  } else {
								tooltipEl.addClass('no-transform');
							  }
						
							var label  = tooltip.title[0];
							var value  = tooltip.title[1];

							var html  = '<div class="monsterinsights-reports-overview-datagraph-tooltip-container">';
								html += '<div class="monsterinsights-reports-overview-datagraph-tooltip-title">' + label + '</div>';
								html += '<div class="monsterinsights-reports-overview-datagraph-tooltip-number">' + value + '%</div>';
								html += '</div>';

							tooltipEl.html(html);
							  
							  // Find Y Location on page
							  var top = 0;
							  
							  if (tooltip.yAlign) {
								var ch = 0;
								if (tooltip.caretHeight) {
								  ch = tooltip.caretHeight;
								}
								if (tooltip.yAlign == 'above') {
								  top = tooltip.y - ch - tooltip.caretPadding;
								} else {
								  top = tooltip.y + ch + tooltip.caretPadding;
								}
							  }
							  // Display, position, and set styles for font
							  tooltipEl.css({
								opacity: 1,
								width: tooltip.width ? (tooltip.width + 'px') : 'auto',
								left: tooltip.x - 50 + 'px',
								top: top - 40 +'px',
								fontFamily: tooltip._fontFamily,
								fontSize: tooltip.fontSize,
								fontStyle: tooltip._fontStyle,
								padding: tooltip.yPadding + 'px ' + tooltip.xPadding + 'px',
								'z-index': 99999,
							  });
							};

								var config = {
									type: 'doughnut',
									data: {
										datasets: [{
											data: [<?php echo implode( ', ', $data['gender']['graph']['data'] ); ?>],
											backgroundColor: [<?php echo implode( ', ', $data['gender']['graph']['colors'] ); ?>],
										}],
										values: [<?php echo implode( ', ', $data['gender']['graph']['data'] ); ?>],
										labels: [<?php echo implode( ', ',  $data['gender']['graph']['labels'] ); ?>],
									},
									options: {
										responsive: true,
										maintainAspectRatio: false,
										tooltips: {
											enabled: false,
											yAlign: 'top',
											xAlign: 'top',
											intersect: true,
											custom: pieTooltips,
											callbacks: {
												  title: function(tooltipItem, data) {
													  tooltipItem    = tooltipItem[0];
													  var label      = data.labels[tooltipItem.index];
													  var value      = data.datasets[0].data[tooltipItem.index];
													  return [label,value];
												  },
												  label: function(tooltipItem, data) {
													 return '';
												  }
											}
										},
										animation: false,
										legendCallback: function (chart) {
											var text = [];
											text.push('<ul class="' + chart.id + '-legend" style="list-style:none">');
											for (var i = 0; i < chart.data.datasets[0].data.length; i++) {
												text.push('<li style="color: #23282d;font-size: 16px;font-weight: 400;"><div style="color: #23282d;width:12px;height:12px;display:inline-block;background:' + chart.data.datasets[0].backgroundColor[i] + '" />&nbsp;');
												if ( typeof(chart) != 'undefined' && typeof(chart.data) != 'undefined' && typeof(chart.data.labels) != 'undefined' && typeof(chart.data.labels[i] ) != 'undefined' ) {
													text.push(chart.data.labels[i]);
												} 

												if (  typeof(chart) != 'undefined' && typeof(chart.data) != 'undefined' && typeof(chart.data.values) != 'undefined' && typeof(chart.data.values[i] ) != 'undefined' ) {
													text.push('<span class="monsterinsights-pie-chart-legend-number">' + chart.data.values[i] + '%</span>');
												}
												text.push('</li>');
											}
											text.push('</ul>');

											return text.join('');
										},
										legend: {display: false},
									}
								};
								var genderbreakdown = new Chart( document.getElementById( "monsterinsights-reports-gender-chart").getContext("2d"), config);
								jQuery(".monsterinsights-publisher-report-gender-key").html(genderbreakdown.generateLegend()); 
							}
						});
						</script>
					</div>
					<div class="monsterinsights-publisher-report-gender-key monsterinsights-reports-pie-graph-key"></div>
				</div>
			  </div>
			</div>
		<?php } 		
		if ( ! empty( $data['interest'] ) ) { ?>
			<div class="monsterinsights-reports-1-column-row panel row container-fluid nopadding list-no-icons" style="position: relative;">
				<div class="monsterinsights-reports-panel-title">
					<?php echo esc_html__( 'Interests', 'ga-premium' );?>
				</div>
				
				<div class="monsterinsights-reports-uright-tooltip monsterinsights-help-tip" style="top:15px;" data-tooltip-title="<?php echo esc_attr( __( 'Interests', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'This list shows the interest groups your visitors belong to.', 'ga-premium' ) ); ?>"></div>
				<div class="monsterinsights-reports-list">
					<table class="table monsterinsights-reports-data-table">
						<thead class="monsterinsights-reports-data-table-thead"> 
							<tr>
								<th><?php echo esc_html__( 'Categories', 'ga-premium' );?></th>
								<th><?php echo esc_html__( '% of Interest', 'ga-premium' );?></th>
							</tr>
						</thead> 


						<tbody class="monsterinsights-reports-data-table-tbody">
							<?php
							if ( ! empty( $data['interest'] ) ) {

								 $i = 1;
								foreach( $data['interest'] as $iqueries => $queriesdata ) { ?>
									<tr class="monsterinsights-reports-data-table-tr">
										<td><?php echo '<span class="monsterinsights-reports-list-count"' . $hide . '>'. $i .'.</span>&nbsp;<span class="monsterinsights-reports-list-text">' . $queriesdata['interest'] . '</span>';?></td>
										<td><?php echo number_format( $queriesdata['percent'], 2 ) . '%';?></td>
									</tr>
									<?php
									$i++;
								}
							} else {
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2">' . esc_html__( 'No interest groups detected for this time period.','ga-premium' ) . '</td></tr>';
							}
							?>
						</tbody>
					</table>
				</div>
				<?php 
				$referral_url = 'https://analytics.google.com/analytics/web/#report/visitors-demographics-interest-others/'. MonsterInsights()->auth->get_referral_url() . $this->get_ga_report_range( $data );
				?>
				<div class="monsterinsights-reports-panel-footer monsterinsights-reports-panel-footer-large">
					<a href="<?php echo $referral_url; ?>" target="_blank" title="<?php echo esc_html__( 'View Full Interests Report', 'ga-premium' );?>" class="monsterinsights-reports-panel-footer-button"><?php echo esc_html__( 'View Full Interests Report', 'ga-premium' );?></a>
				</div>
			</div>
		<?php }
		$html = ob_get_clean();
		return $html;
	}
}