<style type="text/css">
	#wpadminbar {
		z-index: 999992!important;
	}
</style>
<?php
$timezone_offset = get_option('gmt_offset');
$sign = ($timezone_offset < 0 ? '-' : '+');
$min = abs($timezone_offset) * 60;
$hour = floor($min / 60);
$tzd = $sign . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($min % 60, 2, '0', STR_PAD_LEFT);
?>
<div class="thrv_ult_bar tve_no_drag tve_no_icons tve_element_hover thrv_wrapper tvu_set_03 tve_red" >
	<div class="tve-ult-bar-content tve_editor_main_content">
		<div class="thrv_wrapper thrv_columns tve_clearfix" style="margin-top: 0;margin-bottom: 0;">
			<div class="tve_colm tve_tth">
				<div class="thrv_wrapper thrv_columns tve_clearfix" style="margin-top: 0;margin-bottom: 0;">
					<div class="tve_colm tve_oth">
						<h5 class="rft tvu-heading" style="color: #fff; font-size: 30px;margin-top: 15px;margin-bottom: 0;">
							This Offer expires in:
						</h5>
					</div>
					<div class="tve_colm tve_tth tve_lst">
						<div class="thrv_wrapper thrv_countdown_timer tve_cd_timer_plain tve_clearfix init_done tve_white tve_countdown_1"
						     data-date="<?php echo gmdate( 'Y-m-d', time() + 3600 * $timezone_offset + ( 24 * 3600 ) ) ?>"
						     data-hour="<?php echo gmdate( 'H', time() + 3600 * $timezone_offset ) ?>"
						     data-min="<?php echo gmdate( 'i', time() + 3600 * $timezone_offset ) ?>"
						     data-timezone="<?php echo $tzd ?>">
							<div class="sc_timer_content tve_clearfix tve_block_center" style="margin-top: 5px;">
								<div class="tve_t_day tve_t_part">
									<div class="t-digits"></div>
									<div class="t-caption thrv-inline-text">D</div>
								</div>
								<div class="tve_t_hour tve_t_part">
									<div class="t-digits"></div>
									<div class="t-caption thrv-inline-text">H</div>
								</div>
								<div class="tve_t_min tve_t_part">
									<div class="t-digits"></div>
									<div class="t-caption thrv-inline-text">M</div>
								</div>
								<div class="tve_t_sec tve_t_part">
									<div class="t-digits"></div>
									<div class="t-caption thrv-inline-text">S</div>
								</div>
								<div class="tve_t_text"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="tve_colm tve_oth tve_lst">
				<div class="thrv_wrapper thrv_button_shortcode tve_centerBtn" data-tve-style="1">
					<div class="tve_btn tve_btn3 tve_nb tve_red tve_normalBtn" style="">
						<a href="" class="tve_btnLink">
				            <span class="tve_left tve_btn_im">
				                <i></i>
				                <span class="tve_btn_divider"></span>
				            </span>
							<span class="tve_btn_txt">Get this Offer Now</span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<a href="javascript:void(0)" class="tve-ult-bar-close" title="Close">x</a>
</div>
