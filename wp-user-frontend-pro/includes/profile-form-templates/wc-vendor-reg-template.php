<?php

/**
 * WC Vendor registration form template
 */
class WC_Vendor_Reg_Template extends WPUF_Post_Form_Template {

    public function __construct() {
        parent::__construct();

        $this->enabled     = class_exists( 'WC_Vendors' );
        $this->title       = __( 'WC Vendors Registration Form', 'wpuf-pro' );
        $this->description = __( 'Form for vendor registration of WC Vendors plugin.', 'wpuf-pro' );
        $this->image       = WPUF_PRO_ASSET_URI . '/images/templates/wc-vendor.png';
        $this->form_fields = array(
            array(
                'input_type' => 'email',
                'template' => 'user_email',
                'required' => 'yes',
                'label' => 'E-mail',
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
                'input_type' => 'email',
                'template' => 'email_address',
                'required' => 'yes',
                'label' => 'PayPal Address',
                'name' => 'pv_paypal',
                'is_meta' => 'yes',
                'help' => 'Your PayPal address is used to send you your commission.',
                'width' => 'large',
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
                'name' => 'pv_shop_name',
                'is_meta' => 'yes',
                'help' => 'Your shop name is public and must be unique.',
                'width' => 'large',
                'css' => '',
                'placeholder' => '',
                'default' => '',
                'size' => 40,
                'word_restriction' => '',
                'wpuf_cond' => $this->conditionals,
            ),
            array(
                'input_type' => 'textarea',
                'template' => 'textarea_field',
                'required' => 'yes',
                'label' => 'Seller info',
                'name' => 'pv_seller_info',
                'is_meta' => 'yes',
                'help' => 'This is displayed on each of your products.',
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
                'input_type' => 'textarea',
                'template' => 'textarea_field',
                'required' => 'yes',
                'label' => 'Shop Description',
                'name' => 'pv_shop_description',
                'is_meta' => 'yes',
                'help' => 'This is displayed on your shop page.',
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
            'enable_email_verification' => 'no',
            'role' => 'pending_vendor',
            'multistep_progressbar_type' => 'step_by_step',
            'form_template' => 'WC_Vendor_Reg_Template',
            'label_position' => 'above',
            'reg_redirect_to' => 'same',
            'message' => 'Your account has not yet been approved to become a vendor. When it is, you will receive an email telling you that your account is approved!',
            'registration_url' => '',
            'submit_text' => 'Register',
            'profile_redirect_to' => 'same',
            'update_message' => 'Profile has been updated successfully.',
            'profile_url' => '',
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

        $user_email       = isset( $_POST['user_email'] ) ? sanitize_text_field( $_POST['user_email'] ) : '';
        $pv_shop_name     = isset( $_POST['pv_shop_name'] ) ? sanitize_title( $_POST[ 'pv_shop_name' ] ) : '';

        // insert data to vendor profile
        update_user_meta( $user_id, 'billing_email', $user_email );
        update_user_meta( $user_id, 'pv_shop_slug', $pv_shop_name );

    }

}