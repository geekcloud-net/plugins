<?php
add_action('widgets_init', 'porto_recent_portfolios_load_widgets');

function porto_recent_portfolios_load_widgets()
{
    register_widget('Porto_Recent_Portfolios_Widget');
}

class Porto_Recent_Portfolios_Widget extends WP_Widget {

    public function __construct() {

        $widget_ops = array('classname' => 'widget-recent-portfolios', 'description' => __('Show recent portfolios.', 'porto-widgets'));

        $control_ops = array('id_base' => 'recent_portfolios-widget');

        parent::__construct('recent_portfolios-widget', __('Porto: Recent Portfolios', 'porto-widgets'), $widget_ops, $control_ops);
    }

    function widget($args, $instance) {

        extract($args);
        $title = apply_filters('widget_title', $instance['title']);
        $number = $instance['number'];
        $items = $instance['items'];
        $view = $instance['view'];
        $cat = $instance['cat'];

        if ($items == 0)
            $items = 6;

        $options = array();
        $options['themeConfig'] = true;
        $options['lg'] = 1;
        $options['md'] = 3;
        $options['sm'] = 2;
        $options['single'] = $view == 'small' ? true : false;
        $options['animateIn'] = '';
        $options['animateOut'] = '';
        $options = json_encode($options);

        $args = array(
            'post_type' => 'portfolio',
            'posts_per_page' => $number
        );

        if ( $cat ) {
            $categories = explode( ",", $cat );
            $gc = array();
            foreach ( $categories as $grid_cat ) {
                array_push( $gc, $grid_cat );
            }
            $gc = implode( ",", $gc );
            //$args['category_name'] = $gc;

            $taxonomies = get_taxonomies( '', 'object' );
            $args['tax_query'] = array( 'relation' => 'OR' );
            foreach ( $taxonomies as $t ) {
                if ( $t->object_type[0] == 'portfolio' ) {
                    $args['tax_query'][] = array(
                        'taxonomy' => $t->name, //$t->name,//'portfolio_cat',
                        'terms' => $categories
                    );
                }
            }
        }

        $portfolios = new WP_Query($args);

        if ($portfolios->have_posts()) :

            echo $before_widget;

            if ($title) {
                echo $before_title . $title . $after_title;
            }

            ?>
            <div class="row<?php if ($view == 'small') echo ' gallery-row' ?>">
                <div<?php if ($number > $items) : ?> class="portfolio-carousel porto-carousel owl-carousel show-nav-title" data-plugin-options="<?php echo esc_attr($options) ?>"<?php endif; ?>>
                    <?php
                    $count = 0;
                    while ($portfolios->have_posts()) {
                        $portfolios->the_post();

                        if ($count % $items == 0) echo '<div class="portfolio-slide">';

                        get_template_part('content', 'portfolio-item' . ($view == 'small' ? '-small' : ''));

                        if ($count % $items == $items - 1) echo '</div>';

                        $count++;
                    }
                    ?>
                </div>
            </div>
            <a class="btn-flat pt-right btn-xs view-more" href="<?php echo get_post_type_archive_link( 'portfolio' ) ?>"><?php _e('View More', 'porto-widgets') ?> <i class="fa fa-arrow-<?php echo is_rtl() ? 'left' : 'right' ?>"></i></a>
            <?php

            echo $after_widget;

        endif;
        wp_reset_postdata();
    }

    function update($new_instance, $old_instance) {

        $instance = $old_instance;

        $instance['title'] = strip_tags($new_instance['title']);
        $instance['number'] = $new_instance['number'];
        $instance['items'] = $new_instance['items'];
        $instance['view'] = $new_instance['view'];
        $instance['cat'] = $new_instance['cat'];

        return $instance;
    }

    function form($instance) {

        $defaults = array('title' => __('Recent Portfolios', 'porto-widgets'), 'number' => 6, 'items' => 6, 'view' => 'small', 'cat' => '');
        $instance = wp_parse_args((array) $instance, $defaults); ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <strong><?php _e('Title', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php if (isset($instance['title'])) echo $instance['title']; ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>">
                <strong><?php _e('Number of portfolios to show', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" value="<?php if (isset($instance['number'])) echo $instance['number']; ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('view'); ?>">
                <strong><?php _e('View Type', 'porto-widgets') ?>:</strong>
                <select class="widefat" id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('view'); ?>">
                    <option value="small"<?php echo (isset($instance['view']) && $instance['view'] == 'small')? ' selected="selected"' : '' ?>><?php _e('Small', 'porto-widgets') ?></option>
                    <option value="large"<?php echo (isset($instance['view']) && $instance['view'] == 'large')? ' selected="selected"' : '' ?>><?php _e('Large', 'porto-widgets') ?></option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('items'); ?>">
                <strong><?php _e('Number of items per slide', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('items'); ?>" name="<?php echo $this->get_field_name('items'); ?>" value="<?php if (isset($instance['items'])) echo $instance['items']; ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('cat'); ?>">
                <strong><?php _e('Category IDs', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('cat'); ?>" name="<?php echo $this->get_field_name('cat'); ?>" value="<?php if (isset($instance['cat'])) echo $instance['cat']; ?>" />
            </label>
        </p>
    <?php
    }
}

?>