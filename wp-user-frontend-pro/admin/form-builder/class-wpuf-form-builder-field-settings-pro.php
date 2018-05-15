<?php
/**
 * Field Settings
 *
 * @since 2.5
 */
class WPUF_Form_Builder_Field_Settings_Pro extends WPUF_Form_Builder_Field_Settings {

    /**
     * Pro field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function get_field_settings() {
        return array(
            'repeat_field'          => self::repeat_field(),
            'date_field'            => self::date_field(),
            'file_upload'           => self::file_upload(),
            'country_list_field'    => self::country_list_field(),
            'numeric_text_field'    => self::numeric_text_field(),
            'address_field'         => self::address_field(),
            'step_start'            => self::step_start(),
            'google_map'            => self::google_map(),
            'shortcode'             => self::shortcode(),
            'really_simple_captcha' => self::really_simple_captcha(),
            'action_hook'           => self::action_hook(),
            'toc'                   => self::toc(),
            'ratings'               => self::ratings(),
        );
    }

    /**
     * wpuf_cond option field settings
     *
     * This is for sidebar panel, not in builder stage
     *
     * @since 2.5
     *
     * @return array
     */
    public static function get_field_wpuf_cond() {
        return array(
            'name'           => 'wpuf_cond',
            'title'          => __( 'Conditional Logic', 'wpuf-pro' ),
            'type'           => 'conditional-logic',
            'section'        => 'advanced',
            'priority'       => 30,
            'help_text'      => '',
        );
    }

