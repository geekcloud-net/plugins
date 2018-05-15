<?php
add_action('widgets_init', 'porto_contact_info_load_widgets');

function porto_contact_info_load_widgets() {
    register_widget('Porto_Contact_Info_Widget');
}

class Porto_Contact_Info_Widget extends WP_Widget {

    public function __construct() {

        $widget_ops = array('classname' => 'contact-info', 'description' => __('Add contact information.', 'porto-widgets'));

        $control_ops = array('id_base' => 'contact-info-widget');

        parent::__construct('contact-info-widget', __('Porto: Contact Info', 'porto-widgets'), $widget_ops, $control_ops);
    }

    function widget($args, $instance) {
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);
        $contact_before = $instance['contact_before'];
        $address_label = (isset($instance['address_label']) && $instance['address_label']) ? $instance['address_label'] : __('Address', 'porto-widgets');
        $phone_label = (isset($instance['phone_label']) && $instance['phone_label']) ? $instance['phone_label'] : __('Phone', 'porto-widgets');
        $email_label = (isset($instance['email_label']) && $instance['email_label']) ? $instance['email_label'] : __('Email', 'porto-widgets');
        $working_label = (isset($instance['working_label']) && $instance['working_label']) ? $instance['working_label'] : __('Working Days/Hours', 'porto-widgets');
        $address = $instance['address'];
        $phone = $instance['phone'];
        $email = $instance['email'];
        $working = $instance['working'];
        $contact_after = $instance['contact_after'];
        $icon = (isset($instance['icon']) && $instance['icon'] == 'on') ? true : false;
        $view = (isset($instance['view']) && $instance['view']) ? $instance['view'] : 'inline';

        echo $before_widget;

        if ($title) {
            echo $before_title . $title . $after_title;
        }
        ?>
        <div class="contact-info<?php echo $view == 'block' ? ' contact-info-block' : '' ?>">
            <?php if ($contact_before) : ?><?php echo wpautop(do_shortcode($contact_before)) ?><?php endif; ?>
            <ul class="contact-details<?php echo $icon ? ' list list-icons' : '' ?>">
                <?php if ($address) : ?><li><i class="fa fa-map-marker"></i> <strong><?php echo $address_label ?>:</strong> <span><?php echo force_balance_tags($address) ?></span></li><?php endif; ?>
                <?php if ($phone) : ?><li><i class="fa fa-phone"></i> <strong><?php echo $phone_label ?>:</strong> <span><?php echo force_balance_tags($phone) ?></span></li><?php endif; ?>
                <?php if ($email) : ?><li><i class="fa fa-envelope"></i> <strong><?php echo $email_label ?>:</strong> <span><a href="mailto:<?php echo esc_attr($email) ?>"><?php echo force_balance_tags($email) ?></a></span></li><?php endif; ?>
                <?php if ($working) : ?><li><i class="fa fa-clock-o"></i> <strong><?php echo $working_label ?>:</strong> <span><?php echo force_balance_tags($working) ?></span></li><?php endif; ?>
            </ul>
            <?php if ($contact_after) : ?><?php echo wpautop(do_shortcode($contact_after)) ?><?php endif; ?>
        </div>

        <?php
        echo $after_widget;
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;

        $instance['title'] = strip_tags($new_instance['title']);
        $instance['contact_before'] = $new_instance['contact_before'];
        $instance['address_label'] = $new_instance['address_label'];
        $instance['address'] = $new_instance['address'];
        $instance['phone_label'] = $new_instance['phone_label'];
        $instance['phone'] = $new_instance['phone'];
        $instance['email_label'] = $new_instance['email_label'];
        $instance['email'] = $new_instance['email'];
        $instance['working_label'] = $new_instance['working_label'];
        $instance['working'] = $new_instance['working'];
        $instance['contact_after'] = $new_instance['contact_after'];
        $instance['view'] = $new_instance['view'];
        $instance['icon'] = $new_instance['icon'];

