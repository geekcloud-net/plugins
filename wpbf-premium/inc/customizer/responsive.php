<?php
/**
 * Styles
 *
 * Holds Customizer CSS styles
 *
 * @package Page Builder Framework Premium Addon
 * @subpackage Customizer
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wpbf_after_customizer_css', 'wpbf_premium_after_customizer_css_responsive', 20 );
function wpbf_premium_after_customizer_css_responsive() {

	$wpbf_settings = get_option( 'wpbf_settings' );

	if ( !empty( $wpbf_settings['wpbf_breakpoint_medium'] ) || !empty( $wpbf_settings['wpbf_breakpoint_desktop'] ) ) {

		$breakpoint_medium_int = $wpbf_settings['wpbf_breakpoint_medium'] ? (int) $wpbf_settings['wpbf_breakpoint_medium'] : 768;
		$breakpoint_desktop_int = $wpbf_settings['wpbf_breakpoint_desktop'] ? (int) $wpbf_settings['wpbf_breakpoint_desktop'] : 1024;
	
		$breakpoint_medium = $breakpoint_medium_int . 'px';
		$breakpoint_desktop = $breakpoint_desktop_int . 'px';

		$margin_large = $padding_large = '80px';
		$margin_medium = $padding_medium = '50px';
		$margin = $padding = '20px';

		?>

		/* From 481 | Phone Landscape & Bigger */
		@media (min-width: 481px) {

			/* Grid */
			.wpbf-grid-small-1-2 > * {
				width: 50%;
			}
			.wpbf-grid-small-1-3 > * {
				width: 33.333%;
			}
			.wpbf-grid-small-2-3 > * {
				width: 66.666%;
			}
			.wpbf-grid-small-1-4 > * {
				width: 25%;
			}
			.wpbf-grid-small-1-5 > * {
				width: 20%;
			}
			.wpbf-grid-small-1-6 > * {
				width: 16.666%;
			}
			.wpbf-grid-small-1-10 > * {
				width: 10%;
			}

			/* Grid Cells */

			/* Whole */
			.wpbf-small-1-1 {
				width: 100%;
			}
			/* Halves */
			.wpbf-small-1-2,
			.wpbf-small-2-4,
			.wpbf-small-3-6,
			.wpbf-small-5-10 {
				width: 50%;
			}
			/* Thirds */
			.wpbf-small-1-3,
			.wpbf-small-2-6 {
				width: 33.333%;
			}
			.wpbf-small-2-3,
			.wpbf-small-4-6 {
				width: 66.666%;
			}
			/* Quarters */
			.wpbf-small-1-4 {
				width: 25%;
			}
			.wpbf-small-3-4 {
				width: 75%;
			}
			/* Fifths */
			.wpbf-small-1-5,
			.wpbf-small-2-10 {
				width: 20%;
			}
			.wpbf-small-2-5,
			.wpbf-small-4-10 {
				width: 40%;
			}
			.wpbf-small-3-5,
			.wpbf-small-6-10 {
				width: 60%;
			}
			.wpbf-small-4-5,
			.wpbf-small-8-10 {
				width: 80%;
			}
			/* Sixths */
			.wpbf-small-1-6 {
				width: 16.666%;
			}
			.wpbf-small-5-6 {
				width: 83.333%;
			}
			/* Tenths */
			.wpbf-small-1-10 {
				width: 10%;
			}
			.wpbf-small-3-10 {
				width: 30%;
			}
			.wpbf-small-7-10 {
				width: 70%;
			}
			.wpbf-small-9-10 {
				width: 90%;
			}

		}

		/* From <?php echo esc_attr( $breakpoint_medium ); ?> | Tablet & Bigger */
		@media (min-width: <?php echo esc_attr( $breakpoint_medium ); ?>) {

			/* Sidebar */
			.wpbf-grid-divider > [class*='wpbf-medium-']:not(.wpbf-medium-1-1):nth-child(n+2) {
				border-left: 1px solid #d0d0da;
			}

			/* Grid */
			.wpbf-grid-medium-1-2 > * {
				width: 50%;
			}
			.wpbf-grid-medium-1-3 > * {
				width: 33.333%;
			}
			.wpbf-grid-medium-2-3 > * {
				width: 66.666%;
			}
			.wpbf-grid-medium-1-4 > * {
				width: 25%;
			}
			.wpbf-grid-medium-1-5 > * {
				width: 20%;
			}
			.wpbf-grid-medium-1-6 > * {
				width: 16.666%;
			}
			.wpbf-grid-medium-1-10 > * {
				width: 10%;
			}

			/* Grid Cells */

			/* Whole */
			.wpbf-medium-1-1 {
				width: 100%;
			}
			/* Halves */
			.wpbf-medium-1-2,
			.wpbf-medium-2-4,
			.wpbf-medium-3-6,
			.wpbf-medium-5-10 {
				width: 50%;
			}
			/* Thirds */
			.wpbf-medium-1-3,
			.wpbf-medium-2-6 {
				width: 33.333%;
			}
			.wpbf-medium-2-3,
			.wpbf-medium-4-6 {
				width: 66.666%;
			}
			/* Quarters */
			.wpbf-medium-1-4 {
				width: 25%;
			}
			.wpbf-medium-3-4 {
				width: 75%;
			}
			/* Fifths */
			.wpbf-medium-1-5,
			.wpbf-medium-2-10 {
				width: 20%;
			}
			.wpbf-medium-2-5,
			.wpbf-medium-4-10 {
				width: 40%;
			}
			.wpbf-medium-3-5,
			.wpbf-medium-6-10 {
				width: 60%;
			}
			.wpbf-medium-4-5,
			.wpbf-medium-8-10 {
				width: 80%;
			}
			/* Sixths */
			.wpbf-medium-1-6 {
				width: 16.666%;
			}
			.wpbf-medium-5-6 {
				width: 83.333%;
			}
			/* Tenths */
			.wpbf-medium-1-10 {
				width: 10%;
			}
			.wpbf-medium-3-10 {
				width: 30%;
			}
			.wpbf-medium-7-10 {
				width: 70%;
			}
			.wpbf-medium-9-10 {
				width: 90%;
			}

		}

		/* From <?php echo esc_attr( $breakpoint_desktop ); ?> | Desktop & Bigger */
		@media (min-width: <?php echo esc_attr( $breakpoint_desktop ); ?>) {

			/* Sidebar */
			.wpbf-grid-divider > [class*='wpbf-large-']:not(.wpbf-large-1-1):nth-child(n+2) {
				border-left:		1px solid #d0d0da;
			}

			/* Grid */
			.wpbf-grid-large-1-2 > * {
				width: 50%;
			}
			.wpbf-grid-large-1-3 > * {
				width: 33.333%;
			}
			.wpbf-grid-large-2-3 > * {
				width: 66.666%;
			}
			.wpbf-grid-large-1-4 > * {
				width: 25%;
			}
			.wpbf-grid-large-1-5 > * {
				width: 20%;
			}
			.wpbf-grid-large-1-6 > * {
				width: 16.666%;
			}
			.wpbf-grid-large-1-10 > * {
				width: 10%;
			}

			/* Grid Cells */

			/* Whole */
			.wpbf-large-1-1 {
				width: 100%;
			}
			/* Halves */
			.wpbf-large-1-2,
			.wpbf-large-2-4,
			.wpbf-large-3-6,
			.wpbf-large-5-10 {
				width: 50%;
			}
			/* Thirds */
			.wpbf-large-1-3,
			.wpbf-large-2-6 {
				width: 33.333%;
			}
			.wpbf-large-2-3,
			.wpbf-large-4-6 {
				width: 66.666%;
			}
			/* Quarters */
			.wpbf-large-1-4 {
				width: 25%;
			}
			.wpbf-large-3-4 {
				width: 75%;
			}
			/* Fifths */
			.wpbf-large-1-5,
			.wpbf-large-2-10 {
				width: 20%;
			}
			.wpbf-large-2-5,
			.wpbf-large-4-10 {
				width: 40%;
			}
			.wpbf-large-3-5,
			.wpbf-large-6-10 {
				width: 60%;
			}
			.wpbf-large-4-5,
			.wpbf-large-8-10 {
				width: 80%;
			}
			/* Sixths */
			.wpbf-large-1-6 {
				width: 16.666%;
			}
			.wpbf-large-5-6 {
				width: 83.333%;
			}
			/* Tenths */
			.wpbf-large-1-10 {
				width: 10%;
			}
			.wpbf-large-3-10 {
				width: 30%;
			}
			.wpbf-large-7-10 {
				width: 70%;
			}
			.wpbf-large-9-10 {
				width: 90%;
			}

		}

		/* From 1200 | Large Screen & Bigger */
		@media (min-width: 1200px) {

			.wpbf-grid-xlarge-1-2 > * {
				width: 50%;
			}
			.wpbf-grid-xlarge-1-3 > * {
				width: 33.333%;
			}
			.wpbf-grid-xlarge-2-3 > * {
				width: 66.666%;
			}
			.wpbf-grid-xlarge-1-4 > * {
				width: 25%;
			}
			.wpbf-grid-xlarge-1-5 > * {
				width: 20%;
			}
			.wpbf-grid-xlarge-1-6 > * {
				width: 16.666%;
			}
			.wpbf-grid-xlarge-1-10 > * {
				width: 10%;
			}

			/* Grid Cells */

			/* Whole */
			.wpbf-xlarge-1-1 {
				width: 100%;
			}
			/* Halves */
			.wpbf-xlarge-1-2,
			.wpbf-xlarge-2-4,
			.wpbf-xlarge-3-6,
			.wpbf-xlarge-5-10 {
				width: 50%;
			}
			/* Thirds */
			.wpbf-xlarge-1-3,
			.wpbf-xlarge-2-6 {
				width: 33.333%;
			}
			.wpbf-xlarge-2-3,
			.wpbf-xlarge-4-6 {
				width: 66.666%;
			}
			/* Quarters */
			.wpbf-xlarge-1-4 {
				width: 25%;
			}
			.wpbf-xlarge-3-4 {
				width: 75%;
			}
			/* Fifths */
			.wpbf-xlarge-1-5,
			.wpbf-xlarge-2-10 {
				width: 20%;
			}
			.wpbf-xlarge-2-5,
			.wpbf-xlarge-4-10 {
				width: 40%;
			}
			.wpbf-xlarge-3-5,
			.wpbf-xlarge-6-10 {
				width: 60%;
			}
			.wpbf-xlarge-4-5,
			.wpbf-xlarge-8-10 {
				width: 80%;
			}
			/* Sixths */
			.wpbf-xlarge-1-6 {
				width: 16.666%;
			}
			.wpbf-xlarge-5-6 {
				width: 83.333%;
			}
			/* Tenths */
			.wpbf-xlarge-1-10 {
				width: 10%;
			}
			.wpbf-xlarge-3-10 {
				width: 30%;
			}
			.wpbf-xlarge-7-10 {
				width: 70%;
			}
			.wpbf-xlarge-9-10 {
				width: 90%;
			}

		}

		/* Until 1200 */
		@media screen and (max-width: 1200px) {

			/* Margin */
			.wpbf-margin-xlarge {
				margin-top: <?php echo $margin_large; ?>;
				margin-bottom: <?php echo $margin_large; ?>;
			}
			.wpbf-margin-xlarge-top {
				margin-top: <?php echo $margin_large; ?>;
			}
			.wpbf-margin-xlarge-bottom {
				margin-bottom: <?php echo $margin_large; ?>;
			}
			.wpbf-margin-xlarge-left {
				margin-left: <?php echo $margin_large; ?>;
			}
			.wpbf-margin-xlarge-right {
				margin-right: <?php echo $margin_large; ?>;
			}

			/* Padding */
			.wpbf-padding-xlarge {
				padding-top: <?php echo $padding_large; ?>;
				padding-bottom: <?php echo $padding_large; ?>;
			}
			.wpbf-padding-xlarge-top {
				padding-top: <?php echo $padding_large; ?>;
			}
			.wpbf-padding-xlarge-bottom {
				padding-bottom: <?php echo $padding_large; ?>;
			}
			.wpbf-padding-xlarge-left {
				padding-left: <?php echo $padding_large; ?>;
			}
			.wpbf-padding-xlarge-right {
				padding-right: <?php echo $padding_large; ?>;
			}

		}

		/* Until <?php echo esc_attr( $breakpoint_desktop ); ?> */
		@media screen and (max-width: <?php echo esc_attr( $breakpoint_desktop ); ?>) {

			/* Margin */
			.wpbf-margin-large, .wpbf-margin-xlarge {
				margin-top: <?php echo $margin_medium; ?>;
				margin-bottom: <?php echo $margin_medium; ?>;
			}
			.wpbf-margin-large-top {
				margin-top: <?php echo $margin_medium; ?>;
			}
			.wpbf-margin-large-bottom {
				margin-bottom: <?php echo $margin_medium; ?>;
			}
			.wpbf-margin-large-left {
				margin-left: <?php echo $margin_medium; ?>;
			}
			.wpbf-margin-large-right {
				margin-right: <?php echo $margin_medium; ?>;
			}
			.wpbf-margin-xlarge-top {
				margin-top: <?php echo $margin_medium; ?>;
			}
			.wpbf-margin-xlarge-bottom {
				margin-bottom: <?php echo $margin_medium; ?>;
			}
			.wpbf-margin-xlarge-left {
				margin-left: <?php echo $margin_medium; ?>;
			}
			.wpbf-margin-xlarge-right {
				margin-right: <?php echo $margin_medium; ?>;
			}

			/* Padding */
			.wpbf-padding-large, .wpbf-padding-xlarge {
				padding-top: <?php echo $padding_medium; ?>;
				padding-bottom: <?php echo $padding_medium; ?>;
			}
			.wpbf-padding-large-top {
				padding-top: <?php echo $padding_medium; ?>;
			}
			.wpbf-padding-large-bottom {
				padding-bottom: <?php echo $padding_medium; ?>;
			}
			.wpbf-padding-large-left {
				padding-left: <?php echo $padding_medium; ?>;
			}
			.wpbf-padding-large-right {
				padding-right: <?php echo $padding_medium; ?>;
			}
			.wpbf-padding-xlarge-top {
				padding-top: <?php echo $padding_medium; ?>;
			}
			.wpbf-padding-xlarge-bottom {
				padding-bottom: <?php echo $padding_medium; ?>;
			}
			.wpbf-padding-xlarge-left {
				padding-left: <?php echo $padding_medium; ?>;
			}
			.wpbf-padding-xlarge-right {
				padding-right: <?php echo $padding_medium; ?>;
			}

		}

		/* Until <?php echo esc_attr( $breakpoint_medium ); ?> */
		@media screen and (max-width: <?php echo esc_attr( $breakpoint_medium ); ?>) {

			/* General */

			.wpbf-footer-two-columns,
			.wpbf-pre-header-two-columns {
				display: block;
			}

			.wpbf-footer-two-columns .wpbf-inner-footer-left,
			.wpbf-footer-two-columns .wpbf-inner-footer-right,
			.wpbf-pre-header-two-columns .wpbf-inner-pre-header-left,
			.wpbf-pre-header-two-columns .wpbf-inner-pre-header-right {
				display: block;
				width: 100%;
				text-align: center;
			}

			.wpbf-page-footer .wpbf-inner-footer-right .wpbf-menu {
				float: none;
				width: 100%;
		 		display: flex;
		 		align-items: center;
		 		justify-content: center;
			}

			.wpbf-page-footer .wpbf-inner-footer-left .wpbf-menu {
				float: none;
				width: 100%;
		 		display: flex;
		 		align-items: center;
		 		justify-content: center;
			}

		}

		/* Until 480 */
		@media screen and (max-width: 480px) {

			/* Margin */
			.wpbf-margin-medium, .wpbf-margin-large, .wpbf-margin-xlarge {
				margin-top: <?php echo $margin; ?>;
				margin-bottom: <?php echo $margin; ?>;
			}
			.wpbf-margin-large-top {
				margin-top: <?php echo $margin; ?>;
			}
			.wpbf-margin-large-bottom {
				margin-bottom: <?php echo $margin; ?>;
			}
			.wpbf-margin-large-left {
				margin-left: <?php echo $margin; ?>;
			}
			.wpbf-margin-large-right {
				margin-right: <?php echo $margin; ?>;
			}
			.wpbf-margin-medium-top {
				margin-top: <?php echo $margin; ?>;
			}
			.wpbf-margin-medium-bottom {
				margin-bottom: <?php echo $margin; ?>;
			}
			.wpbf-margin-medium-left {
				margin-left: <?php echo $margin; ?>;
			}
			.wpbf-margin-medium-right {
				margin-right: <?php echo $margin; ?>;
			}
			.wpbf-margin-xlarge-top {
				margin-top: <?php echo $margin; ?>;
			}
			.wpbf-margin-xlarge-bottom {
				margin-bottom: <?php echo $margin; ?>;
			}
			.wpbf-margin-xlarge-left {
				margin-left: <?php echo $margin; ?>;
			}
			.wpbf-margin-xlarge-right {
				margin-right: <?php echo $margin; ?>;
			}

			/* Padding */
			.wpbf-padding-medium, .wpbf-padding-large, .wpbf-padding-xlarge {
				padding-top: <?php echo $padding; ?>;
				padding-bottom: <?php echo $padding; ?>;
			}
			.wpbf-padding-large-top {
				padding-top: <?php echo $padding; ?>;
			}
			.wpbf-padding-large-bottom {
				padding-bottom: <?php echo $padding; ?>;
			}
			.wpbf-padding-large-left {
				padding-left: <?php echo $padding; ?>;
			}
			.wpbf-padding-large-right {
				padding-right: <?php echo $padding; ?>;
			}
			.wpbf-padding-medium-top {
				padding-top: <?php echo $padding; ?>;
			}
			.wpbf-padding-medium-bottom {
				padding-bottom: <?php echo $padding; ?>;
			}
			.wpbf-padding-medium-left {
				padding-left: <?php echo $padding; ?>;
			}
			.wpbf-padding-medium-right {
				padding-right: <?php echo $padding; ?>;
			}
			.wpbf-padding-xlarge-top {
				padding-top: <?php echo $padding; ?>;
			}
			.wpbf-padding-xlarge-bottom {
				padding-bottom: <?php echo $padding; ?>;
			}
			.wpbf-padding-xlarge-left {
				padding-left: <?php echo $padding; ?>;
			}
			.wpbf-padding-xlarge-right {
				padding-right: <?php echo $padding; ?>;
			}

		}


		/* Visibility */

		/* Desktop and bigger */
		@media (min-width: <?php echo esc_attr( $breakpoint_desktop_int ) + 1 ?>px) {
			.wpbf-visible-small {
				display: none !important;
			}
			.wpbf-visible-medium {
				display: none !important;
			}
			.wpbf-hidden-large {
				display: none !important;
			}
		}
		/* Tablets portrait */
		@media (min-width: <?php echo esc_attr( $breakpoint_medium_int ) + 1 ?>px) and (max-width: <?php echo esc_attr( $breakpoint_desktop ); ?>) {
			.wpbf-visible-small {
				display: none !important;
			}
			.wpbf-visible-large {
				display: none !important ;
			}
			.wpbf-hidden-medium {
				display: none !important;
			}
		}
		/* Tablets */
		@media (max-width: <?php echo esc_attr( $breakpoint_medium ); ?>) {
			.wpbf-visible-medium {
				display: none !important;
			}
			.wpbf-visible-large {
				display: none !important;
			}
			.wpbf-hidden-small {
				display: none !important;
			}
		}

		<?php if( get_theme_mod( 'sidebar_width' ) ) { ?>

		@media (min-width: <?php echo esc_attr( $breakpoint_medium_int ) + 1 ?>px) {

			.wpbf-sidebar-wrapper.wpbf-medium-1-3 {
				width: <?php echo esc_attr( get_theme_mod( 'sidebar_width' ) ) ?>%;
			}

			.wpbf-main.wpbf-medium-2-3 {
				width: <?php echo esc_attr( 100 - get_theme_mod( 'sidebar_width' ) ) ?>%;
			}

		}

		<?php } ?>

		<?php // WooCommerce | Smallscreen ?>

		<?php if ( class_exists( 'WooCommerce' ) ) { ?>

		@media screen and (max-width: <?php echo esc_attr( $breakpoint_medium ); ?>) {

		.woocommerce table.shop_table_responsive thead,.woocommerce-page table.shop_table_responsive thead{display:none}.woocommerce table.shop_table_responsive tbody tr:first-child td:first-child,.woocommerce-page table.shop_table_responsive tbody tr:first-child td:first-child{border-top:0}.woocommerce table.shop_table_responsive tbody th,.woocommerce-page table.shop_table_responsive tbody th{display:none}.woocommerce table.shop_table_responsive tr,.woocommerce-page table.shop_table_responsive tr{display:block}.woocommerce table.shop_table_responsive tr td,.woocommerce-page table.shop_table_responsive tr td{display:block;text-align:right !important}.woocommerce table.shop_table_responsive tr td.order-actions,.woocommerce-page table.shop_table_responsive tr td.order-actions{text-align:left !important}.woocommerce table.shop_table_responsive tr td::before,.woocommerce-page table.shop_table_responsive tr td::before{content:attr(data-title) ": ";font-weight:700;float:left}.woocommerce table.shop_table_responsive tr td.product-remove::before,.woocommerce table.shop_table_responsive tr td.actions::before,.woocommerce-page table.shop_table_responsive tr td.product-remove::before,.woocommerce-page table.shop_table_responsive tr td.actions::before{display:none}.woocommerce table.shop_table_responsive tr:nth-child(2n) td,.woocommerce-page table.shop_table_responsive tr:nth-child(2n) td{background-color:rgba(0,0,0,0.025)}.woocommerce table.my_account_orders tr td.order-actions,.woocommerce-page table.my_account_orders tr td.order-actions{text-align:left}.woocommerce table.my_account_orders tr td.order-actions::before,.woocommerce-page table.my_account_orders tr td.order-actions::before{display:none}.woocommerce table.my_account_orders tr td.order-actions .button,.woocommerce-page table.my_account_orders tr td.order-actions .button{float:none;margin:0.125em 0.25em 0.125em 0}.woocommerce .col2-set .col-1,.woocommerce .col2-set .col-2,.woocommerce-page .col2-set .col-1,.woocommerce-page .col2-set .col-2{float:none;width:100%}.woocommerce ul.products[class*='columns-'] li.product,.woocommerce-page ul.products[class*='columns-'] li.product{width:48%;float:left;clear:both;margin:0 0 2.992em}.woocommerce ul.products[class*='columns-'] li.product:nth-child(2n),.woocommerce-page ul.products[class*='columns-'] li.product:nth-child(2n){float:right;clear:none !important}.woocommerce div.product div.images,.woocommerce div.product div.summary,.woocommerce #content div.product div.images,.woocommerce #content div.product div.summary,.woocommerce-page div.product div.images,.woocommerce-page div.product div.summary,.woocommerce-page #content div.product div.images,.woocommerce-page #content div.product div.summary{float:none;width:100%}.woocommerce table.cart .product-thumbnail,.woocommerce #content table.cart .product-thumbnail,.woocommerce-page table.cart .product-thumbnail,.woocommerce-page #content table.cart .product-thumbnail{display:none}.woocommerce table.cart td.actions,.woocommerce #content table.cart td.actions,.woocommerce-page table.cart td.actions,.woocommerce-page #content table.cart td.actions{text-align:left}.woocommerce table.cart td.actions .coupon,.woocommerce #content table.cart td.actions .coupon,.woocommerce-page table.cart td.actions .coupon,.woocommerce-page #content table.cart td.actions .coupon{float:none;*zoom:1;padding-bottom:0.5em}.woocommerce table.cart td.actions .coupon::before,.woocommerce table.cart td.actions .coupon::after,.woocommerce #content table.cart td.actions .coupon::before,.woocommerce #content table.cart td.actions .coupon::after,.woocommerce-page table.cart td.actions .coupon::before,.woocommerce-page table.cart td.actions .coupon::after,.woocommerce-page #content table.cart td.actions .coupon::before,.woocommerce-page #content table.cart td.actions .coupon::after{content:' ';display:table}.woocommerce table.cart td.actions .coupon::after,.woocommerce #content table.cart td.actions .coupon::after,.woocommerce-page table.cart td.actions .coupon::after,.woocommerce-page #content table.cart td.actions .coupon::after{clear:both}.woocommerce table.cart td.actions .coupon input,.woocommerce table.cart td.actions .coupon .button,.woocommerce table.cart td.actions .coupon .input-text,.woocommerce #content table.cart td.actions .coupon input,.woocommerce #content table.cart td.actions .coupon .button,.woocommerce #content table.cart td.actions .coupon .input-text,.woocommerce-page table.cart td.actions .coupon input,.woocommerce-page table.cart td.actions .coupon .button,.woocommerce-page table.cart td.actions .coupon .input-text,.woocommerce-page #content table.cart td.actions .coupon input,.woocommerce-page #content table.cart td.actions .coupon .button,.woocommerce-page #content table.cart td.actions .coupon .input-text{width:48%;box-sizing:border-box}.woocommerce table.cart td.actions .coupon .input-text+.button,.woocommerce table.cart td.actions .coupon .button.alt,.woocommerce #content table.cart td.actions .coupon .input-text+.button,.woocommerce #content table.cart td.actions .coupon .button.alt,.woocommerce-page table.cart td.actions .coupon .input-text+.button,.woocommerce-page table.cart td.actions .coupon .button.alt,.woocommerce-page #content table.cart td.actions .coupon .input-text+.button,.woocommerce-page #content table.cart td.actions .coupon .button.alt{float:right}.woocommerce table.cart td.actions .button,.woocommerce #content table.cart td.actions .button,.woocommerce-page table.cart td.actions .button,.woocommerce-page #content table.cart td.actions .button{display:block;width:100%}.woocommerce .cart-collaterals .cart_totals,.woocommerce .cart-collaterals .shipping_calculator,.woocommerce .cart-collaterals .cross-sells,.woocommerce-page .cart-collaterals .cart_totals,.woocommerce-page .cart-collaterals .shipping_calculator,.woocommerce-page .cart-collaterals .cross-sells{width:100%;float:none;text-align:left}.woocommerce.woocommerce-checkout form.login .form-row,.woocommerce-page.woocommerce-checkout form.login .form-row{width:100%;float:none}.woocommerce #payment .terms,.woocommerce-page #payment .terms{text-align:left;padding:0}.woocommerce #payment #place_order,.woocommerce-page #payment #place_order{float:none;width:100%;box-sizing:border-box;margin-bottom:1em}.woocommerce .lost_reset_password .form-row-first,.woocommerce .lost_reset_password .form-row-last,.woocommerce-page .lost_reset_password .form-row-first,.woocommerce-page .lost_reset_password .form-row-last{width:100%;float:none;margin-right:0}.woocommerce-account .woocommerce-MyAccount-navigation,.woocommerce-account .woocommerce-MyAccount-content{float:none;width:100%}

		}

	<?php } ?>

	<?php } ?>

<?php } ?>