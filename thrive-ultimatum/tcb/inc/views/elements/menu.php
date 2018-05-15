<?php
$menus = tve_get_custom_menus();

$attributes = array(
	'menu_id'          => isset( $_POST['menu_id'] ) ? $_POST['menu_id'] : ( ! empty( $menus[0] ) ? $menus[0]['id'] : 0 ),
	'color'            => isset( $_POST['colour'] ) ? $_POST['colour'] : 'tve_red',
	'dir'              => isset( $_POST['dir'] ) ? $_POST['dir'] : 'tve_horizontal',
	'font_class'       => isset( $_POST['font_class'] ) ? $_POST['font_class'] : '',
	'font_size'        => isset( $_POST['font_size'] ) ? $_POST['font_size'] : '',
	'ul_attr'          => isset( $_POST['ul_attr'] ) ? $_POST['ul_attr'] : '',
	'link_attr'        => isset( $_POST['link_attr'] ) ? $_POST['link_attr'] : '',
	'top_link_attr'    => isset( $_POST['top_link_attr'] ) ? $_POST['top_link_attr'] : '',
	'trigger_attr'     => isset( $_POST['trigger_attr'] ) ? $_POST['trigger_attr'] : '',
	'primary'          => isset( $_POST['primary'] ) && ( $_POST['primary'] == 'true' || $_POST['primary'] == '1' ) ? 1 : '',
	'head_css'         => isset( $_POST['head_css'] ) ? $_POST['head_css'] : '',
	'background_hover' => isset( $_POST['background_hover'] ) ? $_POST['background_hover'] : '',
	'main_hover'       => isset( $_POST['main_hover'] ) ? $_POST['main_hover'] : '',
	'child_hover'      => isset( $_POST['child_hover'] ) ? $_POST['child_hover'] : '',
);

$attributes['font_class'] .= ( ! empty( $_POST['custom_class'] ) ? ' ' . $_POST['custom_class'] : '' );

?>
<?php if ( empty( $_POST['nowrap'] ) ) : ?>
<div class="thrv_wrapper thrv_widget_menu" data-tve-style="<?php echo $attributes['dir'] ?>">
	<?php endif ?>
	<div class="thrive-shortcode-config"
	     style="display: none !important"><?php echo '__CONFIG_widget_menu__' . json_encode( $attributes ) . '__CONFIG_widget_menu__' ?></div>
	<?php echo tve_render_widget_menu( $attributes ) ?>
	<?php if ( empty( $_POST['nowrap'] ) ) : ?>
</div>
<?php endif ?>
