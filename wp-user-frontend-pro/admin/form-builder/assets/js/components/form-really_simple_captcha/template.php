<div class="wpuf-fields">
    <template v-if="!is_rs_captcha_active">
        <p v-html="no_plugin_msg"></p>
    </template>

    <template v-else>
        <img class="wpuf-rs-captcha-placeholder" src="<?php echo WPUF_PRO_ASSET_URI . '/images/really-simple-captcha-placeholder.png' ?>" alt="">
        <input type="text">
    </template>
</div>
