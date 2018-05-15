<?php
add_action('widgets_init', 'porto_recent_posts_load_widgets');

function porto_recent_posts_load_widgets()
{
    register_widget('Porto_Recent_Posts_Widget');
}

class Porto_Recent_Posts_Widget extends WP_Widget {

    public function __construct() {

        $widget_ops = array('classname' => 'widget-recent-posts', 'description' => __('Show recent posts.', 'porto-widgets'));

        $control_ops = array('id_base' => 'recent_posts-widget');

        parent::__construct('recent_posts-widget', __('Porto: Recent Posts', 'porto-widgets'), $widget_ops, $control_ops);
    }
    
    function widget($args, $instance) {

        global $porto_settings;

        extract($args);
        $title = apply_filters('widget_title', $instance['title']);
        $number = $instance['number'];
        $items = $instance['items'];
        $view = $instance['view'];
        $cat = $instance['cat'];
        $show_image = $instance['show_image'];

        if ($items == 0)
            $items = 3;

        $options = array();
        $options['themeConfig'] = true;
        $options['lg'] = 1;
        $options['md'] = ($porto_settings && isset($porto_settings['show-mobile-sidebar']) && $porto_settings['show-mobile-sidebar']) ? 1 : 3;
        $options['sm'] = ($porto_settings && isset($porto_settings['show-mobile-sidebar']) && $porto_settings['show-mobile-sidebar']) ? 1 : 2;
        $options['single'] = $view == 'small' ? true : false;
        $options['animateIn'] = '';
        $options['animateOut'] = '';
        $options = json_encode($options);

        $args = array(
            'post_type' => 'post',
            'posts_per_page' => $number
        );

        if ($cat)
            $args['cat'] = $cat;

        $posts = new WP_Query($args);

        if ($posts->have_posts()) :

            echo $before_widget;

            if ($title) {
                echo $before_title . $title . $after_title;
            }

            ?>
            <div<?php if ($number > $items) : ?> class="row"<?php endif; ?>>
                <div<?php if ($number > $items) : ?> class="post-carousel porto-carousel owl-carousel show-nav-title" data-plugin-options="<?php echo esc_attr($options) ?>"<?php endif; ?>>
                    <?php
                    $count = 0;
                    while ($posts->have_posts()) {
                        $posts->the_post();
                        global $previousday;
                        unset($previousday);

                        if ($count % $items == 0) echo '<div class="post-slide">';

                        if ($show_image) {
                            get_template_part('content', 'post-item' . ($view == 'small' ? '-small' : ''));
                        } else {
                            get_template_part('content', 'post-item-no-image' . ($view == 'small' ? '-small' : ''));
                        }

                        if ($count % $items == $items - 1) echo '</div>';

                        $count++;
                    }
                    ?>
                </div>
            </div>
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
        $instance['show_image'] = $new_instance['show_image'];

        return $instance;
    }

    function form($instance) {

        $defaults = array('title' => __('Recent Posts', 'porto-widgets'), 'number' => 6, 'items' => 3, 'view' => 'small', 'cat' => '', 'show_image' => 'on');
        $instance = wp_parse_args((array) $instance, $defaults); ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <strong><?php _e('Title', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php if (isset($instance['title'])) echo $instance['title']; ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>">
                <strong><?php _e('Number of posts to show', 'porto-widgets') ?>:</strong>
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
        <p>
            <input class="checkbox" type="checkbox" <?php checked($instance['show_image'], 'on'); ?> id="<?php echo $this->get_field_id('show_image'); ?>" name="<?php echo $this->get_field_name('show_image'); ?>" />
            <label for="<?php echo $this->get_field_id('show_image'); ?>"><?php echo __('Show Post Image', 'porto-widgets') ?></label>
        </p>
    <?php
    }
}

?>