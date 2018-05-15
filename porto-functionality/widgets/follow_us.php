<?php
add_action('widgets_init', 'porto_follow_us_load_widgets');

function porto_follow_us_load_widgets() {
    register_widget('Porto_Follow_Us_Widget');
}

class Porto_Follow_Us_Widget extends WP_Widget {

    public function __construct() {

        $widget_ops = array('classname' => 'follow-us', 'description' => __('Add Social Links.', 'porto-widgets'));

        $control_ops = array('id_base' => 'follow-us-widget');

        parent::__construct('follow-us-widget', __('Porto: Follow Us', 'porto-widgets'), $widget_ops, $control_ops);
    }

    function widget($args, $instance) {
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);
        $nofollow = isset($instance['nofollow']) ? $instance['nofollow'] : '';
        $default_skin = isset($instance['default_skin']) ? $instance['default_skin'] : '';
        $disable_br = isset($instance['disable_br']) ? $instance['disable_br'] : '';
        $disable_tooltip = isset($instance['disable_tooltip']) ? $instance['disable_tooltip'] : '';
        $follow_before = $instance['follow_before'];
        $facebook = isset($instance['facebook']) ? $instance['facebook'] : '';
        $twitter = isset($instance['twitter']) ? $instance['twitter'] : '';
        $rss = isset($instance['rss']) ? $instance['rss'] : '';
        $pinterest = isset($instance['pinterest']) ? $instance['pinterest'] : '';
        $youtube = isset($instance['youtube']) ? $instance['youtube'] : '';
        $instagram = isset($instance['instagram']) ? $instance['instagram'] : '';
        $skype = isset($instance['skype']) ? $instance['skype'] : '';
        $linkedin = isset($instance['linkedin']) ? $instance['linkedin'] : '';
        $googleplus = isset($instance['googleplus']) ? $instance['googleplus'] : '';
        $vk = isset($instance['vk']) ? $instance['vk'] : '';
        $xing = isset($instance['xing']) ? $instance['xing'] : '';
        $tumblr = isset($instance['tumblr']) ? $instance['tumblr'] : '';
        $reddit = isset($instance['reddit']) ? $instance['reddit'] : '';
        $vimeo = isset($instance['vimeo']) ? $instance['vimeo'] : '';
        $telegram = isset($instance['telegram']) ? $instance['telegram'] : '';
        $yelp = isset($instance['yelp']) ? $instance['yelp'] : '';
        $flickr = isset($instance['flickr']) ? $instance['flickr'] : '';
        $whatsapp = isset($instance['whatsapp']) ? $instance['whatsapp'] : '';
        $follow_after = $instance['follow_after'];

        if ($nofollow)
            $nofollow = ' rel="nofollow"';

        echo $before_widget;

        if ($title) {
            echo $before_title . $title . $after_title;
        }

        $class = 'share-links';
        $tooltip = '';
        if ($disable_br)
            $class .= ' disable-br';
        if ($default_skin)
            $class .= ' default-skin';
        if (!$disable_tooltip)
            $tooltip = 'data-toggle="tooltip" data-placement="bottom" ';
        ?>
        <div class="<?php echo $class ?>">
            <?php if ($follow_before) : ?><?php echo wpautop(do_shortcode($follow_before)) ?><?php endif; ?>
            <?php
            if ($facebook) :
                ?><a href="<?php echo esc_url($facebook) ?>" <?php echo $nofollow ?> target="_blank" <?php echo $tooltip ?>title="<?php echo __('Facebook', 'porto-widgets') ?>" class="share-facebook"><?php echo __('Facebook', 'porto-widgets') ?></a><?php
            endif;

            if ($twitter) :
                ?><a href="<?php echo esc_url($twitter) ?>" <?php echo $nofollow ?> target="_blank" <?php echo $tooltip ?>title="<?php echo __('Twitter', 'porto-widgets') ?>" class="share-twitter"><?php echo __('Twitter', 'porto-widgets') ?></a><?php
            endif;

            if ($rss) :
                ?><a href="<?php echo esc_url($rss) ?>" <?php echo $nofollow ?> target="_blank" <?php echo $tooltip ?>title="<?php echo __('RSS', 'porto-widgets') ?>" class="share-rss"><?php echo __('RSS', 'porto-widgets') ?></a><?php
            endif;

            if ($pinterest) :
                ?><a href="<?php echo esc_url($pinterest) ?>" <?php echo $nofollow ?> target="_blank" <?php echo $tooltip ?>title="<?php echo __('Pinterest', 'porto-widgets') ?>" class="share-pinterest"><?php echo __('Pinterest', 'porto-widgets') ?></a><?php
            endif;

