<div v-if="wpuf_cond && wpuf_cond.condition_status" class="panel-field-opt panel-field-opt-conditional-logic">
    <label>
        <?php _e( 'Conditional Logic', 'wpuf-pro' ); ?>
    </label>

    <ul class="list-inline">
        <li>
            <label><input type="radio" value="yes" v-model="wpuf_cond.condition_status"> <?php _e( 'Yes', 'wpuf-pro' ); ?></label>
        </li>

        <li>
            <label><input type="radio" value="no" v-model="wpuf_cond.condition_status"> <?php _e( 'No', 'wpuf-pro' ); ?></label>
        </li>
    </ul>

    <div v-if="'yes' === wpuf_cond.condition_status" class="condiotional-logic-container">
        <ul class="condiotional-logic-repeater">
            <li v-for="(condition, index) in conditions" class="clearfix">
                <div class="cond-field">
                    <select v-model="condition.name" @change="on_change_cond_field(index, condition.name)">
                        <option value=""><?php _e( '- select -', 'wpuf-pro' ); ?></option>
                        <option
                            v-for="dep_field in dependencies"
                            :value="dep_field.name"
                        >{{ dep_field.label }}</option>
                    </select>
                </div>

                <div class="cond-operator">
                    <select v-model="condition.operator">
                        <option value="="><?php _e( 'is', 'wpuf-pro' ); ?></option>
                        <option value="!="><?php _e( 'is not', 'wpuf-pro' ); ?></option>
                    </select>
                </div>

                <div class="cond-option">
                    <select v-model="condition.option">
                        <option value=""><?php _e( '- select -', 'wpuf-pro' ); ?></option>
                        <option
                            v-for="cond_option in get_cond_options(condition.name)"
                            :value="cond_option.opt_name"
                        >
                            {{ cond_option.opt_title }}
                        </option>
                    </select>
                </div>

                <div class="cond-action-btns">
                    <i class="fa fa-plus-circle" @click="add_condition"></i>
                    <i class="fa fa-minus-circle pull-right" @click="delete_condition(index)"></i>
                </div>
            </li>
        </ul>

        <p>
            <?php
                printf(
                    __( 'Show this field when %s of these rules are met', 'wpuf-pro' ),
                    '<select v-model="wpuf_cond.cond_logic"><option value="any">' . __( 'any', 'wpuf-pro' ) . '</option><option value="all">' . __( 'all', 'wpuf-pro' ) . '</option></select>'
                );
            ?>
        </p>
    </div>
</div>
