<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
$admin_base_url = admin_url( '/', is_ssl() ? 'https' : 'admin' );
// for some reason, the above line does not work in some instances
if ( is_ssl() ) {
	$admin_base_url = str_replace( 'http://', 'https://', $admin_base_url );
}
?>

<div id="tve-menu-component" class="tve-component" data-view="CustomMenu">
	<div class="action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Custom Menu Options', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-control tve-select-menu" data-key="SelectMenu" data-initializer="selectMenu"></div>
			<div class="tve-control tve-menu-direction margin-top-10" data-key="MenuDirection" data-initializer="menuDirection"></div>
			<div class="tve-control margin-top-10" data-view="MakePrimary"></div>
			<div class="margin-top-20">
				<div class="row">
					<div class="col-xs-12">
						<a class="tve-button blue tve-edit-menu" href="<?php echo $admin_base_url; ?>nav-menus.php?action=edit&menu=0" target="_blank" >
							<?php echo __( 'Edit Menu', 'thrive-cb' ) ?>
						</a>
					</div>
				</div>
			</div>
			<hr>
			<div class="tve-control" data-view="MainColor"></div>
			<div class="tve-control margin-top-10" data-view="ChildColor"></div>
			<div class="tve-control margin-top-10" data-view="ChildBackground"></div>
			<div class="tve-advanced-controls extend-grey">
				<div class="dropdown-header" data-prop="advanced">
				<span>
					<?php echo __( 'Advanced', 'thrive-cb' ); ?>
				</span>
					<i></i>
				</div>

				<div class="dropdown-content clear-top">
					<div class="grey-text">
						<?php echo __( 'Hover Colors', 'thrive-cb' ); ?>
					</div>
					<div class="tve-control margin-top-20" data-view="HoverMainColor"></div>
					<div class="tve-control margin-top-10" data-view="HoverMainBackground"></div>
					<div class="tve-control margin-top-10" data-view="HoverChildColor"></div>
					<div class="tve-control margin-top-10" data-view="HoverChildBackground"></div>
					<hr>
					<div class="tve-control margin-top-20" data-view="TriggerColor"></div>
				</div>
			</div>
		</div>
	</div>
</div>

