<?php

$width = $height = $map_type = $lat = $lng = $zoom = $streetviewcontrol = $maptypecontrol = $top_margin = $pancontrol = $zoomcontrol = $zoomcontrolposition = $dragging = $marker_icon = $icon_img = $map_override = $output = $map_style = $scrollwheel = $el_class ='';

extract(shortcode_atts(array(
    //"id" => "map",
    "width" => "100%",
    "height" => "300px",
    "map_type" => "ROADMAP",
    "lat" => "18.591212",
    "lng" => "73.741261",
    "zoom" => "14",
    "scrollwheel" => "",
    "streetviewcontrol" => "false",
    "maptypecontrol" => "false",
    "pancontrol" => "false",
    "zoomcontrol" => "false",
    "zoomcontrolposition" => "RIGHT_BOTTOM",
    "dragging" => "true",
    "marker_icon" => "default",
    "icon_img" => "",
    "top_margin" => "page_margin_top",
    "map_override" => "0",
    "map_style" => "",
    "el_class" => "",
    "infowindow_open" => "on",
), $atts));

$vc_version = (defined('WPB_VC_VERSION')) ? WPB_VC_VERSION : 0;
$is_vc_49_plus = (version_compare(4.9, $vc_version, '<=')) ? 'porto-adjust-bottom-margin' : '';

$border_css= $gmap_design_css ='';
$marker_lat = $lat;
$marker_lng = $lng;
$icon_url = '';
if($marker_icon == "default"){
    $icon_url = "";
} else if ( $icon_img ) {
    $attachment = wp_get_attachment_image_src( $icon_img, 'full' );
    if ( isset( $attachment ) ) {
        $icon_url = $attachment[0];
    }
}
$id = "map_".uniqid();
$wrap_id = "wrap_".$id;
$map_type = strtoupper($map_type);
$width = (substr($width, -1)!="%" && substr($width, -2)!="px" ? $width . "px" : $width);
$map_height = (substr($height, -1)!="%" && substr($height, -2)!="px" ? $height . "px" : $height);

$margin_css = '';
if($top_margin != 'none')
{
    $margin_css = $top_margin;
}

$output .= "<div id='".esc_attr($wrap_id)."' class='porto-map-wrapper ".esc_attr($is_vc_49_plus)." ".esc_attr($el_class)."' style='".esc_attr($gmap_design_css)." ".($map_height!="" ? "height:" . $map_height . ";" : "")."'><div id='" . esc_attr($id) . "' data-map_override='".esc_attr($map_override)."' class='porto_google_map wpb_content_element ".esc_attr($margin_css)."'" . ($width!="" || $map_height!="" ? " style='".esc_attr($border_css) . ($width!="" ? "width:" . esc_attr($width) . ";" : "") . ($map_height!="" ? "height:" . esc_attr($map_height) . ";" : "") . "'" : "") . "></div></div>";

if($scrollwheel == "disable"){
    $scrollwheel = 'false';
} else {
    $scrollwheel = 'true';
}

$output .= "<script type='text/javascript'>
(function($) {
'use strict';
if (typeof google != 'undefined') {
    var map_$id = null;
    var coordinate_$id;
    var isDraggable = $(document).width() > 640 ? true : $dragging;
    try
    {
        var map_$id = null;
        var coordinate_$id;
        coordinate_$id=new google.maps.LatLng($lat, $lng);
        var mapOptions=
        {
            zoom: $zoom,
            center: coordinate_$id,
            scaleControl: true,
            streetViewControl: $streetviewcontrol,
            mapTypeControl: $maptypecontrol,
            panControl: $pancontrol,
            zoomControl: $zoomcontrol,
            scrollwheel: $scrollwheel,
            draggable: isDraggable,
            zoomControlOptions: {
                position: google.maps.ControlPosition.$zoomcontrolposition
            },";
            if($map_style == ""){
                $output .= "mapTypeId: google.maps.MapTypeId.$map_type,";
            } else {
                $output .= " mapTypeControlOptions: {
                    mapTypeIds: [google.maps.MapTypeId.$map_type, 'map_style']
                }";
            }
        $output .= "};";
        if($map_style !== ""){
        $output .= 'var styles = '.rawurldecode(base64_decode(strip_tags($map_style))).';
                var styledMap = new google.maps.StyledMapType(styles,
                    {name: "Styled Map"});';
        }
        $output .= "var map_$id = new google.maps.Map(document.getElementById('$id'),mapOptions);";
        if($map_style !== ""){
        $output .= "map_$id.mapTypes.set('map_style', styledMap);
                     map_$id.setMapTypeId('map_style');";
        }
        if($marker_lat!="" && $marker_lng!="")
        {
        $output .= "
                var x = '".esc_attr($infowindow_open)."';
                var marker_$id = new google.maps.Marker({
                position: new google.maps.LatLng($marker_lat, $marker_lng),
                animation:  google.maps.Animation.DROP,
                map: map_$id,
                icon: '".esc_url($icon_url)."'
            });
            google.maps.event.addListener(marker_$id, 'click', toggleBounce);";

            if(trim($content) !== ""){
                $output .= "var infowindow = new google.maps.InfoWindow();
                    infowindow.setContent('<div class=\"map_info_text\" style=\'color:#000;\'>".trim(preg_replace('/\s+/', ' ', do_shortcode($content)))."</div>');";

                    if($infowindow_open == 'off')
                    {
                        $output .= "infowindow.open(map_$id,marker_$id);";
                    }

                    $output .= "google.maps.event.addListener(marker_$id, 'click', function() {
                        infowindow.open(map_$id,marker_$id);
                    });";

            }
        }
        $output .= "}
    catch(e){};
    jQuery(document).ready(function($){
        google.maps.event.trigger(map_$id, 'resize');
        $(window).resize(function(){
            google.maps.event.trigger(map_$id, 'resize');
            if(map_$id!=null) {
                map_$id.setCenter(coordinate_$id);
            }
        });
        $('.ui-tabs').bind('tabsactivate', function(event, ui) {
           if($(this).find('.porto-map-wrapper').length > 0)
            {
                setTimeout(function(){
                    $(window).trigger('resize');
                },200);
            }
        });
        $('.ui-accordion').bind('accordionactivate', function(event, ui) {
            if($(this).find('.porto-map-wrapper').length > 0) {
                setTimeout(function(){
                    $(window).trigger('resize');
                },200);
            }
        });
        $(window).load(function() {
            setTimeout(function() {
                $(window).trigger('resize');
            },200);
        });
        $(document).on('onPortoModalPopupOpen', function(){
            if($(map_$id).parents('.porto_modal-content')) {
                setTimeout(function(){
                    $(window).trigger('resize');
                },200);
            }
        });
    });
    function toggleBounce() {
      if (marker_$id.getAnimation() != null) {
        marker_$id.setAnimation(null);
      } else {
        marker_$id.setAnimation(google.maps.Animation.BOUNCE);
      }
    }
}
})(jQuery);
</script>";

echo $output;