<?php

/**
 * WooCommerce post form template
 */
class WPUF_Post_Form_Template_WooCommerce extends WPUF_Post_Form_Template {

    public function __construct() {
        $this->enabled     = class_exists( 'WooCommerce' );
        $this->title       = __( 'WooCommerce Product', 'wpuf-pro' );
        $this->description = __( 'Create a simple or downloadable product form for WooCommerce.', 'wpuf-pro' );
        $this->image       = WPUF_ASSET_URI . '/images/templates/woocommerce.png';
        $this->form_fields = array(
            array(
                'input_type'  => 'text',
                'template'    => 'post_title',
                'required'    => 'yes',
                'label'       => 'Product Name',
                'name'        => 'post_title',
                'is_meta'     => 'no',
                'help'        => '',
                'css'         => '',
                'placeholder' => 'Please enter your product name',
                'default'     => '',
                'size'        => '40',
                'wpuf_cond'   => $this->conditionals
            ),
            array(
                'input_type'   => 'taxonomy',
                'template'     => 'taxonomy',
                'required'     => 'yes',
                'label'        => 'Product Categories',
                'name'         => 'product_cat',
                'is_meta'      => 'no',
                'help'         => 'Select a category for your product',
                'css'          => '',
                'type'         => 'select',
                'orderby'      => 'name',
                'order'        => 'ASC',
                'exclude_type' => 'exclude',
                'exclude'      => '',
                'woo_attr'     => 'no',
                'woo_attr_vis' => 'no',
                'options'      => array(),
                'wpuf_cond'    => $this->conditionals
            ),
            array(
                'input_type'       => 'textarea',
                'template'         => 'post_content',
                'required'         => 'yes',
                'label'            => 'Product description',
                'name'             => 'post_content',
                'is_meta'          => 'no',
                'help'             => 'Write the full description of your product',
                'css'              => '',
                'rows'             => '5',
                'cols'             => '25',
                'placeholder'      => '',
                'default'          => '',
                'rich'             => 'yes',
                'insert_image'     => 'yes',
                'word_restriction' => '',
                'wpuf_cond'        => $this->conditionals,
            ),
            array(
                'input_type'  => 'textarea',
                'template'    => 'post_excerpt',
                'required'    => 'no',
                'label'       => 'Product Short Description',
                'name'        => 'post_excerpt',
                'is_meta'     => 'no',
                'help'        => 'Provide a short description of your product',
                'css'         => '',
                'rows'        => '5',
                'cols'        => '25',
                'placeholder' => '',
                'default'     => '',
                'rich'        => 'no',
                'wpuf_cond'   => $this->conditionals
            ),
            array(
                'input_type'      => 'numeric_text',
                'template'        => 'numeric_text_field',
                'required'        => 'yes',
                'label'           => 'Regular Price',
                'name'            => '_regular_price',
                'is_meta'         => 'yes',
                'help'            => '',
                'css'             => '',
                'placeholder'     => 'regular price of your product',
                'default'         => '',
                'size'            => '40',
                'step_text_field' => '0.01',
                'min_value_field' => '0',
                'max_value_field' => '',
                'wpuf_cond'       => $this->conditionals
            ),
            array(
                'input_type'      => 'numeric_text',
                'template'        => 'numeric_text_field',
                'required'        => 'no',
                'label'           => 'Sale Price',
                'name'            => '_sale_price',
                'is_meta'         => 'yes',
                'help'            => '',
                'css'             => '',
                'placeholder'     => 'sale price of your product',
                'default'         => '',
                'size'            => '40',
                'step_text_field' => '0.01',
                'min_value_field' => '0',
                'max_value_field' => '',
                'wpuf_cond'       => $this->conditionals
            ),
            array(
                'input_type' => 'image_upload',
                'template'   => 'featured_image',
                'count'      => '1',
                'required'   => 'yes',
                'label'      => 'Product Image',
                'name'       => 'featured_image',
                'is_meta'    => 'no',
                'help'       => 'Upload the main image of your product',
                'css'        => '',
                'max_size'   => '1024',
                'wpuf_cond'  => $this->conditionals
            ),
            array(
                'input_type' => 'image_upload',
                'template'   => 'image_upload',
                'required'   => 'no',
                'label'      => 'Product Image Gallery',
                'name'       => '_product_image',
                'is_meta'    => 'yes',
                'help'       => 'Upload additional pictures of your product and will be shown as image gallery',
                'css'        => '',
                'max_size'   => '1024',
                'count'      => '5',
                'wpuf_cond'  => $this->conditionals
            ),
            array(
                'input_type' => 'select',
                'template'   => 'dropdown_field',
                'required'   => 'yes',
                'label'      => 'Catalog visibility',
                'name'       => '_visibility',
                'is_meta'    => 'yes',
                'help'       => 'Choose where this product should be displayed in your catalog. The product will always be accessible directly.',
                'css'        => '',
                'first'      => ' - select -',
                'options'    =>
                array(
                    'visible'    => 'Catalog/search',
                    'catalog'    => 'Catalog',
                    'search'     => 'Search',
                    'hidden'     => 'Hidden',
                ),
                'wpuf_cond'  => $this->conditionals
            ),
            array(
                'input_type'       => 'textarea',
                'template'         => 'textarea_field',
                'required'         => 'no',
                'label'            => 'Purchase note',
                'name'             => '_purchase_note',
                'is_meta'          => 'yes',
                'help'             => 'Enter an optional note to send to the customer after purchase',
                'css'              => '',
                'rows'             => '5',
                'cols'             => '25',
                'placeholder'      => '',
                'default'          => '',
                'rich'             => 'no',
                'word_restriction' => '',
                'wpuf_cond'        => $this->conditionals
            ),
            array(
                'input_type'      => 'checkbox',
                'template'        => 'checkbox_field',
                'required'        => 'no',
                'label'           => 'Product Reviews',
                'name'            => 'product_reviews',
                'is_meta'         => 'yes',
                'help'            => '',
                'css'             => '',
                'options'         => array(
                    '_enable_reviews' => 'Enable reviews',
                ),
                'wpuf_cond'       => $this->conditionals
            ),
            array(
                'input_type' => 'radio',
                'template'   => 'radio_field',
                'required'   => 'yes',
                'label'      => 'Downloadable Product',
                'name'       => '_downloadable',
                'is_meta'    => 'yes',
                'help'       => 'Select if this is a downloadable product',
                'css'        => '',
                'options'    => array(
                    'yes' => 'Yes',
                    'no'  => 'No',
                ),
                'wpuf_cond'  => $this->conditionals
            ),
            array(
                'input_type' => 'file_upload',
                'template'   => 'file_upload',
                'required'   => 'yes',
                'label'      => 'Downloadable Files',
                'name'       => '_woo_files',
                'is_meta'    => 'yes',
                'help'       => 'Chose your downloadable files',
                'css'        => '',
                'max_size'   => '1024',
                'count'      => '5',
                'extension'  => array('images','audio','video','pdf','office','zip','exe','csv'),
                'wpuf_cond'  => array(
                    'condition_status' => 'yes',
                    'cond_field'       => array( '_downloadable' ),
                    'cond_operator'    => array( '=' ),
                    'cond_option'      => array( 'yes' ),
                    'cond_logic'       => 'all',
                ),
            )
        );

        $this->form_settings = array (
            'post_type'                  => 'product',
            'post_status'                => 'publish',
            'default_cat'                => '-1',
            'guest_post'                 => 'false',
            'message_restrict'           => 'This page is restricted. Please Log in / Register to view this page.',
            'redirect_to'                => 'post',
            'comment_status'             => 'open',
            'submit_text'                => 'Create Product',
            'edit_post_status'           => 'publish',
            'edit_redirect_to'           => 'same',
            'update_message'             => 'Product has been updated successfully. <a target="_blank" href="%link%">View Product</a>',
            'edit_url'                   => '',
            'update_text'                => 'Update Product',
            'form_template'              => __CLASS__,
            'notification'               => array(
                'new'                        => 'on',
                'new_to'                     => get_option( 'admin_email' ),
                'new_subject'                => 'New product has been created',
                'new_body'                   => 'Hi,
A new product has been created in your site %sitename% (%siteurl%).

Here is the details:
Product Title: %post_title%
Description: %post_content%
Short Description: %post_excerpt%
Author: %author%
Post URL: %permalink%
Edit URL: %editlink%',
                'edit'                       => 'off',
                'edit_to'                    => get_option( 'admin_email' ),
                'edit_subject'               => 'Product has been edited',
                'edit_body'                  => 'Hi,
The product "%post_title%" has been updated.

Here is the details:
Product Title: %post_title%
Description: %post_content%
Short Description: %post_excerpt%
Author: %author%
Post URL: %permalink%
Edit URL: %editlink%',
                ),
            );
    }