        return $instance;
    }

    function form($instance) {
        $defaults = array('title' => __('Contact Us', 'porto-widgets'), 'contact_before' => '', 'address_label' => '', 'address' => '1234 Street Name, City Name, Country Name', 'phone_label' => '', 'phone' => '(123) 456-7890', 'email_label' => '', 'email' => 'mail@example.com', 'working_label' => '', 'working' => 'Mon - Sun / 9:00 AM - 8:00 PM', 'contact_after' => '', 'view' => 'inline', 'icon' => '');
        $instance = wp_parse_args((array) $instance, $defaults); ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <strong><?php echo __('Title', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php if (isset($instance['title'])) echo $instance['title']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('contact_before'); ?>">
                <strong><?php echo __('Before Description', 'porto-widgets') ?>:</strong>
                <textarea class="widefat" id="<?php echo $this->get_field_id('contact_before'); ?>" name="<?php echo $this->get_field_name('contact_before'); ?>"><?php if (isset($instance['contact_before'])) echo $instance['contact_before']; ?></textarea>
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('address_label'); ?>">
                <strong><?php echo __('Address Label', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('address_label'); ?>" name="<?php echo $this->get_field_name('address_label'); ?>" value="<?php if (isset($instance['address_label'])) echo $instance['address_label']; ?>" placeholder="<?php echo __('Address', 'porto-widgets') ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('address'); ?>">
                <strong><?php echo __('Address', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('address'); ?>" name="<?php echo $this->get_field_name('address'); ?>" value="<?php if (isset($instance['address'])) echo $instance['address']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('phone_label'); ?>">
                <strong><?php echo __('Phone Label', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('phone_label'); ?>" name="<?php echo $this->get_field_name('phone_label'); ?>" value="<?php if (isset($instance['phone_label'])) echo $instance['phone_label']; ?>" placeholder="<?php echo __('Phone', 'porto-widgets') ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('phone'); ?>">
                <strong><?php echo __('Phone', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('phone'); ?>" name="<?php echo $this->get_field_name('phone'); ?>" value="<?php if (isset($instance['phone'])) echo $instance['phone']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('email_label'); ?>">
                <strong><?php echo __('Email Label', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('email_label'); ?>" name="<?php echo $this->get_field_name('email_label'); ?>" value="<?php if (isset($instance['email_label'])) echo $instance['email_label']; ?>" placeholder="<?php echo __('Email', 'porto-widgets') ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('email'); ?>">
                <strong><?php echo __('Email', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('email'); ?>" name="<?php echo $this->get_field_name('email'); ?>" value="<?php if (isset($instance['email'])) echo $instance['email']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('working_label'); ?>">
                <strong><?php echo __('Working Days/Hours Label', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('working_label'); ?>" name="<?php echo $this->get_field_name('working_label'); ?>" value="<?php if (isset($instance['working_label'])) echo $instance['working_label']; ?>" placeholder="<?php echo __('Working Days/Hours', 'porto-widgets') ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('working'); ?>">
                <strong><?php echo __('Working Days/Hours', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('working'); ?>" name="<?php echo $this->get_field_name('working'); ?>" value="<?php if (isset($instance['working'])) echo $instance['working']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('contact_after'); ?>">
                <strong><?php echo __('After Description', 'porto-widgets') ?>:</strong>
                <textarea class="widefat" id="<?php echo $this->get_field_id('contact_after'); ?>" name="<?php echo $this->get_field_name('contact_after'); ?>"><?php if (isset($instance['contact_after'])) echo $instance['contact_after']; ?></textarea>
            </label>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked($instance['icon'], 'on'); ?> id="<?php echo $this->get_field_id('icon'); ?>" name="<?php echo $this->get_field_name('icon'); ?>" />
            <label for="<?php echo $this->get_field_id('icon'); ?>"><?php echo __('Highlight Icons', 'porto-widgets') ?></label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('view'); ?>">
                <strong><?php _e('View Type', 'porto-widgets') ?>:</strong>
                <select class="widefat" id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('view'); ?>">
                    <option value="inline"<?php echo (isset($instance['view']) && $instance['view'] == 'inline')? ' selected="selected"' : '' ?>><?php _e('Inline', 'porto-widgets') ?></option>
                    <option value="block"<?php echo (isset($instance['view']) && $instance['view'] == 'block')? ' selected="selected"' : '' ?>><?php _e('Separate', 'porto-widgets') ?></option>
                </select>
            </label>
        </p>
    <?php
    }
}
?>