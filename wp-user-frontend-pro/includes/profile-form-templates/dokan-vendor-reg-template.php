<?php

/**
 * Dokan vendor registration form template
 */
class Dokan_Vendor_Reg_Template extends WPUF_Post_Form_Template {

    public function __construct() {
        parent::__construct();

        $this->enabled     = class_exists( 'WeDevs_Dokan' );
        $this->title       = __( 'Dokan Vendor Registration Form', 'wpuf-pro' );
        $this->description = __( 'Form for vendor registration of Dokan plugin.', 'wpuf-pro' );
        $this->image       = WPUF_PRO_ASSET_URI . '/images/templates/dokan.png';
        $this->form_fields = array(
            array(
                'input_type' => 'text',
                'template' => 'first_name',
                'required' => 'yes',
                'label' => 'First Name',
                'name' => 'first_name',
                'is_meta' => 'no',
                'help' => '',
                'css' => '',
                'placeholder' => '',
                'default' => '',
                'size' => 40,
                'wpuf_cond' => $this->conditionals,
            ),
            array(
                'input_type' => 'text',
                'template' => 'last_name',
                'required' => 'yes',
                'label' => 'Last Name',
                'name' => 'last_name',
                'is_meta' => 'no',
                'help' => '',
                'css' => '',
                'placeholder' => '',
                'default' => '',
                'size' => 40,
                'wpuf_cond' => $this->conditionals,
            ),
            array(
                'input_type' => 'email',
                'template' => 'user_email',
                'required' => 'yes',
                'label' => 'Email Address',
                'name' => 'user_email',
                'is_meta' => 'no',
                'help' => '',
                'css' => '',
                'placeholder' => '',
                'default' => '',
                'size' => 40,
                'wpuf_cond' => $this->conditionals,
            ),
            array(
                'input_type' => 'text',
                'template' => 'text_field',
                'required' => 'yes',
                'label' => 'Shop Name',
                'name' => 'dokan_store_name',
                'is_meta' => 'yes',
                'help' => '',
                'width' => 'large',
                'css' => '',
                'placeholder' => '',
                'default' => '',
                'size' => 40,
                'word_restriction' => '',
                'wpuf_cond' => $this->conditionals,
            ),
            array(
                'input_type' => 'text',
                'template' => 'text_field',
                'required' => 'yes',
                'label' => 'Shop URL',
                'name' => 'shopurl',
                'is_meta' => 'yes',
                'help' => function_exists( 'dokan_get_option' ) ? home_url() . '/' . dokan_get_option( 'custom_store_url', 'dokan_general', 'store' ) . '/<strong id="url-alart"></strong><strong id="url-alart-mgs" class="pull-right"></strong>' : '',
                'width' => 'large',
                'css' => '',
                'placeholder' => '',
                'default' => '',
                'size' => 40,
                'word_restriction' => '',
                'wpuf_cond' => $this->conditionals,
            ),
            array(
                'input_type' => 'image_upload',
                'template' => 'image_upload',
                'required' => 'no',
                'label' => 'Profile Picture',
                'name' => 'dokan_profile_picture',
                'is_meta' => 'yes',
                'help' => 'Upload profile picture',
                'width' => '',
                'css' => '',
                'max_size' => '1024',
                'count' => '1',
                'wpuf_cond' => $this->conditionals,
            ),
            array(
                'input_type' => 'image_upload',
                'template' => 'image_upload',
                'required' => 'no',
                'label' => 'Upload Banner',
                'name' => 'dokan_banner',
                'is_meta' => 'yes',
                'help' => 'Upload a banner for your store. Banner size is (625x300) px',
                'width' => '',
                'css' => '',
                'max_size' => '2048',
                'count' => '1',
                'wpuf_cond' => $this->conditionals,
            ),
            array(
                'input_type' => 'numeric_text',
                'template' => 'numeric_text_field',
                'required' => 'yes',
                'label' => 'Phone Number',
                'name' => 'dokan_store_phone',
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
                'input_type' => 'address',
                'template' => 'address_field',
                'required' => 'no',
                'label' => 'Address',
                'name' => 'dokan_address',
                'is_meta' => 'yes',
                'help' => '',
                'width' => '',
                'css' => '',
                'wpuf_cond' => $this->conditionals,
                'address_desc' => '',
                'address' => array(
                    'street_1' => array(
                        'checked' => 'checked',
                        'type' => 'text',
                        'required' => 'checked',
                        'label' => 'Street',
                        'value' => '',
                        'placeholder' => '',
                    ) ,
                    'street_2' => array(
                        'checked' => 'checked',
                        'type' => 'text',
                        'required' => '',
                        'label' => 'Street 2',
                        'value' => '',
                        'placeholder' => '',
                    ) ,
                    'city' => array(
                        'checked' => 'checked',
                        'type' => 'text',
                        'required' => 'checked',
                        'label' => 'City',
                        'value' => '',
                        'placeholder' => '',
                    ) ,
                    'state' => array(
                        'checked' => 'checked',
                        'type' => 'text',
                        'required' => 'checked',
                        'label' => 'State',
                        'value' => '',
                        'placeholder' => '',
                    ) ,
                    'zip' => array(
                        'checked' => 'checked',
                        'type' => 'text',
                        'required' => 'checked',
                        'label' => 'Zip Code',
                        'value' => '',
                        'placeholder' => '',
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
                'input_type' => 'map',
                'template' => 'google_map',
                'label' => 'Store Location',
                'name' => 'location',
                'is_meta' => 'yes',
                'help' => '',
                'required' => 'no',
                'zoom' => '12',
                'default_pos' => '40.7142337,-74.00616839999998',
                'directions' => false,
                'address' => 'yes',
                'show_lat' => 'no',
                'width' => '',
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
                'repeat_pass' => 'yes',
                're_pass_label' => 'Confirm Password',
                'pass_strength' => 'yes',
                'wpuf_cond' => $this->conditionals,
            ),
        );

        $this->form_settings = array (
            'enable_email_verification' => 'yes',
            'role' => 'seller',
            'multistep_progressbar_type' => 'step_by_step',
            'form_template' => 'Dokan_Vendor_Reg_Template',
            'label_position' => 'above',
            'reg_redirect_to' => 'url',
            'message' => 'Registration successful',
            'registration_url' => site_url() . '/?page=dokan-seller-setup',
            'submit_text' => 'Register',
            'profile_redirect_to' => 'url',
            'update_message' => 'Profile has been updated successfully.',
            'profile_url' => site_url() . '/?page=dokan-seller-setup',
            'update_text' => 'Update Information',
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
        $store_settings = dokan_get_store_info( $user_id );
        $shopurl        = sanitize_text_field( $_POST['shopurl'] );

        //update store setttings info
        $store_settings = array(
            'store_name'      => isset( $_POST['dokan_store_name'] ) ? sanitize_text_field( $_POST['dokan_store_name'] ) : '',
            'phone'           => isset( $_POST['dokan_store_phone'] ) ? sanitize_text_field( $_POST['dokan_store_phone'] ) : '',
            'banner'          => isset( $_POST['wpuf_files']['dokan_banner'] ) ? absint( $_POST['wpuf_files']['dokan_banner'][0] ) : '',
            'gravatar'        => isset( $_POST['wpuf_files']['dokan_profile_picture'] ) ? absint( $_POST['wpuf_files']['dokan_profile_picture'][0] ) : '',
            'location'        => isset( $_POST['location'] ) ? sanitize_text_field( $_POST['location'] ) : '',
            'find_address'    => isset( $_POST['find_address'] ) ? sanitize_text_field( $_POST['find_address'] ) : '',
            'address'         => isset( $_POST['dokan_address'] ) ? $_POST['dokan_address'] : '',
        );

        // insert data to seller profile
        update_user_meta( $user_id, 'dokan_profile_settings', $store_settings );
        update_user_meta( $user_id, 'dokan_store_name', $store_settings['store_name'] );

        if ( dokan_get_option( 'new_seller_enable_selling', 'dokan_selling' ) == 'off' ) {
            update_user_meta( $user_id, 'dokan_enable_selling', 'no' );
        } else {
            update_user_meta( $user_id, 'dokan_enable_selling', 'yes' );
        }

        wp_update_user( array(
            'ID'            => $user_id,
            'user_nicename' => $shopurl
        ));
    }

}
