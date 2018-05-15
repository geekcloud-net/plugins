<?php

if ( !function_exists( 'porto_get_red_from_hexcolor' ) ) {
	function porto_get_red_from_hexcolor($hex) {
		$hex = str_replace( "#", "", $hex );
		return hexdec( strlen($hex) == 3 ? substr( $hex, 0, 1 ) . substr( $hex, 0, 1 )  : substr( $hex, 0, 2 ) );
	}
}
if ( !function_exists( 'porto_get_green_from_hexcolor' ) ) {
	function porto_get_green_from_hexcolor($hex) {
		$hex = str_replace( "#", "", $hex );
		return hexdec( strlen($hex) == 3 ? substr( $hex, 1, 1 ) . substr( $hex, 1, 1 )  : substr( $hex, 2, 2 ) );
	}
}
if ( !function_exists( 'porto_get_blue_from_hexcolor' ) ) {
	function porto_get_blue_from_hexcolor($hex) {
		$hex = str_replace( "#", "", $hex );
		return hexdec( strlen($hex) == 3 ? substr( $hex, 2, 1 ) . substr( $hex, 2, 1 )  : substr( $hex, 4, 2 ) );
	}
}

extract(shortcode_atts(array(
    'btn_img' => '',
    'init_extra_class' => '',
    'modal_contain' => 'html',
    'youtube__url' => '',
    'vimeo_url' => '',
    'modal_on' => 'onload',
    'modal_on_selector' => '',
    'modal_style' => 'mfp-fade',
    'overlay_bg_color' => '',
    'overlay_bg_opacity' => '',
    'el_class' => '',
    ),$atts,'porto_modal'));
$html = $style = $modal_class = $modal_data_class = $uniq = $overlay_bg = $header_style = '';

$modal_uid = 'porto-modal-wrap-'.rand(0000,9999);


$overlay_bg_opacity = ($overlay_bg_opacity/100);
$overlay_bg = '';
if($overlay_bg_color !== ''){
    if(strlen($overlay_bg_color) <= 7) {
        $overlay_bg = 'rgba('. esc_attr( porto_get_red_from_hexcolor( $overlay_bg_color ) ) . ',' . esc_attr( porto_get_green_from_hexcolor( $overlay_bg_color ) ) . ',' . esc_attr( porto_get_blue_from_hexcolor( $overlay_bg_color ) ) . ',' . esc_attr( $overlay_bg_opacity ) . ')';
    }
    else
        $overlay_bg = esc_attr( $overlay_bg_color );

    $overlay_bg = 'background-color:'.$overlay_bg.';';
}

$uniq = uniqid('', true);
$uniq = str_replace('.', '-', $uniq);

if ( $overlay_bg ) {
    $porto_modal_inline_style = '';
    $porto_modal_inline_style .= '<style>';
    $porto_modal_inline_style .= '.mfp-bg { '. $overlay_bg .' }';
    $porto_modal_inline_style .= '</style>';
    $html .= '<script type="text/javascript">
        (function($){
            $("head").append("'. $porto_modal_inline_style .'");
        })(jQuery);
        </script>';
}

$html .= '<div class="porto-modal-input-wrapper '. esc_attr( $init_extra_class ) .'">';

if ( $modal_contain == 'youtube' || $modal_contain == 'vimeo' ) {
    $trigger_id = esc_url( $atts[$modal_contain.'_url'] );
    $content_type = 'iframe';
} else {
    $trigger_id = 'porto-modal-'.$uniq;
    $content_type = 'inline';
}
if($modal_on == "image" && $btn_img){
    $img = wp_get_attachment_image_src( $btn_img, 'full');
    if ( isset( $img ) ) {
        $html .= '<img src="'.esc_url( $img[0] ).'" alt="" data-trigger-id="'. esc_attr( $trigger_id ) .'" data-type="'. $content_type .'" class="porto-modal-trigger img-responsive" width="'. esc_attr( $img[1] ) .'" height="'. esc_attr( $img[2] ) .'" data-overlay-class="'. esc_attr($modal_style) .'" />';
    }
}
elseif($modal_on == "onload"){
    $html .= '<div data-trigger-id="'. esc_attr( $trigger_id ) .'" data-type="'. $content_type .'" class="porto-modal-trigger porto-onload" data-overlay-class="'. esc_attr($modal_style) .'"></div>';
}
elseif($modal_on == "custom-selector" && $modal_on_selector) {
    $html .= '<script type="text/javascript">
    (function($){
        $(document).ready(function(){
            var selector = "'.esc_attr($modal_on_selector).'";
            $(selector).addClass("porto-modal-trigger");
            $(selector).attr("data-trigger-id", "'. esc_attr( $trigger_id ) .'");
            $(selector).attr("data-type", "'. esc_attr( $content_type ) .'");
            $(selector).attr("data-overlay-class", "'.esc_attr($modal_style).'");
        });
    })(jQuery);
    </script>';
}


$html .= '</div>';

// modal box

$html .= '<div id="'. esc_attr( $trigger_id ) .'" class="mfp-hide'. ($modal_style === 'my-mfp-zoom-in' ? ' zoom-anim-dialog' : '') .' '. esc_attr( $el_class ) .'">';
    $html .= '<div class="porto-modal-content">';
        $html .= do_shortcode( $content );
    $html .= '</div>';
$html .= '</div>';

echo $html;