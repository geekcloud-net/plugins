<?php

/**
 * WC Marketplace registration form template
 */
class WC_Marketplace_Reg_Template extends WPUF_Post_Form_Template {

    public function __construct() {
        parent::__construct();

        $this->enabled     = class_exists( 'WC_Dependencies_Product_Vendor' );
        $this->title       = __( 'WC Marketplace Registration Form', 'wpuf-pro' );
        $this->description = __( 'Form for vendor registration of WC Marketplace plugin.', 'wpuf-pro' );
        $this->image       = WPUF_PRO_ASSET_URI . '/images/templates/wc-marketplace.png';
        $this->form_fields = array(
            array(
                'input_type' => 'step_start',
                'template' => 'step_start',
                'label' => 'Step Start',
                'wpuf_cond' => NULL,
                'step_start' => array(
                    'prev_button_text' => 'Previous',
                    'next_button_text' => 'Next',
                ) ,
                'name' => 'step_start',
            ),
            array(
                'input_type' => 'email',
                'template' => 'user_email',
                'required' => 'yes',
                'label' => 'Email',
                'name' => 'user_email',
                'is_meta' => 'no',
                'help' => '',
                'css' => '',
                'placeholder' => '',
                'default' => '',
                'size' => '40',
                'wpuf_cond' => $this->conditionals,
            ),
            array(
                'input_type' => 'text',
                'template' => 'user_login',
                'required' => 'yes',
                'label' => 'Store Name',
                'name' => 'user_login',
                'is_meta' => 'no',
                'help' => '',
                'css' => '',
                'placeholder' => '',
                'default' => '',
                'size' => 40,
                'wpuf_cond' => $this->conditionals,
            ),
            array(
                'input_type' => 'numeric_text',
                'template' => 'numeric_text_field',
                'required' => 'yes',
                'label' => 'Phone',
                'name' => '_vendor_phone',
                'is_meta' => 'yes',
                'help' => '',
                'width' => 'large',
                'css' => '',
                'placeholder' => '',
                'default' => '',
                'size' => 40,
                'step_text_field' => '0',
                'min_value_field' => '0',
                'max_value_field' => '0',
                'wpuf_cond' => $this->conditionals,
            ),
            array(
                'input_type' => 'image_upload',
                'template' => 'image_upload',
                'required' => 'yes',
                'label' => 'Store Logo',
                'name' => '_vendor_image',
                'is_meta' => 'yes',
                'help' => '',
                'width' => '',
                'css' => '',
                'max_size' => '1024',
                'count' => '1',
                'wpuf_cond' => $this->conditionals,
            ),
            array(
                'input_type' => 'image_upload',
                'template' => 'image_upload',
                'required' => 'yes',
                'label' => 'Store Banner',
                'name' => '_vendor_banner',
                'is_meta' => 'yes',
                'help' => '',
                'width' => '',
                'css' => '',
                'max_size' => '1024',
                'count' => '1',
                'wpuf_cond' => $this->conditionals,
            ),
            array(
                'input_type' => 'textarea',
                'template' => 'textarea_field',
                'required' => 'yes',
                'label' => 'Shop Description',
                'name' => '_vendor_description',
                'is_meta' => 'yes',
                'help' => '',
                'width' => 'medium',
                'css' => '',
                'rows' => 5,
                'cols' => 25,
                'placeholder' => '',
                'default' => '',
                'rich' => 'no',
                'word_restriction' => '',
                'wpuf_cond' => $this->conditionals,
            ),
             array(
                'input_type' => 'step_start',
                'template' => 'step_start',
                'label' => 'Step End',
                'wpuf_cond' => NULL,
                'step_start' => array(
                    'prev_button_text' => 'Previous',
                    'next_button_text' => 'Next',
                ) ,
                'name' => 'step_start',
            ),
            array(
                'input_type' => 'address',
                'template' => 'address_field',
                'required' => 'yes',
                'label' => 'Address',
                'name' => '_vendor_address',
                'is_meta' => 'yes',
                'help' => '',
                'width' => '',
                'css' => '',
                'wpuf_cond' => $this->conditionals,
                'address_desc' => '',
                'address' => array(
                    'street_address' => array(
                        'checked' => 'checked',
                        'type' => 'text',
                        'required' => 'checked',
                        'label' => 'Address Line 1',
                        'value' => '',
                        'placeholder' => 'Address line 1',
                    ) ,
                    'street_address2' => array(
                        'checked' => 'checked',
                        'type' => 'text',
                        'required' => '',
                        'label' => 'Address Line 2',
                        'value' => '',
                        'placeholder' => 'Address line 2',
                    ) ,
                    'city_name' => array(
                        'checked' => 'checked',
                        'type' => 'text',
                        'required' => 'checked',
                        'label' => 'City',
                        'value' => '',
                        'placeholder' => 'City',
                    ) ,
                    'state' => array(
                        'checked' => 'checked',
                        'type' => 'text',
                        'required' => 'checked',
                        'label' => 'State',
                        'value' => '',
                        'placeholder' => 'State',
                    ) ,
                    'zip' => array(
                        'checked' => 'checked',
                        'type' => 'text',
                        'required' => 'checked',
                        'label' => 'Zip Code',
                        'value' => '',
                        'placeholder' => 'Zip Code ',
                    ) ,
                    'country_select' => array(
                        'checked' => 'checked',
                        'type' => 'select',
                        'required' => 'checked',
                        'label' => 'Country',
                        'value' => '',
                        'country_list_visibility_opt_name' => 'all',
                        'country_select_hide_list' => array() ,
                        'country_select_show_list' => array() ,
                    ) ,
                ),
            ),
            array(
                'input_type' => 'url',
                'template' => 'website_url',
                'required' => 'no',
                'label' => 'Facebook Profile URL',
                'name' => '_vendor_fb_profile',
                'is_meta' => 'yes',
                'help' => '',
                'width' => 'large',
                'css' => '',
                'placeholder' => '',
                'default' => '',
                'size' => 40,
                'wpuf_cond' => $this->conditionals,
            ),
            array(
                'input_type' => 'url',
                'template' => 'website_url',
                'required' => 'no',
                'label' => 'Twitter Profile URL',
                'name' => '_vendor_twitter_profile',
                'is_meta' => 'yes',
                'help' => '',
                'width' => 'large',
                'css' => '',
                'placeholder' => '',
                'default' => '',
                'size' => 40,
                'wpuf_cond' => $this->conditionals,
            ),
            array(
                'input_type' => 'url',
                'template' => 'website_url',
                'required' => 'no',
                'label' => 'Google Plus Profile URL',
                'name' => '_vendor_google_plus_profile',
                'is_meta' => 'yes',
                'help' => '',
                'width' => 'large',
                'css' => '',
                'placeholder' => '',
                'default' => '',
                'size' => 40,
                'wpuf_cond' => $this->conditionals,
            ),
            array(
                'input_type' => 'url',
                'template' => 'website_url',
                'required' => 'no',
                'label' => 'LinkedIn Profile URL',
                'name' => '_vendor_linkdin_profile',
                'is_meta' => 'yes',
                'help' => '',
                'width' => 'large',
                'css' => '',
                'placeholder' => '',
                'default' => '',
                'size' => 40,
                'wpuf_cond' => $this->conditionals,
            ) ,
            array(
                'input_type' => 'url',
                'template' => 'website_url',
                'required' => 'no',
                'label' => 'Youtube Profile URL',
                'name' => '_vendor_youtube',
                'is_meta' => 'yes',
                'help' => '',
                'width' => 'large',
                'css' => '',
                'placeholder' => '',
                'default' => '',
                'size' => 40,
                'wpuf_cond' => $this->conditionals,
            ) ,
            array(
                'input_type' => 'url',
                'template' => 'website_url',
                'required' => 'no',
                'label' => 'Instagram Profile URL',
                'name' => '_vendor_instagram',
                'is_meta' => 'yes',
                'help' => '',
                'width' => 'large',
                'css' => '',
                'placeholder' => '',
                'default' => '',
                'size' => 40,
                'wpuf_cond' => $this->conditionals,
            ),
            array(
                'input_type' => 'password',
                'template' => 'password',
                'required' => 'yes',
                'label' => 'Password',
                'name' => 'password',
                'is_meta' => 'no',
                'help' => '',
                'css' => '',
                'placeholder' => '',
                'default' => '',
                'size' => 40,
                'min_length' => 5,
                'repeat_pass' => '',
                're_pass_label' => 'Confirm Password',
                'pass_strength' => 'yes',
                'wpuf_cond' => $this->conditionals,
            ),
        );

        $this->form_settings = array (
            'enable_email_verification' => 'no',
            'role' => 'dc_pending_vendor',
            'enable_multistep' => 'yes',
            'multistep_progressbar_type' => 'step_by_step',
            'form_template' => 'WC_Marketplace_Reg_Template',
            'label_position' => 'top',
            'reg_redirect_to' => 'same',
            'message' => 'Congratulations! You have successfully applied as a Vendor. Please wait for further notifications from the admin.',
            'registration_url' => '',
            'submit_text' => 'Register',
            'profile_redirect_to' => 'same',
            'update_message' => 'Profile updated successfully',
            'profile_url' => '',
            'update_text' => 'Update Profile',
        );
    }

