<div>
    <div class="panel-field-opt panel-field-opt-text">
        <label>
            <?php _e( 'Section Name', 'wpuf-pro' ); ?> <help-text text="<?php _e( 'Title', 'wpuf-pro' ); ?>"></help-text>

            <input type="text" v-model="editing_form_field.label">
        </label>
    </div>

    <div class="panel-field-opt panel-field-opt-text">
        <label>
            <?php _e( 'Previous Button Text', 'wpuf-pro' ); ?> <help-text text="<?php _e( 'Previous Button Text', 'wpuf-pro' ); ?>"></help-text>

            <input type="text" v-model="editing_form_field.step_start.prev_button_text">
        </label>
    </div>

    <div class="panel-field-opt panel-field-opt-text">
        <label>
            <?php _e( 'Next Button Text', 'wpuf-pro' ); ?> <help-text text="<?php _e( 'Next Button Text', 'wpuf-pro' ); ?>"></help-text>

            <input type="text" v-model="editing_form_field.step_start.next_button_text">
        </label>
    </div>
</div>
