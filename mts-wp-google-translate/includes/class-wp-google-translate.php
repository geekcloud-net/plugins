<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      1.0.0
 *
 * @package    WP-Google-Translate
 * @subpackage WP-Google-Translate/includes
 */
class WP_Google_Translate {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      WP_Google_Translate_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The defualt options for the setting page
     * the plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      array stores all the defualt option as multi dimension array
     */
    private $default_options;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $mts_google_translator    The string used to uniquely identify this plugin.
     */
    protected $mts_google_translator;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {

        $this->mts_google_translator = 'wp-google-translate';
        $this->version = '1.0.7';
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

        add_action('admin_menu', array($this, 'mts_google_translate_register_menu_page'));
        add_action('widgets_init', array($this, 'register_mts_google_translate_widget'));
        add_action('wp_footer', array($this, 'mts_google_translate_Init'));
        add_action('wp_footer', array($this, 'mts_get_left_languagebar'));
        add_filter('wp_nav_menu_items', array($this, 'add_nav_menu_items'), 10, 2);

        //default options for plugin
        $this->default_options = array(
            'default_language' => 'auto',
            'languages' => array(
                "auto" => 'Detect language',
                "af" => 'Afrikaans',
                "sq" => 'Albanian',
                "ar" => 'Arabic',
                "hy" => 'Armenian',
                "az" => 'Azerbaijani',
                "eu" => 'Basque',
                "be" => 'Belarusian',
                "bg" => 'Bulgarian',
                "ca" => 'Catalan',
                "zh-CN" => 'Chinese (Simplified)',
                "zh-TW" => 'Chinese (Traditional)',
                "hr" => 'Croatian',
                "cs" => 'Czech',
                "da" => 'Danish',
                "nl" => 'Dutch',
                "en" => 'English',
                "et" => 'Estonian',
                "tl" => 'Filipino',
                "fi" => 'Finnish',
                "fr" => 'French',
                "gl" => 'Galician',
                "ka" => 'Georgian',
                "de" => 'German',
                "el" => 'Greek',
                "ht" => 'Haitian Creole',
                "iw" => 'Hebrew',
                "hi" => 'Hindi',
                "hu" => 'Hungarian',
                "is" => 'Icelandic',
                "id" => 'Indonesian',
                "ga" => 'Irish',
                "it" => 'Italian',
                "ja" => 'Japanese',
                "ko" => 'Korean',
                "lv" => 'Latvian',
                "lt" => 'Lithuanian',
                "mk" => 'Macedonian',
                "ms" => 'Malay',
                "mt" => 'Maltese',
                "no" => 'Norwegian',
                "fa" => 'Persian',
                "pl" => 'Polish',
                "pt" => 'Portuguese',
                "ro" => 'Romanian',
                "ru" => 'Russian',
                "sr" => 'Serbian',
                "sk" => 'Slovak',
                "sl" => 'Slovenian',
                "es" => 'Spanish',
                "sw" => 'Swahili',
                "sv" => 'Swedish',
                "ta" => 'Tamil',
                "te" => 'Telugu',
                "th" => 'Thai',
                "tr" => 'Turkish',
                "uk" => 'Ukrainian',
                "ur" => 'Urdu',
                "vi" => 'Vietnamese',
                "cy" => 'Welsh',
                "yi" => 'Yiddish',
                "yo" => 'Yoruba',
                "zu" => 'Zulu'
            ),
            'tracking_enabled' => '',
            'tracking_id' => '',
            'exclude_mobile_browers' => true,
            'button_display' => 'on',
            'button_bg_color' => '#f1f1f1',
            'button_hover_bg_color' => '#f1f1f1',
            'button_font_color' => '#555555',
            'button_hover_font_color' => '#555555',
            'button_border_color' => '#555555',
            'list_bg_color' => '#f1f1f1',
            'list_hover_bg_color' => '#f1f1f1',
            'list_font_color' => '#555555',
            'list_hover_font_color' => '#555555',
            'list_border_color' => '#555555',
            'menu_item' => '',
            'menu_button_text' => 'Select Language',
            'toolbar_position' => 'TOP_RIGHT',
            'toolbar_position_options' => array(
                'TOP_LEFT' => __('Top Left','mts-google-translate'),
                'TOP_RIGHT' => __('Top Right','mts-google-translate'),
                'BOTTOM_LEFT' => __('Bottom Left','mts-google-translate'),
                'BOTTOM_RIGHT' => __('Bottom Right','mts-google-translate')
            )
        );
    }

    /**
     * Register the MTS Google Translate widget
     *
     * @since     1.0.0
     * @access   public
     */
    public function register_mts_google_translate_widget() {
        register_widget('MTS_Google_Translate_Widget');
    }

    public function add_nav_menu_items($items, $args) {
        $options = get_option('mts_google_translate_options');
        if (isset($options['menu_item']) && ($options['menu_item'] != '')) {
            $items .= '<li id="mts-language-btn-top"><a href="javascript:void(0)">' . $options['menu_button_text'] . '</a></li>';
        }
        return $items;
    }

    /**
     * register menu page for the plugin
     *
     * @since     1.0.0
     * @access   public
     */
    public function mts_google_translate_register_menu_page() {
        add_menu_page(
                __('WP Google Translate', 'mts-google-translate'), __('WP Google Translate', 'mts-google-translate'), 'manage_options', 'wp-google-translate', array(
            $this,
            'mts_google_translate_setting_page'
                ), 'dashicons-translation'
        );
        add_action('admin_init', array($this, 'mts_google_translate_register_settings'));
    }

    /**
     * register setting group for the plugin
     *
     * @since     1.0.0
     * @access   public
     */
    public function mts_google_translate_register_settings() {
        register_setting('mts-google-translate-settings-group', 'mts_google_translate_options');
    }

