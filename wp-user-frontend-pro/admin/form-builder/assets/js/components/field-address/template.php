<div class="panel-field-opt panel-field-opt-address">
    <label class="label-hr">{{ option_field.title }}</label>

    <ul class="address-fields">
        <li
            v-for="(address, field) in editing_form_field.address"
        >
            <template v-if="'country_select' !== field">
                <div class="clearfix address-title-header">
                    <label class="pull-left">
                        <input
                            type="checkbox"
                            :checked="address.checked"
                            @click="toggle_address_checked(field)"
                        > {{ i18n[field] }}
                    </label>

                    <div class="pull-right">
                        <label v-show="show_details[field]">
                            <input
                                type="checkbox"
                                :checked="address.required"
                                @click="toggle_address_required(field)"
                            > <?php _e( 'Required', 'wpuf-pro' ); ?>
                        </label>

                        <button
                            type="button"
                            class="button button-link button-dropdown"
                            @click="toggle_show_details(field)"
                        >
                            <i class="fa fa-caret-down"></i>
                        </button>
                    </div>
                </div>

                <div v-show="show_details[field]" class="clearfix address-input-fields">
                    <p class="pull-left">
                        <label><?php _e( 'Label', 'wpuf-pro' ); ?> <input type="text" v-model="address.label"></label>
                    </p>

                    <p class="pull-left">
                        <label><?php _e( 'Default', 'wpuf-pro' ); ?> <input type="text" v-model="address.value"></label>
                    </p>

                    <p class="pull-left">
                        <label><?php _e( 'Placeholder', 'wpuf-pro' ); ?> <input type="text" v-model="address.placeholder"></label>
                    </p>
                </div>
            </template>

            <template v-else>
                <div class="clearfix address-title-header">
                    <label class="pull-left">
                        <input
                            type="checkbox"
                            :checked="address.checked"
                            @click="toggle_address_checked(field)"
                        > {{ i18n[field] }}
                    </label>

                    <div class="pull-right">
                        <label v-show="show_details[field]">
                            <input
                                type="checkbox"
                                :checked="address.required"
                                @click="toggle_address_required(field)"
                            > <?php _e( 'Required', 'wpuf-pro' ); ?>
                        </label>

                        <button
                            type="button"
                            class="button button-link button-dropdown"
                            @click="toggle_show_details(field)"
                        >
                            <i class="fa fa-caret-down"></i>
                        </button>
                    </div>
                </div>

                <div v-show="show_details[field]" class="clearfix address-input-fields country-label">
                    <p>
                        <label><?php _e( 'Label', 'wpuf-pro' ); ?> <input type="text" v-model="address.label"></label>
                    </p>
                </div>

                <div v-show="show_details[field]" class="address-country-default address-input-fields">
                    <label>
                        <?php _e( 'Default Country', 'wpuf-pro' ); ?>

                        <select class="default-country" v-model="default_country">
                            <option value=""><?php _e( 'Select Country', 'wpuf-pro' ); ?></option>
                            <option v-for="country in countries" :value="country.code">{{ country.name }}</option>
                        </select>
                    </label>

                    <div class="panel-field-opt-select country-list-selector-container">
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
            </template>
        </li>
    </ul>
</div>
