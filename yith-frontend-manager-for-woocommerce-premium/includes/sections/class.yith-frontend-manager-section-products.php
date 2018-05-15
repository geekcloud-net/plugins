<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined ( 'ABSPATH' ) ) {
    exit( 'Direct access forbidden.' );
}

if( ! class_exists( 'YITH_Frontend_Manager_Section_Products' ) ) {

    class YITH_Frontend_Manager_Section_Products extends YITH_WCFM_Section {

        public $WC_Admin_Post_Types;

        /**
         * Constructor method
         *
         * @return \YITH_Frontend_Manager_Section
         * @since 1.0.0
         */
        public function __construct() {
            $this->id                   = 'products';
            $this->_default_section_name = _x( 'Products', '[Frontend]: Dashboard menu item', 'yith-frontend-manager-for-woocommerce' );

            $this->_subsections = apply_filters( 'yith_wcfm_products_subsections', array(
                    'products' => array(
                        'slug' => $this->get_option( 'slug', $this->id . '_list' , 'list' ),
                        'name' => __( 'All Products', 'yith-frontend-manager-for-woocommerce' ),
                        'add_delete_script' => true
                    ),

                    'product' => array(
                        'slug' => $this->get_option( 'slug', $this->id . '_product', 'product' ),
                        'name' => __( 'Add Product', 'yith-frontend-manager-for-woocommerce' ),
                    ),
                ),
                $this->id
            );

            $this->deps();

            add_action( 'init', array( $this, 'add_support_for_color_and_label_variations' ), 20 );

            parent::__construct();
        }

        /* === SECTION METHODS === */

        /**
         * Required files for this section
         *
         * @author Andrea Grillo    <andrea.grillo@yithemes.com>
         * @return void
         * @since 1.0.0
         */
        public function deps(){
	        if( ! class_exists( 'WP_Posts_List_Table' ) ){
		        require_once( ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php' );
	        }

	        if( YITH_Frontend_Manager()->is_wc_3_3_or_greather && ! class_exists( 'WC_Admin_List_Table_Products' ) ){
		        require_once( WC()->plugin_path() . '/includes/admin/list-tables/class-wc-admin-list-table-products.php' );
            }

	        if( ! class_exists( 'WC_Admin_Post_Types' ) ){
		        require_once  ( WC()->plugin_path() . '/includes/admin/class-wc-admin-post-types.php' );
	        }

            if( class_exists( 'YITH_WCCL' ) && ! class_exists( 'YITH_WCCL_Admin' ) ){
                require_once ( YITH_WCCL_DIR . 'includes/class.yith-wccl-admin.php' );
            }

	        require_once( YITH_WCFM_LIB_PATH . 'class.yith-frontend-manager-products-list-table.php' );
            require_once( ABSPATH . 'wp-admin/includes/meta-boxes.php' );
        }

        /**
         * Section styles and scripts
         *
         * Override this method in section class to enqueue
         * particular styles and scripts only in correct
         * section
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @return void
         * @since  1.0.0
         */
        public function enqueue_section_scripts() {
            /* === Styles === */
            wp_enqueue_style( 'yith-wcfm-products', YITH_WCFM_URL . 'assets/css/products.css', array( 'woocommerce_admin_styles' ), YITH_WCFM_VERSION );
            wp_enqueue_style( 'wp-edit' );
            wp_enqueue_style( 'jquery-ui-style' );

            /* === Scripts === */
            wp_enqueue_script( 'woocommerce_admin' );

	        wp_enqueue_script( 'woocommerce_quick-edit' );
	        wp_enqueue_script( 'wc-admin-product-meta-boxes' );
	        wp_enqueue_script( 'wc-admin-variation-meta-boxes' );

            wp_enqueue_media();

	        wp_enqueue_script( 'wp-color-picker' );
	        wp_enqueue_script( 'jquery-ui-dialog' );

            $suffix     = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || ! empty( $_GET['yith_debug'] ) ? '' : '.min';
            wp_enqueue_script( 'yith-frontend-manager-product-js', YITH_WCFM_URL . "assets/js/yith-frontend-manager-product{$suffix}.js", array( 'jquery' ), YITH_WCFM_VERSION, true );

            do_action( 'yith_wcfm_products_enqueue_scripts' );
        }

            /**
         * Print shortcode function
         *
         * @author Andrea Grillo    <andrea.grillo@yithemes.com>
         * @return void
         * @since 1.0.0
         */
        public function print_shortcode( $atts = array(), $content = '', $tag ) {
            $section = $subsection = '';
            if ( ! empty( $atts) ) {
                $section = ! empty( $atts['section'] ) ? $atts['section'] : $this->id;
                $subsection = $this->id;
                if( ! empty( $atts['subsection'] ) && 'products' != $atts['subsection'] && ! in_array( $atts['subsection'], $this->_subsections ) ){
                    $subsection = $atts['subsection'];
                }
            }

            $atts = array(
                'section_obj'    => $this,
                'product_status' => YITH_Frontend_Manager_Section_Products::get_product_status(),
                'section'        => $section,
                'subsection'     => $subsection
            );

            if( apply_filters( 'yith_wcfm_print_product_section', true, $subsection, $section, $atts ) ){
                $this->print_section( $subsection, $section, $atts );
            }

            else {
                do_action( 'yith_wcfm_print_section_unauthorized', $this->id );
            }
        }

        /**
         * get the edit post link for frontend
         *
         * @author Andrea Grillo    <andrea.grillo@yithemes.com>
         * @return string post link
         * @since 1.0.0
         */
        public static function get_edit_product_link( $product_id ){
            return add_query_arg( array( 'product_id' => $product_id, ), yith_wcfm_get_section_url( 'products', 'product' ) );
        }

        /**
         * Save a product
         *
         * @author Corrado Porzio   <corradoporzio@gmail.com>
         * @return void
         * @since 1.0.0
         */
        public static function save_product( $array ) {

            $post_id = isset( $array['id'] ) ? $array['id'] : 0;
            $array['_reviews_allowed'] = isset( $array['comment_status'] ) ? $array['comment_status'] : 0;
            $array['_sold_individually'] = isset( $array['_sold_individually'] ) ? $array['_sold_individually'] : 0;
            $is_new = false;

            if ( $post_id > 0 ) {

                wp_update_post( array(
                    'ID'                => $array['id'],
                    'post_title'        => $array['post_title'],
                    'post_content'      => $array['post_content'],
                    'post_excerpt'      => $array['post_excerpt'],
                    'post_status'       => $array['post_status'],
                    'comment_status'    => $array['comment_status'],
                    'menu_order'        => isset( $array['menu_order'] ) ? $array['menu_order'] : 0,
                ) );

            } else {

                $post_id = wp_insert_post( array(
                    'post_title'        => $array['post_title'],
                    'post_content'      => $array['post_content'],
                    'post_excerpt'      => $array['post_excerpt'],
                    'post_status'       => $array['post_status'],
                    'comment_status'    => $array['comment_status'],
                    'menu_order'        => isset( $array['menu_order'] ) ? $array['menu_order'] : 0,
                    'post_type'         => "product",
                ) );

                $is_new = true;
            }

            if ( ! is_wp_error( $post_id ) && $post_id > 0 ) {

                // Product Data
                wp_set_object_terms( $post_id, $array['product-type'], 'product_type' );

                update_post_meta( $post_id, '_visibility'               , isset( $array['_visibility'] ) ? $array['_visibility'] : '' );
                update_post_meta( $post_id, '_virtual'                  , isset( $array['_virtual'] ) ? 'yes' : '' );

                // General
                update_post_meta( $post_id, '_regular_price'            , $array['_regular_price'] );
                update_post_meta( $post_id, '_sale_price'               , $array['_sale_price'] );
                update_post_meta( $post_id, '_sale_price_dates_from'    , strtotime( $array['_sale_price_dates_from'] ) );
                update_post_meta( $post_id, '_sale_price_dates_to'      , strtotime( $array['_sale_price_dates_to'] ) );

                update_post_meta( $post_id, '_sku'                      , isset( $array['_sku'] ) ? $array['_sku'] : '' );
                update_post_meta( $post_id, '_manage_stock'             , isset( $array['_manage_stock'] ) ? 'yes' : 'no' );
                update_post_meta( $post_id, '_stock'                    , $array['_stock'] );
                update_post_meta( $post_id, '_backorders'               , $array['_backorders'] );
                update_post_meta( $post_id, '_stock_status'             , $array['_stock_status'] );
                update_post_meta( $post_id, '_sold_individually'        , $array['_sold_individually'] );

                // Shipping
                update_post_meta( $post_id, '_weight'                   , $array['_weight'] );
                update_post_meta( $post_id, '_length'                   , $array['_length'] );
                update_post_meta( $post_id, '_width'                    , $array['_width'] );
                update_post_meta( $post_id, '_height'                   , $array['_height'] );
                if( ! empty( $array['product_shipping_class'] ) && is_numeric( $array['product_shipping_class'] ) ){
                    wp_set_object_terms( $post_id, absint( $array['product_shipping_class'] ), 'product_shipping_class', false );
                }

                //Advanced
                update_post_meta( $post_id, '_purchase_note'            , $array['_purchase_note'] );
                update_post_meta( $post_id, '_reviews_allowed'          , $array['_reviews_allowed'] );

                // Update price if on sale
                $date_from     = (string) isset( $_POST['_sale_price_dates_from'] ) ? wc_clean( $_POST['_sale_price_dates_from'] ) : '';
                $date_to       = (string) isset( $_POST['_sale_price_dates_to'] ) ? wc_clean( $_POST['_sale_price_dates_to'] )     : '';
                $regular_price = (string) isset( $_POST['_regular_price'] ) ? wc_clean( $_POST['_regular_price'] )                 : '';
                $sale_price    = (string) isset( $_POST['_sale_price'] ) ? wc_clean( $_POST['_sale_price'] )                       : '';

                if ( '' !== $sale_price && '' === $date_to && '' === $date_from ) {
                    update_post_meta( $post_id, '_price', wc_format_decimal( $sale_price ) );
                }

                elseif ( '' !== $sale_price && $date_from && strtotime( $date_from ) <= strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
                    update_post_meta( $post_id, '_price', wc_format_decimal( $sale_price ) );
                }

                else {
                    update_post_meta( $post_id, '_price', '' === $regular_price ? '' : wc_format_decimal( $regular_price ) );
                }

                if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
                    update_post_meta( $post_id, '_price', '' === $regular_price ? '' : wc_format_decimal( $regular_price ) );
                }

                // Thumbnail
                if ( isset( $array['attach_id'] ) && $array['attach_id'] > 0 ) {
                    $attach_id = $array['attach_id'];
                    set_post_thumbnail( $post_id, $attach_id );
                }

                else {
                    delete_post_thumbnail( $post_id );
                }
                // Gallery
                $product_gallery = ! empty( $array['product_gallery'] ) ? implode( ',', $array['product_gallery'] ) : '';
	            update_post_meta( $post_id, '_product_image_gallery' , $product_gallery );

                //Save Taxonomy
                if( ! empty( $_POST['tax_input'] ) ){
                    $tax_input = $_POST['tax_input'];
                    $taxonomies = array_keys( $tax_input );
                    foreach ( $taxonomies as $taxonomy ){
                        if( ! empty( $tax_input[ $taxonomy ] ) && is_array( $tax_input[ $taxonomy ] ) ){
                            $terms = array();
                            foreach( $tax_input[ $taxonomy ] as $tt_id ){
                                if( ! empty( $tt_id )  ){
                                    $term = get_term_by( 'id', $tt_id, $taxonomy, ARRAY_A );
                                    if( ! is_wp_error( $term ) ){
                                        $terms[] = $term['slug'];
                                    }
                                }
                            }
                            wp_set_object_terms( $post_id, $terms, $taxonomy, false );
                        }
                    }
                }

                if( ! empty( $array['_product_url'] ) ){
                    update_post_meta( $post_id, '_product_url', $array['_product_url'] );
                }

                if( ! empty( $array['_button_text'] ) ){
                    update_post_meta( $post_id, '_button_text', $array['_button_text'] );
                }

                if( ! empty( $array['upsell_ids'] ) ){
                    update_post_meta( $post_id, '_upsell_ids', $array['upsell_ids'] );
                }

                if( ! empty( $array['crosssell_ids'] ) ){
                    update_post_meta( $post_id, '_crosssell_ids', $array['crosssell_ids'] );
                }

                //Downlodable
                $_downlodable = isset( $array['_downloadable'] ) ? 'yes' : 'no';
                update_post_meta( $post_id, '_downloadable', $_downlodable  );

                if( 'yes' == $_downlodable ){
                    $downloads      = array();
                    $file_names     = isset( $_POST['_wc_file_names'] ) ? $_POST['_wc_file_names'] : array();
                    $file_urls      = isset( $_POST['_wc_file_urls'] ) ? $_POST['_wc_file_urls'] : array();
                    $file_hashes    = isset( $_POST['_wc_file_hashes'] ) ? $_POST['_wc_file_hashes'] : array();

                    if ( ! empty( $file_urls ) ) {
                        $file_url_size = sizeof( $file_urls );

                        for ( $i = 0; $i < $file_url_size; $i ++ ) {
                            if ( ! empty( $file_urls[ $i ] ) ) {
                                $downloads[] = array(
                                    'name'          => wc_clean( $file_names[ $i ] ),
                                    'file'          => wp_unslash( trim( $file_urls[ $i ] ) ),
                                    'previous_hash' => wc_clean( $file_hashes[ $i ] ),
                                );
                            }
                        }
                    }

                    update_post_meta( $post_id, '_downloadable_files', $downloads );

                    if( ! empty( $array['_download_limit'] ) ){
                        update_post_meta( $post_id, '_download_limit', $array['_download_limit'] );
                    }

                    if( ! empty( $array['_download_expiry'] ) ){
                        update_post_meta( $post_id, '_download_expiry', $array['_download_expiry'] );
                    }
                }

                $attributes   = WC_Meta_Box_Product_Data::prepare_attributes();

                $post = get_post( $post_id );

                $action = $is_new ? 'created' : 'updated';

                do_action( "yith_wcfm_product_{$action}", $post_id, $post );
                do_action( "yith_wcfm_product_save", $post_id, $post );

                return $post_id;
            }

            return false;

        }

        public static function printProductsIdSelect2( $name , $values ){

            ?>

            <input type="hidden" class="wc-product-search" style="width: 350px;" id="<?php echo $name; ?>" name="<?php echo $name; ?>"
                   data-placeholder="<?php esc_attr_e( 'Applied to...', 'yith-woocommerce-product-add-ons' ); ?>"
                   data-action="woocommerce_json_search_products"
                   data-multiple="true"
                   data-exclude=""
                   data-selected="<?php

                   $product_ids = array_filter( array_map( 'absint', explode( ',', $values ) ) );
                   $json_ids    = array();

                   foreach ( $product_ids as $product_id ) {
                       $product = wc_get_product( $product_id );
                       if ( is_object( $product ) ) {
                           $json_ids[ $product_id ] = wp_kses_post( html_entity_decode( $product->get_formatted_name(), ENT_QUOTES, get_bloginfo( 'charset' ) ) );
                       }
                   }

                   echo esc_attr( json_encode( $json_ids ) );
                   ?>"
                   value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>"
            />

            <?php

        }

        /**
         * Get select for product status
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0
         * @return array product allowed status
         */
        public static function get_product_status(){
            $product_status = array(
                'publish'   => __('Published', 'yith-frontend-manager-for-woocommerce'),
                'pending'   => __('Pending review', 'yith-frontend-manager-for-woocommerce'),
                'draft'     => __('Draft', 'yith-frontend-manager-for-woocommerce')
            );
            return apply_filters( 'yith_wcfm_allowed_product_status', $product_status );
        }

        /**
         * Delete product post type
         *
         * @param $product_id
         * @return bool
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0.0
         */
        public static function delete( $product_id ){
	        $product      = wc_get_product( $product_id );
	        $force_delete = apply_filters( 'yith_wcfm_force_delete_product', false );
	        $check        = $product instanceof WC_Product ? $product->delete( $force_delete ) : false;

            if( $check ){
                $message = _x( 'Product deleted successfully', '[Frontend]: user message', 'yith-frontend-manager-for-woocommerce' );
                $type    = 'success';
            }

            else {
                $message = _x( 'Product does not exist', '[Frontend]: user message', 'yith-frontend-manager-for-woocommerce' );
                $type = 'error';
            }

            wc_add_notice( $message, $type );
        }

	    /**
	     * Support for YITH WooCommerce color and label variations
	     */
        public function add_support_for_color_and_label_variations(){
	        if( function_exists( 'YITH_WCCL_Admin' ) && ! empty( YITH_Frontend_Manager()->gui ) ){
		        $yith_wccl_admin = YITH_WCCL_Admin();
		        if( ! empty( $yith_wccl_admin ) ){
			        // product attribute taxonomies
			        remove_action( 'init', array( $yith_wccl_admin, 'attribute_taxonomies' ) );
			        add_action( 'init', array( $yith_wccl_admin, 'attribute_taxonomies' ), 25 );

			        // enqueue style and scripts
			        remove_action( 'admin_enqueue_scripts', array( $yith_wccl_admin, 'enqueue_scripts' ) );
			        add_action( 'wp_enqueue_scripts', array( $yith_wccl_admin, 'enqueue_scripts' ), 20 );

			        add_filter( 'yith_wccl_enqueue_admin_scripts', '__return_true' );
			        add_filter( 'yith_wccl_add_product_add_terms_form', '__return_false' );

			        // add term directly from product variation
			        remove_action( 'admin_footer', array( $yith_wccl_admin, 'product_option_add_terms_form' ) );
			        add_action( 'yith_wcmf_section_product', array( $yith_wccl_admin, 'product_option_add_terms_form' ) );
		        }
	        }
        }

	    /**
	     * Print an admin notice
	     *
	     * @author Andrea Grillo <andrea.grillo@yithemes.com>
	     * @since 1.3.3
	     * @return void
	     * @use admin_notices hooks
	     */
	    public function show_wc_notice( $message = 'success' ) {
            switch( $message ){
                case 'success':
                    $message = __( 'Product Saved', 'yith-woocommerce-product-vendors' );
                    $type = 'success';
                    break;

                case 'error':
                    $message = __( 'Unable to save product', 'yith-woocommerce-product-vendors' );
                    $type = 'error';
                    break;
            }

		    if( ! empty( $message ) ) {
			    wc_print_notice( $message, $type );
		    }
	    }
    }
}