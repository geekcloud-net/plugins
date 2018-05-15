<?php

add_action('widgets_init', 'porto_tweets_load_widgets');

add_action('wp_ajax_porto_twitter_tweets', 'porto_twitter_tweets');
add_action('wp_ajax_nopriv_porto_twitter_tweets', 'porto_twitter_tweets');

function porto_tweets_load_widgets() {
    register_widget('Porto_Twitter_Tweets_Widget');
}

function porto_twitter_tweets() {
    if (!isset($_POST['id'])) die;

    $widget_array = get_option('widget_tweets-widget');

    $instance = $widget_array[$_POST['id']];

    require_once(dirname(__FILE__) . '/tweet-php/TweetPHP.php');

    $consumer_key = $instance['consumer_key'];
    $consumer_secret = $instance['consumer_secret'];
    $access_token = $instance['access_token'];
    $access_secret = $instance['access_token_secret'];
    $twitter_screen_name = $instance['screen_name'];
    $tweets_to_display = $instance['count'];

    $TweetPHP = new TweetPHP(array(
        'consumer_key'          => $consumer_key,
        'consumer_secret'       => $consumer_secret,
        'access_token'          => $access_token,
        'access_token_secret'   => $access_secret,
        'twitter_screen_name'   => $twitter_screen_name,
        'cache_file'            => dirname(__FILE__) . '/tweet-php/cache/twitter.txt', // Where on the server to save the cached formatted tweets
        'cache_file_raw'        => dirname(__FILE__) . '/tweet-php/cache/twitter-array.txt', // Where on the server to save the cached raw tweets
        'cachetime'             => 60 * 60, // Seconds to cache feed
        'tweets_to_display'     => $tweets_to_display, // How many tweets to fetch
        'ignore_replies'        => true, // Ignore @replies
        'ignore_retweets'       => true, // Ignore retweets
        'twitter_style_dates'   => true, // Use twitter style dates e.g. 2 hours ago
        'twitter_date_text'     => array('seconds', 'minutes', 'about', 'hour', 'ago'),
        'date_format'           => '%I:%M %p %b %d%O', // The defult date format e.g. 12:08 PM Jun 12th. See: http://php.net/manual/en/function.strftime.php
        'date_lang'             => get_locale(), // Language for date e.g. 'fr_FR'. See: http://php.net/manual/en/function.setlocale.php
        'format'                => 'array', // Can be 'html' or 'array'
        'twitter_wrap_open'     => '<ul>',
        'twitter_wrap_close'    => '</ul>',
        'tweet_wrap_open'       => '<li><span class="status"><i class="fa fa-twitter"></i> ',
        'meta_wrap_open'        => '</span><span class="meta"> ',
        'meta_wrap_close'       => '</span>',
        'tweet_wrap_close'      => '</li>',
        'error_message'         => __('Oops, our twitter feed is unavailable right now.', 'porto-widgets'),
        'error_link_text'       => __('Follow us on Twitter', 'porto-widgets'),
        'debug'                 => false
    ));

    echo $TweetPHP->get_tweet_list();

    die();
}

class Porto_Twitter_Tweets_Widget extends WP_Widget {

    public function __construct() {
        $widget_ops = array('classname' => 'twitter-tweets', 'description' => __('The most recent tweets from twitter.', 'porto-widgets'));

        $control_ops = array('id_base' => 'tweets-widget');

        parent::__construct('tweets-widget', __('Porto: Twitter Tweets', 'porto-widgets'), $widget_ops, $control_ops);
    }

    function widget($args, $instance) {
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);
        $consumer_key = $instance['consumer_key'];
        $consumer_secret = $instance['consumer_secret'];
        $access_token = $instance['access_token'];
        $access_token_secret = $instance['access_token_secret'];
        $screen_name = $instance['screen_name'];
        $count = (int) $instance['count'];

        echo $before_widget;

        if ($title) {
            echo $before_title.$title.$after_title;
        }