    public function get_default_color_palettes() {
        $default_palettes = array(
            array(
                'name' => __('Default', 'mts-google-translate'),
                'colors' => array(
                    'button_bg_color' => '#f7f7f7',
                    'button_hover_bg_color' => '#e0e0e0',
                    'button_font_color' => '#000000',
                    'button_hover_font_color' => '#000000',
                    'button_border_color' => '#686868',
                    'list_bg_color' => '#f7f7f7',
                    'list_font_color' => '#000000',
                    'list_hover_bg_color' => '#e0e0e0',
                    'list_hover_font_color' => '#000000',
                    'list_border_color' => '#e2e2e2'
                ),
            ),
            array(
                'name' => __('Dark', 'mts-google-translate'),
                'colors' => array(
                    'button_bg_color' => '#444444',
                    'button_font_color' => '#FFFFFF',
                    'button_border_color' => '#444444',
                    'list_bg_color' => '#444444',
                    'list_font_color' => '#FFFFFF',
                    'list_border_color' => '#777777',
                    'button_hover_bg_color' => '#777777',
                    'button_hover_font_color' => '#FFFFFF',
                    'list_hover_bg_color' => '#777777',
                    'list_hover_font_color' => '#FFFFFF'
                )
            ),
            array(
                'name' => __('Orange', 'mts-google-translate'),
                'colors' => array(
                    'button_bg_color' => '#FFFFFF',
                    'button_font_color' => '#000000',
                    'button_border_color' => '#FFA000',
                    'list_bg_color' => '#ffd79b',
                    'list_font_color' => '#000000',
                    'list_border_color' => '#b7b7b7',
                    'button_hover_bg_color' => '#ffe9c6',
                    'button_hover_font_color' => '#000000',
                    'list_hover_bg_color' => '#FFA000',
                    'list_hover_font_color' => '#000000'
                )
            ),
            array(
                'name' => __('Turquoise', 'mts-google-translate'),
                'colors' => array(
                    'button_bg_color' => '#ffffff',
                    'button_font_color' => '#000000',
                    'button_border_color' => '#1abc9c',
                    'list_bg_color' => '#8fbcb6',
                    'list_font_color' => '#000000',
                    'list_border_color' => '#8fbcb6',
                    'button_hover_bg_color' => '#85bcb2',
                    'button_hover_font_color' => '#000000',
                    'list_hover_bg_color' => '#4dbcad',
                    'list_hover_font_color' => '#000000'
                )
            ),
            array(
                'name' => __('Peter River', 'mts-google-translate'),
                'colors' => array(
                    'button_bg_color' => '#FFFFFF',
                    'button_font_color' => '#000000',
                    'button_border_color' => '#3498db',
                    'list_bg_color' => '#83b5db',
                    'list_font_color' => '#000000',
                    'list_border_color' => '#7775a3',
                    'button_hover_bg_color' => '#83b5db',
                    'button_hover_font_color' => '#000000',
                    'list_hover_bg_color' => '#adc7db',
                    'list_hover_font_color' => '#000000'
                ),
            ),
            array(
                'name' => __('Amethyst', 'mts-google-translate'),
                'colors' => array(
                    'button_bg_color' => '#9b59b6',
                    'button_font_color' => '#FFFFFF',
                    'button_border_color' => '#FFA000',
                    'list_bg_color' => '#FFA000',
                    'list_font_color' => '#000000',
                    'list_border_color' => '#606060',
                    'button_hover_bg_color' => '#9b59b6',
                    'button_hover_font_color' => '#ffffff',
                    'list_hover_bg_color' => '#FFA000',
                    'list_hover_font_color' => '#000000'
                )
            )
        );
        return apply_filters('wp_notification_color_sets', $default_palettes);
    }

