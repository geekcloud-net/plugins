<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 7/21/2017
 * Time: 3:27 PM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>

<h2 class="tcb-modal-title"><?php echo __( 'Reset to default content', TVE_Ult_Const::T ) ?></h2>
<div class="margin-top-20">
	<?php echo __( 'Are you sure you want to reset this variation to the default template? This action cannot be undone.', TVE_Ult_Const::T ) ?>
</div>
<div class="tcb-modal-footer clearfix padding-top-20 row end-xs">
	<div class="col col-xs-12">
		<button type="button" class="tcb-right tve-button medium red click" data-fn="reset">
			<?php echo __( 'Reset to default content', TVE_Ult_Const::T ) ?>
		</button>
	</div>
</div>
