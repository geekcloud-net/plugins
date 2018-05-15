<div>
    <div class="wpuf-label">
        <label for="addr_field_label">
            {{ field.label }} <span v-if="'yes' === field.required" class="required">*</span>
        </label>
    </div>

    <div class="wpuf-fields">

        <div v-for="(addr_field_details, addr_field) in field.address" :class="['wpuf-address-field', addr_field]" v-if="addr_field_details.checked">

            <div class="wpuf-sub-fields">
                <template v-if="'country_select' !== addr_field">
                    <input
                        type="text"
                        class="textfield"
                        size="40"
                        :value="addr_field_details.value"
                        :placeholder="addr_field_details.placeholder"
                        :required="'checked' === addr_field_details.required"
                    >
                </template>

                <template v-else>
                    <select :required="'checked' === addr_field_details.required" v-model=default_country>
                        <option value=""><?php _e( 'Select Country', 'wpuf-pro' ); ?></option>
                        <option v-for="country in countries" :value="country.code">{{ country.name }}</option>
                    </select>
                </template>
            </div>

            <label class="wpuf-form-sub-label">
                {{ addr_field_details.label }}
                <span v-if="'checked' === addr_field_details.required" class="required">*</span>
            </label>
        </div>

        <div class="clear"></div>
        <span v-if="field.help" class="wpuf-help">{{ field.help }}</span>
    </div>
</div>
