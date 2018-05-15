<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ywcrbp_get_user_role' ) ) {
	/**
	 * get all user role include guest user
	 * @author YITHEMES
	 * @since 1.0.0
	 * @return array
	 */
	function ywcrbp_get_user_role() {

		global $wp_roles;

		return array_merge( $wp_roles->get_names(), array( 'guest' => __( 'Guest', 'yith-woocommerce-role-based-prices' ) ) );
	}
}

if ( ! function_exists( 'ywcrbp_json_search_product_categories' ) ) {

	/**
	 * get product categories by terms
	 * @author YITHEMES
	 * @since 1.0.0
	 *
	 * @param string $x
	 * @param array $taxonomy_types
	 */
	function ywcrbp_json_search_product_categories( $x = '', $taxonomy_types = array( 'product_cat' ) ) {

		check_ajax_referer( 'search-products', 'security' );

		global $wpdb;
		$term = (string) wc_clean( stripslashes( $_GET['term'] ) );
		$term = "%" . $term . "%";

		$query_cat = $wpdb->prepare( "SELECT {$wpdb->terms}.term_id,{$wpdb->terms}.name, {$wpdb->terms}.slug
                                   FROM {$wpdb->terms} INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id
                                   WHERE {$wpdb->term_taxonomy}.taxonomy IN (%s) AND {$wpdb->terms}.name LIKE %s", implode( ",", $taxonomy_types ), $term );

		$product_categories = $wpdb->get_results( $query_cat );

		$to_json = array();

		foreach ( $product_categories as $product_category ) {

			$to_json[ $product_category->term_id ] = "#" . $product_category->term_id . "-" . $product_category->name;
		}


		wp_send_json( $to_json );
	}
}
add_action( 'wp_ajax_yit_role_price_json_search_product_categories', 'ywcrbp_json_search_product_categories', 10 );

if ( ! function_exists( 'ywcrbp_json_search_product_tags' ) ) {

	/**
	 * get product tags by terms
	 * @author YITHEMES
	 * @since 1.0.0
	 */
	function ywcrbp_json_search_product_tags() {
		ywcrbp_json_search_product_categories( '', array( 'product_tag' ) );
	}
}
add_action( 'wp_ajax_yit_role_price_json_search_product_tags', 'ywcrbp_json_search_product_tags', 10 );

if ( ! function_exists( 'ywcrbp_calculate_product_price_role' ) ) {
	/**
	 * compute the price rule for products
	 * @author YITHEMES
	 * @since 1.0.0
	 *
	 * @param WC_Product $product
	 * @param array $global_rule
	 * @param string $user_role
	 *
	 * @return float|string
	 */
	function ywcrbp_calculate_product_price_role( $product, $global_rule, $user_role ) {

		global $sitepress;


		if ( ! $product->is_type( 'variable' ) ) {

			if( isset( $sitepress ) ) {

				$product_id          = $product->get_id();
				$original_product_id = yit_wpml_object_id( $product_id, 'product', true, $sitepress->get_default_language() );
				$product    = wc_get_product( $original_product_id );

			}else{
				$product_id = yit_get_base_product_id( $product );
			}

			$product_rules = yit_get_prop( $product, '_product_rules' );

			$how_price = get_option( 'ywcrbp_apply_rule', 'regular' );

			// support to WooCommerce Product Bundles
			if ( $product->is_type( 'bundle' ) && $product->is_priced_per_product() ) {

				$base_regular_price = yit_get_prop( $product, 'base_regular_price', true, 'edit' );
				$base_price         = yit_get_prop( $product, 'base_price', true, 'edit' );
				$price              = ( 'regular' == $how_price ) ? $base_regular_price : $base_price;


			} else {

				$sale_price = yit_get_prop( $product, 'sale_price', true, 'edit' );
				if ( $sale_price > 0 && 'on_sale' == $how_price ) {
					$price = $sale_price;
				} else {

					$price = yit_get_prop( $product, 'regular_price', true, 'edit' );
				}

			}

			$price = apply_filters( 'ywcrbp_product_price_choose', $price, $product );

			$role_price             = 'no_price';
			$product_rules_filtered = array();
			$how_apply              = yit_get_prop( $product, 'how_apply_product_rule' );
			$how_apply              = empty( $how_apply ) ? 'only_this' : $how_apply;

			if ( ! empty( $product_rules ) ) {
				$product_rules_filtered = array_filter( $product_rules, function ( $v ) USE ( $user_role ) {
					return $v['rule_role'] == $user_role;
				} );
			}

			$filtered_rules = get_global_rule_for_product( $product_id, $product, $global_rule, $user_role );


			if ( count( $filtered_rules ) > 0 ) {

				usort( $filtered_rules, 'order_rules_by_priority' );
			}

			if ( empty( $product_rules_filtered ) ) {

				if ( 'only_this' !== $how_apply || empty( $product_rules ) ) {


					$role_price = apply_global_rule( $price, $filtered_rules, $role_price );
				}

			} else {

				if ( 'only_this' === $how_apply ) {

					$role_price = apply_product_rule( $price, $product_rules_filtered, $role_price );
				} else {

					$filtered_rules = remove_from_global_rule( $filtered_rules, $product_rules_filtered );
					$product_prices = apply_product_rule( $price, $product_rules_filtered, $role_price );
					$role_price     = apply_global_rule( $price, $filtered_rules, $product_prices );
				}
			}


			$role_price = apply_filters( 'ywcrbp_product_replace_roleprices', $role_price, $user_role, $price, $product );

			if ( $product->is_type( 'variation' ) ) {
				delete_transient( 'wc_var_prices_' . $product_id );
			}


			return $role_price;
		}

		return yit_get_prop( $product, 'price', true, 'edit' );

	}
}

if ( ! function_exists( 'order_rules_by_priority' ) ) {
	function order_rules_by_priority( $a, $b ) {

		$p1 = intval( get_post_meta( $a, '_ywcrbp_priority_rule', true ) );
		$p2 = intval( get_post_meta( $b, '_ywcrbp_priority_rule', true ) );

		if ( $p1 < $p2 ) {
			return - 1;
		} elseif ( $p1 > $p2 ) {
			return 1;
		} else {
			return 0;
		}

	}
}

if ( ! function_exists( 'init_product_prices' ) ) {

	function init_product_prices() {

		$product_prices = array();
		$all_user_role  = ywcrbp_get_user_role();

		//initialize product_price array
		foreach ( $all_user_role as $key => $name ) {

			$product_prices[ $key ] = 'no_price';
		}

		return $product_prices;
	}
}
if ( ! function_exists( 'get_global_rule_for_product' ) ) {
	/**
	 * chooses which global rules apply between global, categories and tags
	 * @author YITHEMES
	 * @since 1.0.0
	 *
	 * @param $product_id
	 * @param $product
	 * @param $global_rule
	 *
	 * @return array
	 */
	function get_global_rule_for_product( $product_id, $product, $global_rule, $user_role ) {

		$parent_id    = yit_get_base_product_id( $product );
		$product_tags = wp_get_object_terms( $parent_id, 'product_tag', array( 'fields' => 'ids' ) );
		$product_cat  = wp_get_object_terms( $parent_id, 'product_cat', array( 'fields' => 'ids' ) );

		$product_cat   = apply_filters( 'ywcrbp_product_categories', $product_cat, $parent_id );
		$category_rule = YITH_Role_Based_Type()->get_price_rule_by_product_categories( $product_cat, $parent_id, $user_role );
		$tag_rule      = YITH_Role_Based_Type()->get_price_rule_by_product_tags( $product_tags, $parent_id, $user_role );

		if ( ! empty( $category_rule ) ) {
			$current_rules = $category_rule;

		} elseif ( ! empty( $tag_rule ) ) {
			$current_rules = $tag_rule;

		} else {
			$current_rules = $global_rule;

		}

		return $current_rules;
	}
}


if ( ! function_exists( 'apply_global_rule' ) ) {

	/**
	 * apply the global rule for product
	 * @author YITHEMES
	 * @since 1.0.0
	 *
	 * @param float $regular_price
	 * @param array $current_rules
	 * @param array $role_price
	 *
	 * @return mixed
	 */
	function apply_global_rule( $regular_price, $current_rules, $role_price ) {

		//apply  rule
		foreach ( $current_rules as $rule_id ) {

			$role_is_active = get_post_meta( $rule_id, '_ywcrbp_active_rule', true );
			$price_type     = get_post_meta( $rule_id, '_ywcrbp_type_price', true );

			if ( $price_type === 'discount_perc' || $price_type === 'markup_perc' ) {
				$value = get_post_meta( $rule_id, '_ywcrbp_decimal_value', true );
			} else {
				$value = get_post_meta( $rule_id, '_ywcrbp_price_value', true );
			}

			$value = wc_format_decimal( $value );


			if ( $role_is_active ) {

				if ( $role_price == 'no_price' ) {
					$role_price = $regular_price;
				}
				$old_price  = $role_price;
				$role_price = compute_price( $old_price, $value, $price_type );

			}
		}

		return $role_price;
	}
}
if ( ! function_exists( 'apply_product_rule' ) ) {
	/**
	 * apply product rule
	 * @author YITHEMES
	 * @since 1.0.0
	 *
	 * @param float $regular_price
	 * @param array $product_rules
	 * @param array $role_price
	 *
	 * @return mixed
	 */
	function apply_product_rule( $regular_price, $product_rules, $role_price ) {
		foreach ( $product_rules as $rule ) {

			$value         = $rule['rule_value'];
			$value         = wc_format_decimal( $value );
			$type          = $rule['rule_type'];
			$role_to_apply = $rule['rule_role'];

			if ( ! empty( $role_to_apply ) ) {

				if ( $role_price == 'no_price' ) {
					$role_price = $regular_price;
				}

				$old_price  = $role_price;
				$role_price = compute_price( $old_price, $value, $type );

			}
		}

		return $role_price;
	}
}
if ( ! function_exists( 'remove_from_global_rule' ) ) {
	/**
	 * remove from global rules, those present from the product rules
	 * @author YITHEMES
	 * @since 1.0.0
	 *
	 * @param array $global_rule
	 * @param array $product_rule
	 *
	 * @return mixed
	 */
	function remove_from_global_rule( $global_rule, $product_rule ) {

		foreach ( $product_rule as $p_rule ) {

			$role = $p_rule['rule_role'];

			foreach ( $global_rule as $key => $g_rule ) {

				$g_role = get_post_meta( $g_rule, '_ywcrbp_role', true );
				if ( $role === $g_role ) {
					unset( $global_rule[ $key ] );
				}
			}

		}

		return $global_rule;
	}
}

if ( ! function_exists( 'compute_price' ) ) {
	/**
	 * @author YITHEMES
	 * @since 1.0.0
	 * compute new price
	 *
	 * @param $old_price
	 * @param $value
	 *
	 * @return float|int
	 */
	function compute_price( $old_price, $value, $price_type ) {

		// $price_type = get_post_meta($rule_id, '_ywcrbp_type_price', true);
		$sign = '';

		if ( 'discount_perc' === $price_type || 'discount_val' === $price_type ) {
			$sign = 'min';
		} else {
			$sign = 'plus';
		}

		// $value = get_post_meta($rule_id, '_ywcrbp_value', true);

		$new_price = 0;

		if ( 'discount_perc' === $price_type || 'markup_perc' === $price_type ) {
			$new_price = ( ( $value * $old_price ) / 100 );
		} else {
			$new_price = $value * 1;
		}

		if ( 'min' == $sign ) {
			$old_price -= $new_price;
		} else {
			$old_price += $new_price;
		}


		return ( $old_price <= 0 ) ? 0 : $old_price;
	}
}


if ( ! function_exists( 'get_first_user_role' ) ) {

	/**
	 * if a user as more role return the first
	 * @author YITHEMES
	 * @since 1.0.0
	 *
	 * @param $user_role
	 *
	 * @return mixed
	 */
	function get_first_user_role( $user_role ) {

		$exit  = false;
		$first = '';

		return reset( $user_role );
	}
}

if ( ! function_exists( 'get_user_role_label_by_slug' ) ) {
	/**
	 * return the role name by slug
	 * @author YITHEMES
	 * @since 1.0.0
	 *
	 * @param $slug
	 *
	 * @return string
	 */
	function get_user_role_label_by_slug( $slug ) {

		$role = ywcrbp_get_user_role();

		return isset( $role[ $slug ] ) ? $role[ $slug ] : '';
	}

}


if( !function_exists( 'ywcrbp_get_format_price_from_to' ) ){

	/**
	 * @param WC_Product $product
	 * @param float $from
	 * @param float $to
	 * @return string
	 */
	function ywcrbp_get_format_price_from_to ( $product, $from, $to ){

		$price_html = '';

		if( function_exists( 'wc_format_price_range' ) ){
			$price_html = wc_format_price_range( $from, $to );

		}else{
			$price_html = $product->get_price_html_from_to( $from,$to );
		}

		return $price_html;
	}
}