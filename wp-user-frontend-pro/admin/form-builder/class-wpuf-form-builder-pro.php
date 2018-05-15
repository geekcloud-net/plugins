<?php
/**
 * Form Builder framework
 */
class WPUF_Admin_Form_Builder_Pro {

    private $gmap_api_key = '';

    /**
     * Class construction
     *
     * @since 2.5
     *
     * @return void
     */
    public function __construct() {
        add_action( 'wpuf-form-builder-enqueue-style', array( $this, 'admin_enqueue_styles' ) );
        add_action( 'wpuf-form-builder-enqueue-after-mixins', array( $this, 'admin_enqueue_scripts_mixins' ) );
        add_action( 'wpuf-form-builder-enqueue-after-components', array( $this, 'admin_enqueue_scripts_components' ) );
        add_action( 'wpuf-form-builder-add-js-templates', array( $this, 'add_js_templates' ) );
        add_action( 'wpuf-form-builder-js-deps', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'wpuf-form-builder-wpuf-forms-js-deps', array( $this, 'wpuf_forms_pro_scripts' ) );
        add_action( 'wpuf-form-builder-localize-script', array( $this, 'localize_script' ) );
        add_action( 'wpuf-form-builder-js-builder-stage-mixins', array( $this, 'add_builder_stage_mixins' ) );
        add_action( 'wpuf-form-builder-js-form-fields-mixins', array( $this, 'add_form_field_mixins' ) );

        add_filter( 'wpuf-form-builder-field-settings', array( $this, 'add_field_settings' ) );
        add_filter( 'wpuf-form-builder-fields-common-properties', array( $this, 'add_fields_common_properties' ) );
        add_filter( 'wpuf-form-builder-fields-custom-fields', array( $this, 'add_custom_fields' ) );
        add_filter( 'wpuf-form-builder-fields-others-fields', array( $this, 'add_others_fields' ) );
        add_filter( 'wpuf-form-builder-i18n', array( $this, 'i18n' ) );

        $this->gmap_api_key = wpuf_get_option( 'gmap_api_key', 'wpuf_general' );
    }

    public function admin_enqueue_styles() {
        wp_enqueue_style( 'wpuf-form-builder-pro', WPUF_PRO_ASSET_URI . '/css/wpuf-form-builder-pro.css', array( 'wpuf-form-builder' ), WPUF_PRO_VERSION );
    }

    /**
     * Enqueue Vue mixins
     *
     * @since 2.5
     *
     * @return void
     */
    public function admin_enqueue_scripts_mixins() {
        wp_enqueue_script( 'wpuf-form-builder-mixins-pro', WPUF_PRO_ASSET_URI . '/js/wpuf-form-builder-mixins-pro.js', array( 'wpuf-form-builder-mixins' ), WPUF_PRO_VERSION, true );
    }

    /**
     * Enqueue Vue components
     *
     * @since 2.5
     *
     * @return void
     */
    public function admin_enqueue_scripts_components() {
        wp_enqueue_script( 'wpuf-form-builder-components-pro', WPUF_PRO_ASSET_URI . '/js/wpuf-form-builder-components-pro.js', array( 'wpuf-form-builder-components' ), WPUF_PRO_VERSION, true );
    }

    /**
     * Add Vue templates
     *
     * @since 2.5
     *
     * @return void
     */
    public function add_js_templates() {
        include WPUF_PRO_ROOT . '/assets/js-templates/form-components.php';
    }

    /**
     * Add script dependencies
     *
     * @since 2.5
     *
     * @param array $deps
     *
     * @return array
     */
    public function admin_enqueue_scripts( $deps ) {

        if ( !empty( $this->gmap_api_key ) ) {
            $scheme = is_ssl() ? 'https' : 'http';

            wp_register_script( 'wpuf-google-maps', $scheme . '://maps.google.com/maps/api/js?libraries=places&key='.$this->gmap_api_key, array(), null, true );

            $deps[] = 'wpuf-google-maps';
        }

        return $deps;
    }

