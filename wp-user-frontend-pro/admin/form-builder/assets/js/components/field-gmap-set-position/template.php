<div>
    <label class="label-hr margin-bottom-10">
        <?php _e( 'Map Settings', 'wpuf-pro' ); ?>
    </label>

    <p>
        <em><?php _e( 'Set default co-ordinate and zoom level', 'wpuf-pro' ); ?></em>
    </p>

    <input class="wpuf-google-map-search" type="text" placeholder="<?php _e( 'Search address', 'wpuf-pro' ); ?>">
    <div class="wpuf-field-google-map"></div>

    <div class="panel-field-opt panel-field-opt-checkbox">
        <ul>
            <li>
                <label>
                    <input
                        type="checkbox"
                        :checked="'yes' === editing_form_field.address"
                        @click="toggle_checkbox_field('address')"
                    > <?php _e( 'Show address search box', 'wpuf-pro' ); ?>
                </label>
            </li>
        </ul>
    </div>
</div>
