<?php
$output = $skin = $border_top_color = $border_radius = $border_top_width = $bg_top_color = $bg_bottom_color = $align = $animation_type = $animation_duration = $animation_delay = $el_class = '';
extract(shortcode_atts(array(
    'skin' => 'custom',
    'border_top_color' => '',
    'border_radius' => '',
    'border_top_width' => '',
    'bg_type' => '',
    'bg_top_color' => '',
    'bg_bottom_color' => '',
    'align' => '',
    'show_icon' => false,
    'icon_type' => 'fontawesome',
    'icon' => '',
    'icon_simpleline' => '',
    'icon_image' => '',
    'box_style' => '',
    'box_effect' => '',
    'icon_color' => '',
    'icon_bg_color' => '',
    'icon_border_color' => '',
    'icon_wrap_border_color' => '',
    'icon_shadow_color' => '',
    'icon_hcolor' => '',
    'icon_hbg_color' => '',
    'icon_hborder_color' => '',
    'icon_wrap_hborder_color' => '',
    'icon_hshadow_color' => '',
    'animation_type' => '',
    'animation_duration' => 1000,
    'animation_delay' => 0,
    'el_class' => ''
), $atts));

$el_class = porto_shortcode_extract_class( $el_class );

switch ($icon_type) {
    case 'simpleline': $icon_class = $icon_simpleline; break;
    case 'porto': $icon_class = $icon_porto; break;
    case 'image': $icon_class = 'icon-image'; break;
    default: $icon_class = $icon;
}
if (!$show_icon)
    $icon_class = '';

if ($skin == 'custom' && ($box_style || $box_effect || $icon_class)) {
    $sc_class = 'porto-content-box'.rand();
    $el_class .= ' '.$sc_class;
    ?>
    <style type="text/css"><?php
        if ($icon_class) :
            if ($icon_color || $icon_bg_color || $icon_border_color) : ?>
            .<?php echo $sc_class ?> .featured-box .icon-featured {
                <?php if ($icon_color) : ?>color: <?php echo $icon_color ?>;<?php endif;
                if ($icon_bg_color) : ?>background-color: <?php echo $icon_bg_color ?>;<?php endif;
                if ($icon_border_color) : ?>border-color: <?php echo $icon_border_color ?>;<?php endif; ?>
            }<?php
            endif;
            if ($icon_hcolor || $icon_hbg_color || $icon_hborder_color) : ?>
            .<?php echo $sc_class ?> .featured-box:hover .icon-featured {
                <?php if ($icon_hcolor) : ?>color: <?php echo $icon_hcolor ?>;<?php endif;
                if ($icon_hbg_color) : ?>background-color: <?php echo $icon_hbg_color ?>;<?php endif;
                if ($icon_hborder_color) : ?>border-color: <?php echo $icon_hborder_color ?>;<?php endif; ?>
            }<?php
            endif;
            if ($box_style == 'featured-boxes-style-7') :
                if ($icon_shadow_color) : ?>
        .<?php echo $sc_class ?> .featured-box .icon-featured:after {
            box-shadow: 3px 3px <?php echo $icon_shadow_color ?>;
        }<?php
                endif;
                if ($icon_hshadow_color) : ?>
        .<?php echo $sc_class ?> .featured-box:hover .icon-featured:after {
            box-shadow: 3px 3px <?php echo $icon_hshadow_color ?>;
        }<?php
                endif;
            endif;
            if ($box_effect == 'featured-box-effect-1' || $box_effect == 'featured-box-effect-2') :
                if ($icon_shadow_color) : ?>
        .<?php echo $sc_class ?> .featured-box .icon-featured:after {
            box-shadow: 0 0 0 3px <?php echo $icon_shadow_color ?>;
        }<?php
                endif;
                if ($icon_hshadow_color) : ?>
        .<?php echo $sc_class ?> .featured-box:hover .icon-featured:after {
            box-shadow: 0 0 0 3px <?php echo $icon_hshadow_color ?>;
        }<?php
                endif;
            endif;
            if ($box_effect == 'featured-box-effect-3') :
                if ($icon_shadow_color) : ?>
        .<?php echo $sc_class ?> .featured-box .icon-featured:after {
            box-shadow: 0 0 0 10px <?php echo $icon_shadow_color ?>;
        }<?php
                endif;
                if ($icon_hshadow_color) : ?>
        .<?php echo $sc_class ?> .featured-box:hover .icon-featured:after {
            box-shadow: 0 0 0 10px <?php echo $icon_hshadow_color ?>;
        }<?php
                endif;
            endif;
            if ($box_effect == 'featured-box-effect-7') :
                if ($icon_shadow_color) : ?>
        .<?php echo $sc_class ?> .featured-box .icon-featured:after {
            box-shadow: 3px 3px <?php echo $icon_shadow_color ?>;
        }<?php
                endif;
                if ($icon_hshadow_color) : ?>
        .<?php echo $sc_class ?> .featured-box:hover .icon-featured:after {
            box-shadow: 3px 3px <?php echo $icon_hshadow_color ?>;
        }<?php
                endif;
            endif;
            if ($box_style == 'featured-boxes-style-6') :
                if ($icon_wrap_border_color) : ?>
        .<?php echo $sc_class ?> .featured-box .icon-featured:after {
            border-color: <?php echo $icon_wrap_border_color ?>;
        }<?php
                endif;
                if ($icon_wrap_hborder_color) : ?>
        .<?php echo $sc_class ?> .featured-box:hover .icon-featured:after {
            border-color: <?php echo $icon_wrap_hborder_color ?>;
        }<?php
                endif;
            endif;
        endif; ?>
    </style>
<?php
}