            if ($youtube) :
                ?><a href="<?php echo esc_url($youtube) ?>" <?php echo $nofollow ?> target="_blank" <?php echo $tooltip ?>title="<?php echo __('Youtube', 'porto-widgets') ?>" class="share-youtube"><?php echo __('Youtube', 'porto-widgets') ?></a><?php
            endif;

            if ($instagram) :
                ?><a href="<?php echo esc_url($instagram) ?>" <?php echo $nofollow ?> target="_blank" <?php echo $tooltip ?>title="<?php echo __('Instagram', 'porto-widgets') ?>" class="share-instagram"><?php echo __('Instagram', 'porto-widgets') ?></a><?php
            endif;

            if ($skype) :
                ?><a href="<?php echo esc_attr($skype) ?>" <?php echo $nofollow ?> target="_blank" <?php echo $tooltip ?>title="<?php echo __('Skype', 'porto-widgets') ?>" class="share-skype"><?php echo __('Skype', 'porto-widgets') ?></a><?php
            endif;

            if ($linkedin) :
                ?><a href="<?php echo esc_url($linkedin) ?>" <?php echo $nofollow ?> target="_blank" <?php echo $tooltip ?>title="<?php echo __('Linkedin', 'porto-widgets') ?>" class="share-linkedin"><?php echo __('Linkedin', 'porto-widgets') ?></a><?php
            endif;

            if ($googleplus) :
                ?><a href="<?php echo esc_url($googleplus) ?>" <?php echo $nofollow ?> target="_blank" <?php echo $tooltip ?>title="<?php echo __('Google +', 'porto-widgets') ?>" class="share-googleplus"><?php echo __('Google +', 'porto-widgets') ?></a><?php
            endif;

            if ($vk) :
                ?><a href="<?php echo esc_url($vk) ?>" <?php echo $nofollow ?> target="_blank" <?php echo $tooltip ?>title="<?php echo __('VK', 'porto-widgets') ?>" class="share-vk"><?php echo __('VK', 'porto-widgets') ?></a><?php
            endif;

            if ($xing) :
                ?><a href="<?php echo esc_url($xing) ?>" <?php echo $nofollow ?> target="_blank" <?php echo $tooltip ?>title="<?php echo __('Xing', 'porto-widgets') ?>" class="share-xing"><?php echo __('Xing', 'porto-widgets') ?></a><?php
            endif;

            if ($tumblr) :
                ?><a href="<?php echo esc_url($tumblr) ?>" <?php echo $nofollow ?> target="_blank" <?php echo $tooltip ?>title="<?php echo __('Tumblr', 'porto-widgets') ?>" class="share-tumblr"><?php echo __('Tumblr', 'porto-widgets') ?></a><?php
            endif;

            if ($reddit) :
                ?><a href="<?php echo esc_url($reddit) ?>" <?php echo $nofollow ?> target="_blank" <?php echo $tooltip ?>title="<?php echo __('Reddit', 'porto-widgets') ?>" class="share-reddit"><?php echo __('Reddit', 'porto-widgets') ?></a><?php
            endif;

            if ($vimeo) :
                ?><a href="<?php echo esc_url($vimeo) ?>" <?php echo $nofollow ?> target="_blank" <?php echo $tooltip ?>title="<?php echo __('Vimeo', 'porto-widgets') ?>" class="share-vimeo"><?php echo __('Vimeo', 'porto-widgets') ?></a><?php
            endif;

            if ($telegram) :
                ?><a href="<?php echo esc_url($telegram) ?>" <?php echo $nofollow ?> target="_blank" <?php echo $tooltip ?>title="<?php echo __('Telegram', 'porto-widgets') ?>" class="share-telegram"><?php echo __('Telegram', 'porto-widgets') ?></a><?php
            endif;

            if ($yelp) :
                ?><a href="<?php echo esc_url($yelp) ?>" <?php echo $nofollow ?> target="_blank" <?php echo $tooltip ?>title="<?php echo __('Yelp', 'porto-widgets') ?>" class="share-yelp"><?php echo __('Yelp', 'porto-widgets') ?></a><?php
            endif;

            if ($flickr) :
                ?><a href="<?php echo esc_url($flickr) ?>" <?php echo $nofollow ?> target="_blank" <?php echo $tooltip ?>title="<?php echo __('Flickr', 'porto-widgets') ?>" class="share-flickr"><?php echo __('Flickr', 'porto-widgets') ?></a><?php
            endif;

