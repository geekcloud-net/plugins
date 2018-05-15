 <?php
$timezone_offset = get_option( 'gmt_offset' );
$sign            = ( $timezone_offset < 0 ? '-' : '+' );
$min             = abs( $timezone_offset ) * 60;
$hour            = floor( $min / 60 );
$tzd             = $sign . str_pad( $hour, 2, '0', STR_PAD_LEFT ) . ':' . str_pad( $min % 60, 2, '0', STR_PAD_LEFT );
?>
<div class="thrv_ult_shortcode thrv_wrapper tve_no_drag tve_no_icons tve_element_hover tve_set_25">
	<div class="tve-ult-shortcode-content tve_editor_main_content">
		<div class="thrv_wrapper thrv_countdown_timer tve_cd_timer_plain tve_clearfix init_done tve_blue tve_countdown_3"
		     data-date="<?php echo gmdate( 'Y-m-d', time() + 3600 * $timezone_offset + ( 24 * 3600 ) ) ?>"
		     data-hour="<?php echo gmdate( 'H', time() + 3600 * $timezone_offset ) ?>"
		     data-min="<?php echo gmdate( 'i', time() + 3600 * $timezone_offset ) ?>"
		     data-timezone="<?php echo $tzd ?>">
			<div class="sc_timer_content tve_clearfix tve_block_center">
				<div class="tve_t_day tve_t_part">
					<div class="t-digits"></div>
					<div class="t-caption thrv-inline-text">days</div>
				</div>
				<div class="tve_t_hour tve_t_part">
					<div class="t-digits"></div>
					<div class="t-caption thrv-inline-text">hrs</div>
				</div>
				<div class="tve_t_min tve_t_part">
					<div class="t-digits"></div>
					<div class="t-caption thrv-inline-text">min</div>
				</div>
				<div class="tve_t_sec tve_t_part">
					<div class="t-digits"></div>
					<div class="t-caption thrv-inline-text">sec</div>
				</div>
				<div class="tve_t_text"></div>
			</div>
		</div>
	</div>
</div>