<?php
$output = $title = $post_layout = $post_style = $columns = $cat = $cats = $post_in = $number = $view_more = $view_more_class = $animation_type = $animation_duration = $animation_delay = $el_class = '';
extract(shortcode_atts(array(
    'title' => '',
    'post_layout' => 'timeline',
    'post_style' => 'default',
    'columns' => '3',
    'cats' => '',
    'cat' => '',
    'post_in' => '',
    'number' => 8,
    'view_more' => false,
    'view_more_class' => '',
    'animation_type' => '',
    'animation_duration' => 1000,
    'animation_delay' => 0,
    'el_class' => ''
), $atts));

$args = array(
    'post_type' => 'post',
    'posts_per_page' => $number
);

if (!$cats)
    $cats = $cat;

if ($cats)
    $args['cat'] = $cats;

if ($post_in) {
    $args['post__in'] = explode(',', $post_in);
    $args['orderby'] = 'post__in';
}

$posts = new WP_Query($args);

if ($posts->have_posts()) {
    $el_class = porto_shortcode_extract_class( $el_class );

    $output = '<div class="porto-blog wpb_content_element ' . $el_class . '"';
    if ($animation_type) {
        $output .= ' data-appear-animation="'.$animation_type.'"';
        if ($animation_delay)
            $output .= ' data-appear-animation-delay="'.$animation_delay.'"';
        if ($animation_duration && $animation_duration != 1000)
            $output .= ' data-appear-animation-duration="'.$animation_duration.'"';
    }
    $output .= '>';

    $output .= porto_shortcode_widget_title( array( 'title' => $title, 'extraclass' => '' ) );

    global $porto_blog_columns;

    $porto_blog_columns = $columns;

    ob_start();

    if ($post_layout == 'timeline') {
        global $prev_post_year, $prev_post_month, $first_timeline_loop, $post_count, $porto_post_style;

        $prev_post_year = null;
        $prev_post_month = null;
        $first_timeline_loop = false;
        $post_count = 1;
        $porto_post_style = $post_style;
        ?>

        <div class="blog-posts posts-<?php echo $post_layout ?><?php if ($post_style == 'related') : ?> blog-posts-related<?php endif; ?>">
            <section class="timeline">
                <div class="timeline-body">

    <?php } else if ($post_layout == 'grid') {
        global $porto_post_style;

        $porto_post_style = $post_style;
        ?>

        <div class="blog-posts posts-<?php echo $post_layout ?><?php if ($post_style == 'related') : ?> blog-posts-related<?php endif; ?>">
            <div class="grid row">

    <?php } else { ?>

        <div class="blog-posts posts-<?php echo $post_layout ?>">

    <?php } ?>

    <?php
    while ($posts->have_posts()) {
        $posts->the_post();

        get_template_part('content', 'blog-'.$post_layout);
    }
    ?>

    <?php if ($post_layout == 'timeline') { ?>

                </div>
            </section>
        </div>

    <?php } else if ($post_layout == 'grid') { ?>

            </div>
        </div>

    <?php } else { ?>

        </div>

    <?php } ?>

    <?php if (get_option( 'show_on_front' ) == 'page' && $view_more) : ?>
    <div class="<?php if ($post_layout == 'timeline') echo 'm-t-n-xxl'; else echo 'push-top'; ?> m-b-xxl text-center">
        <a class="btn btn-primary<?php echo $view_more_class ? ' ' . str_replace('.', '', $view_more_class) : '' ?>" href="<?php echo get_permalink( get_option('page_for_posts' ) ) ?>"><?php _e("View More", 'porto-shortcodes') ?></a>
    </div>
    <?php endif; ?>

    <?php
    $output .= ob_get_clean();

    $porto_blog_columns = $porto_post_style = '';

    $output .= '</div>';

    echo $output;
}

wp_reset_postdata();