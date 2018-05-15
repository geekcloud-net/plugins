<?php
/**
 * The template for displaying the main editor page
 *
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js" style="height: 100%;overflow-y:hidden">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<title><?php echo get_the_title() . ' | ' . __( 'Thrive Architect', 'thrive-cb' ); ?></title>
	<?php wp_head(); ?>
	<?php do_action( 'tcb_hook_editor_head' ); ?>
</head>
<?php $cpanel_attr = tve_cpanel_attributes(); ?>
<body class="tcb-editor-main tve-<?php echo $cpanel_attr['position']; ?>-side preview-desktop" style="padding: 0;margin: 0;height: 100%;overflow-y:hidden;">
<div class="tcb-wrap-all" id="tve-main-frame">
	<div id="tve-page-loader" class="tve-open">
		<?php tcb_template( 'loading-spinner.php' ); ?>
	</div>
	<div id="tcb-top">
		<?php do_action( 'tve_top_buttons' ); ?>
		<div id="tcb-top-nav-list"></div>
	</div>
	<?php $data->render_menu(); ?>
	<?php do_action( 'tcb_editor_iframe_before' ); ?>
	<div id="tcb-frame-container">
		<iframe tabindex="-1" id="tve-editor-frame" src="<?php echo $data->inner_frame_url() ?>"></iframe>
	</div>
	<?php do_action( 'tcb_editor_iframe_after' ); ?>
	<?php include TVE_TCB_ROOT_PATH . 'editor/css/fonts/control-panel.svg' ?>
	<?php include TVE_TCB_ROOT_PATH . 'editor/css/fonts/font-awesome.svg' ?>
	<div id="inline-drop-panels"></div>
</div>
<?php wp_footer(); ?>
<?php do_action( 'tcb_hook_editor_footer' ); ?>
</body>
</html>
