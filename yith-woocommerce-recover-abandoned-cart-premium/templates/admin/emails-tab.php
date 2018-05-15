<div class="wrap">
	<h2><?php _e('Email Templates', 'yith-woocommerce-recover-abandoned-cart') ?> <a href="<?php echo esc_url( add_query_arg( 'post_type', YITH_WC_Recover_Abandoned_Cart_Email()->post_type_name, admin_url('post-new.php') ) ) ?>" class="add-new-h2">Add New</a></h2>

	<div id="poststuff">
		<div id="post-body" class="metabox-holder">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<form method="post">
						<?php
						$this->cpt_obj_emails->prepare_items();
						$this->cpt_obj_emails->display(); ?>
					</form>
				</div>
			</div>
		</div>
		<br class="clear">
	</div>
</div>