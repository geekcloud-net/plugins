<div>
    <div class="wpuf-label"><?php _e( 'Action Hook', 'wpuf-pro' ); ?></div>

    <div class="wpuf-fields">
        <span v-if="!field.label"><em><?php _e( "hook name isn't set", 'wpuf-pro' ); ?></em></span>
        <span v-else>{{ field.label }}</span>
    </div>
</div>
