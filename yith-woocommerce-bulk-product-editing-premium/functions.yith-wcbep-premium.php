<?php
/**
 * Functions
 *
 * @author  Yithemes
 * @package YITH WooCommerce Bulk Product Editing
 * @version 1.0.0
 */

if ( !defined( 'YITH_WCBEP' ) ) {
    exit;
} // Exit if accessed directly

if ( !function_exists( 'yith_wcbep_get_template' ) ) {
    function yith_wcbep_get_template( $template, $args = array() ) {
        extract( $args );
        include( YITH_WCBEP_TEMPLATE_PATH . '/' . $template );
    }
}

if ( !function_exists( 'yith_wcbep_strContains' ) ) {
    function yith_wcbep_strContains( $haystack, $needle ) {
        return stripos( $haystack, $needle ) !== false;
    }
}

if ( !function_exists( 'yith_wcbep_strStartsWith' ) ) {
    function yith_wcbep_strStartsWith( $haystack, $needle ) {
        return $needle === "" || strirpos( $haystack, $needle, -strlen( $haystack ) ) !== false;
    }
}

if ( !function_exists( 'yith_wcbep_strEndsWith' ) ) {
    function yith_wcbep_strEndsWith( $haystack, $needle ) {
        return $needle === "" || ( ( $temp = strlen( $haystack ) - strlen( $needle ) ) >= 0 && stripos( $haystack, $needle, $temp ) !== false );
    }
}

if ( !function_exists( 'yith_wcbep_posts_filter_where' ) ) {
    function yith_wcbep_posts_filter_where( $where = '' ) {
        $f_title_sel = !empty( $_REQUEST[ 'f_title_select' ] ) ? $_REQUEST[ 'f_title_select' ] : 'cont';
        $f_title_val = isset( $_REQUEST[ 'f_title_value' ] ) ? $_REQUEST[ 'f_title_value' ] : '';

        // Filter Title
        if ( isset( $f_title_val ) && strlen( $f_title_val ) > 0 ) {
            $compare = 'LIKE';
            $value   = '%' . $f_title_val . '%';
            switch ( $f_title_sel ) {
                case 'cont':
                    $compare = 'LIKE';
                    break;
                case 'notcont':
                    $compare = 'NOT LIKE';
                    break;
                case 'starts':
                    $compare = 'REGEXP';
                    $value   = '^' . $f_title_val ;
                    break;
                case 'ends':
                    $compare = 'REGEXP';
                    $value   = $f_title_val . '$';
                    break;
            }

            $where .= " AND post_title {$compare} '{$value}'";
        }

        return $where;
    }
}
if ( !function_exists( 'yith_wcbep_get_terms' ) ) {
    function yith_wcbep_get_terms( $args = array() ) {
        global $wp_version;

        if ( version_compare( '4.5.0', $wp_version, '>=' ) ) {
            return get_terms( $args );
        } else {
            $taxonomy = isset( $args[ 'taxonomy' ] ) ? $args[ 'taxonomy' ] : '';
            if ( isset( $args[ 'taxonomy' ] ) )
                unset( $args[ 'taxonomy' ] );

            return get_terms( $taxonomy, $args );
        }
    }
}

if ( !function_exists( 'yith_wcbep_get_wc_product_types' ) ) {
    function yith_wcbep_get_wc_product_types() {
        $terms      = yith_wcbep_get_terms( array( 'taxonomy' => 'product_type' ) );
        $product_types = array();
        foreach ( $terms as $term ) {
            $name = sanitize_title( $term->name );
            switch ( $name ) {
                case 'grouped' :
                    $label = __( 'Grouped product', 'woocommerce' );
                    break;
                case 'external' :
                    $label = __( 'External/Affiliate product', 'woocommerce' );
                    break;
                case 'variable' :
                    $label = __( 'Variable product', 'woocommerce' );
                    break;
                case 'simple' :
                    $label = __( 'Simple product', 'woocommerce' );
                    break;
                default :
                    $label = ucfirst( $term->name );
                    break;
            }
            $product_types[ $name ] = $label;
        }

        return $product_types;
    }
}