<?php
/**
 * Dimensions Report  
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

final class MonsterInsights_Report_Dimensions extends MonsterInsights_Report {

	public $title;
	public $class   = 'MonsterInsights_Report_Dimensions';
	public $name    = 'dimensions';
	public $version = '1.0.0';
	public $level   = 'pro';

	/**
	 * Primary class constructor.
	 *
	 * @access public
	 * @since 6.0.0
	 */
	public function __construct() {
		$this->title = __( 'Dimensions', 'ga-premium' );
		parent::__construct();
	}

	public function requirements( $error = false, $args = array(), $name = '' ) {
		if ( ! empty( $error ) || $name !== 'dimensions' ) {
			return $error;
		}

		if ( ! class_exists('MonsterInsights_Admin_Custom_Dimensions' ) ) {
			return __( 'Please activate the custom dimensions addon.', 'ga-premium' );
		}

		if ( version_compare( MonsterInsights_Dimensions::get_instance()->version, '1.1.0', '<' ) ) {
			return __( 'Please update the dimensions addon.', 'ga-premium' );
		}

		$dimensions = $this->get_active_enabled_custom_dimensions();
		if ( empty( $dimensions ) || ! is_array( $dimensions ) ) {
			return __( 'Please enable at least 1 dimension to use this report', 'ga-premium' );
		}
		
		return $error;
	}

	public function additional_data() {
		$dimensions = $this->get_active_enabled_custom_dimensions();
		return array( 'count' => count( $dimensions ) );
	}

	// Outputs the report.
	protected function get_report_html( $data = array() ){
		ob_start();
		$dimensions = $this->get_active_enabled_custom_dimensions();
		$i = 0;
		foreach ( $dimensions as $index => $dimension ) {
		?>
		<?php if ( $i % 2 == 0 ) { ?>
		<div class="monsterinsights-reports-2-column-container row">
		<?php } ?>
		  <div class="monsterinsights-reports-2-column-item col-md-6">
			<div class="monsterinsights-reports-2-column-panel monsterinsights-white-bg-panel panel nopadding list-no-icons">
				<div class="monsterinsights-reports-panel-title">
					<?php echo $dimension['label'];?>
				</div>
				<div class="monsterinsights-reports-uright-tooltip" data-tooltip-title="<?php echo esc_attr( $dimension['name'] ); ?>" data-tooltip-description="<?php echo esc_attr( $dimension['label'] ); ?>"></div>
				<div class="monsterinsights-reports-list">
					<table class="table monsterinsights-reports-data-table">
						<thead class="monsterinsights-reports-data-table-thead"> 
							<tr>
								<th><?php echo $dimension['name'];?></th>
								<?php if ( ! empty( $dimension['metric'] ) && $dimension['metric'] === 'pageview' ) { ?>
								<th><?php echo esc_html__( 'Pageviews', 'ga-premium' );?></th>
								<?php } else { ?>
								<th><?php echo esc_html__( 'Sessions', 'ga-premium' );?></th>
								<?php } ?>
							</tr>
						</thead> 
						<tbody class="monsterinsights-reports-data-table-tbody">
							<?php $y = 1;
							if ( ! empty( $data[ $index ]['data'] ) && is_array( $data[ $index ]['data'] ) ) {
								foreach( $data[ $index ]['data'] as $iqueries => $queriesdata ) { ?>
									<tr class="monsterinsights-reports-data-table-tr">
										<td><?php echo '<span class="monsterinsights-reports-list-count">'. $y .'</span>'; ?>
											<?php
											$title =  $queriesdata['label'];
											if ( $title === 'true' ) {
												$title = __( 'Logged In', 'monsterinsights-dimensions' );
											} else if ( $title === 'false' ) {
												$title = __( 'Logged Out', 'monsterinsights-dimensions' );
											}
											if ( is_string( $title ) && preg_match('/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/', $title ) > 0 && $title !== 0 ) {
												$title = date( 'l, F jS, Y \a\t g:ia ', strtotime( $title, current_time('timestamp') ) );
											}
											?>
											<?php echo '<span class="monsterinsights-reports-list-text">' . $title . '</span>';?></td>
										<?php if ( ! empty( $dimension['metric'] ) && $dimension['metric'] === 'pageview' ) { ?>
										<td><?php echo $queriesdata['pageview'];?></td>
										<?php } else { ?>
										<td><?php echo $queriesdata['sessions'];?></td>
										<?php } ?>
									</tr>
									<?php
									$y++;
								}
								for ( $y; $y < 11; $y++ ) { // if we have less than 10, make empty rows ?>
									<tr class="monsterinsights-reports-data-table-tr">
										<td colspan="2">&nbsp;</td>
									</tr>
								<?php }
							}  else {
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="2" style="text-align:center">' . esc_html__( 'No data for this time period.','ga-premium' ) . '</td></tr>';
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
				$referral_url = 'https://analytics.google.com/analytics/web/#report/visitors-custom-variables/' . MonsterInsights()->auth->get_referral_url() . $this->get_ga_report_range( $data ) . '%3Fexplorer-segmentExplorer.segmentId%3Danalytics.customVarName' . $dimension['id'] . '%26explorer-table.plotKeys%3D%5B%5D/';
				?>
				<div class="monsterinsights-reports-panel-footer monsterinsights-reports-panel-footer-large">
					<a href="<?php echo $referral_url; ?>" target="_blank"  title="<?php echo esc_html( sprintf( __( 'View %s Report', 'ga-premium' ), $dimension['title'] ) );?>" class="monsterinsights-reports-panel-footer-button"><?php echo esc_html( sprintf( __( 'View %s Report', 'ga-premium' ), $dimension['name'] ) );?></a>
				</div>
			</div>
		  </div>
			<?php if ( ! ( $i % 2 == 0 ) ) { ?>
			</div>
			<?php } ?>
		<?php
		$i++;
		}
		$html = ob_get_clean();
		return $html;
	}

	/**
	 * Get the custom dimensions for the dashboard
	 *
	 * @return array
	 */
	private function get_active_enabled_custom_dimensions() {
		$dimensions                = array();
		$custom_dimensions         = new MonsterInsights_Admin_Custom_Dimensions();
		$default_custom_dimensions = $custom_dimensions->custom_dimensions();
		$custom_dimension_option   = monsterinsights_get_option( 'custom_dimensions', array() );

		foreach ( $custom_dimension_option as $dimension ) {
			$dimensions[$dimension['id']] = array(
				'id'      => $dimension['id'],
				'key'     => $dimension['type'],
				'name'    => $default_custom_dimensions[ $dimension['type'] ]['title'],
				'label'   => $default_custom_dimensions[ $dimension['type'] ]['label'],
				'enabled' => $default_custom_dimensions[ $dimension['type'] ]['enabled'],
				'metric'  => $default_custom_dimensions[ $dimension['type'] ]['metric'],
			);
		}

		return $dimensions;
	}
}