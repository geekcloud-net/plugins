<?php
global $design;
$key  = '';
$type = '';
if ( ! empty( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] ) ) {
	list( $type, $key ) = TU_Template_Manager::tpl_type_key( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] );
}
$key               = preg_replace( '#_v(.+)$#', '', $key );
$design_body_class = 'tve-ult-body-shortcode';

include TVE_Ult_Const::plugin_path() . 'tcb-bridge/editor/head.php'; ?>

<div id="tve-ult-editor-replace">
	<div class="tvu-triggered tve-ult-shortcode">
		<div class="tl-style" id="tvu_<?php echo $key ?>" data-state="<?php echo $design['id'] ?>">
			<?php echo tve_ult_editor_custom_content( $design ) ?>
		</div>
	</div>
	<div class="tve-ult-template-description"
	     style="opacity: .6; padding-top: 240px; text-align: center; position: relative; z-index: -1;">
		<h4><?php echo __( 'This is a Design type called "Shortcode". It is displayed on posts that have its code in the content.', TVE_Ult_Const::T ) ?></h4>
	</div>
</div>

<?php include TVE_Ult_Const::plugin_path( 'tcb-bridge/editor/footer.php' ); ?>
