<?php
@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));
require_once(ABSPATH . 'wp-admin/includes/admin.php');

// In case admin-header.php is included in a function.
global $title, $hook_suffix, $current_screen, $wp_locale, $pagenow, $wp_version,
	$update_title, $total_update_count, $parent_file;
	$pagenow = 'POS';
	$outlets_name = WC_POS()->outlet()->get_data_names();
	$outlet_string = $outlets_name[$data['outlet']];
	
$title = $data['name'] . ' &lsaquo; ' . $outlet_string . ' &lsaquo; ' . __('Point of Sale', 'wc_point_of_sale') . ' &#8212; ' . get_bloginfo('name');

register_admin_color_schemes();

	global $is_IE;

	$admin_html_class = ( is_admin_bar_showing() ) ? 'wp-toolbar' : '';

	if ( $is_IE )
		@header('X-UA-Compatible: IE=edge');

/**
 * Fires inside the HTML tag in the admin header.
 *
 * @since 2.2.0
 */
?>
<!DOCTYPE html>
<!--[if IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" class="ie8 <?php echo $admin_html_class; ?>" <?php do_action( 'admin_xml_ns' ); ?> <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 8) ]><!-->
<?php /** This action is documented in wp-admin/includes/template.php */ ?>
<!-- <html xmlns="http://www.w3.org/1999/xhtml" class="<?php echo $admin_html_class; ?>" <?php do_action( 'admin_xml_ns' ); ?> <?php language_attributes(); ?> manifest="<?php echo WC_POS()->plugin_url(); ?>/assets/cache.manifest"> -->
<html xmlns="http://www.w3.org/1999/xhtml" class="<?php echo $admin_html_class; ?>" <?php do_action( 'admin_xml_ns' ); ?> <?php language_attributes(); ?> >
<!--<![endif]-->
<head>
	<meta http-equiv="Content-Type" name="viewport" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>, width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1">
	<?php wp_site_icon() ?>
	<title><?php echo $title; ?></title>
	<style>
		@media only screen and (min-width: 1024px) {
			.pos_register_user_panel:hover .pos_register_main_name {
				opacity: .75
			}
			#add_customer_to_register:hover {
				color: #b71c1c !important;
				color: <?php echo get_option('woocommerce_pos_register_base_color') ?> !important;
			}
		}
		#pos_user_badge a.page-title-action:hover,
		#pos_register_buttons a.page-title-action:hover {
			opacity: .75;
		}
		#regiser_top_bar #pos_user_badge .button:hover {
			background: #b71c1c !important;
			background: <?php echo get_option('woocommerce_pos_register_base_color') ?> !important;
			opacity: .75;
		}
		#pos_user_badge a.page-title-action,
		#pos_register_buttons a.page-title-action,
		.pos_register_main_name {
			color: #fff !important;
		}
		#regiser_top_bar #pos_user_badge .button,
		.pos_register_user_panel .pos_register_brand_logo:before {
			background: #b71c1c !important;
			background: <?php echo get_option('woocommerce_pos_register_base_color') ?> !important;
		}
		.pos_register_shop_name {
			background: #b71c1c !important;
			background: <?php echo get_option('woocommerce_pos_register_base_color') ?> !important;
			opacity: 0.75;
		}
		.product_is_taxable input:checked + label.pos_register_toggle,
		.fee_taxable input:checked + label.pos_register_toggle,
		.pos_addon_label input:checked ~ .pos_checkmark,
		.pos_addon_label input:checked ~ .pos_radio,
		.select2-container--default .select2-results__option--highlighted[aria-selected], .select2-container--default .select2-results__option--highlighted[data-selected] {
			background: #b71c1c !important;
			background: <?php echo get_option('woocommerce_pos_register_base_color') ?> !important;
		}
	</style>
	<?php
	remove_all_actions('admin_enqueue_scripts') ;
	/********************************************/
	wp_enqueue_style( 'wp-auth-check' );
	wp_enqueue_style( 'colors' );
	wp_enqueue_style( 'ie' );
	/********************************************/
	#wp_enqueue_script( 'heartbeat' );
	wp_enqueue_script( 'jquery' );

	$admin_body_class = preg_replace('/[^a-z0-9_-]+/i', '-', $hook_suffix);
	
	?>
	<script type="text/javascript">
	    var current_cashier_id    = <?php echo json_encode(get_current_user_id()) ?>;
	    var pos_register_data     = <?php echo json_encode($data) ?>;
	    var polyfilter_scriptpath = '<?php echo str_replace('\\', '/', WC_POS()->plugin_assets_path()); ?>/js/register/modal/';
		
		addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
		var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>',
		adminpage = '<?php echo $admin_body_class; ?>',
		thousandsSeparator = '<?php echo addslashes( $wp_locale->number_format['thousands_sep'] ); ?>',
		decimalPoint = '<?php echo addslashes( $wp_locale->number_format['decimal_point'] ); ?>',
		isRtl = <?php echo (int) is_rtl(); ?>;
		var custom_fees = <?php echo json_encode(unserialize(get_option('wc_pos_custom_fees'))); ?>
	</script>
