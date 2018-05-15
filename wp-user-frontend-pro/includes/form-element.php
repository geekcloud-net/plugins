<?php

class WPUF_pro_form_element extends WPUF_Admin_Template_Post {

    /**
     * add formbuilder's custom field button
     */
    public static function add_form_custom_buttons() {
        $title = esc_attr( __( 'Click to add to the editor', 'wpuf-pro' ) );
        ?>
        <button class="button" data-name="custom_repeater" data-type="repeat" title="<?php echo $title; ?>"><?php _e( 'Repeat Field', 'wpuf-pro' ); ?></button>
        <button class="button" data-name="custom_date" data-type="date" title="<?php echo $title; ?>"><?php _e( 'Date', 'wpuf-pro' ); ?></button>
        <button class="button" data-name="custom_image" data-type="image" title="<?php echo $title; ?>"><?php _e( 'Image Upload', 'wpuf-pro' ); ?></button>
        <button class="button" data-name="custom_file" data-type="file" title="<?php echo $title; ?>"><?php _e( 'File Upload', 'wpuf-pro' ); ?></button>
        <button class="button" data-name="custom_map" data-type="map" title="<?php echo $title; ?>"><?php _e( 'Google Maps', 'wpuf-pro' ); ?></button>
        <button class="button" data-name="country_select" data-type="select" title="<?php echo $title; ?>"><?php _e( 'Country List', 'wpuf-pro' ); ?></button>
        <button class="button" data-name="numeric_field" data-type="text" title="<?php echo $title; ?>"><?php _e( 'Numeric Field', 'wpuf-pro' ); ?></button>
        <button class="button" data-name="address_field" data-type="text" title="<?php echo $title; ?>"><?php _e( 'Address Field', 'wpuf-pro' ); ?></button>
        <button class="button" data-name="step_start" data-type="text" title="<?php echo $title; ?>"><?php _e( 'Step Start', 'wpuf-pro' ); ?></button>
    <?php
    }

    /**
     * add formbuilder's button in Others section
     */
    public static function add_form_other_buttons() {
        $title = esc_attr( __( 'Click to add to the editor', 'wpuf-pro' ) );
        ?>
        <button class="button" data-name="shortcode" data-type="action" title="<?php echo $title; ?>"><?php _e( 'Shortcode', 'wpuf-pro' ); ?></button>
        <button class="button" data-name="really_simple_captcha" data-type="rscaptcha" title="<?php echo $title; ?>"><?php _e( 'Really Simple Captcha', 'wpuf-pro' ); ?></button>
        <button class="button" data-name="action_hook" data-type="action" title="<?php echo $title; ?>"><?php _e( 'Action Hook', 'wpuf-pro' ); ?></button>
        <button class="button" data-name="toc" data-type="action" title="<?php echo $title; ?>"><?php _e( 'Term &amp; Conditions', 'wpuf-pro' ); ?></button>
        <button class="button" data-name="ratings" data-type="action" title="<?php echo $title; ?>"><?php _e( 'Ratings', 'wpuf-pro' ); ?></button>
    <?php
    }

