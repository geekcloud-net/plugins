<?php
if ( !defined( 'YITH_WCBEP' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( !class_exists( 'YITH_WCBEP_List_Table_Premium' ) ) {
    /**
     * List table class
     *
     * @since    1.0.0
     * @author   Leanza Francesco <leanzafrancesco@gmail.com>
     */
    class YITH_WCBEP_List_Table_Premium extends WP_List_Table {

        public $columns;
        public $hidden;
        public $sortable;

        /**
         * used to show/hide variations in table
         *
         * @type bool
         * @since 1.1.4
         */
        public $show_variations = false;

        /**
         * Constructor
         *
         * @access public
         * @since  1.0.0
         */
        public function __construct( $columns = array(), $hidden = array(), $sortable = array() ) {
            global $status, $page;

            $this->columns  = $columns;
            $this->hidden   = $hidden;
            $this->sortable = $sortable;

            parent::__construct( array(
                                     'singular' => 'yith_wcbep_product',
                                     'plural'   => 'yith_wcbep_products',
                                     'ajax'     => true,
                                     'screen'   => 'yith-wcbep-product-list',
                                 ) );
        }

        static function get_default_columns() {
            $default = array(
                'cb'                 => '<input type="checkbox">',
                'show'               => '<span class="dashicons dashicons-admin-generic"></span><span class="dashicons dashicons-visibility"></span>',
                'ID'                 => __( 'ID', 'yith-woocommerce-bulk-product-editing' ),
                'title'              => __( 'Title', 'woocommerce' ),
                'slug'               => __( 'Slug', 'woocommerce' ),
                'image'              => __( 'Image', 'woocommerce' ),
                'image_gallery'      => __( 'Product Gallery', 'woocommerce' ),
                'description'        => __( 'Description', 'woocommerce' ),
                'shortdesc'          => __( 'Short Description', 'yith-woocommerce-bulk-product-editing' ),
                'regular_price'      => __( 'Regular Price', 'woocommerce' ),
                'sale_price'         => __( 'Sale Price', 'woocommerce' ),
                'purchase_note'      => __( 'Purchase Note', 'woocommerce' ),
                'categories'         => __( 'Categories', 'woocommerce' ),
                'tags'               => __( 'Tags', 'woocommerce' ),
                'sku'                => __( 'SKU', 'woocommerce' ),
                'weight'             => __( 'Weight', 'woocommerce' ),
                'height'             => __( 'Height', 'woocommerce' ),
                'width'              => __( 'Width', 'woocommerce' ),
                'length'             => __( 'Length', 'woocommerce' ),
                'stock_quantity'     => __( 'Stock Qty', 'woocommerce' ),
                'download_limit'     => __( 'Download Limit', 'woocommerce' ),
                'download_expiry'    => __( 'Download Expiry', 'woocommerce' ),
                'downloadable_files' => __( 'Downloadable Files', 'woocommerce' ),
                'menu_order'         => __( 'Menu Order', 'woocommerce' ),
                'stock_status'       => __( 'Stock status', 'woocommerce' ),
                'manage_stock'       => __( 'Manage Stock', 'woocommerce' ),
                'sold_individually'  => __( 'Sold Individually', 'woocommerce' ),
                'featured'           => __( 'Featured', 'woocommerce' ),
                'virtual'            => __( 'Virtual', 'woocommerce' ),
                'downloadable'       => __( 'Downloadable', 'woocommerce' ),
                'enable_reviews'     => __( 'Enable Reviews', 'woocommerce' ),
                'tax_status'         => __( 'Tax Status', 'woocommerce' ),
                'tax_class'          => __( 'Tax Class', 'woocommerce' ),
                'allow_backorders'   => __( 'Allow Backorders?', 'woocommerce' ),
                'shipping_class'     => __( 'Shipping class', 'woocommerce' ),
                'status'             => __( 'Status', 'woocommerce' ),
                'visibility'         => __( 'Catalog visibility', 'yith-woocommerce-bulk-product-editing' ),
                'download_type'      => __( 'Download Type', 'woocommerce' ),
                'prod_type'          => __( 'Product Type', 'woocommerce' ),
                'date'               => __( 'Date', 'yith-woocommerce-bulk-product-editing' ),
                'sale_price_from'    => __( 'Sale Price From', 'yith-woocommerce-bulk-product-editing' ),
                'sale_price_to'      => __( 'Sale Price To', 'yith-woocommerce-bulk-product-editing' ),
                'button_text'        => __( 'Button text', 'woocommerce' ),
                'product_url'        => __( 'Product URL', 'woocommerce' ),
                'up_sells'           => __( 'Up-Sells', 'woocommerce' ),
                'cross_sells'        => __( 'Cross-Sells', 'woocommerce' ),
            );
            // ATTRIBUTES
            $attribute_taxonomies = wc_get_attribute_taxonomies();
            if ( $attribute_taxonomies ) {
                foreach ( $attribute_taxonomies as $tax ) {
                    $attribute_taxonomy_name                       = wc_attribute_taxonomy_name( $tax->attribute_name );
                    $label                                         = $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name;
                    $default[ 'attr_' . $attribute_taxonomy_name ] = $label;
                }
            }

            return apply_filters( 'yith_wcbep_default_columns', $default );
        }

        static function get_enabled_default_columns() {
            $default = self::get_default_columns();
            $enabled = self::get_enabled_columns();

            $ever_enabled = array( 'cb', 'ID', 'show' );
            $enabled      = array_unique( array_merge( $ever_enabled, $enabled ) );

            $disabled = array_diff( array_keys( $default ), $enabled );

            foreach ( $disabled as $d ) {
                if ( isset( $default[ $d ] ) ) {
                    unset( $default[ $d ] );
                }
            }

            return $default;
        }

        public function get_columns() {
            $default = $this->get_enabled_default_columns();

            return !empty( $this->columns ) ? $this->columns : $default;
        }

        public function get_sortable() {
            $default = array(
                'ID'             => array( 'ID', false ),
                'title'          => array( 'title', false ),
                'regular_price'  => array( 'regular_price', false ),
                'sale_price'     => array( 'sale_price', false ),
                'date'           => array( 'date', false ),
                'weight'         => array( 'weight', false ),
                'height'         => array( 'height', false ),
                'width'          => array( 'width', false ),
                'length'         => array( 'length', false ),
                'stock_quantity' => array( 'stock_quantity', false ),
            );

            return !empty( $this->sortable ) ? $this->sortable : $default;
        }

        static function get_enabled_columns() {
            $enabled = get_option( 'yith_wcbep_enabled_columns' );
            if ( !is_array( $enabled ) ) {
                $enabled = array_keys( self::get_default_columns() );
            }

            return $enabled;
        }

        static function get_default_hidden() {
            $default = get_option( 'yith_wcbep_default_hidden_cols' );

            if ( !is_array( $default ) ) {
                // Set Defaults for first time!
                $default = array(
                    'ID',
                    'slug',
                    'image_gallery',
                    'shortdesc',
                    'purchase_note',
                    'sku',
                    'weight',
                    'height',
                    'width',
                    'length',
                    'stock_quantity',
                    'download_limit',
                    'download_expiry',
                    'downloadable_files',
                    'menu_order',
                    'stock_status',
                    'manage_stock',
                    'sold_individually',
                    'featured',
                    'virtual',
                    'downloadable',
                    'enable_reviews',
                    'tax_status',
                    'tax_class',
                    'allow_backorders',
                    'shipping_class',
                    'status',
                    'visibility',
                    'download_type',
                    'sale_price_from',
                    'sale_price_to',
                    'button_text',
                    'product_url',
                    'up_sells',
                    'cross_sells',
                );

                $attribute_taxonomies = wc_get_attribute_taxonomies();
                if ( $attribute_taxonomies ) {
                    foreach ( $attribute_taxonomies as $tax ) {
                        $attribute_taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );
                        $default[]               = 'attr_' . $attribute_taxonomy_name;
                    }
                }
            }

            return $default;
        }

        public function get_hidden() {
            $default = $this->get_default_hidden();

            return !empty( $this->hidden ) ? $this->hidden : $default;
        }

        /**
         * Generates content for a single row of the table
         *
         * @since  3.1.0
         * @access public
         *
         * @param object $item The current item
         */
        public function single_row( $item ) {
            echo '<tr>';
            $this->single_row_columns( $item );
            echo '</tr>';
            if ( $this->show_variations ) {
                $prod = wc_get_product( $item->ID );
                if ( $prod && $prod->is_type( 'variable' ) && $prod->has_child() ) {
                    $children = $prod->get_children();
                    foreach ( $children as $child ) {
                        $child_post = get_post( $child );
                        if ( $child_post ) {
                            echo '<tr>';
                            $this->single_row_columns( $child_post );
                            echo '</tr>';
                        }
                    }
                }
            }
        }

        public function prepare_items( $items = array() ) {
            $current_page = $this->get_pagenum();
            $per_page     = !empty( $_REQUEST[ 'f_per_page' ] ) && intval( $_REQUEST[ 'f_per_page' ] ) > 0 ? intval( $_REQUEST[ 'f_per_page' ] ) : 10;

            $columns  = $this->get_columns();
            $hidden   = $this->get_hidden();
            $sortable = $this->get_sortable();

            $this->_column_headers = array( $columns, $hidden, $sortable );

            /* ========================================= F I L T E R S ================================================ */
            $f_show_variations          = isset( $_REQUEST[ 'f_show_variations' ] ) ? $_REQUEST[ 'f_show_variations' ] : 'no';
            $f_sku_sel                  = !empty( $_REQUEST[ 'f_sku_select' ] ) ? $_REQUEST[ 'f_sku_select' ] : 'cont';
            $f_sku_val                  = isset( $_REQUEST[ 'f_sku_value' ] ) ? $_REQUEST[ 'f_sku_value' ] : '';
            $filtered_categories        = !empty( $_REQUEST[ 'f_categories' ] ) ? $_REQUEST[ 'f_categories' ] : array();
            $filtered_tags              = !empty( $_REQUEST[ 'f_tags' ] ) ? $_REQUEST[ 'f_tags' ] : array();
            $filtered_attributes        = !empty( $_REQUEST[ 'f_attributes' ] ) ? $_REQUEST[ 'f_attributes' ] : array();
            $filtered_brands            = !empty( $_REQUEST[ 'f_brands' ] ) ? $_REQUEST[ 'f_brands' ] : array();
            $filtered_custom_taxonomies = !empty( $_REQUEST[ 'f_custom_taxonomies' ] ) ? $_REQUEST[ 'f_custom_taxonomies' ] : array();
            $f_regular_price_sel        = !empty( $_REQUEST[ 'f_reg_price_select' ] ) ? $_REQUEST[ 'f_reg_price_select' ] : 'mag';
            $f_regular_price_val        = isset( $_REQUEST[ 'f_reg_price_value' ] ) ? $_REQUEST[ 'f_reg_price_value' ] : null;
            $f_sale_price_sel           = !empty( $_REQUEST[ 'f_sale_price_select' ] ) ? $_REQUEST[ 'f_sale_price_select' ] : 'mag';
            $f_sale_price_val           = isset( $_REQUEST[ 'f_sale_price_value' ] ) ? $_REQUEST[ 'f_sale_price_value' ] : null;
            $f_weight_sel               = !empty( $_REQUEST[ 'f_weight_select' ] ) ? $_REQUEST[ 'f_weight_select' ] : 'mag';
            $f_weight_val               = isset( $_REQUEST[ 'f_weight_value' ] ) ? $_REQUEST[ 'f_weight_value' ] : null;
            $f_stock_qty_sel            = !empty( $_REQUEST[ 'f_stock_qty_select' ] ) ? $_REQUEST[ 'f_stock_qty_select' ] : 'mag';
            $f_stock_qty_val            = isset( $_REQUEST[ 'f_stock_qty_value' ] ) ? $_REQUEST[ 'f_stock_qty_value' ] : null;
            $f_stock_status             = isset( $_REQUEST[ 'f_stock_status' ] ) ? $_REQUEST[ 'f_stock_status' ] : null;
            $f_product_type             = !empty( $_REQUEST[ 'f_product_type' ] ) ? $_REQUEST[ 'f_product_type' ] : false;
            $f_status                   = !empty( $_REQUEST[ 'f_status' ] ) ? $_REQUEST[ 'f_status' ] : false;

            /* =================================== E N D   F I L T E R S ============================================== */


            //$post_types = $f_show_variations != 'yes' ? 'product' : array( 'product', 'product_variation' );
            $order_by = !empty( $_REQUEST[ 'orderby' ] ) ? $_REQUEST[ 'orderby' ] : 'ID';

            /**
             * variations will be showed by function single_row( $item )
             * of this class after associated variable product
             *
             * @since 1.1.4
             */
            $this->show_variations = $f_show_variations == 'yes';
            $post_types            = 'product';

            $query_args = array(
                'post_type'           => $post_types,
                'post_status'         => !!$f_status ? $f_status : 'any',
                'posts_per_page'      => $per_page,
                'ignore_sticky_posts' => true,
                'paged'               => $current_page,
                'orderby'             => !empty( $_REQUEST[ 'orderby' ] ) ? $_REQUEST[ 'orderby' ] : 'ID',
                'order'               => !empty( $_REQUEST[ 'order' ] ) ? $_REQUEST[ 'order' ] : 'DESC',
            );


            if ( $f_product_type ) {
                $product_type_term        = get_term_by( 'slug', $f_product_type, 'product_type' );
                $posts_in                 = array_unique( (array) get_objects_in_term( $product_type_term->term_id, 'product_type' ) );
                $posts_in                 = sizeof( $posts_in ) > 0 ? $posts_in : array( 0 );
                $query_args[ 'post__in' ] = $posts_in;
            }

            $meta_query = array();

            switch ( $order_by ) {
                case 'regular_price':
                    $query_args[ 'orderby' ]  = 'meta_value_num';
                    $query_args[ 'meta_key' ] = '_regular_price';
                    break;
                case 'sale_price':
                    $query_args[ 'orderby' ]  = 'meta_value_num';
                    $query_args[ 'meta_key' ] = '_sale_price';
                    break;
                case 'weight':
                    $query_args[ 'orderby' ]  = 'meta_value_num';
                    $query_args[ 'meta_key' ] = '_weight';
                    break;
                case 'height':
                    $query_args[ 'orderby' ]  = 'meta_value_num';
                    $query_args[ 'meta_key' ] = '_height';
                    break;
                case 'width':
                    $query_args[ 'orderby' ]  = 'meta_value_num';
                    $query_args[ 'meta_key' ] = '_width';
                    break;
                case 'length':
                    $query_args[ 'orderby' ]  = 'meta_value_num';
                    $query_args[ 'meta_key' ] = '_length';
                    break;
                case 'stock_quantity':
                    $query_args[ 'orderby' ]  = 'meta_value_num';
                    $query_args[ 'meta_key' ] = '_stock';
                    break;
            }

            // Filter SKU
            if ( isset( $f_sku_val ) && strlen( $f_sku_val ) > 0 ) {
                $compare = 'LIKE';
                $value   = $f_sku_val;
                switch ( $f_sku_sel ) {
                    case 'cont':
                        $compare = 'LIKE';
                        break;
                    case 'notcont':
                        $compare = 'NOT LIKE';
                        break;
                    case 'starts':
                        $compare = 'REGEXP';
                        $value   = '^' . $f_sku_val;
                        break;
                    case 'ends':
                        $compare = 'REGEXP';
                        $value   = $f_sku_val . '$';
                        break;
                }

                $meta_query[] = array(
                    'key'     => '_sku',
                    'value'   => $value,
                    'compare' => $compare,
                );
            }

            // Filter Regular Price
            if ( isset( $f_regular_price_val ) && is_numeric( $f_regular_price_val ) ) {
                $compare = '>';
                $value   = $f_regular_price_val;
                switch ( $f_regular_price_sel ) {
                    case 'mag':
                        $compare = '>';
                        break;
                    case 'min':
                        $compare = '<';
                        break;
                    case 'ug':
                        $compare = '=';
                        break;
                    case 'magug':
                        $compare = '>=';
                        break;
                    case 'minug':
                        $compare = '<=';
                        break;
                }
                $meta_query[] = array(
                    array(
                        'relation' => 'AND',
                        array(
                            'key'     => '_regular_price',
                            'type'    => 'DECIMAL(10,5)',
                            'value'   => $value,
                            'compare' => $compare,
                        ),
                        array(
                            'relation' => 'OR',
                            array(
                                'key'     => '_regular_price',
                                'compare' => 'NOT EXISTS',
                            ),
                            array(
                                'key'     => '_price',
                                'type'    => 'DECIMAL(10,5)',
                                'value'   => $value,
                                'compare' => $compare,
                            ),
                        )
                    )
                );
            }

            // Filter Sale Price
            if ( isset( $f_sale_price_val ) && is_numeric( $f_sale_price_val ) ) {
                $compare = '>';
                $value   = $f_sale_price_val;
                switch ( $f_sale_price_sel ) {
                    case 'mag':
                        $compare = '>';
                        break;
                    case 'min':
                        $compare = '<';
                        break;
                    case 'ug':
                        $compare = '=';
                        break;
                    case 'magug':
                        $compare = '>=';
                        break;
                    case 'minug':
                        $compare = '<=';
                        break;
                }
                $meta_query[] = array(
                    array(
                        'relation' => 'AND',
                        array(
                            'key'     => '_sale_price',
                            'type'    => 'DECIMAL(10,5)',
                            'value'   => $value,
                            'compare' => $compare,
                        ),
                        array(
                            'relation' => 'OR',
                            array(
                                'key'     => '_sale_price',
                                'compare' => 'NOT EXISTS',
                            ),
                            array(
                                'key'     => '_price',
                                'type'    => 'DECIMAL(10,5)',
                                'value'   => $value,
                                'compare' => $compare,
                            ),
                        )
                    )
                );
            }

            // Filter Weight
            if ( isset( $f_weight_val ) && is_numeric( $f_weight_val ) ) {
                $compare = '>';
                $value   = $f_weight_val;
                switch ( $f_weight_sel ) {
                    case 'mag':
                        $compare = '>';
                        break;
                    case 'min':
                        $compare = '<';
                        break;
                    case 'ug':
                        $compare = '=';
                        break;
                    case 'magug':
                        $compare = '>=';
                        break;
                    case 'minug':
                        $compare = '<=';
                        break;
                }
                $meta_query[] = array(
                    'key'     => '_weight',
                    'type'    => 'NUMERIC',
                    'value'   => $value,
                    'compare' => $compare,
                );
            }

            // Filter Stock Qty
            if ( isset( $f_stock_qty_val ) && is_numeric( $f_stock_qty_val ) ) {
                $compare = '>';
                $value   = $f_stock_qty_val;
                switch ( $f_stock_qty_sel ) {
                    case 'mag':
                        $compare = '>';
                        break;
                    case 'min':
                        $compare = '<';
                        break;
                    case 'ug':
                        $compare = '=';
                        break;
                    case 'magug':
                        $compare = '>=';
                        break;
                    case 'minug':
                        $compare = '<=';
                        break;
                }
                $meta_query[] = array(
                    'key'     => '_stock',
                    'type'    => 'NUMERIC',
                    'value'   => $value,
                    'compare' => $compare,
                );
            }

            // Filter Stock Status
            if ( !empty( $f_stock_status ) ) {
                $meta_query[] = array(
                    'key'   => '_stock_status',
                    'value' => $f_stock_status,
                );
            }

            // Filter Categories
            if ( !empty( $filtered_categories ) ) {
                $query_args[ 'tax_query' ][ 'relation' ] = 'AND';
                $query_args[ 'tax_query' ][]             = array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $filtered_categories,
                    'operator' => 'IN',
                );
            }

            // Filter Brands
            if ( !empty( $filtered_brands ) ) {
                $yith_brands_taxonomy = class_exists( 'YITH_WCBR' ) && isset( YITH_WCBR::$brands_taxonomy ) ? YITH_WCBR::$brands_taxonomy : 'yith_product_brand';

                $query_args[ 'tax_query' ][ 'relation' ] = 'AND';
                $query_args[ 'tax_query' ][]             = array(
                    'taxonomy' => $yith_brands_taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $filtered_brands,
                    'operator' => 'IN',
                );
            }

            // Filter Custom Taxonomies
            if ( !empty( $filtered_custom_taxonomies ) ) {
                foreach ( $filtered_custom_taxonomies as $filtered_custom_taxonomy ) {
                    if ( !empty( $filtered_custom_taxonomy[ 'taxonomy' ] ) && !empty( $filtered_custom_taxonomy[ 'values' ] ) ) {
                        $query_args[ 'tax_query' ][ 'relation' ] = 'AND';
                        $query_args[ 'tax_query' ][]             = array(
                            'taxonomy' => $filtered_custom_taxonomy[ 'taxonomy' ],
                            'field'    => 'term_id',
                            'terms'    => $filtered_custom_taxonomy[ 'values' ],
                            'operator' => 'IN',
                        );
                    }
                }
            }

            // Filter Categories
            if ( !empty( $filtered_tags ) ) {
                $query_args[ 'tax_query' ][ 'relation' ] = 'AND';
                $query_args[ 'tax_query' ][]             = array(
                    'taxonomy' => 'product_tag',
                    'field'    => 'term_id',
                    'terms'    => $filtered_tags,
                    'operator' => 'IN',

                );
            }

            // Filter Attributes
            if ( !empty( $filtered_attributes ) ) {
                if ( !empty( $filtered_attributes[ 0 ] ) ) {

                    foreach ( $filtered_attributes as $attribute ) {
                        if ( !empty( $attribute[ 0 ] ) && !empty( $attribute[ 1 ] ) ) {
                            $attr_name = $attribute[ 0 ];
                            $attr_ids  = $attribute[ 1 ];

                            $query_args[ 'tax_query' ][ 'relation' ] = 'AND';
                            $query_args[ 'tax_query' ][]             = array(
                                'taxonomy' => $attr_name,
                                'field'    => 'id',
                                'terms'    => $attr_ids,
                            );
                        }
                    }
                }
            }

            if ( !empty( $meta_query ) ) {
                $query_args[ 'meta_query' ]               = $meta_query;
                $query_args[ 'meta_query' ][ 'relation' ] = 'AND';
            }

            $query_args = apply_filters( 'yith_wcbep_product_list_query_args', $query_args );

            add_filter( 'posts_where', 'yith_wcbep_posts_filter_where' );
            $p_query = new WP_Query( $query_args );
            remove_filter( 'posts_where', 'yith_wcbep_posts_filter_where' );

            $my_items = $p_query->posts;

            $this->items = $my_items;

            $this->set_pagination_args( array(
                                            'total_items'         => $p_query->found_posts,
                                            'per_page'            => $per_page,
                                            'total_pages'         => $p_query->max_num_pages,
                                            // Set ordering values if needed (useful for AJAX)
                                            'orderby'             => !empty( $_REQUEST[ 'orderby' ] ) && '' != $_REQUEST[ 'orderby' ] ? $_REQUEST[ 'orderby' ] : 'ID',
                                            'order'               => !empty( $_REQUEST[ 'order' ] ) && '' != $_REQUEST[ 'order' ] ? $_REQUEST[ 'order' ] : 'DESC',
                                            'f_title_select'      => !empty( $_REQUEST[ 'f_title_select' ] ) ? $_REQUEST[ 'f_title_select' ] : '',
                                            'f_title_value'       => !empty( $_REQUEST[ 'f_title_value' ] ) ? $_REQUEST[ 'f_title_value' ] : '',
                                            'f_sku_select'        => !empty( $_REQUEST[ 'f_sku_select' ] ) ? $_REQUEST[ 'f_sku_select' ] : '',
                                            'f_sku_value'         => !empty( $_REQUEST[ 'f_sku_value' ] ) ? $_REQUEST[ 'f_sku_value' ] : '',
                                            'f_categories'        => !empty( $_REQUEST[ 'f_categories' ] ) ? $_REQUEST[ 'f_categories' ] : '',
                                            'f_tags'              => !empty( $_REQUEST[ 'f_tags' ] ) ? $_REQUEST[ 'f_tags' ] : '',
                                            'f_attributes'        => !empty( $_REQUEST[ 'f_attributes' ] ) ? $_REQUEST[ 'f_attributes' ] : '',
                                            'f_reg_price_select'  => !empty( $_REQUEST[ 'f_reg_price_select' ] ) ? $_REQUEST[ 'f_reg_price_select' ] : '',
                                            'f_reg_price_value'   => !empty( $_REQUEST[ 'f_reg_price_value' ] ) ? $_REQUEST[ 'f_reg_price_value' ] : '',
                                            'f_sale_price_select' => !empty( $_REQUEST[ 'f_sale_price_select' ] ) ? $_REQUEST[ 'f_sale_price_select' ] : '',
                                            'f_sale_price_value'  => !empty( $_REQUEST[ 'f_sale_price_value' ] ) ? $_REQUEST[ 'f_sale_price_value' ] : '',
                                            'f_weight_select'     => !empty( $_REQUEST[ 'f_weight_select' ] ) ? $_REQUEST[ 'f_weight_select' ] : '',
                                            'f_weight_value'      => !empty( $_REQUEST[ 'f_weight_value' ] ) ? $_REQUEST[ 'f_weight_value' ] : '',
                                            'f_per_page'          => !empty( $_REQUEST[ 'f_per_page' ] ) ? $_REQUEST[ 'f_per_page' ] : '',
                                            'f_show_variations'   => !empty( $_REQUEST[ 'f_show_variations' ] ) ? $_REQUEST[ 'f_show_variations' ] : '',
                                            'f_product_type'      => !empty( $_REQUEST[ 'f_product_type' ] ) ? $_REQUEST[ 'f_product_type' ] : '',
                                        ) );
        }

        function column_default( $item, $column_name ) {
            $r               = '';
            $var_start       = '';
            $var_end         = '';
            $product_id      = $item->ID;
            $product         = wc_get_product( $product_id );
            $base_product_id = yit_get_base_product_id( $product );

            if ( $product->is_type( 'variation' ) ) {
                $var_start = '<div class="not_editable">';
                $var_end   = '</div>';
            }

            switch ( $column_name ) {
                case 'ID':
                    $r = $product_id;
                    break;
                case 'show':
                    $edit_link    = get_edit_post_link( $base_product_id );
                    $product_link = get_the_permalink( $base_product_id );

                    $r = '<a href="' . $edit_link . '" target="_blank"><span class="dashicons dashicons-admin-generic"></span></a>';
                    $r .= '<a href="' . $product_link . '" target="_blank"><span class="dashicons dashicons-visibility"></span></a>';
                    break;
                case 'sku':
                    $r = $product instanceof WC_Data ? yit_get_prop( $product, 'sku', true, 'edit' ) : get_post_meta( $product_id, '_sku', true );
                    break;
                case 'title':
                    if ( !$product->is_type( 'variation' ) ) {
                        $r = $item->post_title;
                    } else {
                        $post            = get_post( $base_product_id );
                        $my_parent_title = $post->post_title;
                        $r               = $my_parent_title . ' [#' . ( $product instanceof WC_Data ? $product->get_id() : $product->variation_id ) . ']';
                    }
                    break;
                case 'slug':
                    $r = $item->post_name;
                    break;
                case 'image':
                    $thumb_id  = get_post_thumbnail_id( $product_id ) ? get_post_thumbnail_id( $product_id ) : '';
                    $image     = wp_get_attachment_image_src( $thumb_id, 'thumbnail' );
                    $image_src = '';
                    if ( $image ) {
                        list( $src, $width, $height ) = $image;
                        $image_src = $src;
                    }
                    $r = '<img src="' . $image_src . '" />';
                    $r .= '<input class="yith-wcbep-hidden-image-value" type="hidden" value="' . $thumb_id . '" />';
                    break;
                case 'image_gallery':
                    $image_gallery = $product instanceof WC_Data ? $product->get_gallery_image_ids() : $product->get_gallery_attachment_ids();
                    $r             = '<div class="yith-wcbep-table-image-gallery">';
                    if ( count( $image_gallery ) > 0 ) {
                        foreach ( $image_gallery as $img_id ) {
                            $image = wp_get_attachment_image_src( $img_id, 'thumbnail' );
                            if ( $image ) {
                                list( $src, $width, $height ) = $image;
                                $r .= '<img data-image-id="' . $img_id . '" src="' . $src . '" />';
                            }
                        }
                    }
                    $r .= '</div>';
                    break;
                case 'downloadable_files':
                    $downloadable_files = $product instanceof WC_Data ? $product->get_meta( '_downloadable_files', true ) : get_post_meta( $product_id, '_downloadable_files', true );
                    $count_file         = 0;
                    if ( is_array( $downloadable_files ) && !empty( $downloadable_files ) ) {
                        foreach ( $downloadable_files as $file ) {
                            $count_file++;
                            $r .= '<input type="hidden" class="yith-wcbep-hidden-downloadable-file" data-file-name="' . $file[ 'name' ] . '" data-file-url="' . $file[ 'file' ] . '" />';
                        }
                    }
                    if ( $count_file > 0 ) {
                        $r .= sprintf( _n( '1 ' . __( 'file', 'yith-woocommerce-bulk-product-editing' ), '%s ' . __( 'files', 'yith-woocommerce-bulk-product-editing' ), $count_file, 'yith-woocommerce-bulk-product-editing' ), $count_file );
                    }
                    break;

                case 'description':
                    $r = htmlspecialchars( $item->post_content );
                    break;

                case 'shortdesc':
                    $r = htmlspecialchars( $item->post_excerpt );
                    break;
                case 'regular_price':
                case 'sale_price':
                    $to_return = array();
                    if ( $product->is_type( 'variable' ) ) {
                        $prices = $product->get_variation_prices();
                        $rp_min = !empty( $prices[ 'regular_price' ] ) ? current( $prices[ 'regular_price' ] ) : '';
                        $rp_max = !empty( $prices[ 'regular_price' ] ) ? end( $prices[ 'regular_price' ] ) : '';
                        $rp     = '';
                        if ( $rp_min != $rp_max )
                            $rp = wc_price( $rp_min ) . ' - ' . wc_price( $rp_max );
                        else if ( $rp_min > 0 )
                            $rp = wc_price( $rp_min );
                        $sp_min = !empty( $prices[ 'sale_price' ] ) ? current( $prices[ 'sale_price' ] ) : '';
                        $sp_max = !empty( $prices[ 'sale_price' ] ) ? end( $prices[ 'sale_price' ] ) : '';
                        $sp     = '';
                        if ( $sp_min != $sp_max )
                            $sp = wc_price( $sp_min ) . ' - ' . wc_price( $sp_max ); else if ( $sp_min > 0 )
                            $sp = wc_price( $sp_min );

                        $to_return[ 'regular_price' ] = '<div class="not_editable">' . $rp . '</div>';
                        $to_return[ 'sale_price' ]    = '<div class="not_editable">' . $sp . '</div>';
                    } else {
                        $to_return[ $column_name ] = yit_get_prop( $product, $column_name, true, 'edit' );
                    }
                    $r = $to_return[ $column_name ];
                    break;
                case 'stock_quantity':
                    if ( !$product->is_type( 'variation' ) ) {
                        $r = $product->get_stock_quantity();
                    } else {
                        $r = $product instanceof WC_Data ? $product->get_stock_quantity( 'edit' ) : $product->stock;
                    }
                    break;
                case 'weight':
                case 'height':
                case 'width' :
                case 'length':
                case 'purchase_note':
                case 'download_limit':
                case 'download_expiry':
                    $r = yit_get_prop( $product, '_' . $column_name, true, 'edit' );
                    if ( 'purchase_note' === $column_name )
                        $r = htmlspecialchars( $r );
                    break;
                case 'menu_order':
                    if ( $product instanceof WC_Data ) {
                        $r = yit_get_prop( $product, '_' . $column_name, true, 'edit' );
                    } else {
                        $r = $product->get_post_data()->menu_order;
                    }
                    break;
                case 'cross_sells':
                    $db_key = 'cross_sell_ids';
                    $value  = yit_get_prop( $product, '_' . $db_key, true, 'edit' );
                    $r      = !!$value ? implode( ', ', $value ) : '';
                    break;
                case 'up_sells':
                    $db_key = 'upsell_ids';
                    $value  = yit_get_prop( $product, '_' . $db_key, true, 'edit' );
                    $r      = !!$value ? implode( ', ', $value ) : '';
                    break;
                case 'stock_status':
                case 'manage_stock':
                case 'sold_individually':
                case 'featured':
                case 'virtual':
                case 'downloadable':
                    $checked_values = array(
                        'stock_status'      => 'instock',
                        'manage_stock'      => 'yes',
                        'sold_individually' => 'yes',
                        'featured'          => 'yes',
                        'virtual'           => 'yes',
                        'downloadable'      => 'yes',
                    );
                    $value          = yit_get_prop( $product, '_' . $column_name, true, 'edit' );
                    $r              = '<input class="yith-wcbep-editable-checkbox" type="checkbox" ' . ( ( $value == $checked_values[ $column_name ] ) ? 'checked="checked"' : '' ) . '/> <input type="hidden" class="yith-wcbep-hidden-checkbox-value" value="' . ( ( $value == $checked_values[ $column_name ] ) ? '1' : '0' ) . '"/>';
                    break;
                case 'enable_reviews':
                    $value = $item->comment_status;
                    $r     = '<input class="yith-wcbep-editable-checkbox" type="checkbox" ' . ( ( $value == 'open' ) ? 'checked="checked"' : '' ) . '/> <input type="hidden" class="yith-wcbep-hidden-checkbox-value" value="' . ( ( $value == 'open' ) ? '1' : '0' ) . '"/>';
                    break;
                case 'tax_status':
                    $value = yit_get_prop( $product, '_' . $column_name, true, 'edit' );
                    $r
                           = '<select class="yith-wcbep-editable-select">
                            <option value="taxable" ' . ( ( $value == 'taxable' ) ? 'selected' : '' ) . '>' . __( 'Taxable', 'woocommerce' ) . '</option>
                            <option value="shipping" ' . ( ( $value == 'shipping' ) ? 'selected' : '' ) . '>' . __( 'Shipping only', 'woocommerce' ) . '</option>
                            <option value="none" ' . ( ( $value == 'none' ) ? 'selected' : '' ) . '>' . _x( 'None', 'Tax status', 'woocommerce' ) . '</option>
                          </select>';
                    $r     .= '<input type="hidden" class="yith-wcbep-hidden-select-value" value="' . $value . '"/>';
                    break;

                case 'tax_class':
                    $db_value = yit_get_prop( $product, '_' . $column_name, true, 'edit' );

                    $tax_classes           = WC_Tax::get_tax_classes();
                    $classes_options       = array();
                    $classes_options[ '' ] = __( 'Standard', 'woocommerce' );
                    if ( $tax_classes ) {
                        foreach ( $tax_classes as $class ) {
                            $classes_options[ sanitize_title( $class ) ] = esc_html( $class );
                        }
                    }
                    $r = '<select class="yith-wcbep-editable-select">';
                    foreach ( $classes_options as $key => $value ) {
                        $r .= '<option value="' . $key . '" ' . ( ( $db_value == $key ) ? 'selected' : '' ) . '>' . $value . '</option>';
                    }
                    $r .= '</select>';
                    $r .= '<input type="hidden" class="yith-wcbep-hidden-select-value" value="' . $db_value . '"/>';
                    break;

                case 'allow_backorders':
                    $value = yit_get_prop( $product, '_backorders', true, 'edit' );

                    $r
                        = '<select class="yith-wcbep-editable-select">
                            <option value="no" ' . ( ( $value == 'no' ) ? 'selected' : '' ) . '>' . __( 'Do not allow', 'woocommerce' ) . '</option>
                            <option value="notify" ' . ( ( $value == 'notify' ) ? 'selected' : '' ) . '>' . __( 'Allow, but notify customer', 'woocommerce' ) . '</option>
                            <option value="yes" ' . ( ( $value == 'yes' ) ? 'selected' : '' ) . '>' . __( 'Allow', 'woocommerce' ) . '</option>
                          </select>';
                    $r  .= '<input type="hidden" class="yith-wcbep-hidden-select-value" value="' . $value . '"/>';
                    break;
                case 'shipping_class':
                    $current_shipping_class = '';
                    $classes                = get_the_terms( $product_id, 'product_shipping_class' );
                    if ( $classes && !is_wp_error( $classes ) ) {
                        $current_shipping_class = current( $classes )->term_id;
                    }
                    $args = array(
                        'taxonomy'         => 'product_shipping_class',
                        'hide_empty'       => 0,
                        'show_option_none' => __( 'No shipping class', 'woocommerce' ),
                        'name'             => 'product_shipping_class',
                        'id'               => 'product_shipping_class',
                        'selected'         => $current_shipping_class,
                        'class'            => 'yith-wcbep-editable-select select short',
                    );

                    ob_start();
                    wp_dropdown_categories( $args );
                    $r                      = ob_get_clean();
                    $current_shipping_class = ( $current_shipping_class > 0 ) ? $current_shipping_class : -1;
                    $r                      .= '<input type="hidden" class="yith-wcbep-hidden-select-value" value="' . $current_shipping_class . '"/>';
                    break;
                case 'status':
                    $statuses = get_post_statuses();
                    $status   = get_post_status( $product_id );
                    $r        = '<select class="yith-wcbep-editable-select">';
                    foreach ( $statuses as $key => $value ) {
                        $r .= '<option value="' . $key . '" ' . ( ( $status == $key ) ? 'selected' : '' ) . '>' . $value . '</option>';
                    }
                    $r .= '</select>';
                    $r .= '<input type="hidden" class="yith-wcbep-hidden-select-value" value="' . $status . '"/>';
                    break;
                case 'visibility':
                    $visibility_options = apply_filters( 'woocommerce_product_visibility_options', array(
                        'visible' => __( 'Catalog/search', 'woocommerce' ),
                        'catalog' => __( 'Catalog', 'woocommerce' ),
                        'search'  => __( 'Search', 'woocommerce' ),
                        'hidden'  => __( 'Hidden', 'woocommerce' ),
                    ) );
                    $r                  = '<select class="yith-wcbep-editable-select">';

                    $visibility = yit_get_prop( $product, '_catalog_visibility', true, 'edit' );
                    foreach ( $visibility_options as $key => $value ) {
                        $r .= '<option value="' . $key . '" ' . ( ( $visibility == $key ) ? 'selected' : '' ) . '>' . $value . '</option>';
                    }
                    $r .= '</select>';
                    $r .= '<input type="hidden" class="yith-wcbep-hidden-select-value" value="' . $visibility . '"/>';
                    break;

                case 'download_type':
                    $download_types = array(
                        ''            => __( 'Standard Product', 'woocommerce' ),
                        'application' => __( 'Application/Software', 'woocommerce' ),
                        'music'       => __( 'Music', 'woocommerce' ),
                    );
                    $r              = '<select class="yith-wcbep-editable-select">';
                    $download_type  = yit_get_prop( $product, '_download_type', true, 'edit' );

                    foreach ( $download_types as $key => $value ) {
                        $r .= '<option value="' . $key . '" ' . ( ( $download_type == $key ) ? 'selected' : '' ) . '>' . $value . '</option>';
                    }
                    $r .= '</select>';
                    $r .= '<input type="hidden" class="yith-wcbep-hidden-select-value" value="' . $download_type . '"/>';
                    break;
                case 'prod_type':
                    if ( $product->is_type( 'variation' ) ) {
                        $r = $var_start . __( 'Variation', 'yith-woocommerce-bulk-product-editing' ) . $var_end;
                    } else {
                        $product_type          = $product instanceof WC_Data ? $product->get_type() : $product->product_type;
                        $product_type_selector = apply_filters( 'product_type_selector', array(
                            'simple'   => __( 'Simple product', 'woocommerce' ),
                            'grouped'  => __( 'Grouped product', 'woocommerce' ),
                            'external' => __( 'External/Affiliate product', 'woocommerce' ),
                            'variable' => __( 'Variable product', 'woocommerce' ),
                        ), $product_type );
                        $r                     = '<select class="yith-wcbep-editable-select">';
                        foreach ( $product_type_selector as $key => $value ) {
                            $r .= '<option value="' . $key . '" ' . ( ( $product_type == $key ) ? 'selected' : '' ) . '>' . $value . '</option>';
                        }
                        $r .= '</select>';
                        $r .= '<input type="hidden" class="yith-wcbep-hidden-select-value" value="' . $product_type . '"/>';
                    }
                    break;
                case 'sale_price_from':
                    $value = yit_get_prop( $product, '_sale_price_dates_from', true, 'edit' );
                    $r     = !!$value ? date_i18n( 'Y/m/d', yit_datetime_to_timestamp( $value ) ) : '';
                    break;
                case 'sale_price_to':
                    $value = yit_get_prop( $product, '_sale_price_dates_to', true, 'edit' );
                    $r     = !!$value ? date_i18n( 'Y/m/d', yit_datetime_to_timestamp( $value ) ) : '';
                    break;
                case 'button_text':
                case 'product_url':
                    $r = yit_get_prop( $product, '_' . $column_name, true, 'edit' );
                    break;

                case 'categories':
                    // CATEGORIES
                    $cats       = get_the_terms( $product_id, 'product_cat' );
                    $cats       = !empty( $cats ) ? $cats : array();
                    $cats_html  = '';
                    $loop       = 0;
                    $my_cats_id = array();
                    foreach ( $cats as $c ) {
                        $loop++;
                        $cats_html .= $c->name;
                        if ( $loop < count( $cats ) ) {
                            $cats_html .= ', ';
                        }
                        $my_cats_id[] = $c->term_id;
                    }

                    $r = '<div class="yith-wcbep-select-values">' . $cats_html . '</div> <input class="yith-wcbep-select-selected" type="hidden" value="' . json_encode( $my_cats_id ) . '">';
                    break;

                case 'tags':
                    $tags       = get_the_terms( $product_id, 'product_tag' );
                    $tags       = !empty( $tags ) ? $tags : array();
                    $tags_html  = '';
                    $loop       = 0;
                    $my_tags_id = array();
                    foreach ( $tags as $t ) {
                        $loop++;
                        $tags_html .= $t->name;
                        if ( $loop < count( $tags ) ) {
                            $tags_html .= ', ';
                        }
                        $my_tags_id[] = $t->term_id;
                    }
                    $r = $tags_html;
                    break;

                case 'date':
                    $r = date_i18n( 'Y/m/d', strtotime( $item->post_date ) );
                    break;

                default:
                    switch ( true ) {
                        case ( substr( $column_name, 0, 8 ) == 'attr_pa_' ):
                            // ATTRIBUTES
                            $attribute_taxonomies = wc_get_attribute_taxonomies();
                            if ( $attribute_taxonomies ) {
                                foreach ( $attribute_taxonomies as $tax ) {
                                    $attribute_taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );
                                    if ( $column_name == 'attr_' . $attribute_taxonomy_name ) {
                                        if ( !$product->is_type( 'variation' ) ) {
                                            $r = '<div class="yith-wcbep-select-values"></div> <input class="yith-wcbep-select-selected" type="hidden" value="[]">';
                                            $r .= '<input class="yith-wcbep-attr-is-visible" type="hidden" value="0">';
                                            $r .= '<input class="yith-wcbep-attr-is-variation" type="hidden" value="0">';
                                        } else {
                                            $r = '<div class="yith-wcbep-select-values"></div> <input class="yith-wcbep-select-selected" type="hidden" value="[]">';
                                            $r .= '<input class="yith-wcbep-attr-is-visible" type="hidden" value="-1">';
                                            $r .= '<input class="yith-wcbep-attr-is-variation" type="hidden" value="-1">';
                                        }
                                    }
                                }
                            }

                            $attributes = $product->get_attributes();

                            if ( !empty( $attributes ) ) {
                                foreach ( $attributes as $a => $value ) {
                                    if ( $column_name != 'attr_' . $a )
                                        continue;

                                    $my_att    = array();
                                    $t_html    = '';
                                    $attribute = $value;

                                    if ( $product->is_type( 'variation' ) ) {
                                        $t_html_array   = array();
                                        $variation_data = $product->get_variation_attributes();
                                        if ( isset( $variation_data[ 'attribute_' . $a ] ) ) {
                                            $my_attributes = explode( ', ', $variation_data[ 'attribute_' . $a ] );
                                            if ( count( $my_attributes ) > 0 ) {
                                                foreach ( $my_attributes as $att_v ) {
                                                    $t = get_term_by( 'slug', $att_v, $a );
                                                    if ( $t ) {
                                                        $my_att[]       = $t->term_id;
                                                        $t_html_array[] = $t->name;
                                                    }
                                                }
                                            }
                                            $t_html = implode( ', ', $t_html_array );
                                        }
                                    } else {
                                        $t_html = $product->get_attribute( $a );

                                        $t = wc_get_product_terms( $product_id, $attribute[ 'name' ], array( 'fields' => 'ids' ) );
                                        if ( count( $t ) > 0 ) {
                                            foreach ( $t as $num ) {
                                                $my_att[] = intval( $num );
                                            }
                                        }
                                    }

                                    if ( !$product->is_type( 'variation' ) ) {
                                        $r = '<input class="yith-wcbep-attr-is-visible" type="hidden" value="' . $attribute[ 'is_visible' ] . '">';
                                        $r .= '<input class="yith-wcbep-attr-is-variation" type="hidden" value="' . $attribute[ 'is_variation' ] . '">';
                                    } else {
                                        $r = '<input class="yith-wcbep-attr-is-visible" type="hidden" value="-1">';
                                        $r .= '<input class="yith-wcbep-attr-is-variation" type="hidden" value="-1">';
                                    }
                                    $r .= '<div class="yith-wcbep-select-values">' . $t_html . '</div> <input class="yith-wcbep-select-selected" type="hidden" value="' . json_encode( $my_att ) . '">';
                                    break;
                                }
                            }
                            break;
                        default:
                            $r = $column_name;
                    }
            }

            $r = apply_filters( 'yith_wcbep_manage_custom_columns', $r, $column_name, $item );

            // VARIATION --------------------------
            if ( strlen( $var_start ) > 0 ) {
                $variation_not_editable = apply_filters( 'yith_wcbep_variation_not_editable', array(
                    'title',
                    'slug',
                ) );

                if ( in_array( $column_name, $variation_not_editable ) )
                    $r = $var_start . $r . $var_end;

                $variation_not_editable_and_empty = apply_filters( 'yith_wcbep_variation_not_editable_and_empty', array(
                    'description',
                    'shortdesc',
                    'purchase_note',
                    'menu_order',
                    'up_sells',
                    'cross_sells',
                    'sold_individually',
                    'enable_reviews',
                    'status',
                    'visibility',
                    'button_text',
                    'product_url',
                    'categories',
                    'tags',
                    'date',
                    'featured',
                    'tax_status',
                    'download_type',
                    'image_gallery',
                ) );
                if ( in_array( $column_name, $variation_not_editable_and_empty ) )
                    $r = $var_start . $var_end;
            }

            return $r;
        }

        function column_cb( $item ) {
            return sprintf( '<input type="checkbox" value="%s" />', $item->ID );
        }

        public function print_column_headers( $with_id = true ) {
            list( $columns, $hidden, $sortable ) = $this->get_column_info();

            $current_url = set_url_scheme( admin_url() . '?page=yith_wcbep_panel' );
            $current_url = remove_query_arg( 'paged', $current_url );

            $f_title_select      = !empty( $_REQUEST[ 'f_title_select' ] ) ? $_REQUEST[ 'f_title_select' ] : '';
            $f_title_value       = !empty( $_REQUEST[ 'f_title_value' ] ) ? $_REQUEST[ 'f_title_value' ] : '';
            $f_sku_select        = !empty( $_REQUEST[ 'f_sku_select' ] ) ? $_REQUEST[ 'f_sku_select' ] : '';
            $f_sku_value         = !empty( $_REQUEST[ 'f_sku_value' ] ) ? $_REQUEST[ 'f_sku_value' ] : '';
            $f_categories        = !empty( $_REQUEST[ 'f_categories' ] ) ? $_REQUEST[ 'f_categories' ] : '';
            $f_tags              = !empty( $_REQUEST[ 'f_tags' ] ) ? $_REQUEST[ 'f_tags' ] : '';
            $f_attributes        = !empty( $_REQUEST[ 'f_attributes' ] ) ? $_REQUEST[ 'f_attributes' ] : '';
            $f_reg_price_select  = !empty( $_REQUEST[ 'f_reg_price_select' ] ) ? $_REQUEST[ 'f_reg_price_select' ] : '';
            $f_reg_price_value   = !empty( $_REQUEST[ 'f_reg_price_value' ] ) ? $_REQUEST[ 'f_reg_price_value' ] : '';
            $f_sale_price_select = !empty( $_REQUEST[ 'f_sale_price_select' ] ) ? $_REQUEST[ 'f_sale_price_select' ] : '';
            $f_sale_price_value  = !empty( $_REQUEST[ 'f_sale_price_value' ] ) ? $_REQUEST[ 'f_sale_price_value' ] : '';
            $f_weight_select     = !empty( $_REQUEST[ 'f_weight_select' ] ) ? $_REQUEST[ 'f_weight_select' ] : '';
            $f_weight_value      = !empty( $_REQUEST[ 'f_weight_value' ] ) ? $_REQUEST[ 'f_weight_value' ] : '';
            $f_per_page          = !empty( $_REQUEST[ 'f_per_page' ] ) ? $_REQUEST[ 'f_per_page' ] : '';
            $f_product_type      = !empty( $_REQUEST[ 'f_product_type' ] ) ? $_REQUEST[ 'f_product_type' ] : '';
            $f_show_variations   = !empty( $_REQUEST[ 'f_show_variations' ] ) ? $_REQUEST[ 'f_show_variations' ] : '';

            if ( isset( $_GET[ 'orderby' ] ) )
                $current_orderby = $_GET[ 'orderby' ]; else
                $current_orderby = 'ID';

            if ( isset( $_GET[ 'order' ] ) && 'desc' == $_GET[ 'order' ] )
                $current_order = 'desc'; else
                $current_order = 'asc';

            if ( !empty( $columns[ 'cb' ] ) ) {
                static $cb_counter = 1;
                $columns[ 'cb' ] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>' . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
                $cb_counter++;
            }

            foreach ( $columns as $column_key => $column_display_name ) {
                $class = array( 'manage-column', "column-$column_key" );

                $style = '';
                if ( in_array( $column_key, $hidden ) )
                    $style = 'display:none;';

                $style = ' style="' . $style . '"';

                if ( 'cb' == $column_key )
                    $class[] = 'check-column'; elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
                    $class[] = 'num';

                if ( isset( $sortable[ $column_key ] ) ) {
                    list( $orderby, $desc_first ) = $sortable[ $column_key ];

                    if ( $current_orderby == $orderby ) {
                        $order   = 'asc' == $current_order ? 'desc' : 'asc';
                        $class[] = 'sorted';
                        $class[] = $current_order;
                    } else {
                        $order   = $desc_first ? 'desc' : 'asc';
                        $class[] = 'sortable';
                        $class[] = $desc_first ? 'asc' : 'desc';
                    }

                    $column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
                }

                $id = $with_id ? "id='$column_key'" : '';

                if ( !empty( $class ) )
                    $class = "class='" . join( ' ', $class ) . "'";

                echo "<th scope='col' $id $class $style>$column_display_name</th>";
            }
        }

        public function display() {

            wp_nonce_field( 'ajax-yith-wcbep-list-nonce', '_ajax_yith_wcbep_list_nonce' );

            echo '<input id="order" type="hidden" name="order" value="' . $this->_pagination_args[ 'order' ] . '" />';
            echo '<input id="orderby" type="hidden" name="orderby" value="' . $this->_pagination_args[ 'orderby' ] . '" />';

            echo '<input id="total_pages" type="hidden" value="' . $this->_pagination_args[ 'total_pages' ] . '" />';

            echo '<input type="hidden" name="f_title_select" value="' . $this->_pagination_args[ 'f_title_select' ] . '" />';
            echo '<input type="hidden" name="f_title_value" value="' . $this->_pagination_args[ 'f_title_value' ] . '" />';
            echo '<input type="hidden" name="f_sku_select" value="' . $this->_pagination_args[ 'f_sku_select' ] . '" />';
            echo '<input type="hidden" name="f_sku_value" value="' . $this->_pagination_args[ 'f_sku_value' ] . '" />';
            echo '<input type="hidden" name="f_categories" value="' . $this->_pagination_args[ 'f_categories' ] . '" />';
            echo '<input type="hidden" name="f_tags" value="' . $this->_pagination_args[ 'f_tags' ] . '" />';
            echo '<input type="hidden" name="f_attributes" value="' . $this->_pagination_args[ 'f_attributes' ] . '" />';
            echo '<input type="hidden" name="f_reg_price_select" value="' . $this->_pagination_args[ 'f_reg_price_select' ] . '" />';
            echo '<input type="hidden" name="f_reg_price_value" value="' . $this->_pagination_args[ 'f_reg_price_value' ] . '" />';
            echo '<input type="hidden" name="f_sale_price_select" value="' . $this->_pagination_args[ 'f_sale_price_select' ] . '" />';
            echo '<input type="hidden" name="f_sale_price_value" value="' . $this->_pagination_args[ 'f_sale_price_value' ] . '" />';
            echo '<input type="hidden" name="f_weight_select" value="' . $this->_pagination_args[ 'f_weight_select' ] . '" />';
            echo '<input type="hidden" name="f_weight_value" value="' . $this->_pagination_args[ 'f_weight_value' ] . '" />';
            echo '<input type="hidden" name="f_per_page" value="' . $this->_pagination_args[ 'f_per_page' ] . '" />';
            echo '<input type="hidden" name="f_product_type" value="' . $this->_pagination_args[ 'f_product_type' ] . '" />';
            echo '<input type="hidden" name="f_show_variations" value="' . $this->_pagination_args[ 'f_show_variations' ] . '" />';


            parent::display();
        }

        function ajax_response() {

            check_ajax_referer( 'ajax-yith-wcbep-list-nonce', '_ajax_yith_wcbep_list_nonce' );

            $this->prepare_items();

            extract( $this->_args );
            extract( $this->_pagination_args, EXTR_SKIP );

            ob_start();
            if ( !empty( $_REQUEST[ 'no_placeholder' ] ) )
                $this->display_rows(); else
                $this->display_rows_or_placeholder();
            $rows = ob_get_clean();

            ob_start();
            $this->print_column_headers();
            $headers = ob_get_clean();

            ob_start();
            $this->pagination( 'top' );
            $pagination_top = ob_get_clean();

            ob_start();
            $this->pagination( 'bottom' );
            $pagination_bottom = ob_get_clean();

            $response                             = array( 'rows' => $rows );
            $response[ 'pagination' ][ 'top' ]    = $pagination_top;
            $response[ 'pagination' ][ 'bottom' ] = $pagination_bottom;
            $response[ 'column_headers' ]         = $headers;

            if ( isset( $total_items ) )
                $response[ 'total_items_i18n' ] = sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) );

            if ( isset( $total_pages ) ) {
                $response[ 'total_pages' ]      = $total_pages;
                $response[ 'total_pages_i18n' ] = number_format_i18n( $total_pages );
            }

            die( json_encode( $response ) );
        }
    }
}
?>