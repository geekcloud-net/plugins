<?php
/**
 * notice to be displayed if license not validated - going to load the styles inline because there are so few lines and not worth an extra server hit.
 */
?>
<div id="thrive_architect_license_wrapper">
	<div id="thrive_architect_license_logo">
		<img src="<?php echo tve_editor_url() . '/admin/assets/images/admin-logo.png'; ?>">
	</div>
	<div id="thrive_architect_license_text">
		<p><?php echo sprintf( __( 'You need to %s before you can use the editor!', 'thrive-cb' ), '<a class="tve-license-link" href="' . admin_url( 'admin.php?page=tve_dash_license_manager_section&return=' . rawurlencode( tcb_get_editor_url() ) ) . '">' . __( 'activate your license', 'thrive-cb' ) . '</a>' ) ?></p>
	</div>
</div>
<style type="text/css">
	#thrive_architect_license_wrapper {
		text-align: center;
		top: 50%;
		left: 40%;
		margin-top: -100px;
		margin-left: -250px;
		z-index: 3000;
		position: fixed;
		background: #FFF;
		display: flex;
	}

	#thrive_architect_license_logo {
		background: #58a245;
		padding: 50px;
	}

	#thrive_architect_license_text {
		padding: 50px;
		position: relative;
		font-size: 20px;
		max-width: 300px;
	}

	#thrive_architect_license_text a, #thrive_architect_license_text a:active, #thrive_architect_license_text a:visited {
		color: #58a245;
	}

	#thrive_architect_license_text::before {
		content: '';
		left: 0;
		top: 0;
		position: absolute;
		width: 0;
		height: 0;
		border-left: 0 solid transparent;
		border-right: 40px solid transparent;
		border-bottom: 174px solid #58a245;
	}
</style>