    /**
     * Render form expiration tab
     */
    public static function render_form_expiration_tab() {
        global $post;

        $form_settings                = wpuf_get_form_settings( $post->ID );
        $is_post_exp_selected         = isset( $form_settings['expiration_settings']['enable_post_expiration'] )?'checked':'';
        $time_value                   = isset( $form_settings['expiration_settings']['expiration_time_value'] )?$form_settings['expiration_settings']['expiration_time_value']:1;
        $time_type                    = isset( $form_settings['expiration_settings']['expiration_time_type'] )?$form_settings['expiration_settings']['expiration_time_type']:'day';
        $expired_post_status          = isset( $form_settings['expiration_settings']['expired_post_status'] )?$form_settings['expiration_settings']['expired_post_status']:'draft';
        $is_enable_mail_after_expired = isset( $form_settings['expiration_settings']['enable_mail_after_expired'] )?'checked':'';
        $post_expiration_message      = isset( $form_settings['expiration_settings']['post_expiration_message'] )?$form_settings['expiration_settings']['post_expiration_message']:'';
        ?>
        <table class="form-table">
            <tr>
                <th><?php _e( 'Post Expiration', 'wpuf-pro' ); ?></th>
                <td>
                    <label>
                        <input type="checkbox" id="wpuf-enable_post_expiration" name="wpuf_settings[expiration_settings][enable_post_expiration]" value="on" <?php echo $is_post_exp_selected;?> />
                        <?php _e( 'Enable Post Expiration', 'wpuf-pro' ); ?>
                    </label>
                </td>
                 <p class="description"><a target="_blank" href="https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/using-post-expiration-wp-user-frontend/"><?php _e('Learn more about Automatic Post Expiration', 'wpuf-pro'); ?></a></p>
            </tr>
            <tr class="wpuf_expiration_field">
                <th><?php _e( 'Post Expiration Time', 'wpuf-pro' ); ?></th>
                <td>
                    <?php
                    $timeType_array = array(
                        'year' => 100,
                        'month' => 12,
                        'day' => 30
                    );

                    ?>
                    <select name="wpuf_settings[expiration_settings][expiration_time_value]" id="wpuf-expiration_time_value">
                        <?php
                        for( $i = 1; $i <= $timeType_array[$time_type]; $i++ ){
                            ?>
                            <option value="<?php echo $i; ?>" <?php echo $i == $time_value?'selected':''; ?> ><?php echo $i;?></option>
                        <?php
                        }
                        ?>
                    </select>
                    <select name="wpuf_settings[expiration_settings][expiration_time_type]" id="wpuf-expiration_time_type">
                        <?php
                        foreach( $timeType_array as $each_time_type=>$each_time_type_val ){
                            ?>
                            <option value="<?php echo $each_time_type;?>" <?php echo $each_time_type==$time_type?'selected':''; ?> ><?php echo ucfirst( $each_time_type ); ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr class="wpuf_expiration_field">
                <th>
                    Post Status :
                </th>
                <td>
                    <?php $post_statuses = get_post_statuses();
                    ?>
                    <select name="wpuf_settings[expiration_settings][expired_post_status]" id="wpuf-expired_post_status">
                        <?php
                        foreach( $post_statuses as $post_status => $text ){
                            ?>
                            <option value="<?php echo $post_status ?>" <?php echo ( $expired_post_status == $post_status )?'selected':''; ?> ><?php echo $text;?></option>
                        <?php
                        }
                        ?>
                    </select>
                    <p class="description"><?php echo _( 'Status of post after post expiration time is over ' ); ?></p>

                </td>
            </tr>
            <tr class="wpuf_expiration_field">
                <th>
                    Send Mail :
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="wpuf_settings[expiration_settings][enable_mail_after_expired]" value="on" <?php echo $is_enable_mail_after_expired;?> />
                        <?php echo _( 'Send Email to Author After Exceeding Post Expiration Time' );?>
                    </label>
                </td>
            </tr>
            <tr class="wpuf_expiration_field">
                <th>Post Expiration Message</th>
                <td>
                    <textarea name="wpuf_settings[expiration_settings][post_expiration_message]" id="wpuf-post_expiration_message" cols="50" rows="5"><?php echo $post_expiration_message; ?></textarea>
                </td>
            </tr>
        </table>
    <?php
    }

    /**
     * Add form settings content
     * @param $form_settings
     * @param $post
     */
    public static function add_form_settings_content( $form_settings, $post ) {

        $is_multistep_enabled    = isset( $form_settings['enable_multistep'] ) ? $form_settings['enable_multistep'] : '';
        $multistep_progress_type = isset( $form_settings['multistep_progressbar_type'] ) ? $form_settings['multistep_progressbar_type'] : 'step_by_step';

        $ms_ac_txt_color         = isset( $form_settings['ms_ac_txt_color'] ) ? $form_settings['ms_ac_txt_color'] : '#ffffff';
        $ms_active_bgcolor       = isset( $form_settings['ms_active_bgcolor'] ) ? $form_settings['ms_active_bgcolor'] : '#00a0d2';
        $ms_bgcolor              = isset( $form_settings['ms_bgcolor'] ) ? $form_settings['ms_bgcolor'] : '#E4E4E4';
        ?>

        <tr class="wpuf_enable_multistep_section">
            <th><?php _e( 'Enable Multistep', 'wpuf-pro' ); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="wpuf_settings[enable_multistep]" value="yes" <?php checked( $is_multistep_enabled, 'yes' ); ?> />
                    <?php _e( 'Enable Multistep', 'wpuf-pro' ); ?>
                </label>

                <p class="description"><?php echo __( 'If checked, form will be displayed in frontend in multiple steps', 'wpuf-pro' ); ?>
                <a target="_blank" href="https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/how-to-add-multi-step-form/">
                <?php _e( ' Learn more about Multistep', 'wpuf-pro' ); ?></a></p>
            </td>
        </tr>
        <tr class="wpuf_multistep_content">
            <td colspan="2" style="padding: 15px 0;">
                <h3><?php _e( 'Multistep Form Settings', 'wpuf-pro' ); ?></h3>
            </td>
        </tr>
        <tr class="wpuf_multistep_progress_type wpuf_multistep_content">
            <th><?php _e( 'Multistep Progressbar Type', 'wpuf-pro' ); ?></th>
            <td>
                <label>
                    <select name="wpuf_settings[multistep_progressbar_type]">
                        <option value="progressive" <?php echo $multistep_progress_type == 'progressive'? 'selected':'' ;?>><?php _e( 'Progressbar', 'wpuf-pro' ); ?></option>
                        <option value="step_by_step" <?php echo $multistep_progress_type == 'step_by_step'? 'selected':'' ;?>><?php _e( 'Step by Step', 'wpuf-pro' ); ?></option>
                    </select>
                </label>


                <p class="description"><?php echo __( 'Choose how you want the progressbar', 'wpuf-pro' ); ?></p>
            </td>
        </tr>

        <tr class="wpuf_multistep_content">
            <th><?php _e( 'Active Text Color', 'wpuf-pro' ); ?></th>
            <td>
                <label>
                    <input type="text" name="wpuf_settings[ms_ac_txt_color]" class="wpuf-ms-color" value="<?php echo $ms_ac_txt_color; ?>"  />

                </label>

                <p class="description"> <?php _e( 'Text color for active step.', 'wpuf-pro' ); ?></p>
            </td>
        </tr>
        <tr class="wpuf_multistep_content">
            <th><?php _e( 'Active Background Color', 'wpuf-pro' ); ?></th>
            <td>
                <label>
                    <input type="text" name="wpuf_settings[ms_active_bgcolor]" class="wpuf-ms-color" value="<?php echo $ms_active_bgcolor; ?>"  />

                </label>

                <p class="description"> <?php _e( 'Background color for progressbar or active step.', 'wpuf-pro' ); ?></p>
            </td>
        </tr>
        <tr class="wpuf_multistep_content">
            <th><?php _e( 'Background Color', 'wpuf-pro' ); ?></th>
            <td>
                <label>
                    <input type="text" name="wpuf_settings[ms_bgcolor]" class="wpuf-ms-color" value="<?php echo $ms_bgcolor; ?>"  />

                </label>

                <p class="description"> <?php _e( 'Background color for normal steps.', 'wpuf-pro' ); ?></p>
            </td>
        </tr>
    <?php
    }

    /**
     * Add content to post notification section
     */
    public static function add_post_notification_content() {
        global $post;

        $new_mail_body  = "Hi Admin,\r\n";
        $new_mail_body  .= "A new post has been created in your site %sitename% (%siteurl%).\r\n\r\n";

        $edit_mail_body = "Hi Admin,\r\n";
        $edit_mail_body .= "The post \"%post_title%\" has been updated.\r\n\r\n";

        $mail_body      = "Here is the details:\r\n";
        $mail_body      .= "Post Title: %post_title%\r\n";
        $mail_body      .= "Content: %post_content%\r\n";
        $mail_body      .= "Author: %author%\r\n";
        $mail_body      .= "Post URL: %permalink%\r\n";
        $mail_body      .= "Edit URL: %editlink%";

        $form_settings    = wpuf_get_form_settings( $post->ID );

        $new_notificaton  = isset( $form_settings['notification']['new'] ) ? $form_settings['notification']['new'] : 'on';
        $new_to           = isset( $form_settings['notification']['new_to'] ) ? $form_settings['notification']['new_to'] : get_option( 'admin_email' );
        $new_subject      = isset( $form_settings['notification']['new_subject'] ) ? $form_settings['notification']['new_subject'] : __( 'New post created', 'wpuf-pro' );
        $new_body         = isset( $form_settings['notification']['new_body'] ) ? $form_settings['notification']['new_body'] : $new_mail_body . $mail_body;

        $edit_notificaton = isset( $form_settings['notification']['edit'] ) ? $form_settings['notification']['edit'] : 'off';
        $edit_to          = isset( $form_settings['notification']['edit_to'] ) ? $form_settings['notification']['edit_to'] : get_option( 'admin_email' );
        $edit_subject     = isset( $form_settings['notification']['edit_subject'] ) ? $form_settings['notification']['edit_subject'] : __( 'A post has been edited', 'wpuf-pro' );
        $edit_body        = isset( $form_settings['notification']['edit_body'] ) ? $form_settings['notification']['edit_body'] : $edit_mail_body . $mail_body;
        ?>

        <h3><?php _e( 'New Post Notification', 'wpuf-pro' ); ?></h3>

        <table class="form-table">
            <tr>
                <th><?php _e( 'Notification', 'wpuf-pro' ); ?></th>
                <td>
                    <label>
                        <input type="hidden" name="wpuf_settings[notification][new]" value="off">
                        <input type="checkbox" name="wpuf_settings[notification][new]" value="on"<?php checked( $new_notificaton, 'on' ); ?>>
                        <?php _e( 'Enable post notification', 'wpuf-pro' ); ?>
                    </label>
                </td>
                 <p class="description"><a target="_blank" href="https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/how-to-set-up-submission-email-notification/"><?php _e('Learn more about Email Notification', 'wpuf-pro'); ?></a></p>
            </tr>

            <tr>
                <th><?php _e( 'To', 'wpuf-pro' ); ?></th>
                <td>
                    <input type="text" name="wpuf_settings[notification][new_to]" class="regular-text" value="<?php echo esc_attr( $new_to ) ?>">
                </td>
            </tr>

            <tr>
                <th><?php _e( 'Subject', 'wpuf-pro' ); ?></th>
                <td><input type="text" name="wpuf_settings[notification][new_subject]" class="regular-text" value="<?php echo esc_attr( $new_subject ) ?>"></td>
            </tr>

            <tr>
                <th><?php _e( 'Message', 'wpuf-pro' ); ?></th>
                <td>
                    <textarea rows="6" cols="60" name="wpuf_settings[notification][new_body]"><?php echo esc_textarea( $new_body ) ?></textarea>
                </td>
            </tr>
        </table>

        <h3><?php _e( 'Update Post Notification', 'wpuf-pro' ); ?></h3>

        <table class="form-table">
            <tr>
                <th><?php _e( 'Notification', 'wpuf-pro' ); ?></th>
                <td>
                    <label>
                        <input type="hidden" name="wpuf_settings[notification][edit]" value="off">
                        <input type="checkbox" name="wpuf_settings[notification][edit]" value="on"<?php checked( $edit_notificaton, 'on' ); ?>>
                        <?php _e( 'Enable post notification', 'wpuf-pro' ); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th><?php _e( 'To', 'wpuf-pro' ); ?></th>
                <td><input type="text" name="wpuf_settings[notification][edit_to]" class="regular-text" value="<?php echo esc_attr( $edit_to ) ?>"></td>
            </tr>

            <tr>
                <th><?php _e( 'Subject', 'wpuf-pro' ); ?></th>
                <td><input type="text" name="wpuf_settings[notification][edit_subject]" class="regular-text" value="<?php echo esc_attr( $edit_subject ) ?>"></td>
            </tr>

            <tr>
                <th><?php _e( 'Message', 'wpuf-pro' ); ?></th>
                <td>
                    <textarea rows="6" cols="60" name="wpuf_settings[notification][edit_body]"><?php echo esc_textarea( $edit_body ) ?></textarea>
                </td>
            </tr>
        </table>

        <h3><?php _e( 'You may use in to, subject & message:', 'wpuf-pro' ); ?></h3>
        <p>
            <code>%post_title%</code>, <code>%post_content%</code>, <code>%post_excerpt%</code>, <code>%tags%</code>, <code>%category%</code>,
            <code>%author%</code>, <code>%author_email%</code>, <code>%author_bio%</code>, <code>%sitename%</code>, <code>%siteurl%</code>, <code>%permalink%</code>, <code>%editlink%</code>
            <br><code>%custom_{NAME_OF_CUSTOM_FIELD}%</code> e.g: <code>%custom_website_url%</code> for <code>website_url</code> meta field
        </p>

    <?php
    }

    /**
     * Render registration form
     */
    public static function render_registration_form() {

        global $post, $pagenow, $form_inputs;
        $form_inputs = wpuf_get_form_fields( $post->ID );
        ?>
        <div style="margin-bottom: 10px">
            <button class="button wpuf-collapse"><?php _e( 'Toggle All', 'wpuf-pro' ); ?></button>
        </div>

        <div class="wpuf-updated">
            <p><?php _e( 'Click on a form element to add to the editor', 'wpuf-pro' ); ?></p>
        </div>

        <ul id="wpuf-form-editor" class="wpuf-form-editor unstyled">

            <?php

            if ($form_inputs) {
                $count = 0;
                foreach ($form_inputs as $order => $input_field) {
                    $method = $input_field['template'];
                    $name = ucwords( str_replace( '_', ' ', $input_field['template'] ) );

                    if ( method_exists( 'WPUF_Admin_Template_Profile', $method ) ) {
                        WPUF_Admin_Template_Profile::$method( $count, $name, $input_field );
                    } else {
                        do_action( 'wpuf_admin_template_post_' . $input_field['template'], $name, $count, $input_field, 'WPUF_Admin_Template_Post', '' );
                    }

                    $count++;
                }
            }
            ?>
        </ul>
    <?php

    }

    /**
     * Render registration settings
     */
    public static function render_registration_settings() {
        global $post;

        $form_settings = wpuf_get_form_settings( $post->ID );

        $email_verification = isset( $form_settings['enable_email_verification'] ) ? $form_settings['enable_email_verification'] : 'no';
        $role_selected      = isset( $form_settings['role'] ) ? $form_settings['role'] : 'subscriber';
        $redirect_to        = isset( $form_settings['redirect_to'] ) ? $form_settings['redirect_to'] : 'post';
        $message            = isset( $form_settings['message'] ) ? $form_settings['message'] : __( 'Registration successful', 'wpuf-pro' );
        $update_message     = isset( $form_settings['update_message'] ) ? $form_settings['update_message'] : __( 'Profile updated successfully', 'wpuf-pro' );
        $page_id            = isset( $form_settings['page_id'] ) ? $form_settings['page_id'] : 0;
        $url                = isset( $form_settings['url'] ) ? $form_settings['url'] : '';
        $submit_text        = isset( $form_settings['submit_text'] ) ? $form_settings['submit_text'] : __( 'Register', 'wpuf-pro' );
        $update_text        = isset( $form_settings['update_text'] ) ? $form_settings['update_text'] : __( 'Update Profile', 'wpuf-pro' );
        ?>
        <tr class="wpuf-post-type">
            <th><?php _e( 'Enable Email Verfication', 'wpuf-pro' ); ?></th>
            <td>
                <input type="hidden" name="wpuf_settings[enable_email_verification]" value="no">
                <input type="checkbox" id="wpuf-enable_email_verification" name="wpuf_settings[enable_email_verification]" value="yes" <?php checked( $email_verification, 'yes' ); ?> > <label for="wpuf-enable_email_verification">Enable Email Verification</label>
            </td>
        </tr>

        <tr class="wpuf-post-type">
            <th><?php _e( 'New User Role', 'wpuf-pro' ); ?></th>
            <td>
                <select name="wpuf_settings[role]">
                    <?php
                    $user_roles = wpuf_get_user_roles();
                    foreach ( $user_roles as $role => $label ) {
                        printf('<option value="%s"%s>%s</option>', $role, selected( $role_selected, $role, false ), $label );
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr class="wpuf-redirect-to">
            <th><?php _e( 'Redirect To', 'wpuf-pro' ); ?></th>
            <td>
                <select name="wpuf_settings[redirect_to]">
                    <?php
                    $redirect_options = array(
                        'same' => __( 'Same Page', 'wpuf-pro' ),
                        'page' => __( 'To a page', 'wpuf-pro' ),
                        'url' => __( 'To a custom URL', 'wpuf-pro' )
                    );

                    foreach ( $redirect_options as $to => $label ) {
                        printf('<option value="%s"%s>%s</option>', $to, selected( $redirect_to, $to, false ), $label );
                    }
                    ?>
                </select>
                <div class="description">
                    <?php _e( 'After successfull submit, where the page will redirect to', 'wpuf-pro' ) ?>
                </div>
            </td>
        </tr>

        <tr class="wpuf-same-page">
            <th><?php _e( 'Registration success message', 'wpuf-pro' ); ?></th>
            <td>
                <textarea rows="3" cols="40" name="wpuf_settings[message]"><?php echo esc_textarea( $message ); ?></textarea>
            </td>
        </tr>

        <tr class="wpuf-same-page">
            <th><?php _e( 'Update profile message', 'wpuf-pro' ); ?></th>
            <td>
                <textarea rows="3" cols="40" name="wpuf_settings[update_message]"><?php echo esc_textarea( $update_message ); ?></textarea>
            </td>
        </tr>

        <tr class="wpuf-page-id">
            <th><?php _e( 'Page', 'wpuf-pro' ); ?></th>
            <td>
                <select name="wpuf_settings[page_id]">
                    <?php
                    $pages = get_posts(  array( 'numberposts' => -1, 'post_type' => 'page') );

                    foreach ($pages as $page) {
                        printf('<option value="%s"%s>%s</option>', $page->ID, selected( $page_id, $page->ID, false ), esc_attr( $page->post_title ) );
                    }
                    ?>
                </select>
            </td>
        </tr>

        <tr class="wpuf-url">
            <th><?php _e( 'Custom URL', 'wpuf-pro' ); ?></th>
            <td>
                <input type="url" name="wpuf_settings[url]" value="<?php echo esc_attr( $url ); ?>">
            </td>
        </tr>

        <tr class="wpuf-submit-text">
            <th><?php _e( 'Submit Button text', 'wpuf-pro' ); ?></th>
            <td>
                <input type="text" name="wpuf_settings[submit_text]" value="<?php echo esc_attr( $submit_text ); ?>">
            </td>
        </tr>

        <tr class="wpuf-update-text">
            <th><?php _e( 'Update Button text', 'wpuf-pro' ); ?></th>
            <td>
                <input type="text" name="wpuf_settings[update_text]" value="<?php echo esc_attr( $update_text ); ?>">
            </td>
        </tr>
    <?php
        self::add_form_settings_content( $form_settings , $post );
    }

    /**
     * Checks what the post type is
     * @param $post
     * @param $update
     */
    public static function check_post_type( $post, $update ) {
        if( get_post_type( $post->ID ) == 'wpuf_profile' ){
            return;
        }
    }

    /**
     * Render custom taxonomies
     */
    public static function render_custom_taxonomies_element() {

        //$custom_taxonomies = get_taxonomies(array('_builtin' => false ) );
        $custom_taxonomies = get_taxonomies(array('_builtin' => false ), 'objects' );

        if( function_exists( 'wc_get_attribute_taxonomies' ) ) :
            $product_attributes = wc_get_attribute_taxonomies();
        else :
            $product_attributes = array();
            endif;


        $attr_name = array();
        foreach( $product_attributes as $attr ) {
            $attr_name[] = $attr->attribute_name;
        }
        //pri($custom_taxonomies);
        $form_settings = wpuf_get_form_settings( get_the_ID() );

        if ( $custom_taxonomies ) {
            ?>
            <div class="wpuf-taxonomies-holder">
                <?php
                $product_attr_tax = '';

                foreach ($custom_taxonomies as $tax_name => $tax) {
                    if ( strstr( $tax_name, 'pa_' ) && strpos( $tax_name, 'pa_' ) == 0 && in_array( 'product' , $tax->object_type ) && in_array( substr( $tax_name, 3 ) , $attr_name ) ) {
                        $product_attr_tax .= '<button class="wpuf-custom-tax-btn button '.  implode( ' ', $tax->object_type  ).'" style="'. ( isset( $form_settings['post_type'] ) && !in_array( $form_settings['post_type'], $tax->object_type )?'display:none' : '' ).'" data-name="taxonomy" data-type="'.$tax_name.'" title="'.__( 'Click to add to the editor', 'wpuf-pro' ).'">'.$tax_name.'</button>';
                    } else {
                        ?>
                        <button class="wpuf-custom-tax-btn button <?php echo implode( ' ', $tax->object_type  )?>" style="<?php echo isset( $form_settings['post_type'] ) && !in_array( $form_settings['post_type'], $tax->object_type )?'display:none' : ''; ?>" data-name="taxonomy" data-type="<?php echo $tax_name; ?>" title="<?php _e( 'Click to add to the editor', 'wpuf-pro' ); ?>"><?php echo $tax_name; ?></button>
                    <?php
                    }

                }
                ?>
                <div class="attributes_holder product" style="<?php echo isset( $form_settings['post_type'] ) && !in_array( $form_settings['post_type'], $tax->object_type )? '' : 'display:none'; ?>">
                    <h2>Product Attributes</h2>
                    <?php echo $product_attr_tax; ?>
                </div>
            </div>
            <?php
        } else {
            _e('No custom taxonomies found', 'wpuf-pro');
        }
    }

    /**
     * Render conditional logic
     * @param $field_id
     * @param $con_fields
     * @param $obj
     */
    public static function render_conditional_field( $field_id, $con_fields, $obj ) {
        global $form_inputs;

        $cond_name = 'wpuf_cond';

        $con_fields_value = isset( $con_fields['wpuf_cond'] ) ? $con_fields['wpuf_cond'] : array();
        $tpl              = '%s[%d][%s]';
        $enable_name      = sprintf( $tpl, $cond_name, $field_id, 'condition_status' );
        $field_name       = sprintf( '%s[%d][cond_field][]', $cond_name, $field_id );
        $operator_name    = sprintf( '%s[%d][cond_operator][]', $cond_name, $field_id );
        $option_name      = sprintf( '%s[%d][cond_option][]', $cond_name, $field_id );
        $logic_name       = sprintf( '%s[%d][cond_logic]', $cond_name, $field_id );

        $class = '';

        $enable_value = isset( $con_fields_value['condition_status'] ) ? $con_fields_value['condition_status'] : 'no';
        $logic_value  = isset( $con_fields_value['cond_logic'] ) ? $con_fields_value['cond_logic'] : 'all';
        $class        = ($enable_value == 'yes') ? '' : ' wpuf-hide';
        ?>
        <div class="wpuf-form-rows">
            <label><?php _e( 'Conditional Logic', 'wpuf-pro' ); ?></label>

            <div class="wpuf-form-sub-fields">
                <label><input type="radio" name="<?php echo $enable_name; ?>" class="wpuf-conditional-enable" value="yes"<?php checked( $enable_value, 'yes' ); ?>> <?php _e( 'Yes', 'wpuf-pro' ); ?></label>
                <label><input type="radio" name="<?php echo $enable_name; ?>" class="wpuf-conditional-enable" value="no"<?php checked( $enable_value, 'no' ); ?>> <?php _e( 'No', 'wpuf-pro' ); ?></label>

                <div class="conditional-rules-wrap<?php echo $class; ?>">
                    <table class="">
                        <?php
                        if ($enable_value == 'yes') {

                            $cond_fields = WPUF_Admin_Form::get_conditional_fields( $form_inputs );

                            $field_dropdown = WPUF_Admin_Form::get_conditional_fields_dropdown( $cond_fields['fields'] );

                            foreach ($con_fields_value['cond_field'] as $key => $field) {
                                $cond_fields['options'][$field] = isset( $cond_fields['options'][$field] ) ? $cond_fields['options'][$field] : array();

                                $option_dropdown = WPUF_Admin_Form::get_conditional_option_dropdown( $cond_fields['options'][$field] );

                                ?>
                                <tr>
                                    <td>
                                        <select name="<?php echo $field_name; ?>" class="wpuf-conditional-fields">
                                            <?php echo wpuf_dropdown_helper($field_dropdown, $con_fields_value['cond_field'][$key]); ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="<?php echo $operator_name; ?>" class="">
                                            <option value="=" <?php selected($con_fields_value['cond_operator'][$key], '=') ;?>>is equal to</option>
                                            <option value="!=" <?php selected($con_fields_value['cond_operator'][$key], '!=') ;?>>is not equal to</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="<?php echo $option_name; ?>" class="wpuf-conditional-fields-option">
                                            <?php
                                            if ( array_key_exists( $field, $cond_fields['options'] ) ) {
                                                echo wpuf_dropdown_helper( $option_dropdown, $con_fields_value['cond_option'][$key] );
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <a class="button wpuf-conditional-plus" href="#">+</a>
                                        <a class="button wpuf-conditional-minus" href="#">-</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td>
                                    <select name="<?php echo $field_name; ?>" class="wpuf-conditional-fields">
                                        <option value="">- select -</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="<?php echo $operator_name; ?>" class="">
                                        <option value="=">is equal to</option>
                                        <option value="!=">is not equal to</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="<?php echo $option_name; ?>" class="wpuf-conditional-fields-option">
                                        <option value="">- select -</option>
                                    </select>
                                </td>
                                <td>
                                    <a class="button wpuf-conditional-plus" href="#">+</a>
                                    <a class="button wpuf-conditional-minus" href="#">-</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>


                    <div class="">
                        Show this field when
                        <select name="<?php echo $logic_name; ?>">
                            <option value="all"<?php selected( $logic_value, 'all') ;?>>all</option>
                            <option value="any"<?php selected( $logic_value, 'any') ;?>>any</option>
                        </select>
                        these rules are met
                    </div>
                </div>
            </div>
        </div> <!-- .wpuf-form-rows -->
    <?php

    }


    /**
     * Render repeat field
     * @param $field_id
     * @param $label
     * @param $classname
     * @param array $values
     *
     */
    public static function repeat_field( $field_id, $label, $classname , $values = array() ) {

        $tpl = '%s[%d][%s]';

        $enable_column_name = sprintf( '%s[%d][multiple]', self::$input_name, $field_id );
        $column_names       = sprintf( '%s[%d][columns]', self::$input_name, $field_id );
        $has_column         = ( $values && isset( $values['multiple'] ) ) ? true : false;

        $placeholder_name   = sprintf( $tpl, self::$input_name, $field_id, 'placeholder' );
        $default_name       = sprintf( $tpl, self::$input_name, $field_id, 'default' );
        $size_name          = sprintf( $tpl, self::$input_name, $field_id, 'size' );

        $placeholder_value  = $values ? esc_attr( $values['placeholder'] ) : '';
        $default_value      = $values ? esc_attr( $values['default'] ) : '';
        $size_value         = $values ? esc_attr( $values['size'] ) : '40';
        ?>
        <li class="custom-field custom_repeater">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'repeat' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'repeat_field' ); ?>

            <div class="wpuf-form-holder">
                <?php self::common( $field_id, '', true, $values ); ?>

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Multiple Column', 'wpuf-pro' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <label><input type="checkbox" class="multicolumn" name="<?php echo $enable_column_name ?>"<?php echo $has_column ? ' checked="checked"' : ''; ?> value="true"> Enable Multi Column</label>
                    </div>
                </div>

                <div class="wpuf-form-rows<?php echo $has_column ? ' wpuf-hide' : ''; ?>">
                    <label><?php _e( 'Placeholder text', 'wpuf-pro' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $placeholder_name; ?>" title="text for HTML5 placeholder attribute" value="<?php echo $placeholder_value; ?>" />
                </div> <!-- .wpuf-form-rows -->

                <div class="wpuf-form-rows<?php echo $has_column ? ' wpuf-hide' : ''; ?>">
                    <label><?php _e( 'Default value', 'wpuf-pro' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $default_name; ?>" title="the default value this field will have" value="<?php echo $default_value; ?>" />
                </div> <!-- .wpuf-form-rows -->

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Size', 'wpuf-pro' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $size_name; ?>" title="Size of this input field" value="<?php echo $size_value; ?>" />
                </div> <!-- .wpuf-form-rows -->

                <div class="wpuf-form-rows column-names<?php echo $has_column ? '' : ' wpuf-hide'; ?>">
                    <label><?php _e( 'Columns', 'wpuf-pro' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <?php
                        if ( $values && $values['columns'] > 0 ) {
                            foreach ($values['columns'] as $key => $value) {
                                ?>
                                <div>
                                    <input type="text" name="<?php echo $column_names; ?>[]" value="<?php echo $value; ?>">

                                    <?php self::remove_button(); ?>
                                </div>
                            <?php
                            }
                        } else {
                            ?>
                            <div>
                                <input type="text" name="<?php echo $column_names; ?>[]" value="">

                                <?php self::remove_button(); ?>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div> <!-- .wpuf-form-rows -->
                <?php self::conditional_field( $field_id, $values ); ?>
            </div> <!-- .wpuf-form-holder -->
        </li>
    <?php
    }


    /**
     * Render date field
     * @param $field_id
     * @param $label
     * @param $classname
     * @param array $values
     */
    public static function date_field( $field_id, $label, $classname, $values = array() ) {
        $format_name  = sprintf( '%s[%d][format]', self::$input_name, $field_id );
        $time_name    = sprintf( '%s[%d][time]', self::$input_name, $field_id );

        $format_value = $values ? $values['format'] : 'dd/mm/yy';
        $time_value   = $values ? $values['time'] : 'no';

        $publish_time_name = sprintf( '%s[%d][is_publish_time]', self::$input_name, $field_id );
        $publish_time_value = $values ? $values['is_publish_time'] : 'no';

        $help         = esc_attr( __( 'The date format', 'wpuf-pro' ) );
        ?>
        <li class="custom-field custom_image">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'date' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'date_field' ); ?>

            <div class="wpuf-form-holder">
                <?php self::common( $field_id, '', true, $values ); ?>

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Date Format', 'wpuf-pro' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $format_name; ?>" value="<?php echo $format_value; ?>" title="<?php echo $help; ?>">
                </div> <!-- .wpuf-form-rows -->

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Time', 'wpuf-pro' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <label>
                            <?php self::hidden_field( "[$field_id][time]", 'no' ); ?>
                            <input type="checkbox" name="<?php echo $time_name ?>" value="yes"<?php checked( $time_value, 'yes' ); ?> />
                            <?php _e( 'Enable time input', 'wpuf-pro' ); ?>
                        </label>
                    </div>

                </div> <!-- .wpuf-form-rows -->
                <div class="wpuf-form-rows">
                    <label><?php _e( 'Is Publish Time ? : ', 'wpuf-pro' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <label>
                            <?php self::hidden_field( "[$field_id][is_publish_time]", 'no' ); ?>
                            <input type="checkbox" name="<?php echo $publish_time_name ?>" value="yes"<?php checked( $publish_time_value, 'yes' ); ?> />
                            <?php _e( 'Set this as publish time input', 'wpuf-pro' ); ?>
                        </label>
                    </div>

                </div> <!-- .wpuf-form-rows -->

                <?php self::conditional_field( $field_id, $values ); ?>
            </div> <!-- .wpuf-form-holder -->
        </li>
    <?php
    }


    /**
     * Render file upload field
     * @param $field_id
     * @param $label
     * @param $classname
     * @param array $values
     */
    public static function file_upload( $field_id, $label, $classname, $values = array() ) {
        $max_size_name    = sprintf( '%s[%d][max_size]', self::$input_name, $field_id );
        $max_files_name   = sprintf( '%s[%d][count]', self::$input_name, $field_id );
        $extensions_name  = sprintf( '%s[%d][extension][]', self::$input_name, $field_id );

        $max_size_value   = $values ? $values['max_size'] : '1024';
        $max_files_value  = $values ? $values['count'] : '1';
        $extensions_value = $values ? $values['extension'] : array('images', 'audio', 'video', 'pdf', 'office', 'zip', 'exe', 'csv');

        $extesions        = wpuf_allowed_extensions();

        $help  = esc_attr( __( 'Enter maximum upload size limit in KB', 'wpuf-pro' ) );
        $count = esc_attr( __( 'Number of images can be uploaded', 'wpuf-pro' ) );
        ?>
        <li class="custom-field custom_image">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'file_upload' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'file_upload' ); ?>

            <div class="wpuf-form-holder">
                <?php self::common( $field_id, '', true, $values ); ?>

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Max. file size', 'wpuf-pro' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $max_size_name; ?>" value="<?php echo $max_size_value; ?>" title="<?php echo $help; ?>">
                </div> <!-- .wpuf-form-rows -->

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Max. files', 'wpuf-pro' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $max_files_name; ?>" value="<?php echo $max_files_value; ?>" title="<?php echo $count; ?>">
                </div> <!-- .wpuf-form-rows -->

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Allowed Files', 'wpuf-pro' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <?php foreach ($extesions as $key => $value) {
                            ?>
                            <label>
                                <input type="checkbox" name="<?php echo $extensions_name; ?>" value="<?php echo $key; ?>"<?php echo in_array( $key, $extensions_value ) ? ' checked="checked"' : ''; ?>>
                                <?php printf( '%s (%s)', $value['label'], str_replace( ',', ', ', $value['ext'] ) ) ?>
                            </label> <br />
                        <?php } ?>
                    </div>
                </div> <!-- .wpuf-form-rows -->
                <?php self::conditional_field( $field_id, $values ); ?>
            </div> <!-- .wpuf-form-holder -->
        </li>
    <?php
    }


    /**
     * Render google map
     * @param $field_id
     * @param $label
     * @param $classname
     * @param array $values
     */
    public static function google_map( $field_id, $label, $classname, $values = array() ) {
        $zoom_name         = sprintf( '%s[%d][zoom]', self::$input_name, $field_id );
        $address_name      = sprintf( '%s[%d][address]', self::$input_name, $field_id );
        $default_pos_name  = sprintf( '%s[%d][default_pos]', self::$input_name, $field_id );
        $show_lat_name     = sprintf( '%s[%d][show_lat]', self::$input_name, $field_id );

        $zoom_value        = $values ? $values['zoom'] : '12';
        $address_value     = $values ? $values['address'] : 'yes';
        $show_lat_value    = $values ? $values['show_lat'] : 'no';
        $default_pos_value = $values ? $values['default_pos'] : '40.7143528,-74.0059731';

        $zoom_help         = esc_attr( __( 'Set the map zoom level', 'wpuf-pro' ) );
        $pos_help          = esc_attr( __( 'Enter default latitude and longitude to center the map', 'wpuf-pro' ) );
        ?>
        <li class="custom-field custom_image">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'map' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'google_map' ); ?>

            <div class="wpuf-form-holder">
                <?php self::common( $field_id, '', true, $values ); ?>

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Zoom Level', 'wpuf-pro' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $zoom_name; ?>" value="<?php echo $zoom_value; ?>" title="<?php echo $zoom_help; ?>">
                </div> <!-- .wpuf-form-rows -->

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Default Co-ordinate', 'wpuf-pro' ); ?></label>
                    <input type="text" class="smallipopInput" name="<?php echo $default_pos_name; ?>" value="<?php echo $default_pos_value; ?>" title="<?php echo $pos_help; ?>">
                </div> <!-- .wpuf-form-rows -->

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Address Button', 'wpuf-pro' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <label>
                            <?php self::hidden_field( "[$field_id][address]", 'no' ); ?>
                            <input type="checkbox" name="<?php echo $address_name ?>" value="yes"<?php checked( $address_value, 'yes' ); ?> />
                            <?php _e( 'Show address find button', 'wpuf-pro' ); ?>
                        </label>
                    </div>
                </div> <!-- .wpuf-form-rows -->

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Show Latitude/Longitude', 'wpuf-pro' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <label>
                            <?php self::hidden_field( "[$field_id][show_lat]", 'no' ); ?>
                            <input type="checkbox" name="<?php echo $show_lat_name ?>" value="yes"<?php checked( $show_lat_value, 'yes' ); ?> />
                            <?php _e( 'Show latitude and longitude input box value', 'wpuf-pro' ); ?>
                        </label>
                    </div>
                </div> <!-- .wpuf-form-rows -->

                <?php self::conditional_field( $field_id, $values ); ?>
            </div> <!-- .wpuf-form-holder -->
        </li>
    <?php
    }

    /**
     * [country_list_field description]
     *
     * @param  int  $field_id
     * @param  string  $label
     * @param  array   $values
     *
     * @since 2.2.7
     *
     * @return void
     */
    public static function country_list_field( $field_id, $label, $classname, $values = array() ) {
        $country_list_name       = sprintf( '%s[%d][country_list]', self::$input_name, $field_id );
        $country_list_value      = isset( $values['country_list'] )? $values['country_list'] : '';

        $first_name              = sprintf( '%s[%d][country_list][name]', self::$input_name, $field_id );
        $first_value             = isset($values['country_list']['name']) ? $values['country_list']['name'] : ' - select -';
        $help                    = esc_attr( __( 'The country to be selected by default.', 'wpuf-pro' ) );
        $hide_country_list_name  = sprintf( '%s[%d][country_list][country_select_hide_list][]', self::$input_name, $field_id );
        $hide_country_list_value = isset( $values['country_list']['country_select_hide_list'] )? $values['country_list']['country_select_hide_list'] : '';
        $show_country_list_name  = sprintf( '%s[%d][country_list][country_select_show_list][]', self::$input_name, $field_id );
        $show_country_list_value = isset( $values['country_list']['country_select_show_list'] )? $values['country_list']['country_select_show_list'] : '';
        $country_list_visibility_opt_name  = sprintf( '%s[%d][country_list][country_list_visibility_opt_name]', self::$input_name, $field_id );
        $country_list_visibility_opt_value = isset( $values['country_list']['country_list_visibility_opt_name'] )? $values['country_list']['country_list_visibility_opt_name'] : '';
        ?>
        <li class="custom-field dropdown_field wpuf-conditional">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'country_list' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'country_list_field' ); ?>

            <div class="wpuf-form-holder">
                <?php self::common( $field_id, '', true, $values ); ?>

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Default Country', 'wpuf-pro' ); ?></label>
                    <!--<input type="text" class="smallipopInput" name="<?php echo $first_name; ?>" value="<?php echo $first_value; ?>" title="<?php echo $help; ?>">-->
                    <!--beta-->
                    <select class="smallipopInput" name="<?php echo $first_name; ?>" value="<?php echo $first_value; ?>" title="<?php echo $help; ?>">

                    </select>
                    <script>
                        var field_name = '<?php echo $first_name;?>';
                        var sel_country = '<?php echo $first_value; ?>';//'<?php echo !empty( $value ) ? $value : '' ; ?>';
                        var countries = <?php echo file_get_contents(WPUF_ASSET_URI . '/js/countries.json');?>;
                        var option_string = '<option value="">Select Country</option>';
                        for (country in countries) {
                            option_string = option_string + '<option value="'+ countries[country].code +'" ' + ( sel_country == countries[country].code ? 'selected':'' ) + ' >'+ countries[country].name +'</option>';
                        }
                        jQuery('select[name="'+ field_name +'"]').html(option_string);
                    </script>
                    <!---->

                </div> <!-- .wpuf-form-rows -->

                <?php
                $param = array(
                    'names_to_hide' => array(
                        'name' => $hide_country_list_name,
                        'value' => $hide_country_list_value
                    ),
                    'names_to_show' => array(
                        'name' => $show_country_list_name,
                        'value' => $show_country_list_value
                    ),
                    'option_to_chose' => array(
                        'name' => $country_list_visibility_opt_name,
                        'value' => $country_list_visibility_opt_value
                    )
                );
                self::render_drop_down_portion($param);
                ?>
            </div> <!-- .wpuf-form-holder -->
        </li>
    <?php
    }

    /**
     * Render parameter fields for numeric text field
     *
     * @since 2.2.7
     *
     * @param $field_id
     * @param $label field label
     * @param $values
     */
    public static function numeric_text_field( $field_id, $label,$classname, $values = array() ) {
        $step_text_field_name  = sprintf( '%s[%d][step_text_field]', self::$input_name, $field_id );
        $step_text_field_value = isset( $values['step_text_field'] )? $values['step_text_field'] : 1;
        $min_value_field_name  = sprintf( '%s[%d][min_value_field]', self::$input_name, $field_id );
        $min_value_field_value = isset( $values['min_value_field'] )? $values['min_value_field'] : 0;
        $max_value_field_name  = sprintf( '%s[%d][max_value_field]', self::$input_name, $field_id );
        $max_value_field_value = isset( $values['max_value_field'] )? $values['max_value_field'] : 100;
        ?>
        <li class="custom-field text_field">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'numeric_text' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'numeric_text_field' ); ?>

            <div class="wpuf-form-holder">
                <?php self::common( $field_id, '', true, $values ); ?>
                <?php self::common_text( $field_id, $values ); ?>
                <?php self::conditional_field( $field_id, $values ); ?>

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Step', 'wpuf-pro' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <label>
                            <input type="text" name="<?php echo $step_text_field_name; ?>" value="<?php echo $step_text_field_value; ?>" />
                        </label>
                    </div>
                </div>
                <div class="wpuf-form-rows">
                    <label><?php _e( 'Min Value', 'wpuf-pro' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <label>
                            <input type="text" name="<?php echo $min_value_field_name; ?>" value="<?php echo $min_value_field_value; ?>" />
                        </label>
                    </div>
                </div>
                <div class="wpuf-form-rows">
                    <label><?php _e( 'Max Value', 'wpuf-pro' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <label>
                            <input type="text" name="<?php echo $max_value_field_name; ?>" value="<?php echo $max_value_field_value; ?>" />
                        </label>
                    </div>
                </div>
            </div> <!-- .wpuf-form-holder -->


        </li>
    <?php
    }

    /**
     * Render parameter fields for address field
     *
     * @param $field_id
     * @param $label
     * @param $values
     *
     */
    public static function address_field( $field_id, $label, $classname, $values = array() ) {
        $address_desc_name                 = sprintf( '%s[%d][address_desc]', self::$input_name, $field_id );
        $address_desc_value                = isset( $values['address_desc'] )? $values['address_desc'] : '';

        //street address
        $street_address_name               = sprintf( '%s[%d][address][street_address]', self::$input_name, $field_id );
        $street_address_checkbox_name      = sprintf( '%s[%d][address][street_address][checked]', self::$input_name, $field_id );
        $street_address_checkbox_value     = isset( $values['address']['street_address']['checked'] )? $values['address']['street_address']['checked'] : 'checked';
        $street_address_ischecked          = $street_address_checkbox_value ? esc_attr( $street_address_checkbox_value ) : '';
        $street_address_label              = sprintf( '%s[%d][address][street_address][label]', self::$input_name, $field_id );
        $street_address_label_value        = isset( $values['address']['street_address']['label'] )? $values['address']['street_address']['label'] : __( 'Address Line 1', 'wpuf-pro' );
        $street_address_value_name         = sprintf( '%s[%d][address][street_address][value]', self::$input_name, $field_id );
        $street_address_value_default      = isset( $values['address']['street_address']['value'] )? $values['address']['street_address']['value'] : '';
        $street_address_placeholder_name   = sprintf( '%s[%d][address][street_address][placeholder]', self::$input_name, $field_id );
        $street_address_placeholder_value  = isset( $values['address']['street_address']['placeholder'] )? $values['address']['street_address']['placeholder'] : '';
        $street_address_field_type         = sprintf( '%s[%d][address][street_address][type]', self::$input_name, $field_id );
        $street_address_field_type_value   = 'text';
        $street_address_req                = sprintf( '%s[%d][address][street_address][required]', self::$input_name, $field_id );
        $street_address_req_value          = isset( $values['address']['street_address']['required'] )? $values['address']['street_address']['required'] : 'checked';

        //street address 2
        $street_address2_name              = sprintf( '%s[%d][address][street_address2]', self::$input_name, $field_id );
        $street_address2_checkbox_name     = sprintf( '%s[%d][address][street_address2][checked]', self::$input_name, $field_id );
        $street_address2_checkbox_value    = isset( $values['address']['street_address2']['checked'] )? $values['address']['street_address2']['checked'] : 'checked';
        $street_address2_ischecked         = $street_address2_checkbox_value ? esc_attr( $street_address2_checkbox_value ) : '';
        $street_address2_label             = sprintf( '%s[%d][address][street_address2][label]', self::$input_name, $field_id );
        $street_address2_label_value       = isset( $values['address']['street_address2']['label'] )? $values['address']['street_address2']['label'] : __( 'Address Line 2', 'wpuf-pro' );
        $street_address2_value_name        = sprintf( '%s[%d][address][street_address2][value]', self::$input_name, $field_id );
        $street_address2_value_default     = isset( $values['address']['street_address2']['value'] )? $values['address']['street_address2']['value'] : '';
        $street_address2_placeholder_name  = sprintf( '%s[%d][address][street_address2][placeholder]', self::$input_name, $field_id );
        $street_address2_placeholder_value = isset( $values['address']['street_address2']['placeholder'] )? $values['address']['street_address2']['placeholder'] : '';
        $street_address2_field_type        = sprintf( '%s[%d][address][street_address2][type]', self::$input_name, $field_id );
        $street_address2_field_type_value  = 'text';
        $street_address2_req               = sprintf( '%s[%d][address][street_address2][required]', self::$input_name, $field_id );
        $street_address2_req_value         = isset( $values['address']['street_address2']['required'] )? $values['address']['street_address2']['required'] : '';
        //city name

        $city_name                         = sprintf( '%s[%d][address][city_name]', self::$input_name, $field_id );
        $city_checkbox_name                = sprintf( '%s[%d][address][city_name][checked]', self::$input_name, $field_id );
        $city_checkbox_value               = isset( $values['address']['city_name']['checked'] )? $values['address']['city_name']['checked'] : 'checked';
        $city_name_ischecked               = $city_checkbox_value ? esc_attr( $city_checkbox_value ) : '';
        $city_label                        = sprintf( '%s[%d][address][city_name][label]', self::$input_name, $field_id );
        $city_label_value                  = isset( $values['address']['city_name']['label'] )? $values['address']['city_name']['label'] : __( 'City', 'wpuf-pro' );
        $city_value_name                   = sprintf( '%s[%d][address][city_name][value]', self::$input_name, $field_id );
        $city_value_default                = isset( $values['address']['city_name']['value'] )? $values['address']['city_name']['value'] : '';
        $city_placeholder_name             = sprintf( '%s[%d][address][city_name][placeholder]', self::$input_name, $field_id );
        $city_placeholder_value            = isset( $values['address']['city_name']['placeholder'] )? $values['address']['city_name']['placeholder'] : '';
        $city_field_type                   = sprintf( '%s[%d][address][city_name][type]', self::$input_name, $field_id );
        $city_field_type_value             = 'text';
        $city_req                          = sprintf( '%s[%d][address][city_name][required]', self::$input_name, $field_id );
        $city_req_value                    = isset( $values['address']['city_name']['required'] )? $values['address']['city_name']['required'] : 'checked';

        //state name
        $state_name                        = sprintf( '%s[%d][address][state]', self::$input_name, $field_id );
        $state_checkbox_name               = sprintf( '%s[%d][address][state][checked]', self::$input_name, $field_id );
        $state_checkbox_value              = isset( $values['address']['state']['checked'] )? $values['address']['state']['checked'] : 'checked';
        $state_ischecked                   = $state_checkbox_value ? esc_attr( $state_checkbox_value ) : '';
        $state_label                       = sprintf( '%s[%d][address][state][label]', self::$input_name, $field_id );
        $state_label_value                 = isset( $values['address']['state']['label'] )? $values['address']['state']['label'] : __( 'State', 'wpuf-pro' );
        $state_value_name                  = sprintf( '%s[%d][address][state][value]', self::$input_name, $field_id );
        $state_value_default               = isset( $values['address']['state']['value'] )? $values['address']['state']['value'] : '';
        $state_placeholder_name            = sprintf( '%s[%d][address][state][placeholder]', self::$input_name, $field_id );
        $state_placeholder_value           = isset( $values['address']['state']['placeholder'] )? $values['address']['state']['placeholder'] : '';
        $state_field_type                  = sprintf( '%s[%d][address][state][type]', self::$input_name, $field_id );
        $state_field_type_value            = 'text';
        $state_req                         = sprintf( '%s[%d][address][state][required]', self::$input_name, $field_id );
        $state_req_value                   = isset( $values['address']['state']['required'] )? $values['address']['state']['required'] : 'checked';

        //zip name
        $zip_name                          = sprintf( '%s[%d][address][zip]', self::$input_name, $field_id );
        $zip_checkbox_name                 = sprintf( '%s[%d][address][zip][checked]', self::$input_name, $field_id );
        $zip_checkbox_value                = isset( $values['address']['zip']['checked'] )? $values['address']['zip']['checked'] : 'checked';
        $zip_ischecked                     = $zip_checkbox_value ? esc_attr( $zip_checkbox_value ) : '';
        $zip_label                         = sprintf( '%s[%d][address][zip][label]', self::$input_name, $field_id );
        $zip_label_value                   = isset( $values['address']['zip']['label'] )? $values['address']['zip']['label'] : __( 'Zip Code', 'wpuf-pro' );
        $zip_value_name                    = sprintf( '%s[%d][address][zip][value]', self::$input_name, $field_id );
        $zip_value_default                 = isset( $values['address']['zip']['value'] )? $values['address']['zip']['value'] : '';
        $zip_placeholder_name              = sprintf( '%s[%d][address][zip][placeholder]', self::$input_name, $field_id );
        $zip_placeholder_value             = isset( $values['address']['zip']['placeholder'] )? $values['address']['zip']['placeholder'] : '';
        $zip_field_type                    = sprintf( '%s[%d][address][zip][type]', self::$input_name, $field_id );
        $zip_field_type_value              = 'text';
        $zip_req                           = sprintf( '%s[%d][address][zip][required]', self::$input_name, $field_id );
        $zip_req_value                     = isset( $values['address']['zip']['required'] )? $values['address']['zip']['required'] : 'checked';

        //country names
        $county_select_name                = sprintf( '%s[%d][address][country_select]', self::$input_name, $field_id );
        $county_select_checkbox_name       = sprintf( '%s[%d][address][country_select][checked]', self::$input_name, $field_id );
        $county_select_checkbox_value      = isset( $values['address']['country_select']['checked'] )? $values['address']['country_select']['checked'] : 'checked';
        $county_select_ischecked           = $county_select_checkbox_value ? esc_attr( $county_select_checkbox_value ) : '';
        $county_select_label               = sprintf( '%s[%d][address][country_select][label]', self::$input_name, $field_id );
        $county_select_label_value         = isset( $values['address']['country_select']['label'] )? $values['address']['country_select']['label'] : __( 'Country', 'wpuf-pro' );
        $county_select_value_name          = sprintf( '%s[%d][address][country_select][value]', self::$input_name, $field_id );
        $county_select_value_default       = isset( $values['address']['country_select']['value'] )? $values['address']['country_select']['value'] : '';
        $county_select_placeholder_name    = sprintf( '%s[%d][address][country_select][placeholder]', self::$input_name, $field_id );
        $county_select_placeholder_value   = isset( $values['address']['country_select']['placeholder'] )? $values['address']['country_select']['placeholder'] : '';
        $county_select_field_type          = sprintf( '%s[%d][address][country_select][type]', self::$input_name, $field_id );
        $county_select_field_type_value    = 'select';
        $county_select_req                 = sprintf( '%s[%d][address][country_select][required]', self::$input_name, $field_id );
        $county_select_req_value           = isset( $values['address']['country_select']['required'] )? $values['address']['country_select']['required'] : 'checked';
        $hide_country_list_name            = sprintf( '%s[%d][address][country_select][country_select_hide_list][]', self::$input_name, $field_id );
        $hide_country_list_value           = isset( $values['address']['country_select']['country_select_hide_list'] )? $values['address']['country_select']['country_select_hide_list'] : '';
        $show_country_list_name            = sprintf( '%s[%d][address][country_select][country_select_show_list][]', self::$input_name, $field_id );
        $show_country_list_value           = isset( $values['address']['country_select']['country_select_show_list'] )? $values['address']['country_select']['country_select_show_list'] : '';
        $country_list_visibility_opt_name  = sprintf( '%s[%d][address][country_select][country_list_visibility_opt_name]', self::$input_name, $field_id );
        $country_list_visibility_opt_value = isset( $values['address']['country_select']['country_list_visibility_opt_name'] )? $values['address']['country_select']['country_list_visibility_opt_name'] : '';
        ?>
        <li class="custom-field text_field">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'address' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'address_field' ); ?>

            <div class="wpuf-form-holder wpuf-address">
                <?php self::common( $field_id, '', true, $values ); ?>
                <?php self::conditional_field( $field_id, $values ); ?>

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Address Description', 'wpuf-pro' ); ?></label>
                    <textarea name="<?php echo $address_desc_name; ?>"><?php echo $address_desc_value; ?></textarea>
                </div>

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Address Field(s)', 'wpuf-pro' ); ?></label>

                    <table class="address-table">
                        <thead>
                        <tr>
                            <th width="45%"><?php _e( 'Fields', 'wpuf-pro' ); ?></th>
                            <th width="10%"><?php _e( 'Required?', 'wpuf-pro' ); ?></th>
                            <th width="15%"><?php _e( 'Label', 'wpuf-pro' ); ?></th>
                            <th width="15%"><?php _e( 'Default Value', 'wpuf-pro' ); ?></th>
                            <th width="15%"><?php _e( 'Placeholder', 'wpuf-pro' ); ?></th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr>
                            <td>
                                <label>
                                    <input type="hidden" name="<?php echo $street_address_checkbox_name; ?>" value="" />
                                    <input type="checkbox" name="<?php echo $street_address_checkbox_name; ?>" value="checked" <?php echo $street_address_ischecked; ?> />
                                    <?php _e( 'Address Line 1', 'wpuf-pro' ); ?>
                                    <input type="hidden" name="<?php echo $street_address_field_type; ?>" value="<?php echo $street_address_field_type_value; ?>"  />
                                </label>
                            </td>
                            <td>
                                <input type="hidden" name="<?php echo $street_address_req; ?>" value="" />
                                <input type="checkbox" name="<?php echo $street_address_req; ?>" value="checked" <?php echo $street_address_req_value; ?> />
                            </td>
                            <td><input type="text" name="<?php echo $street_address_label; ?>" value="<?php echo $street_address_label_value; ?>"  /></td>
                            <td><input type="text" name="<?php echo $street_address_value_name; ?>" value="<?php echo $street_address_value_default; ?>"  /></td>
                            <td><input type="text" name="<?php echo $street_address_placeholder_name; ?>" value="<?php echo $street_address_placeholder_value; ?>"  /></td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    <input type="hidden" name="<?php echo $street_address2_checkbox_name; ?>" value="" />
                                    <input type="checkbox" name="<?php echo $street_address2_checkbox_name; ?>" value="checked" <?php echo $street_address2_ischecked; ?> />
                                    <?php _e( 'Address Line 2', 'wpuf-pro' ); ?>
                                    <input type="hidden" name="<?php echo $street_address2_field_type; ?>" value="<?php echo $street_address2_field_type_value; ?>"  />
                                </label>
                            </td>
                            <td>
                                <input type="hidden" name="<?php echo $street_address2_req; ?>" value="" />
                                <input type="checkbox" name="<?php echo $street_address2_req; ?>" value="checked" <?php echo $street_address2_req_value; ?> /></td>
                            <td>
                                <input type="text" name="<?php echo $street_address2_label; ?>" value="<?php echo $street_address2_label_value; ?>"  />
                            </td>
                            <td><input type="text" name="<?php echo $street_address2_value_name; ?>" value="<?php echo $street_address2_value_default; ?>"  /></td>
                            <td><input type="text" name="<?php echo $street_address2_placeholder_name; ?>" value="<?php echo $street_address2_placeholder_value; ?>"  /></td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    <input type="hidden" name="<?php echo $city_checkbox_name; ?>" value="" />
                                    <input type="checkbox" name="<?php echo $city_checkbox_name; ?>" value="checked" <?php echo $city_name_ischecked; ?> />
                                    <?php _e( 'City Name', 'wpuf-pro' ); ?>
                                    <input type="hidden" name="<?php echo $city_field_type; ?>" value="<?php echo $city_field_type_value; ?>"  />
                                </label>
                            </td>
                            <td>
                                <input type="hidden" name="<?php echo $city_req; ?>" value="" />
                                <input type="checkbox" name="<?php echo $city_req; ?>" value="checked" <?php echo $city_req_value; ?> /></td>
                            <td>
                                <input type="text" name="<?php echo $city_label; ?>" value="<?php echo $city_label_value; ?>"  />
                            </td>
                            <td><input type="text" name="<?php echo $city_value_name; ?>" value="<?php echo $city_value_default; ?>"  /></td>
                            <td><input type="text" name="<?php echo $city_placeholder_name; ?>" value="<?php echo $city_placeholder_value; ?>"  /></td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    <input type="hidden" name="<?php echo $state_checkbox_name; ?>" value="" />
                                    <input type="checkbox" name="<?php echo $state_checkbox_name; ?>" value="checked" <?php echo $state_ischecked; ?> />
                                    <?php _e( 'State/Region', 'wpuf-pro' ); ?>
                                    <input type="hidden" name="<?php echo $state_field_type; ?>" value="<?php echo $state_field_type_value; ?>"  />
                                </label>
                            </td>
                            <td>
                                <input type="hidden" name="<?php echo $state_req; ?>" value="" />
                                <input type="checkbox" name="<?php echo $state_req; ?>" value="checked" <?php echo $state_req_value; ?> /></td>
                            <td>
                                <input type="text" name="<?php echo $state_label; ?>" value="<?php echo $state_label_value; ?>"  />
                            </td>
                            <td><input type="text" name="<?php echo $state_value_name; ?>" value="<?php echo $state_value_default; ?>"  /></td>
                            <td><input type="text" name="<?php echo $state_placeholder_name; ?>" value="<?php echo $state_placeholder_value; ?>"  /></td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    <input type="hidden" name="<?php echo $zip_checkbox_name; ?>" value="" />
                                    <input type="checkbox" name="<?php echo $zip_checkbox_name; ?>" value="checked" <?php echo $zip_ischecked; ?> />
                                    <?php _e( 'Zip/Postal Code', 'wpuf-pro' ); ?>
                                    <input type="hidden" name="<?php echo $zip_field_type; ?>" value="<?php echo $zip_field_type_value; ?>"  />
                                </label>
                            </td>
                            <td>
                                <input type="hidden" name="<?php echo $zip_req; ?>" value="" />
                                <input type="checkbox" name="<?php echo $zip_req; ?>" value="checked" <?php echo $zip_req_value; ?> /></td>
                            <td><input type="text" name="<?php echo $zip_label; ?>" value="<?php echo $zip_label_value; ?>"  />
                            </td>
                            <td><input type="text" name="<?php echo $zip_value_name; ?>" value="<?php echo $zip_value_default; ?>"  /></td>
                            <td><input type="text" name="<?php echo $zip_placeholder_name; ?>" value="<?php echo $zip_placeholder_value; ?>"  /></td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    <input type="hidden" name="<?php echo $county_select_checkbox_name; ?>" value="" />
                                    <input type="checkbox" name="<?php echo $county_select_checkbox_name; ?>" value="checked" <?php echo $county_select_ischecked; ?> />
                                    <?php _e( 'Country', 'wpuf-pro' ); ?>
                                    <input type="hidden" name="<?php echo $county_select_field_type; ?>" value="<?php echo $county_select_field_type_value; ?>"  />
                                </label>
                            </td>
                            <td>
                                <input type="hidden" name="<?php echo $county_select_req; ?>" value="" />
                                <input type="checkbox" name="<?php echo $county_select_req; ?>" value="checked" <?php echo $county_select_req_value; ?> /></td>
                            <td><input type="text" name="<?php echo $county_select_label; ?>" value="<?php echo $county_select_label_value; ?>"  /></td>
                            <td>

                                <select name="<?php echo $county_select_value_name; ?>" style="width: 170px;">

                                </select>
                                <script>
                                    var countries = <?php echo file_get_contents(WPUF_ASSET_URI . '/js/countries.json');?>;console.log(countries);
                                    var sel_country = '<?php echo $county_select_value_default; ?>';
                                    var field_name = '<?php echo $county_select_value_name; ?>';
                                    var option_string = '<option value="">Select Country</option>';
                                    for (country in countries) {
                                        option_string = option_string + '<option value="'+ countries[country].code +'" ' + ( sel_country == countries[country].code ? 'selected':'' ) + ' >'+ countries[country].name +'</option>';
                                    }
                                    jQuery('select[name="'+ field_name +'"]').html(option_string);
                                </script>
                            </td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                    <?php
                    $param = array(
                        'names_to_hide' => array(
                            'name'  => $hide_country_list_name,
                            'value' => $hide_country_list_value
                        ),
                        'names_to_show' => array(
                            'name'  => $show_country_list_name ,
                            'value' => $show_country_list_value
                        ),
                        'option_to_chose' => array(
                            'name' => $country_list_visibility_opt_name,
                            'value' => $country_list_visibility_opt_value
                        )
                    );
                    self::render_drop_down_portion($param);
                    ?>
                </div>
            </div> <!-- .wpuf-form-holder -->
        </li>
    <?php
    }

    /**
     * Render Section start in case of multistep form
     *
     * @param $field_id
     * @param $label
     * @param $values
     *
     */
    public static function step_start( $field_id, $label, $classname, $values = array() ) {
        $title_name  = sprintf( '%s[%d][label]', self::$input_name, $field_id );
        $title_value = $values ? esc_attr( $values['label'] ) : 'Section';

        $step_start_name               = sprintf( '%s[%d][step_start]', self::$input_name, $field_id );
        $step_start_prev_button_name      = sprintf( '%s[%d][step_start][prev_button_text]', self::$input_name, $field_id );
        $step_start_prev_button_value     = isset( $values['step_start']['prev_button_text'] )? $values['step_start']['prev_button_text'] : 'Previous';

        $step_start_next_button_name      = sprintf( '%s[%d][step_start][next_button_text]', self::$input_name, $field_id );
        $step_start_next_button_value     = isset( $values['step_start']['next_button_text'] )? $values['step_start']['next_button_text'] : 'Next';
        ?>
        <li class="custom-field custom_html">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'step_start' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'step_start' ); ?>

            <div class="wpuf-form-holder">
                <div class="wpuf-form-rows">
                    <label><?php _e( 'Section Name', 'wpuf-pro' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <input type="text" class="smallipopInput" title="<?php _e( 'Title', 'wpuf-pro' ); ?>" name="<?php echo $title_name; ?>" value="<?php echo esc_attr( $title_value ); ?>" />
                    </div> <!-- .wpuf-form-rows -->
                </div>
                <div class="wpuf-form-rows">
                    <label><?php _e( 'Previous Button Text', 'wpuf-pro' ); ?></label>
                    <div class="wpuf-form-sub-fields">
                        <input type="text" class="smallipopInput" title="<?php _e( 'Previous Button Text', 'wpuf-pro' ); ?>" name="<?php echo $step_start_prev_button_name; ?>" value="<?php echo esc_attr( $step_start_prev_button_value ); ?>" />
                    </div> <!-- .wpuf-form-rows -->
                </div>
                <div class="wpuf-form-rows">
                    <label><?php _e( 'Next Button Text', 'wpuf-pro' ); ?></label>
                    <div class="wpuf-form-sub-fields">
                        <input type="text" class="smallipopInput" title="<?php _e( 'Next Button Text', 'wpuf-pro' ); ?>" name="<?php echo $step_start_next_button_name; ?>" value="<?php echo esc_attr( $step_start_next_button_value ); ?>" />
                    </div> <!-- .wpuf-form-rows -->
                </div>
            </div> <!-- .wpuf-form-holder -->
        </li>
    <?php
    }

    /**
     * Render really simple captcha
     * @param $field_id
     * @param $label
     * @param $classname
     * @param array $values
     */
    public static function really_simple_captcha( $field_id, $label, $classname, $values = array() ) {
        $title_name  = sprintf( '%s[%d][label]', self::$input_name, $field_id );
        $html_name   = sprintf( '%s[%d][html]', self::$input_name, $field_id );

        $title_value = $values ? esc_attr( $values['label'] ) : '';
        $html_value  = isset( $values['html'] ) ? esc_attr( $values['html'] ) : '';
        ?>
        <li class="custom-field custom_html">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'really_simple_captcha' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'really_simple_captcha' ); ?>

            <div class="wpuf-form-holder">
                <div class="wpuf-form-rows">
                    <label><?php _e( 'Title', 'wpuf-pro' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <input type="text" class="smallipopInput" title="Title of the section" name="<?php echo $title_name; ?>" value="<?php echo esc_attr( $title_value ); ?>" />

                        <div class="description" style="margin-top: 8px;">
                            <?php printf( __( "This field depends on <a href='%s' target='_blank'>Really Simple Captcha</a> Plugin. Install it first." ), 'http://wordpress.org/extend/plugins/really-simple-captcha/' ); ?>
                        </div>
                    </div> <!-- .wpuf-form-rows -->
                </div>

                <?php self::conditional_field( $field_id, $values ); ?>
            </div> <!-- .wpuf-form-holder -->
        </li>
    <?php
    }


    /**
     * Action hook
     * @param $field_id
     * @param $label
     * @param $classname
     * @param array $values
     */
    public static function action_hook( $field_id, $label, $classname, $values = array() ) {
        $title_name  = sprintf( '%s[%d][label]', self::$input_name, $field_id );
        $title_value = $values ? esc_attr( $values['label'] ) : '';
        ?>
        <li class="custom-field custom_html">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'action_hook' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'action_hook' ); ?>

            <div class="wpuf-form-holder">
                <div class="wpuf-form-rows">
                    <label><?php _e( 'Hook Name', 'wpuf-pro' ); ?></label>

                    <div class="wpuf-form-sub-fields">
                        <input type="text" class="smallipopInput" title="<?php _e( 'Name of the hook', 'wpuf-pro' ); ?>" name="<?php echo $title_name; ?>" value="<?php echo esc_attr( $title_value ); ?>" />

                        <div class="description" style="margin-top: 8px;">
                            <?php _e( "An option for developers to add dynamic elements they want. It provides the chance to add whatever input type you want to add in this form.", 'wpuf-pro' ); ?>
                            <?php _e( 'This way, you can bind your own functions to render the form to this action hook. You\'ll be given 3 parameters to play with: $form_id, $post_id, $form_settings.', 'wpuf-pro' ); ?>
                            <pre>
                                add_action('HOOK_NAME', 'your_function_name', 10, 3 );
                                function your_function_name( $form_id, $post_id, $form_settings ) {
                                    // do what ever you want
                                }
                            </pre>
                        </div>
                    </div> <!-- .wpuf-form-rows -->
                </div>
            </div> <!-- .wpuf-form-holder -->
        </li>
    <?php
    }


    /**
     * Render toc
     * @param $field_id
     * @param $label
     * @param $classname
     * @param array $values
     */
    public static function toc( $field_id, $label, $classname, $values = array() ) {

        $title_name        = sprintf( '%s[%d][label]', self::$input_name, $field_id );
        $description_name  = sprintf( '%s[%d][description]', self::$input_name, $field_id );

        $title_value       = $values ? esc_attr( $values['label'] ) : '';
        $description_value = $values ? esc_attr( $values['description'] ) : '';
        ?>

        <li class="custom-field custom_html">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'toc' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'toc' ); ?>

            <div class="wpuf-form-holder">
                <div class="wpuf-form-rows">
                    <?php self::common( $field_id, '', true, $values ); ?>
                    <!--<label><?php _e( 'Label', 'wpuf-pro' ); ?></label>
                    <input type="text" name="<?php echo $title_name; ?>" value="<?php echo esc_attr( $title_value ); ?>" />
                -->
                </div> <!-- .wpuf-form-rows -->

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Terms & Conditions', 'wpuf-pro' ); ?></label>
                    <textarea class="smallipopInput" title="<?php _e( 'Insert terms and condtions here.', 'wpuf-pro' ); ?>" name="<?php echo $description_name; ?>" rows="3"><?php echo esc_html( $description_value ); ?></textarea>
                </div> <!-- .wpuf-form-rows -->
                <?php self::conditional_field( $field_id, $values ); ?>
            </div> <!-- .wpuf-form-holder -->
        </li>
    <?php
    }

    /**
     * Ratings field
     * @param $field_id
     * @param $label
     * @param $classname
     * @param array $values
     */
    public static function ratings( $field_id, $label, $classname, $values = array() ) {
        ?>
        <li class="custom-field custom_html">
            <?php self::legend( $label, $values, $field_id ); ?>
            <?php self::hidden_field( "[$field_id][input_type]", 'ratings' ); ?>
            <?php self::hidden_field( "[$field_id][template]", 'ratings' ); ?>

            <div class="wpuf-form-holder">
                <?php self::common( $field_id, '', true, $values ); ?>

                <div class="wpuf-form-rows">
                    <label><?php _e( 'Options', 'wpuf-pro' ); ?></label>

                    <div class="wpuf-form-sub-fields wpuf-options">
                        <?php self::common_ratings_options( $field_id, 'options', $values ); ?>

                    </div> <!-- .wpuf-form-sub-fields -->
                    <?php self::conditional_field( $field_id, $values ); ?>
                </div> <!-- .wpuf-form-rows -->
            </div> <!-- .wpuf-form-holder -->
        </li>
    <?php
    }


    /**
     * Displays the options of ratings
     *
     * @param int $field_id
     * @param string $name
     * @param array $values
     */
    public static function common_ratings_options( $field_id, $name, $values = array() ) {


        $selected_name    = sprintf( '%s[%d][selected]', self::$input_name, $field_id );
        $input_name       = sprintf( '%s[%d][%s]', self::$input_name, $field_id, $name );
        $input_value_name = sprintf( '%s[%d][%s]', self::$input_name, $field_id, $name.'_values' );

        $selected_value   = ( $values && isset( $values['selected'] ) ) ? $values['selected'] : array();

        ?>
        <style>
            .wpuf-option-label-value span {
                font-weight: bold;
                margin-left: 5%;
                margin-right: 27%;
            }
        </style>
        <input type="checkbox" class="wpuf-value-handelar" id="<?php echo $name.'_'.$field_id; ?>"><label for="<?php echo $name.'_'.$field_id; ?>"><?php _e('show values', 'wpuf-pro'); ?></label>
        <div class="wpuf-option-label-value"><span><?php _e( 'Label', 'wpuf-pro' ); ?></span><span class="wpuf-option-value" style="display: none;"><?php _e( 'Value', 'wpuf-pro' ); ?></span></div>
        <?php
        if ( $values && $values['options'] > 0 ) {
            foreach ($values['options'] as $key => $value) {
                ?>
                <div class="wpuf-clone-field">

                    <input type="checkbox" name="<?php echo $selected_name ?>[]" value="<?php echo $value; ?>"<?php echo in_array( $value, $selected_value ) ? ' checked="checked"' : ''; ?> />
                    <input type="text" data-type="option" name="<?php echo $input_name; ?>[]" value="<?php echo $value; ?>">
                    <input type="text" data-type="option_value" name="<?php echo $input_value_name; ?>[]" value="<?php echo $key; ?>" style="display:none;">
                    <?php self::remove_button(); ?>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="wpuf-clone-field">
                <input type="checkbox" name="<?php echo $selected_name ?>[]">
                <input type="text" data-type="option" name="<?php echo $input_name; ?>[]" value="">
                <input type="text" data-type="option_value" name="<?php echo $input_value_name; ?>[]" value="" style="display:none;">

                <?php self::remove_button(); ?>
            </div>
            <?php
        }
    }

}
