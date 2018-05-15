<?php
$output = $label = $link = $icon = $skin = $link_color = $link_bg_color = $link_acolor = $link_abg_color = $el_class = '';
extract(shortcode_atts(array(
    'label' => '',
    'link' => '',
    'show_icon' => false,
    'icon_type' => 'fontawesome',
    'icon' => '',
    'icon_image' => '',
    'icon_simpleline' => '',
    'skin' => 'custom',
    'link_color' => '',
    'link_bg_color' => '',
    'link_acolor' => '',
    'link_abg_color' => '',
    'el_class' => ''
), $atts));

$el_class = porto_shortcode_extract_class( $el_class );

switch ($icon_type) {
    case 'simpleline': $icon_class = $icon_simpleline; break;
    case 'image': $icon_class = 'icon-image'; break;
    default: $icon_class = $icon;
}

if (!$show_icon)
    $icon_class = '';

if ($label) {

    if ($skin == 'custom' && ($link_color || $link_bg_color || $link_acolor || $link_abg_color)) {
        $sc_class = 'nav-link'.rand();
        $el_class .= ' '.$sc_class;
        ?>
        <style type="text/css">
        <?php if ($link_color) : ?>.porto-sticky-nav .nav-pills > li.<?php echo $sc_class ?> > a { color: <?php echo $link_color ?> !important; }<?php endif; ?>
        <?php if ($link_bg_color) : ?>.porto-sticky-nav .nav-pills > li.<?php echo $sc_class ?> > a { background-color: <?php echo $link_bg_color ?> !important; }<?php endif; ?>
        <?php if ($link_acolor) : ?>.porto-sticky-nav .nav-pills > li.<?php echo $sc_class ?>.active > a { color: <?php echo $link_acolor ?> !important; }<?php endif; ?>
        <?php if ($link_abg_color) : ?>.porto-sticky-nav .nav-pills > li.<?php echo $sc_class ?>.active > a { background-color: <?php echo $link_abg_color ?> !important; }<?php endif; ?>
        </style><?php
    }

    $output = '<li class="' . $el_class . '">';

    if ($link) {
        $output .= '<a href="' . esc_url($link) . '">';
    } else {
        $output .= '<span>';
    }

    if ($icon_class) {
        $output .= '<i class="' . $icon_class . '">';
        if ($icon_class == 'icon-image' && $icon_image) {
            $icon_image = preg_replace('/[^\d]/', '', $icon_image);
            $image_url = wp_get_attachment_url($icon_image);
            $image_url = str_replace(array('http:', 'https:'), '', $image_url);
            if ($image_url)
                $output .= '<img alt="" src="' . esc_url($image_url) . '">';
        }
        $output .= '</i>';
    }

    $output .= $label;

    if ($link) {
        $output .= '</a>';
    } else {
        $output .= '</span>';
    }

    $output .= '</li>';
}

echo $output;