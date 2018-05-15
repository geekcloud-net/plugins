<div>

    <div class="wpuf-password-field">
        <div class="wpuf-label">
            <label :for="'wpuf-' + field.name ? field.name : 'cls'">
                {{ field.label }} <span v-if="field.required && 'yes' === field.required" class="required">*</span>
            </label>
        </div>

        <div class="wpuf-fields">
            <input
                type="text"
                :class="class_names('textfield')"
                :placeholder="field.placeholder"
                :value="field.default"
                :size="field.size"
            >
            <span v-if="field.help" class="wpuf-help">{{ field.help }}</span>
        </div>
    </div>

    <div v-if="field.repeat_pass && 'yes' === field.repeat_pass" class="wpuf-password-field">
        <div class="wpuf-label">
            <label :for="'wpuf-' + field.name ? field.name : 'cls'">
                {{ field.re_pass_label }} <span class="required">*</span>
            </label>
        </div>

        <div class="wpuf-fields">
            <input
                type="text"
                :size="field.size"
            >
        </div>
    </div>

    <div v-if="field.pass_strength && 'yes' === field.pass_strength" class="wpuf-password-field">
        <div class="wpuf-label">
        </div>
        <div class="wpuf-fields">
            <div class="password-strength-meter">Strength indicator</div>
        </div>
    </div>
</div>