    /**
     * Repeatable field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function repeat_field() {
        $settings = self::get_common_properties();

        $settings = array_merge( $settings, array(
            array(
                'name'          => 'multiple',
                'title'         => __( 'Multiple Column', 'wpuf-pro' ),
                'type'          => 'checkbox',
                'is_single_opt' => true,
                'options'       => array(
                    'true'   => __( 'Enable Multi Column', 'wpuf-pro' )
                ),
                'section'       => 'advanced',
                'priority'      => 23,
                'help_text'     => '',
            ),

            array(
                'name'          => 'columns',
                'title'         => __( 'Columns', 'wpuf-pro' ),
                'type'          => 'repeater-columns',
                'section'       => 'advanced',
                'priority'      => 24,
                'help_text'     => '',
                'dependencies' => array(
                    'multiple' => 'true'
                )
            ),

            array(
                'name'         => 'placeholder',
                'title'        => __( 'Placeholder text', 'wpuf-pro' ),
                'type'         => 'text',
                'section'      => 'advanced',
                'priority'     => 24,
                'help_text'    => __( 'Text for HTML5 placeholder attribute', 'wpuf-pro' ),
                'dependencies' => array(
                    'multiple' => ''
                )
            ),

            array(
                'name'         => 'default',
                'title'        => __( 'Default value', 'wpuf-pro' ),
                'type'         => 'text',
                'section'      => 'advanced',
                'priority'     => 25,
                'help_text'    => __( 'The default value this field will have', 'wpuf-pro' ),
                'dependencies' => array(
                    'multiple' => ''
                )
            ),

            array(
                'name'         => 'size',
                'title'        => __( 'Size', 'wpuf-pro' ),
                'type'         => 'text',
                'section'      => 'advanced',
                'priority'     => 26,
                'help_text'    => __( 'Size of this input field', 'wpuf-pro' )
            ),
        ) );

        return array(
            'template'      => 'repeat_field',
            'title'         => __( 'Repeat Field', 'wpuf-pro' ),
            'icon'          => 'clone',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'repeat',
                'template'          => 'repeat_field',
                'required'          => 'no',
                'label'             => __( 'Repeat Field', 'wpuf-pro' ),
                'name'              => '',
                'is_meta'           => 'yes',
                'help'              => '',
                'width'             => 'large',
                'css'               => '',
                'multiple'          => '',
                'placeholder'       => '',
                'default'           => '',
                'size'              => '40',
                'columns'           => array( __( 'Column 1', 'wpuf-pro' ) ),
                'id'                => 0,
                'is_new'            => true,
                'show_in_post'      => 'yes',
                'wpuf_visibility'   => self::get_wpuf_visibility_prop(),
                'wpuf_cond'         => self::get_wpuf_cond_prop(),
            )
        );
    }

    /**
     * Date Field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function date_field() {
        $settings = self::get_common_properties();
        $settings = array_merge( $settings, array(
            array(
                'name'      => 'format',
                'title'     => __( 'Date Format', 'wpuf-pro' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 23,
                'help_text' => __( 'The date format', 'wpuf-pro' ),
            ),

            array(
                'name'          => 'time',
                'title'         => '',
                'type'          => 'checkbox',
                'is_single_opt' => true,
                'options'       => array(
                    'yes'   => __( 'Enable time input', 'wpuf-pro' )
                ),
                'section'       => 'advanced',
                'priority'      => 24,
                'help_text'     => '',
            ),

            array(
                'name'          => 'is_publish_time',
                'title'         => '',
                'type'          => 'checkbox',
                'is_single_opt' => true,
                'options'       => array(
                    'yes'   => __( 'Set this as publish time input', 'wpuf-pro' )
                ),
                'section'       => 'advanced',
                'priority'      => 24,
                'help_text'     => '',
            ),
        ) );

        return array(
            'template'      => 'date_field',
            'title'         => __( 'Date / Time', 'wpuf-pro' ),
            'icon'          => 'calendar-o',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'date',
                'template'          => 'date_field',
                'required'          => 'no',
                'label'             => __( 'Date / Time', 'wpuf-pro' ),
                'name'              => '',
                'is_meta'           => 'yes',
                'help'              => '',
                'width'             => 'large',
                'css'               => '',
                'format'            => 'dd/mm/yy',
                'time'              => '',
                'is_publish_time'   => '',
                'id'                => 0,
                'is_new'            => true,
                'show_in_post'      => 'yes',
                'wpuf_visibility'   => self::get_wpuf_visibility_prop(),
                'wpuf_cond'         => self::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * File upload field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function file_upload() {
        $settings = array(
            array(
                'name'      => 'label',
                'title'     => __( 'Field Label', 'wpuf-pro' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 10,
                'help_text' => __( 'Enter a title of this field', 'wpuf-pro' ),
            ),

            array(
                'name'      => 'name',
                'title'     => __( 'Meta Key', 'wpuf-pro' ),
                'type'      => 'text-meta',
                'section'   => 'basic',
                'priority'  => 11,
                'help_text' => __( 'Name of the meta key this field will save to', 'wpuf-pro' ),
            ),

            array(
                'name'      => 'help',
                'title'     => __( 'Help text', 'wpuf-pro' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 20,
                'help_text' => __( 'Give the user some information about this field', 'wpuf-pro' ),
            ),

            array(
                'name'      => 'required',
                'title'     => __( 'Required', 'wpuf-pro' ),
                'type'      => 'radio',
                'options'   => array(
                    'yes'   => __( 'Yes', 'wpuf-pro' ),
                    'no'    => __( 'No', 'wpuf-pro' ),
                ),
                'section'   => 'basic',
                'priority'  => 21,
                'default'   => 'no',
                'inline'    => true,
                'help_text' => __( 'Check this option to mark the field required. A form will not submit unless all required fields are provided.', 'wpuf-pro' ),
            ),

            array(
                'name'      => 'css',
                'title'     => __( 'CSS Class Name', 'wpuf-pro' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 22,
                'help_text' => __( 'Give the user some information about this field', 'wpuf-pro' ),
            ),

            array(
                'name'          => 'max_size',
                'title'         => __( 'Max. file size', 'wpuf-pro' ),
                'type'          => 'text',
                'variation'     => 'number',
                'section'       => 'advanced',
                'priority'      => 20,
                'help_text'     => __( 'Enter maximum upload size limit in KB', 'wpuf-pro' ),
            ),

            array(
                'name'          => 'count',
                'title'         => __( 'Max. files', 'wpuf-pro' ),
                'type'          => 'text',
                'variation'     => 'number',
                'section'       => 'advanced',
                'priority'      => 21,
                'help_text'     => __( 'Number of images can be uploaded', 'wpuf-pro' ),
            ),

            array(
                'name'          => 'extension',
                'title'         => __( 'Allowed Files', 'wpuf-pro' ),
                'title_class'   => 'label-hr',
                'type'          => 'checkbox',
                'options'       => array(
                    'images'    => __( 'Images (jpg, jpeg, gif, png, bmp)', 'wpuf-pro' ),
                    'audio'     => __( 'Audio (mp3, wav, ogg, wma, mka, m4a, ra, mid, midi)', 'wpuf-pro' ),
                    'video'     => __( 'Videos (avi, divx, flv, mov, ogv, mkv, mp4, m4v, divx, mpg, mpeg, mpe)', 'wpuf-pro' ),
                    'pdf'       => __( 'PDF (pdf)', 'wpuf-pro' ),
                    'office'    => __( 'Office Documents (doc, ppt, pps, xls, mdb, docx, xlsx, pptx, odt, odp, ods, odg, odc, odb, odf, rtf, txt)', 'wpuf-pro' ),
                    'zip'       => __( 'Zip Archives (zip, gz, gzip, rar, 7z)', 'wpuf-pro' ),
                    'exe'       => __( 'Executable Files (exe)', 'wpuf-pro' ),
                    'csv'       => __( 'CSV (csv)', 'wpuf-pro' ),
                ),
                'section'       => 'advanced',
                'priority'      => 22,
                'help_text'     => '',
            ),

            self::get_field_wpuf_cond()
        );

        if ( is_wpuf_post_form_builder() ) {
            
            $settings[] = array(
                'name'      => 'show_in_post',
                'title'     => __( 'Show Data in Post', 'wpuf' ),
                'type'      => 'radio',
                'options'   => array(
                    'yes'   => __( 'Yes', 'wpuf' ),
                    'no'    => __( 'No', 'wpuf' ),
                ),
                'section'   => 'advanced',
                'priority'  => 24,
                'default'   => 'yes',
                'inline'    => true,
                'help_text' => __( 'Select Yes if you want to show the field data in single post.', 'wpuf' ),
            );
            
        }

        return array(
            'template'      => 'file_upload',
            'title'         => __( 'File Upload', 'wpuf-pro' ),
            'icon'          => 'upload',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'file_upload',
                'template'          => 'file_upload',
                'required'          => 'no',
                'label'             => __( 'File Upload', 'wpuf-pro' ),
                'name'              => '',
                'is_meta'           => 'yes',
                'help'              => '',
                'width'             => '',
                'css'               => '',
                'max_size'          => '1024',
                'count'             => '1',
                'extension'         => array( 'images', 'audio', 'video', 'pdf', 'office', 'zip', 'exe', 'csv' ),
                'id'                => 0,
                'is_new'            => true,
                'show_in_post'      => 'yes',
                'wpuf_visibility'   => self::get_wpuf_visibility_prop(),
                'wpuf_cond'         => self::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Country list field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function country_list_field() {
        $settings = self::get_common_properties();

        $settings = array_merge( $settings, array(
            array(
                'name'          => 'country_list',
                'title'         => '',
                'type'          => 'country-list',
                'section'       => 'advanced',
                'priority'      => 22,
                'help_text'     => '',
            )
        ) );

        return array(
            'template'      => 'country_list_field',
            'title'         => __( 'Country List', 'wpuf-pro' ),
            'icon'          => 'globe',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'country_list',
                'template'          => 'country_list_field',
                'required'          => 'no',
                'label'             => __( 'Country List', 'wpuf-pro' ),
                'name'              => '',
                'is_meta'           => 'yes',
                'help'              => '',
                'width'             => '',
                'css'               => '',
                'id'                => 0,
                'is_new'            => true,
                'show_in_post'      => 'yes',
                'wpuf_visibility'   => self::get_wpuf_visibility_prop(),
                'wpuf_cond'         => self::get_wpuf_cond_prop(),
                'country_list'      => array(
                    'name'                              => '',
                    'country_list_visibility_opt_name'  => 'all', // all, hide, show
                    'country_select_show_list'          => array(),
                    'country_select_hide_list'          => array()
                )
            )
        );
    }

    /**
     * Numeric text field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function numeric_text_field() {
        $settings = self::get_common_properties();

        $settings = array_merge( $settings, array(
            array(
                'name'          => 'step_text_field',
                'title'         => __( 'Step', 'wpuf-pro' ),
                'type'          => 'text',
                'variation'     => 'number',
                'section'       => 'advanced',
                'priority'      => 9,
                'help_text'     => '',
            ),

            array(
                'name'          => 'min_value_field',
                'title'         => __( 'Min Value', 'wpuf-pro' ),
                'type'          => 'text',
                'variation'     => 'number',
                'section'       => 'advanced',
                'priority'      => 9,
                'help_text'     => '',
            ),

            array(
                'name'          => 'max_value_field',
                'title'         => __( 'Max Value', 'wpuf-pro' ),
                'type'          => 'text',
                'variation'     => 'number',
                'section'       => 'advanced',
                'priority'      => 9,
                'help_text'     => '',
            ),

            array(
                'name'      => 'placeholder',
                'title'     => __( 'Placeholder text', 'wpuf-pro' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 10,
                'help_text' => __( 'Text for HTML5 placeholder attribute', 'wpuf-pro' ),
            ),

            array(
                'name'      => 'default',
                'title'     => __( 'Default value', 'wpuf-pro' ),
                'type'      => 'text',
                'variation' => 'number',
                'section'   => 'advanced',
                'priority'  => 11,
                'help_text' => __( 'The default value this field will have', 'wpuf-pro' ),
            ),

            array(
                'name'      => 'size',
                'title'     => __( 'Size', 'wpuf-pro' ),
                'type'      => 'text',
                'variation' => 'number',
                'section'   => 'advanced',
                'priority'  => 20,
                'help_text' => __( 'Size of this input field', 'wpuf-pro' ),
            ),
        ) );

        return array(
            'template'      => 'numeric_text_field',
            'title'         => __( 'Numeric Field', 'wpuf-pro' ),
            'icon'          => 'hashtag',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'numeric_text',
                'template'          => 'numeric_text_field',
                'required'          => 'no',
                'label'             => __( 'Numeric Field', 'wpuf-pro' ),
                'name'              => '',
                'is_meta'           => 'yes',
                'help'              => '',
                'width'             => 'large',
                'css'               => '',
                'placeholder'       => '',
                'default'           => '',
                'size'              => 40,
                'step_text_field'   => '0',
                'min_value_field'   => '0',
                'max_value_field'   => '0',
                'id'                => 0,
                'is_new'            => true,
                'show_in_post'      => 'yes',
                'wpuf_visibility'   => self::get_wpuf_visibility_prop(),
                'wpuf_cond'         => self::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Address field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function address_field() {
        $settings = self::get_common_properties();

        $settings = array_merge( $settings, array(
            array(
                'name'          => 'address',
                'title'         => __( 'Address Fields', 'wpuf-pro' ),
                'type'          => 'address',
                'section'       => 'advanced',
                'priority'      => 21,
                'help_text'     => '',
            )
        ) );

        return array(
            'template'      => 'address_field',
            'title'         => __( 'Address Field', 'wpuf-pro' ),
            'icon'          => 'address-card-o',
            'is_full_width' => true,
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'address',
                'template'          => 'address_field',
                'required'          => 'no',
                'label'             => __( 'Address Field', 'wpuf-pro' ),
                'name'              => '',
                'is_meta'           => 'yes',
                'help'              => '',
                'width'             => '',
                'css'               => '',
                'id'                => 0,
                'is_new'            => true,
                'show_in_post'      => 'yes',
                'wpuf_visibility'   => self::get_wpuf_visibility_prop(),
                'wpuf_cond'         => self::get_wpuf_cond_prop(),
                'address_desc'      => '',
                'address'           => array(
                    'street_address'    => array(
                        'checked'       => 'checked',
                        'type'          => 'text',
                        'required'      => 'checked',
                        'label'         => __( 'Address Line 1', 'wpuf-pro' ),
                        'value'         => '',
                        'placeholder'   => ''
                    ),

                    'street_address2'   => array(
                        'checked'       => 'checked',
                        'type'          => 'text',
                        'required'      => '',
                        'label'         => __( 'Address Line 2', 'wpuf-pro' ),
                        'value'         => '',
                        'placeholder'   => ''
                    ),

                    'city_name'         => array(
                        'checked'       => 'checked',
                        'type'          => 'text',
                        'required'      => 'checked',
                        'label'         => __( 'City', 'wpuf-pro' ),
                        'value'         => '',
                        'placeholder'   => ''
                    ),

                    'state'             => array(
                        'checked'       => 'checked',
                        'type'          => 'text',
                        'required'      => 'checked',
                        'label'         => __( 'State', 'wpuf-pro' ),
                        'value'         => '',
                        'placeholder'   => ''
                    ),

                    'zip'               => array(
                        'checked'       => 'checked',
                        'type'          => 'text',
                        'required'      => 'checked',
                        'label'         => __( 'Zip Code', 'wpuf-pro' ),
                        'value'         => '',
                        'placeholder'   => ''
                    ),

                    'country_select'    => array(
                        'checked'                           => 'checked',
                        'type'                              => 'select',
                        'required'                          => 'checked',
                        'label'                             => __( 'Country', 'wpuf-pro' ),
                        'value'                             => '',
                        'country_list_visibility_opt_name'  => 'all',
                        'country_select_hide_list'          => array(),
                        'country_select_show_list'          => array()
                    )
                )
            )
        );
    }

    /**
     * Step Start field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function step_start() {
        $settings = array(
            array(
                'name'          => 'step_start',
                'title'         => '',
                'type'          => 'step-start',
                'section'       => 'basic',
                'priority'      => 10,
                'help_text'     => '',
            )
        );

        return array(
            'template'      => 'step_start',
            'title'         => __( 'Step Start', 'wpuf-pro' ),
            'icon'          => 'step-forward',
            'is_full_width' => true,
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'    => 'step_start',
                'template'      => 'step_start',
                'label'         => __( 'Step Start', 'wpuf-pro' ),
                'id'            => 0,
                'is_new'        => true,
                'wpuf_cond'     => null,
                'step_start'    => array(
                    'prev_button_text'  => __( 'Previous', 'wpuf-pro' ),
                    'next_button_text'  => __( 'Next', 'wpuf-pro' )
                )
            )
        );
    }

    /**
     * Google Map
     *
     * @since 2.5
     *
     * @return array
     */
    public static function google_map() {
        $settings = self::get_common_properties();

        $settings = array_merge( $settings, array(
            array(
                'name'          => 'default_pos',
                'title'         => '',
                'type'          => 'gmap-set-position',
                'section'       => 'basic',
                'priority'      => 21,
                'help_text'     => __( 'Enter default latitude and longitude to center the map', 'wpuf-pro' ),
            ),

            array(
                'name'          => 'directions',
                'type'          => 'checkbox',
                'options'       => array(
                    true        => __( 'Show directions link', 'wpuf-pro' )
                ),
                'section'       => 'basic',
                'priority'      => 11,
            )
        ) );

        return array(
            'template'      => 'google_map',
            'title'         => __( 'Google Map', 'wpuf-pro' ),
            'icon'          => 'map-marker',
            'validator'     => array(
                'callback'      => 'has_gmap_api_key',
                'button_class'  => 'button-faded',
                'msg_title'     => __( 'Google Map API key', 'wpuf-pro' ),
                'msg'           => sprintf(
                    __( 'You need to set Google Map API key in <a href="%s" target="_blank">WPUF Settings</a> in order to use "Google Map" field. <a href="%s" target="_blank">Click here to get the API key</a>.', 'wpuf-pro' ),
                    admin_url( 'admin.php?page=wpuf-settings' ),
                    'https://developers.google.com/maps/documentation/javascript'
                )
            ),
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'map',
                'template'          => 'google_map',
                'label'             => __( 'Google Map', 'wpuf-pro' ),
                'name'              => '',
                'is_meta'           => 'yes',
                'help'              => '',
                'required'          => 'no',
                'zoom'              => '12',
                'default_pos'       => '40.7143528,-74.0059731',
                'directions'        => true,
                'address'           => 'no',
                'show_lat'          => 'no',
                'width'             => '',
                'id'                => 0,
                'is_new'            => true,
                'show_in_post'      => 'yes',
                'wpuf_visibility'   => self::get_wpuf_visibility_prop(),
                'wpuf_cond'         => null
            )
        );
    }

    /**
     * Shortcode field settings
     *
     * @since 2.5.4
     *
     * @return array
     */
    public static function shortcode() {
        $settings = array(
            array(
                'name'          => 'shortcode',
                'title'         => __( 'Shortcode', 'wpuf-pro' ),
                'type'          => 'text',
                'section'       => 'basic',
                'priority'      => 10,
                'help_text'     => __( 'Input your shortcode here', 'wpuf-pro' ),
            ),
        );

        return array(
            'template'      => 'shortcode',
            'title'         => __( 'Shortcode', 'wpuf-pro' ),
            'icon'          => 'file-code-o',
            'is_full_width' => true,
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'shortcode',
                'template'          => 'shortcode',
                'label'             => 'Shortcode',
                'shortcode'         => '[your_shortcode]',
                'id'                => 0,
                'is_new'            => true,
                'wpuf_visibility'   => self::get_wpuf_visibility_prop(),
                'wpuf_cond'         => null
            )
        );
    }

    /**
     * Really Simple Captcha
     *
     * @since 2.5
     *
     * @return array
     */
    public static function really_simple_captcha() {
        $settings = array(
            array(
                'name'          => 'label',
                'title'         => __( 'Title', 'wpuf-pro' ),
                'type'          => 'text',
                'section'       => 'basic',
                'priority'      => 10,
                'help_text'     => __( 'Title of the section', 'wpuf-pro' ),
            )
        );

        return array(
            'template'      => 'really_simple_captcha',
            'title'         => __( 'Really Simple Captcha', 'wpuf-pro' ),
            'icon'          => 'check-circle-o',
            'validator'     => array(
                'callback'      => 'is_rs_captcha_active',
                'button_class'  => 'button-faded',
                'msg_title'     => __( 'Plugin dependency', 'wpuf-pro' ),
                'msg'           => sprintf(
                    __( 'This field depends on <a href="%s" target="_blank">Really Simple Captcha</a> plugin. Install and activate it first.', 'wpuf-pro' ),
                    'https://wordpress.org/plugins/really-simple-captcha/'
                ),
            ),
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'really_simple_captcha',
                'template'          => 'really_simple_captcha',
                'label'             => '',
                'id'                => 0,
                'is_new'            => true,
                'wpuf_visibility'   => self::get_wpuf_visibility_prop(),
                'wpuf_cond'         => self::get_wpuf_cond_prop(),
            )
        );
    }

    /**
     * Action Hook
     *
     * @since 2.5
     *
     * @return array
     */
    public static function action_hook() {
        $settings = array(
            array(
                'name'          => 'label',
                'title'         => __( 'Hook Name', 'wpuf-pro' ),
                'type'          => 'text',
                'section'       => 'basic',
                'priority'      => 10,
                'help_text'     => __( 'Name of the hook', 'wpuf-pro' ),
            ),

            array(
                'name'          => 'help_text',
                'title'         => '',
                'type'          => 'html_help_text',
                'section'       => 'basic',
                'priority'      => 11,
                'text'          => sprintf( __( 'An option for developers to add dynamic elements they want. It provides the chance to add whatever input type you want to add in this form. This way, you can bind your own functions to render the form to this action hook. You\'ll be given 3 parameters to play with: $form_id, $post_id, $form_settings.', 'wpuf-pro' ) )
                                   . '<pre>add_action(\'HOOK_NAME\', \'your_function_name\', 10, 3 );<br>'
                                   . 'function your_function_name( $form_id, $post_id, $form_settings ) {<br>'
                                   . '    // do what ever you want<br>'
                                   . '}</pre>',
            )
        );

        return array(
            'template'      => 'action_hook',
            'title'         => __( 'Action Hook', 'wpuf-pro' ),
            'icon'          => 'anchor',
            'is_full_width' => true,
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'action_hook',
                'template'          => 'action_hook',
                'label'             => 'HOOK_NAME',
                'id'                => 0,
                'is_new'            => true,
                'wpuf_cond'         => null
            )
        );
    }

    /**
     * Term & Conditions
     *
     * @since 2.5
     *
     * @return array
     */
    public static function toc() {
        $settings = array(
            array(
                'name'          => 'name',
                'title'         => __( 'Meta Key', 'wpuf-pro' ),
                'type'          => 'text',
                'section'       => 'basic',
                'priority'      => 10,
                'help_text'     => __( 'Name of the meta key this field will save to', 'wpuf-pro' ),
            ),

            array(
                'name'          => 'description',
                'title'         => __( 'Terms & Conditions', 'wpuf-pro' ),
                'type'          => 'textarea',
                'section'       => 'basic',
                'priority'      => 11,
            ),

            array(
                'name'          => 'show_checkbox',
                'type'          => 'checkbox',
                'options'       => array(
                    true        => __( 'Show checkbox', 'wpuf-pro' )
                ),
                'section'       => 'basic',
                'priority'      => 11,
            )
        );

        return array(
            'template'      => 'toc',
            'title'         => __( 'Terms & Conditions', 'wpuf-pro' ),
            'icon'          => 'file-text',
            'is_full_width' => true,
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'        => 'toc',
                'template'          => 'toc',
                'label'             => '',
                'name'              => '',
                'is_meta'           => 'yes',
                'description'       => __( 'I have read and agree to the <a href="#">Terms and Conditions</a> and <a href="#">Privacy Policy</a>', 'wpuf-pro' ),
                'show_checkbox'     => true,
                'id'                => 0,
                'is_new'            => true,
                'show_in_post'      => 'yes',
                'wpuf_visibility'   => self::get_wpuf_visibility_prop(),
                'wpuf_cond'         => self::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Ratings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function ratings() {
        $settings = self::get_common_properties();

        $ratings_settings = array(
            self::get_option_data_setting(),
        );

        $settings = array_merge( $settings, $ratings_settings );

        return array(
            'template'      => 'dropdown_field',
            'title'         => __( 'Ratings', 'wpuf-pro' ),
            'icon'          => 'star-half-o ',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'       => 'ratings',
                'template'         => 'ratings',
                'required'         => 'no',
                'label'            => __( 'Ratings', 'wpuf-pro' ),
                'name'             => '',
                'is_meta'          => 'yes',
                'help'             => '',
                'width'            => '',
                'css'              => '',
                'selected'         => array(),
                'inline'           => 'no',
                'options'          => array( 'Option' => __( 'Option', 'wpuf-pro' ) ),
                'id'               => 0,
                'is_new'           => true,
                'show_in_post'     => 'yes',
                'wpuf_visibility'  => self::get_wpuf_visibility_prop(),
                'wpuf_cond'        => self::get_wpuf_cond_prop()
            )
        );
    }

}