    /**
     * Enqueue pro scripts in post forms editor page
     *
     * @since 2.5.3
     *
     * @param array $deps
     *
     * @return array
     */
    public function wpuf_forms_pro_scripts( $deps ) {
        wp_register_script( 'wpuf-form-builder-wpuf-forms-pro', WPUF_PRO_ASSET_URI . '/js/wpuf-form-builder-wpuf-forms-pro.js', [ 'wpuf-form-builder-wpuf-forms' ], WPUF_PRO_VERSION, true );

        $deps[] = 'wpuf-form-builder-wpuf-forms-pro';

        return $deps;
    }

    /**
     * Add data to localize script data array
     *
     * @since 2.5
     *
     * @param array $data
     *
     * @return array
     */
    public function localize_script( $data ) {
        return array_merge( $data, array(
            'gmap_api_key'                => $this->gmap_api_key,
            'is_rs_captcha_active'        => class_exists( 'ReallySimpleCaptcha' ) ? true : false,
            'countries'                   => wpuf_get_countries(),
            'wpuf_cond_supported_fields'  => array( 'radio_field', 'checkbox_field', 'dropdown_field' )
        ) );
    }

    /**
     * Add mixin_form_field_pro mixin
     *
     * @since 2.5
     *
     * @param array $mixins
     *
     * @return array
     */
    public function add_builder_stage_mixins( $mixins ) {
        return array_merge( $mixins, array( 'mixin_form_field_pro', 'mixin_builder_stage_pro' ) );
    }

    /**
     * Add mixins to form_fields
     *
     * @since 2.5
     *
     * @param array $mixins
     *
     * @return array
     */
    public function add_form_field_mixins( $mixins ) {
        return array_merge( $mixins, array( 'mixin_form_field_pro' ) );
    }

    /**
     * Field settings hook
     *
     * @since 2.5
     *
     * @param array $settings
     *
     * @return array
     */
    public function add_field_settings( $settings ) {
        require_once WPUF_PRO_ROOT . '/admin/form-builder/class-wpuf-form-builder-field-settings-pro.php';

        return array_merge( $settings, WPUF_Form_Builder_Field_Settings_Pro::get_field_settings() );
    }

    /**
     * Add common properties
     *
     * @since 2.5
     *
     * @param array $common_properties
     */
    public function add_fields_common_properties( $common_properties ) {
        require_once WPUF_PRO_ROOT . '/admin/form-builder/class-wpuf-form-builder-field-settings-pro.php';

        array_push( $common_properties, WPUF_Form_Builder_Field_Settings_Pro::get_field_wpuf_cond() );
        return $common_properties;
    }

    /**
     * Add fields in Custom Fields
     *
     * @since 2.5
     *
     * @param array $fields
     *
     * @return void
     */
    public function add_custom_fields( $fields ) {
        return array_merge( $fields, array(
            'repeat_field', 'date_field', 'file_upload', 'country_list_field',
            'numeric_text_field', 'address_field', 'step_start', 'google_map'
        ) );
    }

    /**
     * Add fields in Others Fields
     *
     * @since 2.5
     *
     * @param array $fields
     *
     * @return void
     */
    public function add_others_fields( $fields ) {
        return array_merge( $fields, array(
            'shortcode', 'really_simple_captcha', 'action_hook', 'toc', 'ratings'
        ) );
    }

    /**
     * i18n translatable strings
     *
     * @since 2.5
     *
     * @param array $i18n
     *
     * @return array
     */
    public function i18n( $i18n ) {
        return array_merge( $i18n, array(
            'street_address'    => __( 'Address Line 1', 'wpuf-pro' ),
            'street_address2'   => __( 'Address Line 2', 'wpuf-pro' ),
            'city_name'         => __( 'City', 'wpuf-pro' ),
            'state'             => __( 'State', 'wpuf-pro' ),
            'zip'               => __( 'Zip Code', 'wpuf-pro' ),
            'country_select'    => __( 'Country', 'wpuf-pro' ),
            'show_all'          => __( 'Show all', 'wpuf-pro' ),
            'hide_these'        => __( 'Hide these', 'wpuf-pro' ),
            'only_show_these'   => __( 'Only show these', 'wpuf-pro' ),
            'select_countries'  => __( 'Select Countries', 'wpuf-pro' ),
        ) );
    }
}
