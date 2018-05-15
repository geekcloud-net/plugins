<?php
$output = $title = $view = $author = $btn_style = $btn_size = $btn_color = $image_size = $number = $cat = $cats = $show_metas = $show_image = $excerpt_length = $items_desktop = $items_tablets = $items_mobile = $items_row = $slider_config = $show_nav = $show_nav_hover = $nav_pos = $nav_type = $show_dots = $animation_type = $animation_duration = $animation_delay = $el_class = '';
extract(shortcode_atts(array(
    'title' => '',
    'view' => '',
    'author' => '',
    'btn_style' => '',
    'btn_size' => '',
    'btn_color' => '',
    'image_size' => '',
    'number' => 8,
    'cats' => '',
    'cat' => '',
    'show_metas' => true,
    'show_image' => true,
    'excerpt_length' => 20,
    'items_desktop' => 4,
    'items_tablets' => 3,
    'items_mobile' => 2,
    'items_row' => 1,
    'slider_config' => false,
    'show_nav' => false,
    'show_nav_hover' => false,
    'nav_pos' => '',
    'nav_type' => '',
    'show_dots' => false,
    'animation_type' => '',
    'animation_duration' => 1000,
    'animation_delay' => 0,
    'el_class' => ''
), $atts));

global $porto_settings;

$carousel_class = '';
$options = array();
$options['themeConfig'] = true;
if ($slider_config) {
    if ($show_nav) {
        if ($nav_pos) $carousel_class .= ' ' . $nav_pos;
        if ($nav_type) $carousel_class .= ' ' . $nav_type;
        if ($show_nav_hover) $carousel_class .= ' show-nav-hover';
    }
    $options['nav'] = $show_nav;
    $options['dots'] = $show_dots;
}
$options['lg'] = (int)$items_desktop;
$options['md'] = (int)$items_tablets;
$options['sm'] = (int)$items_mobile;
$options = json_encode($options);

$items_row = (int)$items_row;

$args = array(
    'post_type' => 'post',
    'posts_per_page' => $number
);

if (!$cats)
    $cats = $cat;

if ($cats)
    $args['cat'] = $cats;

$posts = new WP_Query($args);

if ($posts->have_posts()) {
    $el_class = porto_shortcode_extract_class( $el_class );

    $output = '<div class="porto-recent-posts wpb_content_element ' . $el_class . '"';
    if ($animation_type) {
        $output .= ' data-appear-animation="'.$animation_type.'"';
        if ($animation_delay)
            $output .= ' data-appear-animation-delay="'.$animation_delay.'"';
        if ($animation_duration && $animation_duration != 1000)
            $output .= ' data-appear-animation-duration="'.$animation_duration.'"';
    }
    $output .= '>';

    $output .= porto_shortcode_widget_title( array( 'title' => $title, 'extraclass' => '' ) );

    global $porto_post_view, $porto_post_btn_style, $porto_post_btn_size, $porto_post_btn_color, $porto_post_image_size, $porto_post_author, $porto_post_excerpt_length;

    $porto_post_view = $view;
    $porto_post_author = $author;
    $porto_post_btn_style = $btn_style;
    $porto_post_btn_size = $btn_size;
    $porto_post_btn_color = $btn_color;
    $porto_post_image_size = $image_size;
    $porto_post_excerpt_length = $excerpt_length;

    if (isset($porto_settings)) {
        $prev_post_metas = $porto_settings['post-metas'];

        if (!$show_metas)
            $porto_settings['post-metas'] = array();
    }

    ob_start();
    ?>
    <div class="row">
        <div class="post-carousel porto-carousel owl-carousel<?php echo esc_attr($carousel_class) ?>" data-plugin-options="<?php echo esc_attr($options) ?>">
            <?php
            $i = 0;
            while ($posts->have_posts()) {
                $posts->the_post();
                global $previousday;
                unset($previousday);

                if ($i % $items_row == 0) echo '<div class="post-slide' . ($items_row > 1 ? ' no-single' : '') . '">';

                if ($show_image) {
                    get_template_part('content', 'post-item');
                } else {
                    get_template_part('content', 'post-item-no-image');
                }

                if ($i % $items_row == $items_row - 1) echo '</div>';
                $i++;
            }
            ?>
        </div>
    </div>
    <?php
    $output .= ob_get_clean();

    $porto_post_view = $porto_post_author = $porto_post_btn_style = $porto_post_btn_size = $porto_post_btn_color = $porto_post_image_size = $porto_post_excerpt_length = '';

    if (isset($porto_settings)) {
        $porto_settings['post-metas'] = $prev_post_metas;
    }

    $output .= '</div>';

    echo $output;
}

wp_reset_postdata();