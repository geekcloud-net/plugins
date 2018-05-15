<div class="wpuf-toc-container">
    <div class="wpuf-label"></div>

    <div :class="['wpuf-fields clearfix', field.show_checkbox ? 'has-toc-checkbox' : '']">
        <span v-if="field.show_checkbox" class="wpuf-toc-checkbox">
            <input type="checkbox">
        </span>

        <div class="wpuf-toc-description" v-html="content"></div>
    </div>
</div>

