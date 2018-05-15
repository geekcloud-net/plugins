<div>
    <div class="panel-field-opt panel-field-opt-select">
        <label>
            <?php _e( 'Default Country', 'wpuf-pro' ); ?>

            <select class="default-country" v-model="default_country">
                <option value=""><?php _e( 'Select Country', 'wpuf-pro' ); ?></option>
                <option v-for="country in countries" :value="country.code">{{ country.name }}</option>
            </select>
        </label>
    </div>

    <div class="panel-field-opt panel-field-opt-select">
        <label class="label-title-block"><?php _e( 'Country List', 'wpuf-pro' ); ?></label>

        <div class="button-group wpuf-flex-button-group">
            <button
                v-for="button in visibility_buttons"
                type="button"
                :class="['button', button.name === active_visibility ? 'active button-primary' : '' ]"
                @click.prevent="set_visibility(button.name)"
            >{{ button.title }}</button>
        </div>

        <select
            v-show="'all' === active_visibility"
            :class="['country-list-selector selectize-element-group', 'all' === active_visibility ? 'active' : '']"
            disabled
        >
            <option value=""><?php _e( 'Select Countries', 'wpuf-pro' ); ?></option>
        </select>

        <select
            v-show="'hide' === active_visibility"
            :class="['country-list-selector selectize-element-group', 'hide' === active_visibility ? 'active' : '']"
            v-model="country_in_hide_list"
            data-visibility="hide"
            multiple
        >
            <option v-for="country in countries" :value="country.code">{{ country.name }}</option>
        </select>

        <select
            v-show="'show' === active_visibility"
            :class="['country-list-selector selectize-element-group', 'show' === active_visibility ? 'active' : '']"
            v-model="country_in_show_list"
            data-visibility="show"
            multiple
        >
            <option v-for="country in countries" :value="country.code">{{ country.name }}</option>
        </select>
    </div>
</div>
