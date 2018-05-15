<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * @var $data array sent from add_footer_modals()
 */
$icon_data = get_option( 'thrive_icon_pack' );
if ( empty( $icon_data['icons'] ) ) {
	$icon_data['icons'] = array();
}
$all = apply_filters( 'tcb_get_extra_icons', $icon_data['icons'], $data['post']->ID ); //
if ( ! is_array( $all ) ) {
	//TODO: Refactor this. I don't think $all is needed anymore here
	$all = $icon_data;
}
?>
<h2 class="tcb-modal-title"><?php echo __( 'Choose an icon', 'thrive-cb' ) ?></h2>
<div class="row">
	<div class="col col-xs-12 tve-search-field">
		<input type="text" class="tve-search-icon" data-search-for="tve-icomoon-icons" placeholder="<?php echo __( 'Search Icon', 'thrive-cb' ) ?>"/>
	</div>
</div>
<div class="tve-icons-wrapper">
	<div id="tve-icomoon-icons" class="tve-icons-list">
		<?php foreach ( $icon_data['icons'] as $class ) : ?>
			<?php $title = str_replace( 'icon-', '', $class ) ?>
			<span class="tve-icon tve-icomoon-icon click"
				  title="<?php echo str_replace( '-', ' ', $title ) ?>"
				  data-cls="<?php echo $class ?>"
				  data-fn="select_icon">
					<span class="<?php echo $class ?>"></span>
				</span>
		<?php endforeach ?>
	</div>
</div>

<div class="row padding-top-10">
	<div class="col col-xs-12">
		<button type="button" class="tcb-right tve-button medium green tcb-modal-save"><?php echo __( 'Select', 'thrive-cb' ) ?></button>
	</div>
</div>
<?php unset( $icon_click ) ?>
