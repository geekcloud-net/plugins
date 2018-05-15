<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/16/2017
 * Time: 12:42 PM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>

<div class="thrv_wrapper thrv_countdown_timer thrv-countdown_timer_evergreen tve_clearfix init_done tve_red"
	 data-day="0"
	 data-hour="2"
	 data-min="0"
	 data-sec="0"
	 data-id="<?php echo uniqid( 'evergreen_' ); ?>"
	 data-expday="0"
	 data-exphour="1"
	 data-norestart="0">
	<div class="sc_timer_content tve_clearfix tve_block_center">
		<div class="tve_t_day tve_t_part">
			<div class="t-digits"></div>
			<div class="thrv-inline-text t-caption">Days</div>
		</div>
		<div class="tve_t_hour tve_t_part">
			<div class="t-digits"></div>
			<div class="thrv-inline-text t-caption">Hours</div>
		</div>
		<div class="tve_t_min tve_t_part">
			<div class="t-digits"></div>
			<div class="thrv-inline-text t-caption">Minutes</div>
		</div>
		<div class="tve_t_sec tve_t_part">
			<div class="t-digits"></div>
			<div class="thrv-inline-text t-caption">Seconds</div>
		</div>
		<div class="tve_t_text"></div>
	</div>
</div>