            if ($whatsapp) :
                ?><a href="whatsapp://send?text=<?php echo esc_url($whatsapp) ?>" <?php echo $nofollow ?> target="_blank" <?php echo $tooltip ?>title="<?php echo __('WhatsApp', 'porto-widgets') ?>" class="share-whatsapp" style="display:none"><?php echo __('WhatsApp', 'porto-widgets') ?></a><?php
            endif;
            ?>
            <?php if ($follow_after) : ?><?php echo wpautop(do_shortcode($follow_after)) ?><?php endif; ?>
        </div>

        <?php
        echo $after_widget;
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;

        $instance['title'] = strip_tags($new_instance['title']);
        $instance['nofollow'] = $new_instance['nofollow'];
        $instance['default_skin'] = $new_instance['default_skin'];
        $instance['disable_br'] = $new_instance['disable_br'];
        $instance['disable_tooltip'] = $new_instance['disable_tooltip'];
        $instance['follow_before'] = $new_instance['follow_before'];
        $instance['facebook'] = $new_instance['facebook'];
        $instance['twitter'] = $new_instance['twitter'];
        $instance['rss'] = $new_instance['rss'];
        $instance['pinterest'] = $new_instance['pinterest'];
        $instance['youtube'] = $new_instance['youtube'];
        $instance['instagram'] = $new_instance['instagram'];
        $instance['skype'] = $new_instance['skype'];
        $instance['linkedin'] = $new_instance['linkedin'];
        $instance['googleplus'] = $new_instance['googleplus'];
        $instance['vk'] = $new_instance['vk'];
        $instance['xing'] = $new_instance['xing'];
        $instance['tumblr'] = $new_instance['tumblr'];
        $instance['reddit'] = $new_instance['reddit'];
        $instance['vimeo'] = $new_instance['vimeo'];
        $instance['telegram'] = $new_instance['telegram'];
        $instance['yelp'] = $new_instance['yelp'];
        $instance['flickr'] = $new_instance['flickr'];
        $instance['whatsapp'] = $new_instance['whatsapp'];
        $instance['follow_after'] = $new_instance['follow_after'];