    /**
     * Run necessary processing after new post insert
     *
     * @param  int   $post_id
     * @param  int   $form_id
     * @param  array $form_settings
     *
     * @return void
     */
    public function after_insert( $post_id, $form_id, $form_settings ) {
        $this->handle_form_updates( $post_id, $form_id, $form_settings );
    }

    /**
     * Run necessary processing after editing a post
     *
     * @param  int   $post_id
     * @param  int   $form_id
     * @param  array $form_settings
     *
     * @return void
     */
    public function after_update( $post_id, $form_id, $form_settings ) {
        $this->handle_form_updates( $post_id, $form_id, $form_settings );
    }

    /**
     * Run the functions on update/insert
     *
     * @param  int $post_id
     * @param  int $form_id
     * @param  array $form_settings
     *
     * @return void
     */
    public function handle_form_updates( $post_id, $form_id, $form_settings ) {
        $this->update_reviews( $post_id );
        $this->update_price( $post_id );
        $this->update_gallery_images( $post_id );
        $this->update_downloadable_files( $post_id );
        $this->update_meta( $post_id );
    }

    /**
     * Update the product reviews
     *
     * @param  int $post_id
     *
     * @return void
     */
    public function update_reviews( $post_id ) {
        $reviews = get_post_meta( $post_id, 'product_reviews', true );
        $status  = !empty( $reviews ) ? 'open' : 'close';

        if ( 'open' === $status ) {
            apply_filters( 'comments_open', true , $post_id );
        } else {
            apply_filters( 'comments_open', false , $post_id );
        }
    }