    public function custom_meta_field($args, $value) {

        $type = isset($args['type']) ? $args['type'] : '';
        $name = isset($args['name']) ? $args['name'] : '';
        $label = isset($args['label']) ? $args['label'] : '';
        $options = isset($args['options']) ? $args['options'] : array();
        $default = isset($args['default']) ? $args['default'] : '';

        $class = isset($args['class']) ? $args['class'] : '';

        // For show/hide options based on select value
        $data_parent_select = isset($args['parent_select']) ? ' data-parent-select-id="mtsnb_fields_' . $args['parent_select'] . '"' : '';
        $data_parent_value = '';
        if (isset($args['parent_value'])) {
            $parent_values = '';
            if (is_array($args['parent_value'])) {
                $parent_values = '';
                foreach ($args['parent_value'] as $val) {

                    $parent_values .= $val . ',';
                }
            } else {
                $parent_values = $args['parent_value'];
            }

            $data_parent_value = ' data-parent-select-value="' . rtrim($parent_values, ',') . '"';
        }
        $parent_data = $data_parent_select . $data_parent_value;

        // Option value
        $opt_val = isset($value[$name]) ? $value[$name] : $default;
        ?>
        <div id="mtsnb_fields_<?php echo $name; ?>_row" class="form-row"<?php echo $parent_data; ?>>
            <label class="form-label" for="mtsnb_fields_<?php echo $name; ?>"><?php echo $label; ?></label>
            <div class="form-option <?php echo $class; ?>">
                <?php switch ($type) {
                    case 'text': ?>
                        <input type="text" name="mtsnb_fields[<?php echo $name; ?>]" id="mtsnb_fields_<?php echo $name; ?>" value="<?php echo esc_attr($opt_val); ?>" />
                    <?php break;
                    case 'select': ?>
                        <select name="mtsnb_fields[<?php echo $name; ?>]" id="mtsnb_fields_<?php echo $name; ?>">
                            <?php foreach ($options as $val => $label) { ?>
                                    <option value="<?php echo $val; ?>" <?php selected($opt_val, $val, true); ?>><?php echo $label ?></option>
                            <?php } ?>
                        </select>
                        <?php
                    break;
                    case 'image': ?>
                        <div  class="clearfix" id="mtsnb_fields_<?php echo $name . '_preview'; ?>">
                            <?php if (isset($opt_val['url']) && $opt_val['url'] != '') {
                                echo '<img class="custom_media_image" src="' . $opt_val['url'] . '" style="margin:0 0 10px;padding:0;max-width:100%;height:auto;float:left;display:inline-block" />';
                            } ?>
                        </div>
                        <input type="hidden" id="mtsnb_fields_<?php echo $name . '_id'; ?>" name="mtsnb_fields[<?php echo $name; ?>][id]" value="<?php if (isset($opt_val['id'])) echo $opt_val['id']; ?>" />
                        <input type="hidden" id="mtsnb_fields_<?php echo $name . '_url'; ?>" name="mtsnb_fields[<?php echo $name; ?>][url]" value="<?php if (isset($opt_val['url'])) echo $opt_val['url']; ?>" />
                        <button class="button" name="mtsnb_fields_<?php echo $name . '_upload'; ?>" id="mtsnb_fields_<?php echo $name . '_upload'; ?>" data-id="<?php echo 'mtsnb_fields_' . $name; ?>" onclick="mtsImageField.uploader('<?php echo 'mtsnb_fields_' . $name; ?>'); return false;"><?php _e('Select Image', $this->plugin_name); ?></button>
                        <?php if (isset($opt_val['url']) && $opt_val['url'] != '') {
                            echo '<a href="#" class="clear-image">' . __('Remove Image', $this->plugin_name) . '</a>';
                        }
                    break;
                    case 'number': ?>
                        <input type="number" step="1" min="0" name="mtsnb_fields[<?php echo $name; ?>]" id="mtsnb_fields_<?php echo $name; ?>" value="<?php echo $opt_val; ?>" class="small-text"/>
                    <?php break;
                    case 'color': ?>
                        <input type="text" name="mtsnb_fields[<?php echo $name; ?>]" id="mtsnb_fields_<?php echo $name; ?>" value="<?php echo $opt_val; ?>" class="mtsnb-color-picker" />
                    <?php break;
                    case 'textarea': ?>
                        <textarea name="mtsnb_fields[<?php echo $name; ?>]" id="mtsnb_fields_<?php echo $name; ?>" class="mtsnb-textarea"><?php echo esc_textarea($opt_val); ?></textarea>
                    <?php break;
                    case 'checkbox': ?>
                        <input type="checkbox" name="mtsnb_fields[<?php echo $name; ?>]" id="mtsnb_fields_<?php echo $name; ?>" value="1" <?php checked($opt_val, '1', true); ?> />
                    <?php break;
                    case 'select_adv': ?>
                        <select multiple class="mtsnb-multi-select" name="mtsnb_fields[<?php echo $name; ?>][]" id="mtsnb_fields_<?php echo $name; ?>">
                            <?php if (!empty($options)) {
                                foreach ($options as $id => $name) {
                                    $selected = in_array($id, $opt_val) ? ' selected="selected"' : ''; ?>
                                        <option value="<?php echo esc_attr($id); ?>"<?php echo $selected; ?>><?php echo esc_html($name); ?></option>
                                    <?php
                                }
                            } ?>
                        </select>
                    <?php break;
                    case 'select_icon': ?>
                        <select class="mtsnb-icon-select" name="mtsnb_fields[<?php echo $name; ?>]" id="mtsnb_fields_<?php echo $name; ?>">
                            <option value=""<?php selected($opt_val, '', true); ?>><?php _e('No Icon', $this->plugin_name); ?></option>
                            <?php if (!empty($options)) {
                                foreach ($options as $icon_category => $icons) {
                                    echo '<optgroup label="' . $icon_category . '">';
                                    foreach ($icons as $icon) {
                                        echo '<option value="' . $icon . '"' . selected($opt_val, $icon, false) . '>' . ucwords(str_replace('-', ' ', $icon)) . '</option>';
                                    }
                                    echo '</optgroup>';
                                }
                            } ?>
                        </select>
                    <?php break;
                    case 'ajax_list': ?>
                        <select class="mtsnb-ajax-select" name="mtsnb_fields[<?php echo $name; ?>]" id="mtsnb_fields_<?php echo $name; ?>" data-list="<?php echo $opt_val; ?>"></select>
                    <?php break;
                    case 'ajax_client': ?>
                        <select class="mtsnb-ajax-select" name="mtsnb_fields[<?php echo $name; ?>]" id="mtsnb_fields_<?php echo $name; ?>" data-client="<?php echo $opt_val; ?>"></select>
                    <?php break;
                    case 'aweber_actions': ?>
                        <a href="https://auth.aweber.com/1.0/oauth/authorize_app/e59c401b" target="_blank" class="button mtsnb-aweber-connect"><?php isset($value['aweber']['access_key']) && $value['aweber']['access_key'] == '' ? _e('Get Authorization Code', $this->plugin_name) : _e('Reconnect Account', $this->plugin_name); ?></a>
                        <input type="hidden" id="mtsnb_fields_aweber_consumer_key" name="mtsnb_fields[aweber][consumer_key]" value="<?php echo (isset($value['aweber']['consumer_key']) ? $value['aweber']['consumer_key'] : ''); ?>" />
                        <input type="hidden" id="mtsnb_fields_aweber_consumer_secret" name="mtsnb_fields[aweber][consumer_secret]" value="<?php echo (isset($value['aweber']['consumer_secret']) ? $value['aweber']['consumer_secret'] : ''); ?>" />
                        <input type="hidden" id="mtsnb_fields_aweber_access_key" name="mtsnb_fields[aweber][access_key]" value="<?php echo (isset($value['aweber']['access_key']) ? $value['aweber']['access_key'] : ''); ?>" />
                        <input type="hidden" id="mtsnb_fields_aweber_access_secret" name="mtsnb_fields[aweber][access_secret]" value="<?php echo (isset($value['aweber']['access_secret']) ? $value['aweber']['access_secret'] : ''); ?>" />
                    <?php break;
                    case 'date': ?>
                        <input class="mtsnb-datepicker" type="text" name="mtsnb_fields[<?php echo $name; ?>]" id="mtsnb_fields_<?php echo $name; ?>" value="<?php echo $opt_val; ?>" size="30" />
                    <?php break;
                    case 'time': ?>
                        <input class="mtsnb-timepicker" type="text" name="mtsnb_fields[<?php echo $name; ?>]" id="mtsnb_fields_<?php echo $name; ?>" value="<?php echo $opt_val; ?>" size="30" />
                    <?php break;
                } ?>
            </div>
        </div>
    <?php }