        return $instance;
    }

    function form($instance) {
        $defaults = array('title' => __('Follow Us', 'porto-widgets'), 'nofollow' => '', 'default_skin' => '', 'disable_br' => '', 'disable_tooltip' => '', 'follow_before' => '', 'facebook' => '', 'twitter' => '', 'rss' => '', 'pinterest' => '', 'youtube' => '', 'instagram' => '', 'skype' => '', 'linkedin' => '', 'googleplus' => '', 'vk' => '', 'xing' => '', 'tumblr' => '', 'reddit' => '', 'vimeo' => '', 'telegram' => '', 'yelp' => '', 'flickr' => '', 'whatsapp' => '', 'follow_after' => '');
        $instance = wp_parse_args((array) $instance, $defaults); ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <strong><?php echo __('Title', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php if (isset($instance['title'])) echo $instance['title']; ?>" />
            </label>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked($instance['nofollow'], 'on'); ?> id="<?php echo $this->get_field_id('nofollow'); ?>" name="<?php echo $this->get_field_name('nofollow'); ?>" />
            <label for="<?php echo $this->get_field_id('nofollow'); ?>"><?php echo __('Add rel="nofollow" to links', 'porto-widgets') ?></label>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked($instance['disable_br'], 'on'); ?> id="<?php echo $this->get_field_id('disable_br'); ?>" name="<?php echo $this->get_field_name('disable_br'); ?>" />
            <label for="<?php echo $this->get_field_id('disable_br'); ?>"><?php echo __('Disable border radius', 'porto-widgets') ?></label>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked($instance['disable_tooltip'], 'on'); ?> id="<?php echo $this->get_field_id('disable_tooltip'); ?>" name="<?php echo $this->get_field_name('disable_tooltip'); ?>" />
            <label for="<?php echo $this->get_field_id('disable_tooltip'); ?>"><?php echo __('Disable tooltip', 'porto-widgets') ?></label>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked($instance['default_skin'], 'on'); ?> id="<?php echo $this->get_field_id('default_skin'); ?>" name="<?php echo $this->get_field_name('default_skin'); ?>" />
            <label for="<?php echo $this->get_field_id('default_skin'); ?>"><?php echo __('Use default skin', 'porto-widgets') ?></label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('follow_before'); ?>">
                <strong><?php echo __('Before Description', 'porto-widgets') ?>:</strong>
                <textarea class="widefat" id="<?php echo $this->get_field_id('follow_before'); ?>" name="<?php echo $this->get_field_name('follow_before'); ?>"><?php if (isset($instance['follow_before'])) echo $instance['follow_before']; ?></textarea>
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('facebook'); ?>">
                <strong><?php echo __('Facebook', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('facebook'); ?>" name="<?php echo $this->get_field_name('facebook'); ?>" value="<?php if (isset($instance['facebook'])) echo $instance['facebook']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('twitter'); ?>">
                <strong><?php echo __('Twitter', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('twitter'); ?>" name="<?php echo $this->get_field_name('twitter'); ?>" value="<?php if (isset($instance['twitter'])) echo $instance['twitter']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('rss'); ?>">
                <strong><?php echo __('RSS', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('rss'); ?>" name="<?php echo $this->get_field_name('rss'); ?>" value="<?php if (isset($instance['rss'])) echo $instance['rss']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('pinterest'); ?>">
                <strong><?php echo __('Pinterest', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('pinterest'); ?>" name="<?php echo $this->get_field_name('pinterest'); ?>" value="<?php if (isset($instance['pinterest'])) echo $instance['pinterest']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('youtube'); ?>">
                <strong><?php echo __('Youtube', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('youtube'); ?>" name="<?php echo $this->get_field_name('youtube'); ?>" value="<?php if (isset($instance['youtube'])) echo $instance['youtube']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('instagram'); ?>">
                <strong><?php echo __('Instagram', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('instagram'); ?>" name="<?php echo $this->get_field_name('instagram'); ?>" value="<?php if (isset($instance['instagram'])) echo $instance['instagram']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('skype'); ?>">
                <strong><?php echo __('Skype', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('skype'); ?>" name="<?php echo $this->get_field_name('skype'); ?>" value="<?php if (isset($instance['skype'])) echo $instance['skype']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('linkedin'); ?>">
                <strong><?php echo __('Linkedin', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('linkedin'); ?>" name="<?php echo $this->get_field_name('linkedin'); ?>" value="<?php if (isset($instance['linkedin'])) echo $instance['linkedin']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('googleplus'); ?>">
                <strong><?php echo __('Google +', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('googleplus'); ?>" name="<?php echo $this->get_field_name('googleplus'); ?>" value="<?php if (isset($instance['googleplus'])) echo $instance['googleplus']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('vk'); ?>">
                <strong><?php echo __('VK', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('vk'); ?>" name="<?php echo $this->get_field_name('vk'); ?>" value="<?php if (isset($instance['vk'])) echo $instance['vk']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('xing'); ?>">
                <strong><?php echo __('Xing', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('xing'); ?>" name="<?php echo $this->get_field_name('xing'); ?>" value="<?php if (isset($instance['xing'])) echo $instance['xing']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('tumblr'); ?>">
                <strong><?php echo __('Tumblr', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('tumblr'); ?>" name="<?php echo $this->get_field_name('tumblr'); ?>" value="<?php if (isset($instance['tumblr'])) echo $instance['tumblr']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('reddit'); ?>">
                <strong><?php echo __('Reddit', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('reddit'); ?>" name="<?php echo $this->get_field_name('reddit'); ?>" value="<?php if (isset($instance['reddit'])) echo $instance['reddit']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('vimeo'); ?>">
                <strong><?php echo __('Vimeo', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('vimeo'); ?>" name="<?php echo $this->get_field_name('vimeo'); ?>" value="<?php if (isset($instance['vimeo'])) echo $instance['vimeo']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('telegram'); ?>">
                <strong><?php echo __('Telegram', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('telegram'); ?>" name="<?php echo $this->get_field_name('telegram'); ?>" value="<?php if (isset($instance['telegram'])) echo $instance['telegram']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('yelp'); ?>">
                <strong><?php echo __('Yelp', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('yelp'); ?>" name="<?php echo $this->get_field_name('yelp'); ?>" value="<?php if (isset($instance['yelp'])) echo $instance['yelp']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('flickr'); ?>">
                <strong><?php echo __('Flickr', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('flickr'); ?>" name="<?php echo $this->get_field_name('flickr'); ?>" value="<?php if (isset($instance['flickr'])) echo $instance['flickr']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('whatsapp'); ?>">
                <strong><?php echo __('WhatsApp', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('whatsapp'); ?>" name="<?php echo $this->get_field_name('whatsapp'); ?>" value="<?php if (isset($instance['whatsapp'])) echo $instance['whatsapp']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('follow_after'); ?>">
                <strong><?php echo __('After Description', 'porto-widgets') ?>:</strong>
                <textarea class="widefat" id="<?php echo $this->get_field_id('follow_after'); ?>" name="<?php echo $this->get_field_name('follow_after'); ?>"><?php if (isset($instance['follow_after'])) echo $instance['follow_after']; ?></textarea>
            </label>
        </p>
    <?php
    }
}
?>