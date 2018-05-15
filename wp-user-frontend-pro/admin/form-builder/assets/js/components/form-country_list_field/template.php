<div class="wpuf-fields">
    <select v-model="default_country">
        <option value=""><?php _e( 'Select Country', 'wpuf-pro' ); ?></option>
        <option v-for="country in countries" :value="country.code">{{ country.name }}</option>
    </select>

    <span v-if="field.help" class="wpuf-help">{{ field.help }}</span>
</div>