    /**
     * Used to render the setting page in admin area
     *
     * @since     1.0.0
     * @access   public
     */
    public function mts_google_translate_setting_page() {
        $options = get_option('mts_google_translate_options');

        if (empty($options)) {
            update_option('mts_google_translate_options', $options = $this->default_options);
        }

        $default_language = !empty($options['default_language']) ? $options['default_language'] : 'auto';
        $tracking_enabled = (isset($options['tracking_enabled']) && $options['tracking_enabled'] != '') ? 'checked=checked' : '';
        $tracking_id = !empty($options['tracking_id']) ? $options['tracking_id'] : '';
        $exclude_mobile_browers = isset($options['exclude_mobile_browers']) ? 'checked=checked' : '';
        $button_display = isset($options['button_display']) ? 'checked=checked' : '';
        $button_bg_color = !empty($options['button_bg_color']) ? $options['button_bg_color'] : '';
        $button_hover_bg_color = !empty($options['button_hover_bg_color']) ? $options['button_hover_bg_color'] : '';
        $button_font_color = !empty($options['button_font_color']) ? $options['button_font_color'] : '';
        $button_hover_font_color = !empty($options['button_hover_font_color']) ? $options['button_hover_font_color'] : '';
        $button_border_color = !empty($options['button_border_color']) ? $options['button_border_color'] : '';
        $list_bg_color = !empty($options['list_bg_color']) ? $options['list_bg_color'] : '';
        $list_hover_bg_color = !empty($options['list_hover_bg_color']) ? $options['list_hover_bg_color'] : '';
        $list_font_color = !empty($options['list_font_color']) ? $options['list_font_color'] : '';
        $list_hover_font_color = !empty($options['list_hover_font_color']) ? $options['list_hover_font_color'] : '';
        $list_border_color = !empty($options['list_border_color']) ? $options['list_border_color'] : '';
        $menu_item = (isset($options['menu_item']) && $options['menu_item'] != '') ? 'checked=checked' : '';
        $menu_button_text = !empty($options['menu_button_text']) ? $options['menu_button_text'] : '';
        $toolbar_position = !empty($options['toolbar_position']) ? $options['toolbar_position'] : 'TOP_RIGHT';
        $last_tab = !empty($options['last_tab']) ? $options['last_tab'] : '0';

        ?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32">
                <br />
            </div>
            <h2><?php echo __('WP Google Translate Settings', 'mts-google-translate') ?></h2>
        <?php if (isset($_GET['settings-updated'])) echo '<div class="updated" ><p>WP Google Translate Settings updated.</p></div>'; ?>
            <div id="google_translate_tabs">
                <ul>
                    <li><a href="#general"><?php _e('General Options', ''); ?></a></li>
                    <li><a href="#color"><?php _e('Styling Options', ''); ?></a></li>
                </ul>
                <form method="post" action="options.php">
                    <?php settings_fields('mts-google-translate-settings-group'); ?>
                    <div class="postbox-container" style="width: 100%;">
                        <div class="metabox-holder">
                            <div class="meta-box-sortables ui-sortable">
                                <div class="postbox mts_postbox">

                                    <div class="inside">
                                        <div id="general">
                                            <h3 class="hndle" style="cursor: default; padding: 10px 0;"><span> <?php echo __('General Settings', 'mts-google-translate'); ?></span></h3>
                                            <table class="form-table">
                                                <tr>
                                                    <th><?php echo __('Default Language', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <select name="mts_google_translate_options[default_language]" id="default_language" style="width: 250px;">';
                                                            <?php foreach ($this->default_options['languages'] as $key => $value) {
                                                                echo '<option value="' . $key . '"' . ($key == $default_language ? ' selected="selected"' : '') . '>' . $value . '</option>';
                                                            } ?>
                                                        </select>
                                                        <label for="default_language">
                                                            &nbsp;<em><?php echo __('Set the default langauge or let the plugin pick it by it\'s own', 'mts-google-translate') ?></em>
                                                        </label>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <th><?php echo __('Google Analytics Tracking', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <input type="checkbox" class="fieldtoggle" data-rel=".trackingid" name="mts_google_translate_options[tracking_enabled]" <?php echo esc_attr($tracking_enabled) ?> id="tracking_enabled"/>
                                                        <label for="tracking_enabled">
                                                            &nbsp;<em><?php echo __('Track translation statistics with Google Analytics', 'mts-google-translate') ?></em>
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr class="trackingid">
                                                    <th><?php echo __('Tracking ID', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <input type="text" id="tracking_id" name="mts_google_translate_options[tracking_id]" value="<?php echo esc_attr($tracking_id) ?>" placeholder="Example UA-31232161-1" style="width: 250px;">
                                                        <label for="tracking_id">
                                                            &nbsp;<em><?php echo __('Tracking ID for Google Analytics.', 'mts-google-translate') ?></em>
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo __('Exclude Mobile Browsers', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <input type="checkbox" id="exclude_mobile_browers" name="mts_google_translate_options[exclude_mobile_browers]" <?php echo esc_attr($exclude_mobile_browers) ?>/>
                                                        <label for="exclude_mobile_browers">
                                                            &nbsp;<em><?php echo __('Don\'t show the language changing options on mobile browsers', 'mts-google-translate') ?></em>
                                                        </label>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <th><?php echo __('Show Button', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <input type="checkbox" id="button_display" class="fieldtoggle" data-rel=".buttonposition" name="mts_google_translate_options[button_display]" <?php echo esc_attr($button_display) ?>/>
                                                        <label for="button_display">
                                                            &nbsp;<em><?php echo __('Show the translate button on your site. You can also use the widget or the menu item instead.', 'mts-google-translate') ?></em>
                                                        </label>
                                                    </td>
                                                </tr>

                                                <tr class="buttonposition">
                                                    <th><?php echo __('Button Position', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <select name="mts_google_translate_options[toolbar_position]" id="toolbar_position" style="width: 250px;">
                                                            <?php foreach ($this->default_options['toolbar_position_options'] as $key => $position) {
                                                                echo '<option value="' . $key . '"' . ($key == $toolbar_position ? ' selected="selected"' : '') . '>' . $position . '</option>';
                                                            } ?>
                                                        </select>
                                                        <label for="toolbar_position">
                                                            &nbsp;<em><?php echo __('Set the default position to show the language switch button', 'mts-google-translate') ?></em>
                                                        </label>
                                                    </td>
                                                </tr>



                                                <tr>
                                                    <th><?php echo __('Menu Item', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <input type="checkbox" id="menu_item" class="fieldtoggle" data-rel=".menutext" name="mts_google_translate_options[menu_item]" <?php echo esc_attr($menu_item) ?>/>
                                                        <label for="menu_item">
                                                            &nbsp;<em><?php echo __('Add translate button as list menu on top ', 'mts-google-translate') ?></em>
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr class="menutext">
                                                    <th><?php echo __('Menu Button Text', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <input type="text" id="menu_button_text" name="mts_google_translate_options[menu_button_text]" value="<?php echo esc_attr($menu_button_text) ?>" style="width: 250px;">
                                                        <label for="menu_button_text">
                                                            &nbsp;<br /><em><?php echo __('Menu button text. Default is <strong>Select Language</strong>.', 'mts-google-translate') ?></em>
                                                        </label>
                                                    </td>
                                                </tr>

                                            </table>
                                        </div>
                                        <div id="color">
                                            <h3 class="hndle" style="cursor: default; padding: 10px 0;"><span> <?php echo __('Styling Options', 'mts-google-translate'); ?></span></h3>
                                            <table id="color-options" style="float: left; width: 67%; text-align: left;">
                                                <tr>
                                                    <th><?php echo __('Predefined Color Schemes', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <div id="tab-style" class="mtsnb-tabs-content">
                                                            <div class="mtsnb-tab-options clearfix">
                                                                <div class="form-row">
                                                                    <?php $palettes = $this->get_default_color_palettes();
                                                                    if (!empty($palettes)) { ?>
                                                                        <div class="mtsnb-colors-loader">
                                                                            <select class="mtsnb-colors-select" name="mts_google_translate_options[selected_color_pallate]">
                                                                                <option></option>
                                                                                <?php foreach ($palettes as $i => $palette) { ?>
                                                                                    <option value="<?php echo $i; ?>" data-target="<?php //echo esc_attr($target); ?>" data-colors="<?php echo esc_attr(json_encode($palette['colors'])); ?>"><?php echo $palette['name']; ?></option>
                                                                                <?php } ?>
                                                                            </select>
                                                                        </div>
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <th><?php echo __('Button Background Color', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <input type="text" class="color-picker" id="button_bg_color" name="mts_google_translate_options[button_bg_color]" value="<?php echo esc_attr($button_bg_color) ?>" />
                                                        <label for="button_bg_color">
                                                            &nbsp;<em><?php echo __('Background color for the translate button', 'mts-google-translate') ?></em>
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo __('Button Hover Background Color', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <input type="text" class="color-picker" id="button_hover_bg_color" name="mts_google_translate_options[button_hover_bg_color]" value="<?php echo esc_attr($button_hover_bg_color) ?>" />
                                                        <label for="button_hover_bg_color">
                                                            &nbsp;<em><?php echo __('Background color for the translate button', 'mts-google-translate') ?></em>
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo __('Button Font Color', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <input type="text" id="button_font_color" class="color-picker" name="mts_google_translate_options[button_font_color]" value="<?php echo esc_attr($button_font_color) ?>" />
                                                        <label for="button_font_color">
                                                            &nbsp;<em><?php echo __('Font color for the translate button', 'mts-google-translate') ?></em>
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo __('Button Hover Font Color', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <input type="text" id="button_hover_font_color" class="color-picker" name="mts_google_translate_options[button_hover_font_color]" value="<?php echo esc_attr($button_hover_font_color) ?>" />
                                                        <label for="button_hover_font_color">
                                                            &nbsp;<em><?php echo __('Font color for the translate button', 'mts-google-translate') ?></em>
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo __('Button Border Color', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <input type="text" id="button_border_color" class="color-picker" name="mts_google_translate_options[button_border_color]" value="<?php echo esc_attr($button_border_color) ?>" />
                                                        <label for="button_border_color">
                                                            &nbsp;<em><?php echo __('Border color for the translate button', 'mts-google-translate') ?></em>
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo __('Language List Background Color', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <input type="text" id="list_bg_color" class="color-picker" name="mts_google_translate_options[list_bg_color]" value="<?php echo esc_attr($list_bg_color) ?>" />
                                                        <label for="list_bg_color">
                                                            &nbsp;<em><?php echo __('Background color for the Language List', 'mts-google-translate') ?></em>
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo __('List Hover Background Color', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <input type="text" id="list_hover_bg_color" class="color-picker" name="mts_google_translate_options[list_hover_bg_color]" value="<?php echo esc_attr($list_hover_bg_color) ?>" />
                                                        <label for="list_hover_bg_color">
                                                            &nbsp;<em><?php echo __('Background color for the Language List', 'mts-google-translate') ?></em>
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo __('Language List Font Color', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <input type="text" id="list_font_color" class="color-picker" name="mts_google_translate_options[list_font_color]" value="<?php echo esc_attr($list_font_color) ?>" />
                                                        <label for="list_font_color">
                                                            &nbsp;<em><?php echo __('Font color for the Language List', 'mts-google-translate') ?></em>
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo __('List Hover Font Color', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <input type="text" id="list_hover_font_color" class="color-picker" name="mts_google_translate_options[list_hover_font_color]" value="<?php echo esc_attr($list_hover_font_color) ?>" />
                                                        <label for="list_hover_font_color">
                                                            &nbsp;<em><?php echo __('Font color for the Language List', 'mts-google-translate') ?></em>
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo __('Language List Border Color', 'mts-google-translate') ?>:</th>
                                                    <td>
                                                        <input type="text" id="list_border_color" class="color-picker" name="mts_google_translate_options[list_border_color]" value="<?php echo esc_attr($list_border_color) ?>" />
                                                        <label for="list_border_color">
                                                            &nbsp;<em><?php echo __('Border color for the Language List', 'mts-google-translate') ?></em>
                                                        </label>
                                                    </td>
                                                </tr>
                                            </table>
                                            <style>
                                                #translate_preview_button{
                                                    padding: 5px;
                                                    text-align: center;
                                                    background-color : <?php echo $button_bg_color ?>;
                                                    max-width: 162px;
                                                    color: <?php echo $button_font_color; ?>;
                                                    border:1px solid;
                                                    border-color: <?php echo $button_border_color; ?>;
                                                }
                                                #translate_preview_button:hover{
                                                    background-color: <?php echo $button_hover_bg_color; ?>;
                                                    color: <?php echo $button_hover_font_color; ?>;
                                                }
                                                #translate_preview_list li{
                                                    margin-bottom: 0px;
                                                }
                                                #translate_preview_list li a{
                                                    background: <?php echo $list_bg_color; ?>;
                                                    display: block;
                                                    text-decoration: none;
                                                    padding: 5px 0px;
                                                    padding-left: 15px;
                                                    border-bottom: solid 1px;
                                                    border-color: <?php echo $list_border_color; ?>;
                                                    color: <?php echo $list_font_color; ?>;
                                                }
                                                #translate_preview_list li a:hover{
                                                    color: <?php echo $list_hover_font_color; ?>;
                                                    background-color: <?php echo $list_hover_bg_color; ?>;
                                                }
                                                #translate_preview_list ol li a {
                                                    display: block;
                                                    text-decoration: none;
                                                    padding: 5px 0px;
                                                    padding-left: 15px;
                                                }
                                                #translate_preview_list span{
                                                    display: block;
                                                    width: 23px;
                                                    float: left;
                                                    margin-right: 5px;
                                                    height: 15px;
                                                    margin-top: 2px;
                                                }
                                                #translate_preview_list span.tl { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/philippines.png'; ?>) no-repeat; }
                                                #translate_preview_list span.fi { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/finland.png'; ?>) no-repeat; }
                                                #translate_preview_list span.fr { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/france.png'; ?>) no-repeat; }
                                                #translate_preview_list span.gl { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/galacia.png'; ?>) no-repeat; }
                                                #translate_preview_list span.ka { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/georgian.png'; ?>) no-repeat; }
                                                #translate_preview_list span.de { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/germany.png'; ?>) no-repeat; }
                                            </style>
                                            <style id="button_bg_hover"></style>
                                            <style id="button_font_hover"></style>
                                            <style id="list_bg_hover"></style>
                                            <style id="list_font_hover"></style>
                                            <table style="width: 30%; padding-left: 10%;">
                                                <tr>
                                                    <td>
                                                        <div id="translate_preview_tab" class="">
                                                            <h3>Style Preview</h3>
                                                            <div id="translate_preview_button_container" style="">
                                                                <p id="translate_preview_button">Select Language</p>
                                                            </div>
                                                            <div id="someId">
                                                                <ul id="translate_preview_list" style="width: 175px; margin: 0;padding: 0;">
                                                                    <li><a href="#" data-lang="tl"><span class="tl"></span>Filipino</a></li>
                                                                    <li><a href="#" data-lang="fi"><span class="fi"></span>Finnish</a></li>
                                                                    <li><a href="#" data-lang="fr"><span class="fr"></span>French</a></li>
                                                                    <li><a href="#" data-lang="gl"><span class="gl"></span>Galician</a></li>
                                                                    <li><a href="#" data-lang="ka"><span class="ka"></span>Georgian</a></li>
                                                                    <li><a href="#" data-lang="de"><span class="de"></span>German</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <table style="clear: both;">
                                            <tr>
                                                <th style="display:none;"></th>
                                                <td style="vertical-align: middle;">
                                                    <div class="alignright">
                                                        <input type="submit" class="mts_google_translate_button button-primary" name="submit" value=" <?php echo __('Save Settings', 'mts-google-translate') ?>">
                                                        <input type="hidden" name="mts-google-translate-submit" value="Y" />
                                                        <input type="hidden" name="mts_google_translate_options[last_tab]" id="last_tab_field" value="<?php echo esc_attr($last_tab) ?>" />
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Used to render the leftvertical language table
     *
     * @since     1.0.0
     * @access   public
     */
    public function mts_get_left_languagebar() {
        $wpTranslateOptions = get_option('mts_google_translate_options');

        if (empty($wpTranslateOptions)) {
            update_option('mts_google_translate_options', $wpTranslateOptions = $this->default_options);
        }

        if ( isset($wpTranslateOptions["exclude_mobile_browers"]) && wp_is_mobile() ) {
            return; // mobile excluded
        }

        $button_bg_color = 'background: ' . $wpTranslateOptions['button_bg_color'] . ';';
        $button_hover_bg_color = 'background: ' . $wpTranslateOptions['button_hover_bg_color'] . ';';
        $button_font_color = 'color: ' . $wpTranslateOptions['button_font_color'] . ';';
        $button_hover_font_color = 'color: ' . $wpTranslateOptions['button_hover_font_color'] . ';';
        $button_border_color = 'border: 1px solid ' . $wpTranslateOptions['button_border_color'] . ';';
        $list_bg_color = 'background: ' . $wpTranslateOptions['list_bg_color'] . ';';
        $list_hover_bg_color = 'background: ' . $wpTranslateOptions['list_hover_bg_color'] . ';';
        $list_font_color = 'color: ' . $wpTranslateOptions['list_font_color'] . ';';
        $list_hover_font_color = 'color: ' . $wpTranslateOptions['list_hover_font_color'] . ';';
        $list_border_color = 'border-bottom: solid 1px ' . $wpTranslateOptions['list_border_color'] . ';';

        switch ($wpTranslateOptions['toolbar_position']) {
            case 'TOP_LEFT':
                $mts_languages = 'left: 0; right: auto; top: 0';
                $mts_language_btn = 'right: inherit; left: 0; top: 0;';
                $viewport = 'height: 100%; overflow-y: scroll;';
                break;
            case 'TOP_RIGHT':
                $mts_languages = 'right: 0; top: 0;';
                $mts_language_btn = 'right: 0; left: inherit; top: 0;';
                $viewport = 'height: 100%; overflow-y: scroll;';
                break;
            case 'BOTTOM_LEFT':
                $mts_languages = 'left: 0; right: auto; bottom: 33px';
                $mts_language_btn = 'right: inherit; left: 0; bottom:0; top: auto;';
                $viewport = 'height: 100%; overflow-y: scroll; padding-top: 19%;';
                break;
            case 'BOTTOM_RIGHT':
                $mts_languages = 'right: 0; left: auto; bottom: 33px';
                $mts_language_btn = 'right: 0; left: inherit; top: auto; bottom: 0;';
                $viewport = 'height: 100%; overflow-y: scroll; padding-top: 19%;';
                break;
            default:
                break;
        }
        if (isset($wpTranslateOptions['button_display'])) {
            ?>
            <style>
                #mts-language-btn{
            <?php echo $button_font_color . $button_bg_color . $button_border_color; ?>
                }
                #mts-language-btn:hover{
            <?php echo $button_hover_bg_color . $button_hover_font_color; ?>
                }
                .mts-languages ol li a {
            <?php echo $list_bg_color . $list_font_color . $list_border_color; ?>
                }
                .mts-languages ol li a:hover {
            <?php echo $list_hover_bg_color . $list_hover_font_color; ?>

                }
						</style>
								 <div id="mts-language-btn" style="<?php echo $mts_language_btn; ?>">Select Language</div>

				<?php } ?>
        				<style>
                .mts-languages span.af { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/south-africa.png'; ?>) no-repeat; }
                .mts-languages span.sq { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/albania.png'; ?>) no-repeat; }
                .mts-languages span.ar { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/saudi-arabia.png'; ?>) no-repeat; }
                .mts-languages span.hy { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/armenia.png'; ?>) no-repeat; }
                .mts-languages span.az { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/azerbaijan.png'; ?>) no-repeat; }
                .mts-languages span.eu { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/argentina.png'; ?>) no-repeat; }
                .mts-languages span.be { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/belarus.png'; ?>) no-repeat; }
                .mts-languages span.bn { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/bengal.png'; ?>) no-repeat; }
                .mts-languages span.bs { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/bosnia.png'; ?>) no-repeat; }
                .mts-languages span.bg { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/bulgaria.png'; ?>) no-repeat; }
                .mts-languages span.ca { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/catalonia.png'; ?>) no-repeat; }
                .mts-languages span.ceb { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/philippines.png'; ?>) no-repeat; }
                .mts-languages span.ny { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/zimbabwe.png'; ?>) no-repeat; }
                .mts-languages span.zh-CN { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/china.png'; ?>) no-repeat; }
                .mts-languages span.zh-TW { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/taiwan.png'; ?>) no-repeat; }
                .mts-languages span.hr { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/croatia.png'; ?>) no-repeat; }
                .mts-languages span.cs { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/czech-republic.png'; ?>) no-repeat; }
                .mts-languages span.da { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/denmark.png'; ?>) no-repeat; }
                .mts-languages span.nl { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/netherlands.png'; ?>) no-repeat; }
                .mts-languages span.en { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/united-states.png'; ?>) no-repeat; }
                .mts-languages span.eo { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/esperanto.png'; ?>) no-repeat; }
                .mts-languages span.es { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/estonia.png'; ?>) no-repeat; }
                .mts-languages span.tl { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/philippines.png'; ?>) no-repeat; }
                .mts-languages span.fi { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/finland.png'; ?>) no-repeat; }
                .mts-languages span.fr { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/france.png'; ?>) no-repeat; }
                .mts-languages span.gl { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/galacia.png'; ?>) no-repeat; }
                .mts-languages span.ka { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/georgian.png'; ?>) no-repeat; }
                .mts-languages span.de { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/germany.png'; ?>) no-repeat; }
                .mts-languages span.el { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/greece.png'; ?>) no-repeat; }
                .mts-languages span.gu { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/india.png'; ?>) no-repeat; }
                .mts-languages span.ht { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/haiti.png'; ?>) no-repeat; }
                .mts-languages span.ha { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/niger.png'; ?>) no-repeat; }
                .mts-languages span.iw { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/israel.png'; ?>) no-repeat; }
                .mts-languages span.hi { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/india.png'; ?>) no-repeat; }
                .mts-languages span.hmn { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/china.png'; ?>) no-repeat; }
                .mts-languages span.hu { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/hungary.png'; ?>) no-repeat; }
                .mts-languages span.is { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/iceland.png'; ?>) no-repeat; }
                .mts-languages span.ig { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/nigeria.png'; ?>) no-repeat; }
                .mts-languages span.id { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/indonesia.png'; ?>) no-repeat; }
                .mts-languages span.ga { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/ireland.png'; ?>) no-repeat; }
                .mts-languages span.it { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/italy.png'; ?>) no-repeat; }
                .mts-languages span.ja { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/japan.png'; ?>) no-repeat; }
                .mts-languages span.jw { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/java.png'; ?>) no-repeat; }
                .mts-languages span.kn { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/india.png'; ?>) no-repeat; }
                .mts-languages span.kk { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/kazakhstan.png'; ?>) no-repeat; }
                .mts-languages span.km { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/cambodia.png'; ?>) no-repeat; }
                .mts-languages span.ko { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/south-korea.png'; ?>) no-repeat; }
                .mts-languages span.lo { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/laos.png'; ?>) no-repeat; }
                .mts-languages span.la { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/vatican-city.png'; ?>) no-repeat; }
                .mts-languages span.lv { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/latvia.png'; ?>) no-repeat; }
                .mts-languages span.lt { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/lithuania.png'; ?>) no-repeat; }
                .mts-languages span.mk { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/macedonia.png'; ?>) no-repeat; }
                .mts-languages span.mg { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/madagascar.png'; ?>) no-repeat; }
                .mts-languages span.ms { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/malaysia.png'; ?>) no-repeat; }
                .mts-languages span.ml { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/india.png'; ?>) no-repeat; }
                .mts-languages span.mt { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/malta.png'; ?>) no-repeat; }
                .mts-languages span.mi { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/new-zealand.png'; ?>) no-repeat; }
                .mts-languages span.mn { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/india.png'; ?>) no-repeat; }
                .mts-languages span.no { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/norway.png'; ?>) no-repeat; }
                .mts-languages span.fa { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/iran.png'; ?>) no-repeat; }
                .mts-languages span.pl { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/poland.png'; ?>) no-repeat; }
                .mts-languages span.pt { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/portugal.png'; ?>) no-repeat; }
                .mts-languages span.pa { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/pakistan.png'; ?>) no-repeat; }
                .mts-languages span.ro { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/romania.png'; ?>) no-repeat; }
                .mts-languages span.ru { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/russia.png'; ?>) no-repeat; }
                .mts-languages span.sr { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/serbia.png'; ?>) no-repeat; }
                .mts-languages span.st { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/lesotho.png'; ?>) no-repeat; }
                .mts-languages span.si { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/sri-lanka.png'; ?>) no-repeat; }
                .mts-languages span.sk { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/slovakia.png'; ?>) no-repeat; }
                .mts-languages span.sl { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/slovenia.png'; ?>) no-repeat; }
                .mts-languages span.so { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/somalia.png'; ?>) no-repeat; }
                .mts-languages span.es { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/spain.png'; ?>) no-repeat; }
                .mts-languages span.su { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/sudan.png'; ?>) no-repeat; }
                .mts-languages span.sw { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/tanzania.png'; ?>) no-repeat; }
                .mts-languages span.sv { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/sweden.png'; ?>) no-repeat; }
                .mts-languages span.tg { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/tajikistan.png'; ?>) no-repeat; }
                .mts-languages span.ta { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/sri-lanka.png'; ?>) no-repeat; }
                .mts-languages span.te { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/india.png'; ?>) no-repeat; }
                .mts-languages span.th { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/thailand.png'; ?>) no-repeat; }
                .mts-languages span.tr { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/turkey.png'; ?>) no-repeat; }
                .mts-languages span.uk { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/ukraine.png'; ?>) no-repeat; }
                .mts-languages span.ur { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/pakistan.png'; ?>) no-repeat; }
                .mts-languages span.vi { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/vietnam.png'; ?>) no-repeat; }
                .mts-languages span.cy { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/wales.png'; ?>) no-repeat; }
                .mts-languages span.yi { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/bosnia.png'; ?>) no-repeat; }
                .mts-languages span.yo { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/nigeria.png'; ?>) no-repeat; }
                .mts-languages span.zu { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/south-africa.png'; ?>) no-repeat; }
                .mts-languages span.et { background:url(<?php echo plugin_dir_url(__FILE__) . '../public/images/estonia.png'; ?>) no-repeat; }
            </style>

        <div class="mts-languages" id="mts_languages" style="<?php echo $mts_languages; ?>" tabindex="5000" style="overflow: hidden; outline: none;">
            <div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>
            <div class="viewport" style="<?php echo $viewport; ?>">
                <div class="overview">
                    <ol class="translation-links">
        <?php
        $language_array = $this->default_options['languages'];
        $shift = array_shift($language_array);
        foreach ($language_array as $key => $value) {
            ?>
                            <li><a href="#" data-lang="<?php echo $key ?>"><span class="<?php echo $key; ?>"></span><?php echo $value ?></a></li>
            <?php
        }
        ?>

                    </ol>
                </div>
            </div>
        </div>
                        <?php
                    }

                    /**
                     * Used to initiate the mail translate function by including the google translate element javascript.
                     *
                     * @since     1.0.0
                     * @access   public
                     */
                    public function mts_google_translate_Init() {
                        $wpTranslateOptions = get_option('mts_google_translate_options');

                        if ( isset($wpTranslateOptions["exclude_mobile_browers"]) && wp_is_mobile() ) {
                            return; // mobile excluded
                        }

                            ?>

            <?php global $wpgt_is_active_widget; if (!$wpgt_is_active_widget) { ?>
            <div id="mts_google_translate"></div>
            <?php } else { echo '<div id="tr1"></div>'; } ?>

            <script type='text/javascript'>
                function googleTranslateElementInit() {
                    new google.translate.TranslateElement({
                    pageLanguage: '<?php echo ($wpTranslateOptions["default_language"] == 'auto') ? 'en' : $wpTranslateOptions["default_language"]; ?>',
            <?php if (isset($wpTranslateOptions["tracking_enabled"])) { ?>
                        gaTrack: true,
                                gaId: '<?php echo $wpTranslateOptions["tracking_id"]; ?>',
            <?php } ?>
                    autoDisplay: true
                    }, 'mts_google_translate');
                }
            </script>
            <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>


            <?php

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - WP_Google_Translate_Loader. Orchestrates the hooks of the plugin.
     * - WP_Google_Translate_i18n. Defines internationalization functionality.
     * - WP_Google_Translate_Admin. Defines all hooks for the admin area.
     * - WP_Google_Translate_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-google-translate-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-google-translate-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-google-translate-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wp-google-translate-public.php';

        $this->loader = new WP_Google_Translate_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the WP_Google_Translate_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new WP_Google_Translate_i18n();
        $plugin_i18n->set_domain($this->get_wp_google_translate());

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new WP_Google_Translate_Admin($this->get_wp_google_translate(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        $plugin_public = new WP_Google_Translate_Public($this->get_wp_google_translate(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_wp_google_translate() {
        return $this->mts_google_translator;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    WP_Google_Translate_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

}

/**
 * The file that defines the core plugin class
 *
 * A class used to register translate widget
 *
 * @since      1.0.0
 *
 * @package    WP_Google_Translate
 * @subpackage WP_Google_Translate/includes
 */
class MTS_Google_Translate_Widget extends WP_Widget {

    //register widget
    function __construct() {
        parent::__construct(
                'mts_google_translate_widget', __('WP Google Translate Widget', 'mts-google-translate'), array('description' => __('Creates a simple drop down list of languages to translate content and hides tool bar from header', 'mts-google-translate'),)
        );
    }

    //front-end
    public function widget($args, $instance) {
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        echo '<div id="mts_google_translate"></div>';

        echo $args['after_widget'];
        global $wpgt_is_active_widget;
        $wpgt_is_active_widget = true;
    }

    //back-end
    public function form($instance) {
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('Translate', 'wp-translate');
        }
        ?>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        <?php
    }

    //sanitize form values when updated
    public function update($new_instance, $old_instance) {
        $instance = array();

        $instance['title'] = (!empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';

        return $instance;
    }

}
