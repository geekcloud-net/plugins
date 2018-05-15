<div v-show="met_dependencies" class="panel-field-opt panel-field-opt-repeater-columns">

    <label>
        {{ option_field.title }} <help-text v-if="option_field.help_text" :text="option_field.help_text"></help-text>
    </label>

    <ul class="repeater-columns">
        <li v-for="(column, index) in editing_form_field.columns" class="clearfix repeater-single-column" :data-index="index">
            <div class="sorter">
                <i class="fa fa-bars sort-handler"></i>
            </div>

            <div class="input-container">
                <input type="text" v-model="editing_form_field.columns[index]">
            </div>

            <div class="action-buttons">
                    <i class="fa fa-plus-circle" @click="add_column"></i>
                    <i class="fa fa-minus-circle" @click="delete_column(index)"></i>
            </div>
        </li>
    </ul>
</div>