    /**
     * Update the proper price
     *
     * @param  int $post_id
     *
     * @return void
     */
    function update_price( $post_id ) {
        $regular_price = (float) get_post_meta( $post_id, '_regular_price', true );
        $sale_price    = (float) get_post_meta( $post_id, '_sale_price', true );

        if ( $sale_price && $regular_price > $sale_price ) {
            update_post_meta( $post_id, '_price', $sale_price );
        } else {
            update_post_meta( $post_id, '_price', $regular_price );
        }
    }

    /**
     * Update image gallery
     *
     * @param  int $post_id
     *
     * @return void
     */
    public function update_gallery_images( $post_id ) {
        $images = get_post_meta( $post_id, '_product_image' );
        if ( !empty( $images ) ) {
            update_post_meta( $post_id, '_product_image_gallery', implode(',', $images) );
        }
    }

    /**
     * Update the downloadable file array with appropriate meta values
     *
     * @param  int $post_id
     * @return void
     */
    function update_downloadable_files( $post_id ) {
        $files     = get_post_meta( $post_id, '_woo_files' );
        $woo_files = array();

        if ( !$files ) {
            update_post_meta( $post_id, '_downloadable_files', array() );
            update_post_meta( $post_id, '_virtual', 'no' );
            update_post_meta( $post_id, '_downloadable', 'no' );
            return;
        }

        foreach ($files as $file_id) {
            $file_url = wp_get_attachment_url( $file_id );
            $woo_files[md5( $file_url )] = array(
                'file' => $file_url,
                'name' => basename( $file_url )
            );
        }

        update_post_meta( $post_id, '_downloadable_files', $woo_files );
        update_post_meta( $post_id, '_virtual', 'yes' );
    }

    /**
     *  Fix for visibily not updating from frontend post
     *
     * @param  int $post_id
     * @return void
     */
    public function update_meta( $post_id ) {
    
        $visibility = get_post_meta( $post_id, '_visibility', true );

        $product = wc_get_product( $post_id );
        if ( !empty( $visibility ) ) {
            $product->set_catalog_visibility( $visibility );
        }
        $product->save();
    }
}