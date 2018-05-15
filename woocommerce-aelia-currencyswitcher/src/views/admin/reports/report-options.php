<?php
namespace Aelia\WC\CurrencySwitcher;
if(!defined('ABSPATH')) exit; // Exit if accessed directly

$text_domain = Definitions::TEXT_DOMAIN;
$report_currency = get_value(Definitions::ARG_REPORT_CURRENCY, $_REQUEST, Definitions::DEF_REPORT_CURRENCY);

$currency_options = array_merge(array(Definitions::DEF_REPORT_CURRENCY => __('All data, totals in base currency', $text_domain)),
																WC_Aelia_Reporting_Manager::get_currencies_from_sales());
?>
<div id="aelia_cs_report_header" class="wc_aelia_currencyswitcher report header Hidden">
	<div class="options">
		<h3><?php echo __('Options', $text_domain); ?></h3>
		<div class="clearfix"><?php
			echo __('Select the options, then click on any of the date ranges to generate ' .
							'the report.', $text_domain);
		?></div>
		<!-- Filter reports by currency -->
		<div class="report_currency clearfix">
			<h4 class="option_title"><?php
				echo __('Show data for this currency', $text_domain); ?>
				<span class="tips"
							more_info_target="report_currency_more_info"
							data-tip="<?php echo __('Choose for which currency you would like to generate ' .
																			'the report. WooCommerce can only show one currency at ' .
																			'a time.', $text_domain); ?>"><?php
					echo __('[What is this?]', $text_domain);
				?></span>
			</h4>
			<!-- Show tax types - More info - Start -->
			<div id="report_currency_more_info" class="more_info Hidden">
				<div class="info">
					<?php
						echo __('This filter allows you to generate a report that includes data ' .
										'only for the specified currency. If you select the "<em>all sales, with ' .
										'totals in base currency</em>" option (which is the default), you will see ' .
										'a grand total of all the sales, in base currency. If you select a specific ' .
										'currency, then only the sales in that currency will be displayed.', $text_domain);
					?>
					<span class="close"><?php
						echo __('[Close]', $text_domain);
					?></span>
				</div>
			</div>
			<!-- Show tax types - More info - End -->
			<ul>
				<li>
					<!--<label for="report_currency_selector"><?php echo __('Report currency', $text_domain); ?></label>-->
					<select id="report_currency_selector" name="<?php echo Definitions::ARG_REPORT_CURRENCY; ?>" target_field="report_currency"><?php
						foreach($currency_options as $currency_code => $currency_name) {
							$selected = ($currency_code === $report_currency) ? 'selected="selected"' : '';
							echo '<option value="' . $currency_code . '" ' . $selected . '>' . $currency_name . '</option>';
						}
					?></select>
				</li>
			</ul>
		</div>
	</div>
</div>
