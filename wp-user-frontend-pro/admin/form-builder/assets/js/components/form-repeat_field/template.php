<div class="wpuf-fields">
    <table v-if="'true' === field.multiple" class="wpuf-repeatable-field">
        <thead>
            <th v-for="column in field.columns">
                {{ column }}
            </th>
            <th>&nbsp;</th>
        </thead>
        <tbody>
            <tr>
                <td v-for="column in field.columns">
                    <input
                        type="text"
                        placeholder=""
                        value=""
                        :size="field.size"
                    >
                </td>

                <td class="wpuf-repeater-buttons">
                    <i class="wpuf-repeater-add">
                        <img src="<?php echo WPUF_ASSET_URI . '/images/icon-plus.png' ?>" alt="">
                    </i>
                    <i class="wpuf-repeater-remove">
                        <img src="<?php echo WPUF_ASSET_URI . '/images/icon-minus.png' ?>" alt="">
                    </i>
                </td>
            </tr>
        </tbody>
    </table>

    <table v-else class="wpuf-repeatable-field">
        <tbody>
            <tr>
                <td>
                    <input
                        type="text"
                        :placeholder="field.placeholder"
                        :value="field.default"
                        :size="field.size"
                    >
                </td>

                <td class="wpuf-repeater-buttons">
                    <i class="wpuf-repeater-add">
                        <img src="<?php echo WPUF_ASSET_URI . '/images/icon-plus.png' ?>" alt="">
                    </i>
                    <i class="wpuf-repeater-remove">
                        <img src="<?php echo WPUF_ASSET_URI . '/images/icon-minus.png' ?>" alt="">
                    </i>
                </td>
            </tr>
        </tbody>
    </table>

    <span v-if="field.help" class="wpuf-help">{{ field.help }}</span>
</div>
