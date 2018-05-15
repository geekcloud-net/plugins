<?php
$is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

if ( ! $is_ajax && ! tve_ult_is_editor_page() ) {
	return;
}
global $design; // this is the main variation (variation parent)
if ( ! isset( $current_design ) ) {
	$current_design = $design; // this is the variation being edited now
}
/**
 * Shows a bar at the bottom of the page having all of the states defined for this form
 */
$states = tve_ult_get_related_states( $design );

?>
<?php if ( empty( $do_not_wrap ) ) : ?>
	<div class="tl-form-states-container" id="tu-form-states">
<?php endif ?>
	<div class="design-states">
		<span class="title"><?php echo __( 'Current States', TVE_Ult_Const::T ) ?></span>
		<button data-fn="collapse" title="<?php echo __( 'Close', TVE_Ult_Const::T ) ?>" class="click state-close btn-icon">
			<?php tcb_icon( 'close2' ) ?>
		</button>
		<ul class="state-steps">
			<?php foreach ( $states as $index => $s ) : ?>
				<li data-fn="select" data-id="<?php echo $s['id'] ?>" class="click<?php echo $s['id'] == $current_design['id'] ? ' state-active' : '' ?>">
					<button data-fn="duplicate"
							data-id="<?php echo $s['id'] ?>"
							title="<?php echo __( 'Duplicate state', TVE_Ult_Const::T ) ?>"
							class="state-clone click btn-icon"><?php tcb_icon( 'clone' ) ?></button>
					<?php if ( $index > 0 ) : ?>
						<button data-fn="remove"
								data-id="<?php echo $s['id'] ?>"
								title="<?php echo __( 'Delete state', TVE_Ult_Const::T ) ?>"
								class="state-delete btn-icon click"><?php tcb_icon( 'trash' ) ?></button>
						<button data-fn="edit"
								data-id="<?php echo $s['id'] ?>"
								data-state-name="<?php echo $s['post_title']; ?>"
								title="<?php echo __( 'Edit state', TVE_Ult_Const::T ) ?>"
								class="state-edit btn-icon click"><?php tcb_icon( 'edit' ) ?></button>
					<?php endif ?>
					<span class="state-name"><?php echo $s['post_title'] . ( empty( $s['parent_id'] ) ? '<strong> (' . __( 'Main', TVE_Ult_Const::T ) . ')</strong>' : '' ); ?></span>
				</li>
			<?php endforeach ?>
			<li data-fn="add"
				class="state-add click">
				<?php tcb_icon( 'plus' ) ?>
				<?php echo __( 'Add new state', TVE_Ult_Const::T ) ?>
			</li>
		</ul>
	</div>
<?php
if ( empty( $do_not_wrap ) ) :
	?>
	<div class="states-button-container">
		<button class="states-expand click" data-fn="expand">+</button>
	</div>
	</div>
<?php endif ?>