<?php
$timezone_offset = get_option( 'gmt_offset' );
$sign            = ( $timezone_offset < 0 ? '-' : '+' );
$min             = abs( $timezone_offset ) * 60;
$hour            = floor( $min / 60 );
$tzd             = $sign . str_pad( $hour, 2, '0', STR_PAD_LEFT ) . ':' . str_pad( $min % 60, 2, '0', STR_PAD_LEFT );
?>
<div class="thrv_ult_widget thrv_wrapper tve_no_drag tve_no_icons tve_element_hover tve_set_01 tve_white" style="background-image: url('<?php echo TVE_Ult_Const::plugin_url("tcb-bridge/editor-templates/css/images/tvu_set1_widget_bg.jpg") ?>'); background-size:cover; background-position: center center;">
	<div class="tve-ult-widget-content tve_editor_main_content">
		<h5 class="tve_p_center tvu-heading" style="color: #d7fe3a; font-size:  26px;margin-top: 10px;margin-bottom: 20px;">
			Time before this offer runs out:
		</h5>
		<div class="thrv_wrapper thrv_countdown_timer tve_cd_timer_plain tve_clearfix init_done tve_black tve_countdown_2"
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
					<div class="t-caption thrv-inline-text">hours</div>
				</div>
				<div class="tve_t_min tve_t_part">
					<div class="t-digits"></div>
					<div class="t-caption thrv-inline-text">minutes</div>
				</div>
				<div class="tve_t_sec tve_t_part">
					<div class="t-digits"></div>
					<div class="t-caption thrv-inline-text">seconds</div>
				</div>
				<div class="tve_t_text"></div>
			</div>
		</div>
		<div class="thrv_wrapper thrv_button_shortcode tve_centerBtn" data-tve-style="1">
			<div class="tve_btn tve_btn3 tve_nb tve_orange tve_normalBtn" style="margin-top: 20px;">
				<a href="" class="tve_btnLink">
				            <span class="tve_left tve_btn_im">
				                <i></i>
				                <span class="tve_btn_divider"></span>
				            </span>
					<span class="tve_btn_txt">Get it Now!</span>
				</a>
			</div>
		</div>
	</div>
</div>