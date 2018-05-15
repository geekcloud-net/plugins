<h2 class="tvd-card-title"><?php echo $this->getTitle() ?></h2>
<div class="tvd-row">
	<form class="tvd-col tvd-s12">
		<input type="hidden" name="api" value="<?php echo $this->getKey() ?>"/>
		<div class="tvd-input-field">
			<input id="tvd-wj-api-key" type="text" name="connection[key]"
			       value="<?php echo $this->param( 'key' ) ?>">
			<label for="tvd-wj-api-key"><?php echo __( "API key", TVE_DASH_TRANSLATE_DOMAIN ) ?></label>
		</div>
		<?php $version = $this->param( 'version' ); ?>

        <div class="tvd-col tvd-s12 tvd-m6 tvd-no-padding">
            <p>
                <input class="tvd-version-1 tvd-api-show-extra-options" name="connection[version]" type="radio" value="1"
                       id="tvd-version-1" <?php echo ! empty( $version ) && $version == 1 ? 'checked="checked"' : ''; ?> />
                <label for="tvd-version-1"><?php echo __( 'New version', TVE_DASH_TRANSLATE_DOMAIN ); ?></label>
            </p>
        </div>

        <div class="tvd-col tvd-s12 tvd-m6 tvd-no-padding">
            <p>
                <input class="tvd-version-0 tvd-api-hide-extra-options" name="connection[version]" type="radio" value="0"
                       id="tvd-version-0" <?php echo empty( $version ) || $version == 0 ? 'checked="checked"' : ''; ?> />
                <label for="tvd-version-0"><?php echo __( 'Old version', TVE_DASH_TRANSLATE_DOMAIN ); ?></label>
            </p>
        </div>
    </form>
</div>
<div class="tvd-row tvd-card-title">
	<?php
	echo __( "When you switch between versions, please make sure to update all forms connected to this API." );
	?>
</div>
<div class="tvd-card-action">
	<div class="tvd-row tvd-no-margin">
		<div class="tvd-col tvd-s12 tvd-m6">
			<a class="tvd-api-cancel tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-dark tvd-full-btn tvd-waves-effect"><?php echo __( "Cancel", TVE_DASH_TRANSLATE_DOMAIN ) ?></a>
		</div>
		<div class="tvd-col tvd-s12 tvd-m6">
			<a class="tvd-api-connect tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-full-btn"><?php echo __( "Connect", TVE_DASH_TRANSLATE_DOMAIN ) ?></a>
		</div>
	</div>
</div>
