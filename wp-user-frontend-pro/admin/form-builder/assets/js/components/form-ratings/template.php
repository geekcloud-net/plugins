<div class="wpuf-fields">
    <div class="br-wrapper br-theme-css-stars">
        <div class="br-widget">
            <a v-for="option in field.options" href="#"></a>
        </div>
    </div>

    <span v-if="field.help" class="wpuf-help">{{ field.help }}</span>
</div>