if ($bg_type)
    $el_class .= ' ' . $bg_type;

if ($box_style)
    $el_class .= ' ' . $box_style;

$output = '<div class="porto-content-box featured-boxes wpb_content_element ' . $el_class .'"';
if ($animation_type) {
    $output .= ' data-appear-animation="'.$animation_type.'"';
    if ($animation_delay)
        $output .= ' data-appear-animation-delay="'.$animation_delay.'"';
    if ($animation_duration && $animation_duration != 1000)
        $output .= ' data-appear-animation-duration="'.$animation_duration.'"';
}
$output .= '>';

$output .= '<div class="featured-box ' . ($skin != 'custom' ? 'featured-box-' . $skin . ' ' : '') . $box_effect . ($align?' align-'.$align:''). '"' . ($skin == 'custom' ? ' style="'.(($border_radius) ? 'border-radius:'.$border_radius.'px;' : '').
    (($bg_top_color && $bg_bottom_color)?'background:-webkit-linear-gradient(top, '.$bg_top_color.' 1%, '.$bg_bottom_color.' 98%) repeat scroll 0 0 transparent; background: linear-gradient(to bottom, '.$bg_top_color.' 1%, '.$bg_bottom_color.' 98%) repeat scroll 0 0 transparent; ':'').'"' : '') . '>';
$output .= '<div class="box-content" style="'.(($border_radius) ? 'border-radius:'.$border_radius.'px;' : '').
    ($border_top_color?'border-top-color:'.$border_top_color.';':'').($border_top_width?'border-top-width:'.$border_top_width.'px;':'').'">';
if ($icon_class) {
    $output .= '<i class="icon-featured ' . $icon_class . '">';
    if ($icon_class == 'icon-image' && $icon_image) {
        $icon_image = preg_replace('/[^\d]/', '', $icon_image);
        $image_url = wp_get_attachment_url($icon_image);
        $image_url = str_replace(array('http:', 'https:'), '', $image_url);
        if ($image_url)
            $output .= '<img alt="" src="' . esc_url($image_url) . '">';
    }
    $output .= '</i>';
}
$output .= do_shortcode($content);
$output .= '</div></div>';

$output .= '</div>';

echo $output;