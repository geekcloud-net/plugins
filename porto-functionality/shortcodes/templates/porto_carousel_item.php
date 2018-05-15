<?php
$output = $type = $video_url = $image_url = $image_id = $merge_items = $el_class = '';
extract(shortcode_atts(array(
    'type' => '',
    'video_url' => '',
    'image_url' => '',
    'image_id' => '',
    'merge_items' => 1,
    'el_class' => ''
), $atts));

$el_class = porto_shortcode_extract_class( $el_class );

$merge_items = (int)$merge_items;
$merge = '';
if ($merge_items !== 1) {
    $merge = ' data-merge="' . $merge_items . '"';
}

if ($type === 'lazyload') {
    if (!$image_url && $image_id)
        $image_url = wp_get_attachment_url($image_id);

    $image_url = str_replace(array('http:', 'https:'), '', $image_url);
    if ($image_url) {
        $output .= '<img class="owl-lazy ' . $el_class . '" data-src="' . $image_url . '" alt=""' . $merge . '>';
    }
} else if ($type == 'video') {
    if ($video_url) {
        $output .= '<div class="item-video ' . $el_class . '"' . $merge . '><a class="owl-video" href="' . $video_url . '"></a></div>';
    }
} else {
    $output .= '<div class="' . $el_class . '"' . $merge . '>';
    $output .= do_shortcode($content);
    $output .= '</div>';
}

echo $output;