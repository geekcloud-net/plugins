<?php
global $design;
$key = '';
if ( ! empty( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] ) ) {
	list( $type, $key ) = explode( '|', $design[ TVE_Ult_Const::FIELD_TEMPLATE ] );
}
$key = preg_replace( '#_v(.+)$#', '', $key );

$design_body_class = 'tve-ult-body-widget';

include TVE_Ult_Const::plugin_path() . 'tcb-bridge/editor/head.php';
?>

<div id="tve-ult-editor-replace">
	<div class="tve-ult-widget tvu-triggered">
		<div class="tl-style" id="tvu_<?php echo empty( $key ) ? '' : $key ?>" data-state="<?php echo $design['id'] ?>">
			<?php echo tve_ult_editor_custom_content( $design ) ?>
		</div>
	</div>
</div>

<?php include TVE_Ult_Const::plugin_path() . 'tcb-bridge/editor/footer.php'; ?>
