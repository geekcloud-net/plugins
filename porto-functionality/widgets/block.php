<?php
add_action('widgets_init', 'porto_block_load_widgets');

function porto_block_load_widgets() {
    register_widget('Porto_Block_Widget');
}

class Porto_Block_Widget extends WP_Widget {

    public function __construct() {
        
        $widget_ops = array('classname' => 'widget-block', 'description' => __('Show block.', 'porto-widgets'));

		$control_ops = array('id_base' => 'block-widget');

        parent::__construct('block-widget', __('Porto: Block', 'porto-widgets'), $widget_ops, $control_ops);
	}

	function widget($args, $instance) {
		extract($args);

        $title = '';
        if (isset($instance['title']))
		    $title = apply_filters('widget_title', $instance['title']);

        $output = '';
        if ($instance['name']) {
            $output = do_shortcode('[porto_block name="' . $instance['name'] . '"]');
        }

        if (!$output) return;

        echo $before_widget;

		if ($title) {
			echo $before_title . $title . $after_title;
		}

        ?>
            <div class="block">
                <?php echo $output; ?>
            </div>
        <?php

        echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;

        $instance['title'] = $new_instance['title'];
        $instance['name'] = $new_instance['name'];

		return $instance;
	}

	function form($instance) {
		$defaults = array();
		$instance = wp_parse_args((array) $instance, $defaults);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <strong><?php _e('Title', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php if (isset($instance['title'])) echo $instance['title']; ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('name'); ?>">
                <strong><?php _e('Block Slug Name', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('name'); ?>" name="<?php echo $this->get_field_name('name'); ?>" value="<?php if (isset($instance['name'])) echo $instance['name']; ?>" />
            </label>
        </p>
	    <?php
	}
}
?>