        if ($screen_name && $consumer_key && $consumer_secret && $access_token && $access_token_secret && $count) {
            ?>
            <div class="tweets-box">
                <p><?php _e('Please wait...', 'porto-widgets') ?></p>
            </div>

            <script type="text/javascript">
                jQuery(function($) {
                    $.post('<?php echo esc_js(admin_url( 'admin-ajax.php' )) ?>', {
                            id: '<?php echo str_replace('tweets-widget-', '', $widget_id) ?>',
                            action: 'porto_twitter_tweets'
                        },
                        function(data) {
                            if (data) {
                                $('#<?php echo $widget_id ?> .tweets-box').html(data);
                                $("#<?php echo $widget_id; ?> .twitter-slider").owlCarousel({
                                    rtl: <?php echo is_rtl() ? 'true' : 'false' ?>,
                                    dots : false,
                                    nav : true,
                                    navText: ["", ""],
                                    items: 1,
                                    autoplay : true,
                                    autoplayTimeout: 5000
                                }).addClass('show-nav-title');
                            }
                        }
                    );
                });
            </script>
            <?php
        } else {
            echo '<p>'.__('Please configure widget options.', 'porto-widgets').'</p>';
        }

        echo $after_widget;
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;

        $instance['title'] = strip_tags($new_instance['title']);
        $instance['consumer_key'] = $new_instance['consumer_key'];
        $instance['consumer_secret'] = $new_instance['consumer_secret'];
        $instance['access_token'] = $new_instance['access_token'];
        $instance['access_token_secret'] = $new_instance['access_token_secret'];
        $instance['screen_name'] = $new_instance['screen_name'];
        $instance['count'] = $new_instance['count'];

        return $instance;
    }

    function form($instance) {
        $defaults = array('title' => __('Latest Tweets', 'porto-widgets'), 'screen_name' => '', 'count' => 2, 'consumer_key' => '', 'consumer_secret' => '', 'access_token' => '', 'access_token_secret' => '');
        $instance = wp_parse_args((array) $instance, $defaults); ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <strong><?php echo __('Title', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php if (isset($instance['title'])) echo $instance['title']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('consumer_key'); ?>">
                <strong><?php echo __('Consumer Key', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('consumer_key'); ?>" name="<?php echo $this->get_field_name('consumer_key'); ?>" value="<?php if (isset($instance['consumer_key'])) echo $instance['consumer_key']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('consumer_secret'); ?>">
                <strong><?php echo __('Consumer Secret', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('consumer_secret'); ?>" name="<?php echo $this->get_field_name('consumer_secret'); ?>" value="<?php if (isset($instance['consumer_secret'])) echo $instance['consumer_secret']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('access_token'); ?>">
                <strong><?php echo __('Access Token', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('access_token'); ?>" name="<?php echo $this->get_field_name('access_token'); ?>" value="<?php if (isset($instance['access_token'])) echo $instance['access_token']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('access_token_secret'); ?>">
                <strong><?php echo __('Access Token Secret', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('access_token_secret'); ?>" name="<?php echo $this->get_field_name('access_token_secret'); ?>" value="<?php if (isset($instance['access_token_secret'])) echo $instance['access_token_secret']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('screen_name'); ?>">
                <strong><?php echo __('Twitter Screen Name', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('screen_name'); ?>" name="<?php echo $this->get_field_name('screen_name'); ?>" value="<?php if (isset($instance['screen_name'])) echo $instance['screen_name']; ?>" />
            </label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('count'); ?>">
                <strong><?php echo __('Number of Tweets', 'porto-widgets') ?>:</strong>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" value="<?php if (isset($instance['count'])) echo $instance['count']; ?>" />
            </label>
        </p>

        <p><strong><?php echo __('Info', 'porto-widgets') ?>:</strong><br/><?php echo __('You can find or create <a href="http://dev.twitter.com/apps" target="_blank">Twitter App here</a>.', 'porto-widgets') ?></p>

    <?php
    }
}
?>