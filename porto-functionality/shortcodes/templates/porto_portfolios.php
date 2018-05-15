<?php
$output = $title = $portfolio_layout = $columns = $view = $info_view = $info_view_style = $thumb_bg = $thumb_image = $image_counter = $cat = $cats = $post_in = $number = $slider = $load_more_posts = $load_more = $view_more = $view_more_class = $filter = $pagination = $animation_type = $animation_duration = $animation_delay = $ajax_load = $ajax_modal = $el_class = '';
global $portfolio_num;
extract(shortcode_atts(array(
    'title' => '',
    'portfolio_layout' => 'timeline',
    'columns' => '3',
    'view' => 'classic',
    'info_view' => '',
	'info_view_type_style' => '',
    'thumb_bg' => '',
    'thumb_image' => '',
	'image_counter' => '',
    'cats' => '',
    'cat' => '',
    'post_in' => '',
	'slider' => '',
    'number' => 8,
    'view_more' => false,
	'load_more_posts' => '',
    'view_more_class' => '',
    'filter' => false,
    'pagination' => false,
    'ajax_load' => false,
    'ajax_modal' => false,
    'animation_type' => '',
    'animation_duration' => 1000,
    'animation_delay' => 0,
    'el_class' => ''
), $atts));

$args = array(
    'post_type' => 'portfolio',
    'posts_per_page' => $number
);

if (!$cats)
    $cats = $cat;

if ($cats) {
    $cat = explode(',', $cats);
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'portfolio_cat',
            'field' => 'term_id',
            'terms' => $cat,
        )
    );
}

if ($post_in) {
    $args['post__in'] = explode(',', $post_in);
    $args['orderby'] = 'post__in';
}

if( is_front_page() ){	$paged = get_query_var('page');}else{	$paged = get_query_var('paged');}if( $load_more_posts && $paged ){	$args['paged'] = $paged;}

if( $load_more_posts == 'load-more-btn' ){
	$load_more = true;
}

$posts = new WP_Query($args);

$portfolio_taxs = array();

    global $porto_settings;

if ($filter) {
    $taxs = get_categories(array(
        'taxonomy' => 'portfolio_cat',
        'orderby' => isset($porto_settings['portfolio-cat-orderby']) ? $porto_settings['portfolio-cat-orderby'] : 'name',
        'order' => isset($porto_settings['portfolio-cat-order']) ? $porto_settings['portfolio-cat-order'] : 'asc'
    ));

    foreach ($taxs as $tax) {
        $portfolio_taxs[urldecode($tax->slug)] = $tax->name;
    }

    if (is_array($posts->posts) && !empty($posts->posts)) {
        $posts_portfolio_taxs = array();
        foreach($posts->posts as $post) {
            $post_taxs = wp_get_post_terms($post->ID, 'portfolio_cat', array("fields" => "all"));
            if (is_array($post_taxs) && !empty($post_taxs)) {
                foreach ($post_taxs as $post_tax) {
                    if (is_array($cat) && !empty($cat) && in_array($post_tax->term_id, $cat)) {
                        $posts_portfolio_taxs[urldecode($post_tax->slug)] = $post_tax->name;
                    }

                    if(empty($cat) || !isset($cat)) {
                        $posts_portfolio_taxs[urldecode($post_tax->slug)] = $post_tax->name;
                    }
                }
            }
        }

        foreach ($portfolio_taxs as $key => $value) {
            if (!isset($posts_portfolio_taxs[$key]))
                unset($portfolio_taxs[$key]);
        }
    }
}

$shortcode_id = md5(json_encode($atts));

