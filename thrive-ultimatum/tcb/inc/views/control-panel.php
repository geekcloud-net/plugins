<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/** @var $data TCB_Editor */
?>

<div id="tve_cpanel">

	<div class="tve-header">
		<span class="click settings-icon header-icon">
			<?php tcb_icon( 'settings' ); ?>
		</span>
		<span class="click back-icon header-icon" style="display: none;">
			<?php tcb_icon( 'back' ); ?>
		</span>
		<span class="click uber-name"><strong>Thrive</strong> Architect</span>
	</div>

	<div class="tve-search sidebar-block">
		<input type="text" placeholder="<?php echo __( 'Search Elements', 'thrive-cb' ) ?>">
		<?php tcb_icon( 'src' ); ?>
        <a href="javascript:void(0)" title="Clear search" style="display:none" class="clear-search click" data-fn="clear_search" data-index="0">
		<?php tcb_icon( 'clear' ); ?>
        </a>
	</div>

	<div class="tve-active-element sidebar-block" style="display: none;">
		<div class="element-name"></div>
		<div class="element-states"></div>
	</div>

	<div id="tve-scroll-panel">
		<div class="tve-panel">
			<div id="tve-elements" class="sidebar-block">
				<?php foreach ( $data->elements->get_for_front() as $category => $elements ) : ?>
					<div <?php echo empty( $elements ) ? 'style="display: none;"' : ''; ?> class="tve-category" data-category="<?php echo $category; ?>"><?php echo $category; ?></div>
					<?php foreach ( $elements as $element ) : ?>
						<div class="tve-element" data-elem="<?php echo $element->tag() ?>" data-alternate="<?php echo $element->alternate() ?>" draggable="true"><?php /* commented out as a fix for the reported performance issues
							<div style="position:absolute; top: 10%;left:25%;z-index:-1">
								<span class="tcb-drag-image">
									<?php tcb_icon( $element->icon() ); ?>
								</span>
							</div> */ ?>
							<button class="tve-element-pin <?php echo $element->pinned ? 'pinned' : ''; ?>"
									data-default-category="<?php echo $element->category() ?>"
									data-element="<?php echo $element->tag() ?>"></button>
							<div class="item">
								<span class="tve-e-icon">
									<?php tcb_icon( $element->icon() ); ?>
								</span>
								<span class="tve-e-name">
									<?php echo $element->name(); ?>
								</span>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</div>

			<div id="tve-sidebar-settings" class="sidebar-block" style="display: none;">
				<?php tcb_template( 'sidebar-settings' ) ?>
			</div>

            <div id="tve-editor-settings-elements" class="sidebar-block" style="display: none;">
				<?php tcb_template( 'editor-settings-elements' ) ?>
			</div>

			<div id="tve-components" style="display: none;" class="tcb-flex sidebar-block">
				<div id="tcb-drop-panels"></div>
				<?php $data->elements->output_components(); ?>
			</div>

			<div class="tcb-inline-tinymce" id="tcb-inline-tinymce"></div>

			<div id="migrate-element" style="display: none;">
				<span><?php echo __( 'Migrate Element', 'thrive-cb' ) ?></span>
				<span><?php echo __( 'You can migrate this element to Thrive Architect. Click the following button to migrate the element', 'thrive-cb' ) ?></span>
				<button class="click tve-btn tve-button grey" data-fn="migrate_element"><?php echo __( 'Migrate element', 'thrive-cb' ) ?></button>
			</div>

			<div id="multiple-select-elements">
				<span class="tve-header tve-header-white"><?php echo __( 'Multiple-Selected Mode', 'thrive-cb' ) ?></span>
				<div class="row padding-top-15">
					<div class="col-xs-12">
						<p><?php echo __( 'Multiple selection mode activated. You can now move the selected elements across the page.', 'thrive-cb' ) ?></p>
					</div>
				</div>
				<div class="row padding-top-15">
					<div class="col-xs-12 tcb-text-center">
						<button class="click tve-btn tve-button grey" data-fn="exit_multiple_selected_mode"><?php echo __( 'Exit mode', 'thrive-cb' ) ?></button>
					</div>
				</div>
			</div>

			<?php /* custom sidebar states for elements */ ?>
			<?php foreach ( $data->elements->custom_sidebars() as $key => $element_sidebar ) : ?>
				<div class="sidebar-block" style="display: none" id="sidebar-<?php echo $key ?>"><?php echo $element_sidebar ?></div>
			<?php endforeach ?>

			<div style="display: none" id="tve-static-elements">
				<?php echo $data->elements->layout(); ?>
			</div>
		</div>
	</div>

	<div class="tve-settings tcb-relative" id="tcb-editor-settings"><?php tcb_template( 'editor-settings' ) ?></div>
	<div class="panel-extend">
		<div class="extend-bg"></div>
		<div class="extend-bg extend-inner">
			<button class="panel-arrow click" data-fn="togglePanel" data-title-collapsed="<?php echo __( 'Expand panel', 'thrive-cb' ) ?>"
					data-title-expanded="<?php echo __( 'Collapse panel', 'thrive-cb' ) ?>" title="<?php echo __( 'Collapse panel', 'thrive-cb' ) ?>"></button>
		</div>
	</div>
</div>