    /**
     * Run necessary processing after new insert
     *
     * @param  int   $user_id
     * @param  int   $form_id
     * @param  array $form_settings
     *
     * @return void
     */
    public function after_insert( $user_id, $form_id, $form_settings ) {
        $this->handle_form_updates( $user_id, $form_id, $form_settings );
    }

    /**
     * Run necessary processing after editing a profile
     *
     * @param  int   $user_id
     * @param  int   $form_id
     * @param  array $form_settings
     *
     * @return void
     */
    public function after_update( $user_id, $form_id, $form_settings ) {
        $this->handle_form_updates( $user_id, $form_id, $form_settings );
    }

    /**
     * Run the functions on update/insert
     *
     * @param  int $user_id
     * @param  int $form_id
     * @param  array $form_settings
     *
     * @return void
     */
    public function handle_form_updates( $user_id, $form_id, $form_settings ) {

        // get fields data
        $user_email         = isset( $_POST['user_email'] ) ? sanitize_text_field( $_POST['user_email'] ) : '';
        $shop_image_id      = isset( $_POST['wpuf_files']['_vendor_image'] ) ? absint( $_POST['wpuf_files']['_vendor_image'][0] ) : '';
        $shop_banner_id     = isset( $_POST['wpuf_files']['_vendor_banner'] ) ? absint( $_POST['wpuf_files']['_vendor_banner'][0] )  : '';
        $shop_image         = wp_get_attachment_url( $shop_image_id );
        $shop_banner        = wp_get_attachment_url( $shop_banner_id );
        $stree_address      = isset( $_POST['_vendor_address']['street_address'] ) ? sanitize_text_field( $_POST['_vendor_address']['street_address'] ) : '';
        $street_address2    = isset( $_POST['_vendor_address']['street_address2'] ) ? sanitize_text_field( $_POST['_vendor_address']['street_address2'] ) : '';
        $city_name          = isset( $_POST['_vendor_address']['city_name'] ) ? sanitize_text_field( $_POST['_vendor_address']['city_name'] ) : '';
        $state              = isset( $_POST['_vendor_address']['state'] ) ? sanitize_text_field( $_POST['_vendor_address']['state'] ) : '';
        $zip                = isset( $_POST['_vendor_address']['zip'] ) ? sanitize_text_field( $_POST['_vendor_address']['zip'] ) : '';
        $country_select     = isset( $_POST['_vendor_address']['country_select'] ) ? sanitize_text_field( $_POST['_vendor_address']['country_select'] ) : '';

        // insert data to vendor profile
        update_user_meta( $user_id, 'billing_email', $user_email );
        update_user_meta( $user_id, '_vendor_image', $shop_image );
        update_user_meta( $user_id, '_vendor_banner', $shop_banner );
        update_user_meta( $user_id, '_vendor_address_1', $stree_address );
        update_user_meta( $user_id, '_vendor_address_2', $street_address2 );
        update_user_meta( $user_id, '_vendor_city', $city_name );
        update_user_meta( $user_id, '_vendor_state', $state );
        update_user_meta( $user_id, '_vendor_postcode', $zip );
        update_user_meta( $user_id, '_vendor_country', $country_select );

    }

}