<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 7/21/2017
 * Time: 12:11 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/*
 * Lightbox content for listing all the templates for a campaign design
 */
global $design;
if ( empty( $design ) ) {
	$design = tve_ult_get_design( $_REQUEST[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ] );
}

$design_type_details = TVE_Ult_Const::design_details( $design['post_type'] );
$current_template    = ! empty( $design[ TVE_Ult_Const::FIELD_TEMPLATE ] ) ? $design[ TVE_Ult_Const::FIELD_TEMPLATE ] : '';
$templates           = TU_Template_Manager::get_templates( $design['post_type'] );


?>

<h2 class="tcb-modal-title"><?php echo sprintf( __( 'Choose the %s template you would like to use for this design', TVE_Ult_Const::T ), $design_type_details['name'] ); ?></h2>
<div class="margin-top-20">
	<?php echo __( 'If you change your the template without saving the current revision, you won\'t be able to revert back to it later.', TVE_Ult_Const::T ) ?>
</div>
<div class="tve-templates-wrapper">
	<div class="tve-header-tabs">
		<div class="tab-item active click" data-fn="tab_click" data-content="default"><?php echo __( 'Default Templates', TVE_Ult_Const::T ); ?></div>
		<div class="tab-item click" data-fn="tab_click" data-content="saved"><?php echo sprintf( __( 'Saved %s Templates', TVE_Ult_Const::T ), $design_type_details['name'] ); ?></div>
	</div>
	<div class="tve-tabs-content">
		<div class="tve-tab-content active" data-content="default">
			<div class="tve-default-templates-list expanded-set">
				<?php foreach ( $templates as $data ) : ?>
					<div class="tve-template-item">
						<div class="template-wrapper click<?php echo $current_template == $data['key'] ? ' active' : '' ?>" data-fn="select_template" data-key="<?php echo $data['key']; ?>">
							<div class="template-thumbnail" style="background-image: url('<?php echo $data['thumbnail']; ?>')"></div>
							<div class="template-name">
								<?php echo $data['name']; ?>
							</div>
							<div class="selected"></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="tve-tab-content" data-content="saved">
			<div class="tve-saved-templates-list expanded-set"></div>
		</div>
		<div class="tve-template-preview"></div>
	</div>
</div>
<div class="tcb-modal-footer clearfix padding-top-20 row end-xs">
	<div class="col col-xs-12">
		<button type="button" class="tcb-right tve-button medium green click" data-fn="save">
			<?php echo __( 'Choose Template', TVE_Ult_Const::T ) ?>
		</button>
	</div>
</div>
