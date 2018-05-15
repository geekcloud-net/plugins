<?php
/**
 * eCommerce Report  
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

final class MonsterInsights_Report_eCommerce extends MonsterInsights_Report {

	public $title;
	public $class   = 'MonsterInsights_Report_eCommerce';
	public $name    = 'ecommerce';
	public $version = '1.0.0';
	public $level   = 'pro';

	/**
	 * Primary class constructor.
	 *
	 * @access public
	 * @since 6.0.0
	 */
	public function __construct() {
		$this->title = __( 'eCommerce', 'ga-premium' );
		parent::__construct();
	}

	public function requirements( $error = false, $args = array(), $name = '' ) {
		if ( ! empty( $error ) || $name !== $this->name ) {
			return $error;
		}

		if ( ! class_exists('MonsterInsights_eCommerce' ) ) {
			return __( 'Please activate the ecommerce addon.', 'ga-premium' );
		}

		$enhanced_commerce = (bool) monsterinsights_get_option( 'enhanced_ecommerce', false );

		if ( ! $enhanced_commerce ) {
			return __( 'Please enable enhanced eCommerce in the MonsterInsights eCommerce settings to use the eCommerce report.', 'ga-premium' );
		}
		
		return $error;
	}

	// Outputs the report.
	protected function get_report_html( $data = array() ){
		ob_start();
		if ( ! empty( $data['infobox'] ) ) {
			$up         = MONSTERINSIGHTS_PLUGIN_URL . 'assets/images/up.png';
			$up2x       = MONSTERINSIGHTS_PLUGIN_URL . 'assets/images/up@2x.png';
			$down       = MONSTERINSIGHTS_PLUGIN_URL . 'assets/images/down.png';
			$down2x     = MONSTERINSIGHTS_PLUGIN_URL . 'assets/images/down@2x.png';
			$uplabel    = esc_attr__( 'Up', 'ga-premium' );
			$downlabel  = esc_attr__( 'Down', 'ga-premium' );
			?>
			<div class="monsterinsights-overview-report-infobox-panel panel row container-fluid">
			  <div class="monsterinsights-reports-infobox col-md-3 col-xs-6">
					<div class="monsterinsights-reports-infobox-title">
						<?php echo esc_html__( 'Conversion Rate', 'ga-premium' ); ?>
					</div>
					<div class="monsterinsights-reports-uright-tooltip" data-tooltip-title="<?php echo esc_attr( __( 'Conversion Rate', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'The percentage of website sessions resulting in a transaction.', 'ga-premium' ) ); ?>"></div>
					<div class="monsterinsights-reports-infobox-number">
						<?php echo esc_html( number_format_i18n( $data['infobox']['conversionrate']['value'], 2) ) . '%'; ?>
					</div>
					<?php if ( empty( $data['infobox']['conversionrate']['prev'] ) ) { ?>
					<div class="monsterinsights-reports-infobox-prev">
						<?php echo esc_html__( 'No change', 'ga-premium' ); ?>
					</div>
					<?php } else if ( $data['infobox']['conversionrate']['prev'] > 0 ) { ?>
					<div class="monsterinsights-reports-infobox-prev">
						<img src="<?php echo $up; ?>" srcset="<?php echo $up2x; ?> 2x" alt="<?php echo $uplabel; ?>"/>
						<?php echo esc_html( $data['infobox']['conversionrate']['prev'] ) . '%'; ?>
					</div>
					<?php } else  { ?>
					<div class="monsterinsights-reports-infobox-prev">
						<img src="<?php echo $down; ?>" srcset="<?php echo $down2x; ?> 2x" alt="<?php echo $downlabel; ?>"/>
						<?php echo esc_html( absint( $data['infobox']['conversionrate']['prev'] ) ) . '%'; ?>
					</div>
					<?php } ?>
					<div class="monsterinsights-reports-infobox-compare">
						<?php echo sprintf( esc_html__( 'vs. Previous %s Days', 'ga-premium' ), $data['infobox']['range'] ); ?>
					</div>
			  </div>
			  <div class="monsterinsights-reports-infobox col-md-3 col-xs-6">
					<div class="monsterinsights-reports-infobox-title">
						<?php echo esc_html__( 'Transactions', 'ga-premium' ); ?>
					</div>
					<div class="monsterinsights-reports-uright-tooltip" data-tooltip-title="<?php echo esc_attr( __( 'Transactions', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'The number of orders on your website.', 'ga-premium' ) ); ?>"></div>
					<div class="monsterinsights-reports-infobox-number">
						<?php echo esc_html( number_format_i18n( $data['infobox']['transactions']['value'] ) ); ?>
					</div>
					<?php if ( empty( $data['infobox']['transactions']['prev'] ) ) { ?>
					<div class="monsterinsights-reports-infobox-prev">
						<?php echo esc_html__( 'No change', 'ga-premium' ); ?>
					</div>
					<?php } else if ( $data['infobox']['transactions']['prev'] > 0 ) { ?>
					<div class="monsterinsights-reports-infobox-prev">
						<img src="<?php echo $up; ?>" srcset="<?php echo $up2x; ?> 2x" alt="<?php echo $uplabel; ?>"/>
						<?php echo esc_html( $data['infobox']['transactions']['prev'] ) . '%'; ?>
					</div>
					<?php } else  { ?>
					<div class="monsterinsights-reports-infobox-prev">
						<img src="<?php echo $down; ?>" srcset="<?php echo $down2x; ?> 2x" alt="<?php echo $downlabel; ?>"/>
						<?php echo esc_html( absint( $data['infobox']['transactions']['prev'] ) ) . '%'; ?>
					</div>
					<?php } ?>
					<div class="monsterinsights-reports-infobox-compare">
						<?php echo sprintf( esc_html__( 'vs. Previous %s Days', 'ga-premium' ), $data['infobox']['range'] ); ?>
					</div>
			  </div>
			  <div class="monsterinsights-reports-infobox col-md-3 col-xs-6">
					<div class="monsterinsights-reports-infobox-title">
						<?php echo esc_html__( 'Revenue', 'ga-premium' ); ?>
					</div>
					<div class="monsterinsights-reports-uright-tooltip" data-tooltip-title="<?php echo esc_attr( __( 'Revenue', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'The total of the orders placed.', 'ga-premium' ) ); ?>"></div>
					<div class="monsterinsights-reports-infobox-number">
						<?php echo esc_html( number_format_i18n( $data['infobox']['revenue']['value'], 2 ) ); ?>
					</div>
					<?php if ( empty( $data['infobox']['revenue']['prev'] ) ) { ?>
					<div class="monsterinsights-reports-infobox-prev">
						<?php echo esc_html__( 'No change', 'ga-premium' ); ?>
					</div>
					<?php } else if ( $data['infobox']['revenue']['prev'] > 0 ) { ?>
					<div class="monsterinsights-reports-infobox-prev">
						<img src="<?php echo $up; ?>" srcset="<?php echo $up2x; ?> 2x" alt="<?php echo $uplabel; ?>"/>
						<?php echo esc_html( $data['infobox']['revenue']['prev'] ) . '%'; ?>
					</div>
					<?php } else  { ?>
					<div class="monsterinsights-reports-infobox-prev">
						<img src="<?php echo $down; ?>" srcset="<?php echo $down2x; ?> 2x" alt="<?php echo $downlabel; ?>"/>
						<?php echo esc_html( absint(  $data['infobox']['revenue']['prev'] ) ) . '%'; ?>
					</div>
					<?php } ?>
					<div class="monsterinsights-reports-infobox-compare">
						<?php echo sprintf( esc_html__( 'vs. Previous %s Days', 'ga-premium' ), $data['infobox']['range'] ); ?>
					</div>
			  </div>
			  <div class="monsterinsights-reports-infobox col-md-3 col-xs-6">
					<div class="monsterinsights-reports-infobox-title">
						<?php echo esc_html__( 'Avg. Order Value', 'ga-premium' ); ?>
					</div>
					<div class="monsterinsights-reports-uright-tooltip" data-tooltip-title="<?php echo esc_attr( __( 'Avg. Order Value', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'The average amount of the orders placed on your website.', 'ga-premium' ) ); ?>"></div>
					<div class="monsterinsights-reports-infobox-number">
						<?php echo esc_html( number_format_i18n( $data['infobox']['ordervalue']['value'], 2 ) ); ?>
					</div>
					<?php if ( empty( $data['infobox']['ordervalue']['prev'] ) ) { ?>
					<div class="monsterinsights-reports-infobox-prev">
						<?php echo esc_html__( 'No change', 'ga-premium' ); ?>
					</div>
					<?php } else if ( $data['infobox']['ordervalue']['prev'] > 0 ) { ?>
					<div class="monsterinsights-reports-infobox-prev">
						<img src="<?php echo $up; ?>" srcset="<?php echo $up2x; ?> 2x" alt="<?php echo $uplabel; ?>"/>
						<?php echo esc_html( $data['infobox']['ordervalue']['prev'] ) . '%'; ?>
					</div>
					<?php } else  { ?>
					<div class="monsterinsights-reports-infobox-prev">
						<img src="<?php echo $down; ?>" srcset="<?php echo $down2x; ?> 2x" alt="<?php echo $downlabel; ?>"/>
						<?php echo esc_html( absint( $data['infobox']['ordervalue']['prev'] ) ) . '%'; ?>
					</div>
					<?php } ?>
					<div class="monsterinsights-reports-infobox-compare">
						<?php echo sprintf( esc_html__( 'vs. Previous %s Days', 'ga-premium' ), $data['infobox']['range'] ); ?>
					</div>
			  </div>
			</div>
		<?php } ?>
		<div class="monsterinsights-reports-1-column-row panel row container-fluid nopadding list-no-icons" style="position: relative;">
			<div class="monsterinsights-reports-panel-title">
				<?php echo esc_html__( 'Top Products', 'ga-premium' );?>
			</div>
			
			<div class="monsterinsights-reports-uright-tooltip monsterinsights-help-tip" style="top:15px;" data-tooltip-title="<?php echo esc_attr( __( 'Top Products', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'This list shows the top selling products on your website.', 'ga-premium' ) ); ?>"></div>
			<div class="monsterinsights-reports-list">
				<table id="monsterinsights-report-top-product-list" class="table monsterinsights-reports-data-table">
					<thead class="monsterinsights-reports-data-table-thead"> 
						<tr>
							<th><?php echo esc_html__( 'Product Name', 'ga-premium' );?></th>
							<th><?php echo esc_html__( 'Quantity', 'ga-premium' );?></th>
							<th><?php echo esc_html__( '% of Sales', 'ga-premium' );?></th>
							<th><?php echo esc_html__( 'Total Revenue', 'ga-premium' );?></th>
						</tr>
					</thead> 


					<tbody class="monsterinsights-reports-data-table-tbody monsterinsights-reports-pages-list">
						<?php $i = 1;
						if ( ! empty( $data['products'] ) ) { 
							foreach( $data['products'] as $iqueries => $queriesdata ) { 
								$hide = $i > 10 ? ' style="display: none;" ': ''; ?>
							<tr class="monsterinsights-reports-data-table-tr monsterinsights-listing-table-row" <?php echo $hide;?>>
								<td><?php echo '<span class="monsterinsights-reports-list-count">'. $i .'.</span>&nbsp;<span class="monsterinsights-reports-list-text">' . $queriesdata['name'] . '</span>';?></td>
								<td><?php echo number_format_i18n( $queriesdata['quantity'] );?></td>
								<td><?php echo number_format_i18n( $queriesdata['percent'], 2 ) . '%';?></td>
								<td><?php echo number_format_i18n( $queriesdata['revenue'], 2 );?></td>
							</tr>
							<?php
								$i++;
							}
						} else { ?>
								<tr class="monsterinsights-reports-data-table-tr monsterinsights-listing-table-row">
									<td colspan="4"><?php echo esc_html__( 'No product sales tracked during this time period.', 'ga-premium') ;?></td>
								</tr>
							<?php }
						?>
					</tbody>
				</table>
			</div>
			<?php 
			$referral_url = 'https://analytics.google.com/analytics/web/#report/conversions-ecommerce-product-performance/'. MonsterInsights()->auth->get_referral_url() . $this->get_ga_report_range( $data );
			?>
			<div class="monsterinsights-reports-panel-footer monsterinsights-reports-panel-footer-large">
				<?php echo esc_html__( 'Show', 'ga-premium' );?>&nbsp;
				<div class="monsterinsights-reports-show-selector-group btn-group" role="group" aria-label="<?php echo esc_html__( 'How many to show', 'ga-premium' );?>">
					 <button type="button" data-tid="monsterinsights-report-top-product-list" class="monsterinsights-reports-show-selector-button ten btn btn-default active" disabled="disabled">10</button>
					 <button type="button" data-tid="monsterinsights-report-top-product-list" class="monsterinsights-reports-show-selector-button twentyfive btn btn-default">25</button>
					 <button type="button" data-tid="monsterinsights-report-top-product-list" class="monsterinsights-reports-show-selector-button fifty btn btn-default">50</button>
				</div>
				<a href="<?php echo $referral_url; ?>" target="_blank" title="<?php echo esc_html__( 'View Full Top Products Report', 'ga-premium' );?>" class="monsterinsights-reports-panel-footer-button alignright" style="margin-right: 20px;"><?php echo esc_html__( 'View Full Top Products Report', 'ga-premium' );?></a>
			</div>
		</div>
		<div class="monsterinsights-reports-1-column-row panel row container-fluid nopadding list-has-icons" style="position: relative;">
			<div class="monsterinsights-reports-panel-title">
				<?php echo esc_html__( 'Top Conversion Sources', 'ga-premium' );?>
			</div>
			
			<div class="monsterinsights-reports-uright-tooltip monsterinsights-help-tip" style="top:15px;" data-tooltip-title="<?php echo esc_attr( __( 'Top Conversion Sources', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'This list shows the top referral websites in terms of product revenue.', 'ga-premium' ) ); ?>"></div>
			<div class="monsterinsights-reports-list">
				<table class="table monsterinsights-reports-data-table">
					<thead class="monsterinsights-reports-data-table-thead"> 
						<tr>
							<th><?php echo esc_html__( 'Sources', 'ga-premium' );?></th>
							<th><?php echo esc_html__( 'Visits', 'ga-premium' );?></th>
							<th><?php echo esc_html__( '% of Visits', 'ga-premium' );?></th>
							<th><?php echo esc_html__( 'Revenue', 'ga-premium' );?></th>
						</tr>
					</thead> 


					<tbody class="monsterinsights-reports-data-table-tbody monsterinsights-reports-pages-list">
						<?php $i = 1;
						if ( ! empty( $data['conversions'] ) ) { 
							foreach( $data['conversions'] as $iqueries => $queriesdata ) { ?>
							<tr class="monsterinsights-reports-data-table-tr monsterinsights-listing-table-row">
								<td><?php echo '<span class="monsterinsights-reports-list-count">'. $i .'.</span>&nbsp;<img class="monsterinsights-reports-referral-icon"  src="https://www.google.com/s2/favicons?domain=' . $queriesdata['url'] . '" width="16px" height="16px" /><span class="monsterinsights-reports-list-text">' . $queriesdata['url'] . '</span>';?></td>
								<td><?php echo number_format_i18n( $queriesdata['sessions'] );?></td>
								<td><?php echo number_format_i18n( $queriesdata['percent'], 2 ) . '%';?></td>
								<td><?php echo number_format_i18n( $queriesdata['revenue'], 2 );?></td>
							</tr>
							<?php
								$i++;
							}
						} else { ?>
								<tr class="monsterinsights-reports-data-table-tr monsterinsights-listing-table-row" colspan="4">
									<td colspan="4"><?php echo esc_html__( 'No conversion sources tracked during this time period.', 'ga-premium') ;?></td>
								</tr>
							<?php }
						?>
					</tbody>
				</table>
			</div>
			<?php 
			$referral_url = 'https://analytics.google.com/analytics/web/#report/trafficsources-referrals/'. MonsterInsights()->auth->get_referral_url() . $this->get_ga_report_range( $data ) . '%3F_u.dateOption%3Dlast7days%26explorer-table-dataTable.sortColumnName%3Danalytics.transactionRevenue%26explorer-table-dataTable.sortDescending%3Dtrue%26explorer-table.plotKeys%3D%5B%5D/';
			?>
			<div class="monsterinsights-reports-panel-footer monsterinsights-reports-panel-footer-large">
				<a href="<?php echo $referral_url; ?>" target="_blank" title="<?php echo esc_html__( 'View Top Conversions Sources Report', 'ga-premium' );?>" class="monsterinsights-reports-panel-footer-button"><?php echo esc_html__( 'View Top Conversions Sources Report', 'ga-premium' );?></a>
			</div>
		</div>
		<?php if ( ! empty( $data['infobox'] ) ) {
			$up         = MONSTERINSIGHTS_PLUGIN_URL . 'assets/images/up.png';
			$up2x       = MONSTERINSIGHTS_PLUGIN_URL . 'assets/images/up@2x.png';
			$down       = MONSTERINSIGHTS_PLUGIN_URL . 'assets/images/down.png';
			$down2x     = MONSTERINSIGHTS_PLUGIN_URL . 'assets/images/down@2x.png';
			$uplabel    = esc_attr__( 'Up', 'ga-premium' );
			$downlabel  = esc_attr__( 'Down', 'ga-premium' );
			?>
			<div class="monsterinsights-overview-report-infobox-panel panel row container-fluid">
			  <div class="monsterinsights-reports-infobox col-md-6 col-xs-12">
					<div class="monsterinsights-reports-infobox-title">
						<?php echo esc_html__( 'Total Add to Carts', 'ga-premium' ); ?>
					</div>
					<div class="monsterinsights-reports-uright-tooltip" data-tooltip-title="<?php echo esc_attr( __( 'Total Add to Carts', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'The number of times products on your site were added to the cart.', 'ga-premium' ) ); ?>"></div>
					<div class="monsterinsights-reports-infobox-number">
						<?php echo esc_html( number_format_i18n( $data['infobox']['addtocart']['value'], 0 ) ); ?>
					</div>
					<?php if ( empty( $data['infobox']['addtocart']['prev'] ) ) { ?>
					<div class="monsterinsights-reports-infobox-prev">
						<?php echo esc_html__( 'No change', 'ga-premium' ); ?>
					</div>
					<?php } else if ( $data['infobox']['addtocart']['prev'] > 0 ) { ?>
					<div class="monsterinsights-reports-infobox-prev">
						<img src="<?php echo $up; ?>" srcset="<?php echo $up2x; ?> 2x" alt="<?php echo $uplabel; ?>"/>
						<?php echo esc_html( $data['infobox']['addtocart']['prev'] ) . '%'; ?>
					</div>
					<?php } else  { ?>
					<div class="monsterinsights-reports-infobox-prev">
						<img src="<?php echo $down; ?>" srcset="<?php echo $down2x; ?> 2x" alt="<?php echo $downlabel; ?>"/>
						<?php echo esc_html( absint( $data['infobox']['addtocart']['prev'] ) ) . '%'; ?>
					</div>
					<?php } ?>
					<div class="monsterinsights-reports-infobox-compare">
						<?php echo sprintf( esc_html__( 'vs. Previous %s Days', 'ga-premium' ), $data['infobox']['range'] ); ?>
					</div>
			  </div>
			  <div class="monsterinsights-reports-infobox col-md-6 col-xs-12">
					<div class="monsterinsights-reports-infobox-title">
						<?php echo esc_html__( 'Total Removed from Cart', 'ga-premium' ); ?>
					</div>
					<div class="monsterinsights-reports-uright-tooltip" data-tooltip-title="<?php echo esc_attr( __( 'Total Removed from Cart', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'The number of times products on your site were removed from the cart.', 'ga-premium' ) ); ?>"></div>
					<div class="monsterinsights-reports-infobox-number">
						<?php echo esc_html( number_format_i18n( $data['infobox']['remfromcart']['value'] ) ); ?>
					</div>
					<?php if ( empty( $data['infobox']['remfromcart']['prev'] ) ) { ?>
					<div class="monsterinsights-reports-infobox-prev">
						<?php echo esc_html__( 'No change', 'ga-premium' ); ?>
					</div>
					<?php } else if ( $data['infobox']['remfromcart']['prev'] > 0 ) { ?>
					<div class="monsterinsights-reports-infobox-prev">
						<img src="<?php echo $up; ?>" srcset="<?php echo $up2x; ?> 2x" alt="<?php echo $uplabel; ?>"/>
						<?php echo esc_html( $data['infobox']['remfromcart']['prev'] ) . '%'; ?>
					</div>
					<?php } else  { ?>
					<div class="monsterinsights-reports-infobox-prev">
						<img src="<?php echo $down; ?>" srcset="<?php echo $down2x; ?> 2x" alt="<?php echo $downlabel; ?>"/>
						<?php echo esc_html( absint( $data['infobox']['remfromcart']['prev'] ) ) . '%'; ?>
					</div>
					<?php } ?>
					<div class="monsterinsights-reports-infobox-compare">
						<?php echo sprintf( esc_html__( 'vs. Previous %s Days', 'ga-premium' ), $data['infobox']['range'] ); ?>
					</div>
			  </div>
		</div>
		<?php } ?>
		<div class="monsterinsights-reports-2-column-container row">
		  <div class="monsterinsights-reports-2-column-item col-md-6">
			<div class="monsterinsights-reports-2-column-panel monsterinsights-white-bg-panel panel nopadding list-no-icons">
				<div class="monsterinsights-reports-panel-title">
					<?php echo esc_html__( 'Time to Purchase', 'ga-premium' );?>
				</div>
				<div class="monsterinsights-reports-uright-tooltip" data-tooltip-title="<?php echo esc_attr( __( 'Time to Purchase', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'This list shows how many days from first visit it took users to purchase products from your site.', 'ga-premium' ) ); ?>"></div>
				<div class="monsterinsights-reports-list">
					<table class="table monsterinsights-reports-data-table">
						<thead class="monsterinsights-reports-data-table-thead"> 
							<tr>
								<th><?php echo esc_html__( 'Days', 'ga-premium' );?></th>
								<th><?php echo esc_html__( 'Transactions', 'ga-premium' );?></th>
								<th><?php echo esc_html__( '% of Total', 'ga-premium' );?></th>
							</tr>
						</thead> 
						<tbody class="monsterinsights-reports-data-table-tbody">
							<?php $i = 0;
							if ( ! empty( $data['days'] ) && is_array( $data['days'] ) ) {
								foreach( $data['days'] as $iqueries => $queriesdata ) { ?>
									<tr class="monsterinsights-reports-data-table-tr">
										<td><?php echo '<span class="monsterinsights-reports-list-count">'. $i .'</span>'; ?></td>
										<td><?php echo '<span class="monsterinsights-reports-list-text">' . number_format_i18n( $queriesdata['transactions'], 0 ) . '</span>';?></td>
										<td><?php echo number_format_i18n( $queriesdata['percent'], 2 ). '%';?></td>
									</tr>
									<?php
									$i++;
								}
							}  else {
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3" style="text-align:center">' . esc_html__( 'No data for this time period.','ga-premium' ) . '</td></tr>';
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3">&nbsp;</td></tr>';
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3">&nbsp;</td></tr>';
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3">&nbsp;</td></tr>';
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3">&nbsp;</td></tr>';
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3">&nbsp;</td></tr>';
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3">&nbsp;</td></tr>';
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3">&nbsp;</td></tr>';
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3">&nbsp;</td></tr>';
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3">&nbsp;</td></tr>';
							}
							?>
						</tbody>
					</table>
				</div>
				<?php 
				$referral_url = 'https://analytics.google.com/analytics/web/#report/bf-time-lag/' . MonsterInsights()->auth->get_referral_url() . $this->get_ga_report_range( $data );
				?>
				<div class="monsterinsights-reports-panel-footer monsterinsights-reports-panel-footer-large">
					<a href="<?php echo $referral_url; ?>" target="_blank" title="<?php echo esc_html__( 'View Time to Purchase Report', 'ga-premium' );?>" class="monsterinsights-reports-panel-footer-button"><?php echo esc_html__( 'View Time to Purchase Report', 'ga-premium' );?></a>
				</div>
			</div>
		  </div>
		  <div class="monsterinsights-reports-2-column-item col-md-6">
			<div class="monsterinsights-reports-2-column-panel  monsterinsights-white-bg-panel panel nopadding list-no-icons">
				<div class="monsterinsights-reports-panel-title">
					<?php echo esc_html__( 'Sessions to Purchase', 'ga-premium' );?>
				</div>
				<div class="monsterinsights-reports-uright-tooltip" data-tooltip-title="<?php echo esc_attr( __( 'Sessions to Purchase', 'ga-premium' ) ); ?>" data-tooltip-description="<?php echo esc_attr( __( 'This list shows the number of sessions it took users before they purchased a product from your website.', 'ga-premium' ) ); ?>"></div>
				<div class="monsterinsights-reports-list">
					<table class="table monsterinsights-reports-data-table">
						<thead class="monsterinsights-reports-data-table-thead"> 
							<tr>
								<th><?php echo esc_html__( 'Sessions', 'ga-premium' );?></th>
								<th><?php echo esc_html__( 'Transactions', 'ga-premium' );?></th>
								<th><?php echo esc_html__( '% of Total', 'ga-premium' );?></th>
							</tr>
						</thead> 
						<tbody class="monsterinsights-reports-data-table-tbody">
							<?php $i = 1;
							if ( ! empty( $data['sessions'] ) && is_array( $data['sessions'] ) ) {
								foreach( $data['sessions'] as $iqueries => $queriesdata ) { ?>
									<tr class="monsterinsights-reports-data-table-tr">
										<td><?php echo '<span class="monsterinsights-reports-list-count">'. $i .'</span>'; ?></td>
										<td><?php echo '<span class="monsterinsights-reports-list-text">' . number_format_i18n( $queriesdata['transactions'], 0 ) . '</span>';?></td>
										<td><?php echo number_format_i18n( $queriesdata['percent'], 2 ) . '%';?></td>
									</tr>
									<?php
									$i++;
								}
							}  else {
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3" style="text-align:center">' . esc_html__( 'No data for this time period.','ga-premium' ) . '</td></tr>';
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3">&nbsp;</td></tr>';
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3">&nbsp;</td></tr>';
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3">&nbsp;</td></tr>';
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3">&nbsp;</td></tr>';
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3">&nbsp;</td></tr>';
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3">&nbsp;</td></tr>';
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3">&nbsp;</td></tr>';
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3">&nbsp;</td></tr>';
								echo '<tr class="monsterinsights-reports-data-table-tr"><td colspan="3">&nbsp;</td></tr>';
							}
							?>
						</tbody>
					</table>
				</div>
				<?php 
				$referral_url = 'https://analytics.google.com/analytics/web/#report/bf-time-lag/' . MonsterInsights()->auth->get_referral_url() . $this->get_ga_report_range( $data );
				?>
				<div class="monsterinsights-reports-panel-footer monsterinsights-reports-panel-footer-large">
					<a href="<?php echo $referral_url; ?>" target="_blank" title="<?php echo esc_html__( 'View Session to Purchase Report', 'ga-premium' );?>" class="monsterinsights-reports-panel-footer-button"><?php echo esc_html__( 'View Session to Purchase Report', 'ga-premium' );?></a>
				</div>
			</div>
		  </div>
		</div>
		<?php
		$html = ob_get_clean();
		return $html;
	}
}