<?php

do_action( 'pos_admin_enqueue_scripts');
do_action( 'admin_enqueue_scripts', $hook_suffix );        

remove_action('admin_print_scripts', 'wplc_admin_scripts') ;
do_action( 'admin_print_scripts' );

do_action( 'pos_admin_print_scripts' );
do_action( 'admin_print_styles' );

$this->header();

if ( get_user_setting('mfold') == 'f' )
	$admin_body_class .= ' folded';

if ( !get_user_setting('unfold') )
	$admin_body_class .= ' auto-fold';

if ( is_admin_bar_showing() )
	$admin_body_class .= ' admin-bar';

if ( is_rtl() )
	$admin_body_class .= ' rtl';


$admin_body_class .= ' branch-' . str_replace( array( '.', ',' ), '-', floatval( $wp_version ) );
$admin_body_class .= ' version-' . str_replace( '.', '-', preg_replace( '/^([.0-9]+).*/', '$1', $wp_version ) );
$admin_body_class .= ' admin-color-' . sanitize_html_class( get_user_option( 'admin_color' ), 'fresh' );
$admin_body_class .= ' locale-' . sanitize_html_class( strtolower( str_replace( '_', '-', get_locale() ) ) );

if ( wp_is_mobile() )
	$admin_body_class .= ' mobile';

if ( is_multisite() )
	$admin_body_class .= ' multisite';

if ( is_network_admin() )
	$admin_body_class .= ' network-admin';

$admin_body_class .= ' no-customize-support no-svg';
$layout = get_option('woocommerce_pos_register_layout', 'two');
if($layout == 'one'){
	$admin_body_class .= ' register_layout_one ';	
}

$wc_pos_tile_layout = get_option('wc_pos_tile_layout', 'image_title');
switch ($wc_pos_tile_layout) {
	case 'image':
		$admin_body_class .= ' hide_text_on_tiles';
		break;
	case 'image_title_price':
		$admin_body_class .= ' image_title_price';
		break;
}

?>
</head>
<body class="wp-admin wp-core-ui no-js wc_poin_of_sale_body <?php echo $admin_body_class; ?>">
<?php if( !isset($_GET['print_pos_receipt']) ){ ?>
	<?php $this->validate(); ?>
	<script type="text/javascript">
		document.body.className = document.body.className.replace('no-js','js');
	</script>


	<div id="wpwrap" class="pos_blur_background">
	<a tabindex="1" href="#wpbody-content" class="screen-reader-shortcut"><?php _e('Skip to main content'); ?></a>

	<div id="wpcontent">

	<?php
	/**
	 * Fires at the beginning of the content section in an admin page.
	 *
	 * @since 3.0.0
	 */
	do_action( 'in_admin_header' );

	set_current_screen('pos_page');
	?>

	<div id="wpbody">
	<?php
	unset($title_class, $blog_name, $total_update_count, $update_title);
	?>

	<div id="wpbody-content" aria-label="<?php esc_attr_e('Main content'); ?>" tabindex="0">
	<?php
}