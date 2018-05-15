<h2 class="tvd-card-title"><?php echo $this->getTitle() ?></h2>
<?php
/** @var $this Thrive_Dash_List_Connection_MailPoet */
?>
<?php $installed = $this->pluginInstalled(); ?>
<?php if ( ! empty( $installed ) ) : ?>
	<div class="tvd-row">
		<p><?php echo __( 'Click the button below to enable MailPoet integration.', TVE_DASH_TRANSLATE_DOMAIN ) ?></p>
	</div>
	<form>
		<input type="hidden" name="api" value="<?php echo $this->getKey() ?>">

		<?php if ( count( $installed ) > 1 ) : ?>
			<?php $version = $this->param( 'version' ); ?>
			<div class="tvd-row">
				<div class="tvd-col tvd-s12 tvd-m6">
					<p>
						<input class="tvd-new-connection-yes" name="connection[version]" type="radio" value="2"
							   id="tvd-new-connection-yes" <?php echo empty( $version ) || $version == 2 ? 'checked="checked"' : ''; ?> />
						<label for="tvd-new-connection-yes"><?php echo __( 'Version 2', TVE_DASH_TRANSLATE_DOMAIN ); ?></label>
					</p>
				</div>
				<div class="tvd-col tvd-s12 tvd-m6">
					<p>

						<input class="tvd-new-connection-no" name="connection[version]" type="radio" value="3"
							   id="tvd-new-connection-no" <?php echo $version == 3 ? 'checked="checked"' : ''; ?> />
						<label for="tvd-new-connection-no"><?php echo __( 'Version 3', TVE_DASH_TRANSLATE_DOMAIN ); ?></label>
					</p>
				</div>
			</div>
		<?php else : ?>
			<input type="hidden" name="connection[version]" value="<?php echo $installed[0] ?>">
		<?php endif; ?>
	</form>
	<div class="tvd-card-action">
		<div class="tvd-row tvd-no-margin">
			<div class="tvd-col tvd-s12 tvd-m6">
				<a class="tvd-api-cancel tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-dark tvd-full-btn tvd-waves-effect"><?php echo __( 'Cancel', TVE_DASH_TRANSLATE_DOMAIN ) ?></a>
			</div>
			<div class="tvd-col tvd-s12 tvd-m6">
				<a class="tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-full-btn tvd-api-connect"
				   href="javascript:void(0)"><?php echo __( 'Connect', TVE_DASH_TRANSLATE_DOMAIN ) ?></a>
			</div>
		</div>
	</div>
<?php else : ?>
	<p><?php echo __( 'You currently do not have any MailPoet WP plugin installed or activated.', TVE_DASH_TRANSLATE_DOMAIN ) ?></p>
	<br>
	<div class="tvd-card-action">
		<div class="tvd-row tvd-no-margin">
			<div class="tvd-col tvd-s12">
				<a class="tvd-api-cancel tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-dark tvd-full-btn tvd-waves-effect"><?php echo __( 'Cancel', TVE_DASH_TRANSLATE_DOMAIN ) ?></a>
			</div>
		</div>
	</div>
<?php endif ?>
