<div class="wpuf-fields">
    <div :class="['wpuf-form-google-map-container', 'yes' === field.address ? 'show-search-box': 'hide-search-box']">
        <input class="wpuf-google-map-search" type="text" placeholder="<?php _e( 'Search address', 'wpuf-pro' ); ?>">
        <div class="wpuf-form-google-map"></div>
    </div>
    <div :class="['wpuf-fields clearfix', field.directions ? 'has-directions-checkbox' : '']">
	    <span v-if="field.directions" class="wpuf-directions-checkbox">
	        <a class="btn btn-brand btn-sm" href="#" ><i class="fa fa-map-marker" aria-hidden="true"></i><?php _e( 'Directions »', 'wpuf-pro' ); ?></a>
	    </span>
    </div>

    <span v-if="field.help" class="wpuf-help">{{ field.help }}</span>
</div>
