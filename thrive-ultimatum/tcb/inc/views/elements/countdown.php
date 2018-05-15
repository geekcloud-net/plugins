<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/12/2017
 * Time: 9:10 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$time_settings = tve_get_time_settings();
?>

<div class="thrv_wrapper thrv_countdown_timer thrv-countdown_timer_plain tve_clearfix init_done tve_red"
	 data-date="<?php echo gmdate( 'Y-m-d', time() + 3600 * $time_settings['timezone_offset'] + ( 3600 * 24 ) ); ?>"
	 data-hour="<?php echo gmdate( 'H', time() + 3600 * $time_settings['timezone_offset'] ); ?>"
	 data-min="<?php echo gmdate( 'i', time() + 3600 * $time_settings['timezone_offset'] ); ?>"
	 data-timezone="<?php echo $time_settings['tzd']; ?>">
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