if ($posts->have_posts()) {
    $el_class = porto_shortcode_extract_class( $el_class );

    $output = '<div class="porto-portfolios porto-portfolios' . $shortcode_id . ' wpb_content_element ' . $el_class . '"';
    if ($animation_type) {
        $output .= ' data-appear-animation="'.$animation_type.'"';
        if ($animation_delay)
            $output .= ' data-appear-animation-delay="'.$animation_delay.'"';
        if ($animation_duration && $animation_duration != 1000)
            $output .= ' data-appear-animation-duration="'.$animation_duration.'"';
    }
    $output .= '>';

    $output .= porto_shortcode_widget_title( array( 'title' => $title, 'extraclass' => '' ) );

    global $porto_portfolio_columns, $porto_portfolio_view, $porto_portfolio_thumb, $porto_portfolio_thumb_style, $porto_portfolio_thumb_bg, $porto_portfolio_thumb_image, $porto_portfolio_slider, $porto_portfolio_image_counter, $porto_portfolio_ajax_load, $porto_portfolio_ajax_modal, $porto_portfolio_thumbs_html;
	
    $porto_portfolio_columns = $columns;
    $porto_portfolio_view = $view;
    $porto_portfolio_thumb = $info_view;
	$porto_portfolio_thumb_style = $info_view_type_style;
    $porto_portfolio_thumb_bg = $thumb_bg;
    $porto_portfolio_thumb_image = $thumb_image;
	
	$porto_portfolio_slider = $slider;
	
	$porto_portfolio_image_counter = $image_counter;
    $portfolio_columns = $columns;
    $portfolio_view = $view;
    $porto_portfolio_ajax_load = $ajax_load ? 'yes' : 'no';
    $porto_portfolio_ajax_modal = $ajax_modal ? 'yes' : 'no';

    ob_start(); ?>

    <?php if (isset($porto_settings['portfolio-archive-link-zoom']) && $porto_settings['portfolio-archive-link-zoom']) : ?><div class="portfolios-lightbox <?php if( $porto_settings[ 'portfolio-archive-img-lightbox-thumb' ] ) { echo " with-thumbs"; }?>"><?php endif; ?>

    <div class="page-portfolios portfolios-<?php echo $portfolio_layout ?> clearfix <?php echo $title ? 'm-t-lg' : '' ?>">

    <?php if ($ajax_load && !$ajax_modal) : ?>
        <div id="portfolioAjaxBox" class="ajax-box">
            <div class="bounce-loader">
                <div class="bounce1"></div>
                <div class="bounce2"></div>
                <div class="bounce3"></div>
            </div>
            <div class="ajax-box-content" id="portfolioAjaxBoxContent"></div>
        </div>
    <?php endif; ?>

    <?php if (is_array($portfolio_taxs) && !empty($portfolio_taxs)):
    ?>
    <ul class="portfolio-filter nav nav-pills sort-source">
        <li class="active" data-filter="*"><a><?php echo __('Show All', 'porto-shortcodes'); ?></a></li>
        <?php foreach ($portfolio_taxs as $portfolio_tax_slug => $portfolio_tax_name) : ?>
            <li data-filter="<?php echo esc_attr($portfolio_tax_slug) ?>"><a><?php echo esc_html($portfolio_tax_name) ?></a></li>
        <?php endforeach; ?>
    </ul>
    <?php if ($portfolio_layout == 'grid' || $portfolio_layout == 'masonry') { ?>
        <hr>
    <?php } else if ($portfolio_layout == 'timeline') { ?>
        <hr class="invisible">
    <?php } else { ?>
        <hr class="tall">
    <?php } ?>
    <?php endif; ?>

    <?php if ($portfolio_layout == 'timeline') :
        global $prev_post_year, $prev_post_month, $first_timeline_loop, $post_count;

        $prev_post_year = null;
        $prev_post_month = null;
        $first_timeline_loop = false;
        $post_count = 1;
        ?>

        <section class="timeline">

            <div class="timeline-body">

    <?php else : ?>

        <div class="clearfix <?php if ($portfolio_layout == 'grid' || $portfolio_layout == 'masonry') : ?> portfolio-row portfolio-row-<?php echo $portfolio_columns ?> <?php echo $portfolio_view ?><?php endif; ?>">

    <?php endif; ?>

    <?php
	$portfolio_num = 0;
    while ($posts->have_posts()) {
        $posts->the_post();
		++$portfolio_num;
		
        get_template_part('content', 'archive-portfolio-'.$portfolio_layout);
    }
	if( $porto_settings['portfolio-archive-img-lightbox-thumb'] && ( $portfolio_layout == 'medium' || $portfolio_layout == 'full' || $portfolio_layout == 'large' ) ){
		while ($posts->have_posts()) {
			global $post;
			$posts->the_post();	
			
			$archive_image = (int)get_post_meta($post->ID, 'portfolio_archive_image', true);
			if ($archive_image) {
				$featured_images = array();
				$featured_image         = array(
					'thumb'         => wp_get_attachment_thumb_url( $archive_image ),
					'full'          => wp_get_attachment_url( $archive_image ),
					'attachment_id' => $archive_image
				);
				$featured_images[] = $featured_image;
			} else {
				$featured_images = porto_get_featured_images();
			}
			foreach ($featured_images as $featured_image){
				$attachment_id = $featured_image['attachment_id'];
				if( $attachment_id ){
					$attachment_thumb = porto_get_attachment( $attachment_id, 'widget-thumb-medium');
					$porto_portfolio_thumbs_html .= '<span><img src="'.$attachment_thumb['src'].'" alt="" ></span>';
				}
				break;
			}
		}
	}
    ?>
	
	<?php if( $porto_settings[ 'portfolio-archive-img-lightbox-thumb' ] ): 
		$thumbs_carousel_options = array( 'items' => 15, 'loop' => false, 'dots' => false, 'nav' => false, 'margin' => 8 );
	?>
		<div class="porto-portfolios-lighbox-thumbnails">
			<div class="owl-carousel owl-theme nav-center" data-plugin-options='<?php echo json_encode( $thumbs_carousel_options ); ?>'>
				<?php echo $porto_portfolio_thumbs_html; ?>
			</div>
		</div>
	<?php endif; ?>
	
    <?php if ($portfolio_layout == 'timeline') : ?>

            </div>

        </section>

    <?php else : ?>

        </div>

    <?php endif; ?>

    <?php if ( $load_more_posts && function_exists('porto_pagination') ) : ?>
        <input type="hidden" class="shortcode-id" value="<?php echo esc_attr($shortcode_id) ?>"/>
        <?php porto_pagination( $posts->max_num_pages, $load_more ); ?>
    <?php endif; ?>

    </div>

    <?php if ($view_more) : ?>
    
    
        <div class="<?php if ($portfolio_layout == 'timeline') echo 'm-t-n-xxl'; else echo 'push-top'; ?> m-b-xxl text-center">
            <a class="btn btn-primary<?php echo $view_more_class ? ' ' . str_replace('.', '', $view_more_class) : '' ?>" href="<?php echo get_post_type_archive_link( 'portfolio' ) ?>"><?php _e("View More", 'porto-shortcodes') ?></a>
        </div>
    <?php endif; ?>

    <?php if (isset($porto_settings['portfolio-archive-link-zoom']) && $porto_settings['portfolio-archive-link-zoom']) : ?></div><?php endif; ?>

    <?php
    $output .= ob_get_clean();

    $porto_portfolio_columns = $porto_portfolio_view = $porto_portfolio_thumb = $porto_portfolio_thumb_style = $porto_portfolio_thumb_bg = $porto_portfolio_thumb_image = $porto_portfolio_slider = $porto_portfolio_ajax_load = $porto_portfolio_ajax_modal = $porto_portfolio_thumbs_html = '';

    $output .= '</div>';

    echo $output;
}

wp_reset_postdata();