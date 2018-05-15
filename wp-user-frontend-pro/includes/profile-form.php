<?php
/**
 * Profile Forms or wpuf_profile form builder class
 *
 * @package WP User Frontend
 */

class WPUF_Admin_Profile_Form_Pro {
    /**
     * Form type of which we're working on
     *
     * @var string
     */
    private $form_type = 'profile';

    /**
     * Form settings key
     *
     * @var string
     */
    private $form_settings_key = 'wpuf_form_settings';

    /**
     * WP post types
     *
     * @var string
     */
    private $wp_post_types = array();

    /**
     * Add neccessary actions and filters
     *
     * @return void
     */
    public function __construct() {
        add_action( 'wpuf-form-builder-init-type-wpuf_profile', array( $this, 'init_pro' ) );
        add_action( 'init', array($this, 'register_post_type') );
        add_action( "load-user-frontend_page_wpuf-profile-forms", array( $this, 'profile_forms_builder_init' ) );
    }

    /**
     * Initialize the framework
     *
     * @since 2.5
     *
     * @return void
     */
    public function init_pro() {
        require_once WPUF_PRO_ROOT . '/admin/form-builder/class-wpuf-form-builder-pro.php';
        new WPUF_Admin_Form_Builder_Pro();
    }

    /**
     * Register form post types
     *
     * @return void
     */
    public function register_post_type() {
        $capability = wpuf_admin_role();

        register_post_type( 'wpuf_profile', array(
            'label'           => __( 'Registraton Forms', 'wpuf-pro' ),
            'public'          => false,
            'show_ui'         => false,
            'show_in_menu'    => false,
            'capability_type' => 'post',
            'hierarchical'    => false,
            'query_var'       => false,
            'supports'        => array('title'),
            'capabilities' => array(
                'publish_posts'       => $capability,
                'edit_posts'          => $capability,
                'edit_others_posts'   => $capability,
                'delete_posts'        => $capability,
                'delete_others_posts' => $capability,
                'read_private_posts'  => $capability,
                'edit_post'           => $capability,
                'delete_post'         => $capability,
                'read_post'           => $capability,
            ),
            'labels' => array(
                'name'               => __( 'Forms', 'wpuf-pro' ),
                'singular_name'      => __( 'Form', 'wpuf-pro' ),
                'menu_name'          => __( 'Registration Forms', 'wpuf-pro' ),
                'add_new'            => __( 'Add Form', 'wpuf-pro' ),
                'add_new_item'       => __( 'Add New Form', 'wpuf-pro' ),
                'edit'               => __( 'Edit', 'wpuf-pro' ),
                'edit_item'          => __( 'Edit Form', 'wpuf-pro' ),
                'new_item'           => __( 'New Form', 'wpuf-pro' ),
                'view'               => __( 'View Form', 'wpuf-pro' ),
                'view_item'          => __( 'View Form', 'wpuf-pro' ),
                'search_items'       => __( 'Search Form', 'wpuf-pro' ),
                'not_found'          => __( 'No Form Found', 'wpuf-pro' ),
                'not_found_in_trash' => __( 'No Form Found in Trash', 'wpuf-pro' ),
                'parent'             => __( 'Parent Form', 'wpuf-pro' ),
            ),
        ) );
    }

    /**
     * Initiate form builder for wpuf_profile post type
     *
     * @since 2.5
     *
     * @return void
     */
    public function profile_forms_builder_init() {

        if ( ! isset( $_GET['action'] ) ) {
            return;
        }

        if ( 'add-new' === $_GET['action'] && empty( $_GET['id'] ) ) {
            $form_id = wpuf_create_sample_form( 'Sample Registration Form', 'wpuf_profile', true );
            $add_new_page_url = add_query_arg( array( 'id' => $form_id ), admin_url( 'admin.php?page=wpuf-profile-forms&action=edit' ) );
            wp_redirect( $add_new_page_url );
        }

        if ( ( 'edit' === $_GET['action'] ) && ! empty( $_GET['id'] ) ) {

            add_action( 'wpuf-form-builder-settings-tabs-profile', array( $this, 'add_settings_tabs' ) );
            add_action( 'wpuf-form-builder-settings-tab-contents-profile', array( $this, 'add_settings_tab_contents' ) );
            add_filter( 'wpuf-form-builder-fields-section-before', array( $this, 'add_profile_field_section' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            add_filter( 'wpuf-form-builder-js-root-mixins', array( $this, 'js_root_mixins' ) );
            add_action( 'wpuf-form-builder-js-deps', array( $this, 'js_dependencies' ) );
            add_filter( 'wpuf-form-builder-js-builder-stage-mixins', array( $this, 'js_builder_stage_mixins' ) );
            add_action( 'wpuf-form-builder-template-builder-stage-submit-area', array( $this, 'add_form_submit_area' ) );
            add_filter( 'wpuf-form-builder-field-settings', array( $this, 'add_field_settings' ) );
            add_filter( 'wpuf-form-builder-i18n', array( $this, 'i18n' ) );

            do_action( 'wpuf-form-builder-init-type-wpuf_profile' );

            $settings = array(
                'form_type'         => 'profile',
                'post_type'         => 'wpuf_profile',
                'post_id'           => $_GET['id'],
                'form_settings_key' => $this->form_settings_key,
                'shortcodes'        => array(
                    array( 'name' => 'wpuf_profile', 'type' => 'registration' ),
                    array( 'name' => 'wpuf_profile', 'type' => 'profile' )
                )
            );

            new WPUF_Admin_Form_Builder( $settings );
        }
    }

    /**
     * Add settings tabs
     *
     * @since 2.5
     *
     * @return void
     */
    public function add_settings_tabs() {
        ?>

        <a href="#wpuf-metabox-settings" class="nav-tab"><?php _e( 'General', 'wpuf-pro' ); ?></a>
        <a href="#wpuf-metabox-settings-reg-display-settings" class="nav-tab"><?php _e( 'Display Settings', 'wpuf-pro' ); ?></a>
        <a href="#wpuf-metabox-settings-registration" class="nav-tab"><?php _e( 'Registration', 'wpuf-pro' ); ?></a>
        <a href="#wpuf-metabox-settings-profile" class="nav-tab"><?php _e( 'Profile Update', 'wpuf-pro' ); ?></a>
        <?php do_action( 'wpuf_profile_form_tab' ); ?>

        <?php
    }

    /**
     * Add settings tabs
     *
     * @since 2.5
     *
     * @return void
     */
    public function add_settings_tab_contents() {
        ?>

        <div id="wpuf-metabox-settings" class="group">
            <?php $this->form_settings_general(); ?>
        </div>
        <div id="wpuf-metabox-settings-reg-display-settings" class="group">
            <?php $this->form_settings_reg_display_settings(); ?>
        </div>
        <div id="wpuf-metabox-settings-registration" class="group">
            <?php $this->form_settings_registration(); ?>
        </div>
        <div id="wpuf-metabox-settings-profile" class="group">
            <?php $this->form_settings_profile(); ?>
        </div>

        <?php do_action( 'wpuf_profile_form_tab_content' ); ?>

        <?php
    }

    /**
     * Displays settings on registration form builder
     *
     * @since 2.3.2
     *
     * @return void
     */
    public function form_settings_general() {
        global $post;

        $form_settings = wpuf_get_form_settings( $post->ID );

        $email_verification      = isset( $form_settings['enable_email_verification'] ) ? $form_settings['enable_email_verification'] : 'no';
        $role_selected           = isset( $form_settings['role'] ) ? $form_settings['role'] : 'subscriber';

        // Multisteps
        $is_multistep_enabled    = isset( $form_settings['enable_multistep'] ) ? $form_settings['enable_multistep'] : '';
        $multistep_progress_type = isset( $form_settings['multistep_progressbar_type'] ) ? $form_settings['multistep_progressbar_type'] : 'step_by_step';

        $ms_ac_txt_color         = isset( $form_settings['ms_ac_txt_color'] ) ? $form_settings['ms_ac_txt_color'] : '#ffffff';
        $ms_active_bgcolor       = isset( $form_settings['ms_active_bgcolor'] ) ? $form_settings['ms_active_bgcolor'] : '#00a0d2';
        $ms_bgcolor              = isset( $form_settings['ms_bgcolor'] ) ? $form_settings['ms_bgcolor'] : '#E4E4E4';
        ?>
        <table class="form-table">
            <tr class="wpuf-post-type">
                <th><?php _e( 'Enable Email Verfication', 'wpuf-pro' ); ?></th>
                <td>
                    <input type="hidden" name="wpuf_settings[enable_email_verification]" value="no">
                    <input type="checkbox" id="wpuf-enable_email_verification" name="wpuf_settings[enable_email_verification]" value="yes" <?php checked( $email_verification, 'yes' ); ?> > <label for="wpuf-enable_email_verification">Enable Email Verification</label>

                    <p class="description"><?php _e( 'An email will be sent to the user to verify and activate the registration.', 'wpuf-pro' ); ?></p>
                </td>
            </tr>

            <tr class="wpuf-post-type">
                <th><?php _e( 'User Role', 'wpuf-pro' ); ?></th>
                <td>
                    <select name="wpuf_settings[role]">
                        <?php
                        $user_roles = wpuf_get_user_roles();
                        foreach ( $user_roles as $role => $label ) {
                            printf('<option value="%s"%s>%s</option>', $role, selected( $role_selected, $role, false ), $label );
                        }
                        ?>
                    </select>

                    <p class="description"><?php _e( 'The user role of the newly registered user.', 'wpuf-pro' ); ?></p>
                </td>
            </tr>
            <tr class="wpuf_enable_multistep_section">
                <th><?php _e( 'Enable Multistep', 'wpuf-pro' ); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wpuf_settings[enable_multistep]" value="yes" <?php checked( $is_multistep_enabled, 'yes' ); ?> />
                        <?php _e( 'Enable Multistep', 'wpuf-pro' ); ?>
                    </label>

                    <p class="description"><?php echo __( 'If checked, form will be displayed in frontend in multiple steps', 'wpuf-pro' ); ?></p>
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

            <?php do_action( 'wpuf_profile_setting', $form_settings, $post ); ?>
        </table>
        <?php
    }

    /**
     * Adds registration redirect tab content
     *
     * @since 2.3.2
     *
     * @return void
     */
    public function form_settings_registration() {
        global $post;

        $form_settings = wpuf_get_form_settings( $post->ID );

        $redirect_to        = isset( $form_settings['reg_redirect_to'] ) ? $form_settings['reg_redirect_to'] : 'post';

        if ( ! isset( $form_settings['reg_redirect_to'] ) ) {
            $redirect_to = isset( $form_settings['reg_redirect_to'] ) ? $form_settings['reg_redirect_to'] : 'post';
        }

        $message                = isset( $form_settings['message'] ) ? $form_settings['message'] : __( 'Registration successful', 'wpuf-pro' );
        $page_id                = isset( $form_settings['reg_page_id'] ) ? $form_settings['reg_page_id'] : 0;
        $url                    = isset( $form_settings['registration_url'] ) ? $form_settings['registration_url'] : '';

        if ( ! isset( $form_settings['registration_url'] ) ) {
            $url = isset( $form_settings['url'] ) ? $form_settings['url'] : '';
        }

        $submit_text            = isset( $form_settings['submit_text'] ) ? $form_settings['submit_text'] : __( 'Register', 'wpuf-pro' );
        $ms_ac_txt_color        = isset( $form_settings['ms_ac_txt_color'] ) ? $form_settings['ms_ac_txt_color'] : '#ffffff';
        $ms_active_bgcolor      = isset( $form_settings['ms_active_bgcolor'] ) ? $form_settings['ms_active_bgcolor'] : '#00a0d2';
        $ms_bgcolor             = isset( $form_settings['ms_bgcolor'] ) ? $form_settings['ms_bgcolor'] : '#E4E4E4';
        ?>
        <table class="form-table">
            <tr class="wpuf-reg-redirect-to">
                <th><?php _e( 'Redirect To', 'wpuf-pro' ); ?></th>
                <td>
                    <select name="wpuf_settings[reg_redirect_to]">
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

            <tr class="wpuf-page-id">
                <th><?php _e( 'Page', 'wpuf-pro' ); ?></th>
                <td>
                    <select name="wpuf_settings[reg_page_id]">
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
                    <input type="url" name="wpuf_settings[registration_url]" value="<?php echo esc_attr( $url ); ?>">
                </td>
            </tr>

            <tr class="wpuf-submit-text">
                <th><?php _e( 'Submit Button text', 'wpuf-pro' ); ?></th>
                <td>
                    <input type="text" name="wpuf_settings[submit_text]" value="<?php echo esc_attr( $submit_text ); ?>">
                </td>
            </tr>

            <?php do_action( 'wpuf_profile_setting_reg', $form_settings, $post ); ?>
        </table>
    <?php
    }

    /**
     * Adds profile update redirect tab content
     *
     * @since 2.3.2
     *
     * @return void
     */

    public function form_settings_profile() {
        global $post;

        $form_settings = wpuf_get_form_settings( $post->ID );

        $redirect_to             = isset( $form_settings['profile_redirect_to'] ) ? $form_settings['profile_redirect_to'] : 'post';

        if ( ! isset( $form_settings['reg_redirect_to'] ) ) {
            $redirect_to         = isset( $form_settings['profile_redirect_to'] ) ? $form_settings['profile_redirect_to'] : 'post';
        }

        $update_message          = isset( $form_settings['update_message'] ) ? $form_settings['update_message'] : __( 'Profile updated successfully', 'wpuf-pro' );
        $page_id                 = isset( $form_settings['profile_page_id'] ) ? $form_settings['profile_page_id'] : 0;
        $url                     = isset( $form_settings['profile_url'] ) ? $form_settings['profile_url'] : '';

        if ( ! isset( $form_settings['profile_url'] ) ) {
            $url = isset( $form_settings['url'] ) ? $form_settings['url'] : '';
        }


        $update_text             = isset( $form_settings['update_text'] ) ? $form_settings['update_text'] : __( 'Update Profile', 'wpuf-pro' );

        $ms_ac_txt_color         = isset( $form_settings['ms_ac_txt_color'] ) ? $form_settings['ms_ac_txt_color'] : '#ffffff';
        $ms_active_bgcolor       = isset( $form_settings['ms_active_bgcolor'] ) ? $form_settings['ms_active_bgcolor'] : '#00a0d2';
        $ms_bgcolor              = isset( $form_settings['ms_bgcolor'] ) ? $form_settings['ms_bgcolor'] : '#E4E4E4';
        ?>
        <table class="form-table">
            <tr class="wpuf-profile-redirect-to">
                <th><?php _e( 'Redirect To', 'wpuf-pro' ); ?></th>
                <td>
                    <select name="wpuf_settings[profile_redirect_to]">
                    <?php
                    $redirect_options = array(
                        'same' => __( 'Same Page', 'wpuf-pro' ),
                        'page' => __( 'To a page', 'wpuf-pro' ),
                        'url'  => __( 'To a custom URL', 'wpuf-pro' )
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
                <th><?php _e( 'Update profile message', 'wpuf-pro' ); ?></th>
                <td>
                    <textarea rows="3" cols="40" name="wpuf_settings[update_message]"><?php echo esc_textarea( $update_message ); ?></textarea>
                </td>
            </tr>

            <tr class="wpuf-page-id">
                <th><?php _e( 'Page', 'wpuf-pro' ); ?></th>
                <td>
                    <select name="wpuf_settings[profile_page_id]">
                    <?php
                    $pages = get_posts(  array( 'numberposts' => -1, 'post_type' => 'page') );

                    foreach ( $pages as $page ) {
                        printf('<option value="%s"%s>%s</option>', $page->ID, selected( $page_id, $page->ID, false ), esc_attr( $page->post_title ) );
                    }
                    ?>
                    </select>
                </td>
            </tr>

            <tr class="wpuf-url">
                <th><?php _e( 'Custom URL', 'wpuf-pro' ); ?></th>
                <td>
                    <input type="url" name="wpuf_settings[profile_url]" value="<?php echo esc_attr( $url ); ?>">
                </td>
            </tr>

            <tr class="wpuf-update-text">
                <th><?php _e( 'Update Button text', 'wpuf-pro' ); ?></th>
                <td>
                    <input type="text" name="wpuf_settings[update_text]" value="<?php echo esc_attr( $update_text ); ?>">
                </td>
            </tr>

            <?php do_action( 'wpuf_profile_setting_profile', $form_settings, $post ); ?>
        </table>
    <?php
    }

    public function form_settings_reg_display_settings () {
        global $post;

        $form_settings  = wpuf_get_form_settings( get_the_ID() );
        $label_position = isset( $form_settings['label_position'] ) ? $form_settings['label_position'] : 'left';
        $form_layout    = isset( $form_settings['form_layout'] ) ? $form_settings['form_layout'] : 'layout1';
        ?>
        <table class="form-table">
            <tr class="wpuf-pro-label-position">
                <th><?php _e( 'Label Position', 'wpuf-pro' ); ?></th>
                <td>
                    <select name="wpuf_settings[label_position]">
                        <?php
                        $positions = array(
                            'above'  => __( 'Above Element', 'wpuf-pro' ),
                            'left'   => __( 'Left of Element', 'wpuf-pro' ),
                            'right'  => __( 'Right of Element', 'wpuf-pro' ),
                            'hidden' => __( 'Hidden', 'wpuf-pro' ),
                        );

                        foreach ($positions as $to => $label) {
                            printf('<option value="%s"%s>%s</option>', $to, selected( $label_position, $to, false ), $label );
                        }
                        ?>
                    </select>

                    <p class="description">
                        <?php _e( 'Where the labels of the form should display', 'wpuf-pro' ) ?>
                    </p>
                </td>
            </tr>

            <tr class="wpuf-form-layouts">
                <th><?php _e( 'Form Style', 'wpuf-pro' ); ?></th>
                <td>
                    <ul>
                        <?php
                            $layouts = array(
                                'layout1' => WPUF_PRO_ASSET_URI . '/images/forms/layout1.png',
                                'layout2' => WPUF_PRO_ASSET_URI . '/images/forms/layout2.png',
                                'layout3' => WPUF_PRO_ASSET_URI . '/images/forms/layout3.png'
                            );

                            foreach ($layouts as $key => $image) {
                                $active = '';

                                if ( $key == $form_layout ) {
                                    $active = 'active';
                                }

                                $output  = '<li class="' . $active . '">';
                                $output .= '<input type="radio" name="wpuf_settings[form_layout]" value="' . $key . '" ' . checked( $form_layout, $key, false ). '>';
                                $output .= '<img src="' . $image . '" alt="">';
                                $output .= '</li>';

                                echo $output;
                            }
                        ?>
                    </ul>
                </td>
            </tr>
        </table>

        <?php
    }

    /**
     * Add post fields in form builder
     *
     * @since 2.5
     *
     * @return array
     */
    public function add_profile_field_section() {
        $profile_fields = apply_filters( 'wpuf-form-builder-wp_profile-fields-section-post-fields', array(
            'user_login', 'first_name', 'last_name', 'display_name', 'nickname', 'user_email', 'user_url', 'user_bio', 'password', 'avatar'
        ) );

        return array(
            array(
                'title'     => __( 'Profile Fields', 'wpuf-pro' ),
                'id'        => 'profile-fields',
                'fields'    => $profile_fields
            )
        );
    }

    /**
     * Admin script form wpuf_forms form builder
     *
     * @since 2.5
     *
     * @return void
     */
    public function admin_enqueue_scripts() {
        wp_register_script(
            'wpuf-form-builder-wpuf-profile',
            WPUF_PRO_ASSET_URI . '/js/wpuf-form-builder-wpuf-profile.js',
            array( 'jquery', 'underscore', 'wpuf-vue', 'wpuf-vuex' ),
            WPUF_PRO_VERSION,
            true
            );
    }

    /**
     * Add dependencies to form builder script
     *
     * @since 2.5
     *
     * @param array $deps
     *
     * @return array
     */
    public function js_dependencies( $deps ) {
        array_push( $deps, 'wpuf-form-builder-wpuf-profile' );

        return $deps;
    }

    /**
     * Add mixins to root instance
     *
     * @since 2.5
     *
     * @param array $mixins
     *
     * @return array
     */
    public function js_root_mixins( $mixins ) {
        array_push( $mixins , 'wpuf_forms_mixin_root' );

        return $mixins;
    }

    /**
     * Add mixins to form builder builder stage component
     *
     * @since 2.5
     *
     * @param array $mixins
     *
     * @return array
     */
    public function js_builder_stage_mixins( $mixins ) {
        array_push( $mixins , 'wpuf_forms_mixin_builder_stage' );

        return $mixins;
    }

    /**
     * Add buttons in form submit area
     *
     * @since 2.5
     *
     * @return void
     */
    public function add_form_submit_area() {
        ?>
        <input @click.prevent="" type="submit" name="submit" :value="post_form_settings.submit_text">

        <a
        v-if="post_form_settings.draft_post"
        @click.prevent=""
        href="#"
        class="btn"
        id="wpuf-post-draft"
        >
        <?php _e( 'Save Draft', 'wpuf-pro' ); ?>
        </a>
        <?php
    }

    /**
     * Add field settings
     *
     * @since 2.5
     *
     * @param array $field_settings
     *
     * @return array
     */
    public function add_field_settings( $field_settings ) {
        $field_settings = array_merge( $field_settings, array(
            'user_login'  => self::user_login(),
            'first_name'  => self::first_name(),
            'last_name'   => self::last_name(),
            'display_name'=> self::display_name(),
            'nickname'    => self::nickname(),
            'user_email'  => self::user_email(),
            'user_url'    => self::user_url(),
            'user_bio'    => self::user_bio(),
            'password'    => self::password(),
            'avatar'      => self::avatar(),
        ) );

        return $field_settings;
    }

    /**
     * Username field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function user_login() {
        $settings = WPUF_Form_Builder_Field_Settings::get_common_properties( false );
        $settings = array_merge( $settings, WPUF_Form_Builder_Field_Settings::get_common_text_properties() );

        return array(
            'template'      => 'user_login',
            'title'         => __( 'Username', 'wpuf-pro' ),
            'icon'          => 'user',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'    => 'text',
                'template'      => 'user_login',
                'required'      => 'yes',
                'label'         => __( 'Username', 'wpuf-pro' ),
                'name'          => 'user_login',
                'is_meta'       => 'no',
                'help'          => '',
                'css'           => '',
                'placeholder'   => '',
                'default'       => '',
                'size'          => 40,
                'id'            => 0,
                'is_new'        => true,
                'wpuf_cond'     => WPUF_Form_Builder_Field_Settings::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * First Name field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function first_name() {
        $settings = WPUF_Form_Builder_Field_Settings::get_common_properties( false );
        $settings = array_merge( $settings, WPUF_Form_Builder_Field_Settings::get_common_text_properties() );

        return array(
            'template'      => 'first_name',
            'title'         => __( 'First Name', 'wpuf-pro' ),
            'icon'          => 'user',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'    => 'text',
                'template'      => 'first_name',
                'required'      => 'yes',
                'label'         => __( 'First Name', 'wpuf-pro' ),
                'name'          => 'first_name',
                'is_meta'       => 'no',
                'help'          => '',
                'css'           => '',
                'placeholder'   => '',
                'default'       => '',
                'size'          => 40,
                'id'            => 0,
                'is_new'        => true,
                'wpuf_cond'     => WPUF_Form_Builder_Field_Settings::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Last Name field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function last_name() {
        $settings = WPUF_Form_Builder_Field_Settings::get_common_properties( false );
        $settings = array_merge( $settings, WPUF_Form_Builder_Field_Settings::get_common_text_properties() );

        return array(
            'template'      => 'last_name',
            'title'         => __( 'Last Name', 'wpuf-pro' ),
            'icon'          => 'user',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'    => 'text',
                'template'      => 'last_name',
                'required'      => 'yes',
                'label'         => __( 'Last Name', 'wpuf-pro' ),
                'name'          => 'last_name',
                'is_meta'       => 'no',
                'help'          => '',
                'css'           => '',
                'placeholder'   => '',
                'default'       => '',
                'size'          => 40,
                'id'            => 0,
                'is_new'        => true,
                'wpuf_cond'     => WPUF_Form_Builder_Field_Settings::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Display Name field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function display_name() {
        $settings = WPUF_Form_Builder_Field_Settings::get_common_properties( false );
        $settings = array_merge( $settings, WPUF_Form_Builder_Field_Settings::get_common_text_properties() );

        return array(
            'template'      => 'display_name',
            'title'         => __( 'Display Name', 'wpuf-pro' ),
            'icon'          => 'user',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'    => 'text',
                'template'      => 'display_name',
                'required'      => 'no',
                'label'         => __( 'Display Name', 'wpuf-pro' ),
                'name'          => 'display_name',
                'is_meta'       => 'no',
                'help'          => '',
                'css'           => '',
                'placeholder'   => '',
                'default'       => '',
                'size'          => 40,
                'id'            => 0,
                'is_new'        => true,
                'wpuf_cond'     => WPUF_Form_Builder_Field_Settings::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Nickname field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function nickname() {
        $settings = WPUF_Form_Builder_Field_Settings::get_common_properties( false );
        $settings = array_merge( $settings, WPUF_Form_Builder_Field_Settings::get_common_text_properties() );

        return array(
            'template'      => 'nickname',
            'title'         => __( 'Nickname', 'wpuf-pro' ),
            'icon'          => 'user',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'    => 'text',
                'template'      => 'nickname',
                'required'      => 'yes',
                'label'         => __( 'Nickname', 'wpuf-pro' ),
                'name'          => 'nickname',
                'is_meta'       => 'no',
                'help'          => '',
                'css'           => '',
                'placeholder'   => '',
                'default'       => '',
                'size'          => 40,
                'id'            => 0,
                'is_new'        => true,
                'wpuf_cond'     => WPUF_Form_Builder_Field_Settings::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * User Email field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function user_email() {
        $settings = WPUF_Form_Builder_Field_Settings::get_common_properties( false );
        $settings = array_merge( $settings, WPUF_Form_Builder_Field_Settings::get_common_text_properties() );

        return array(
            'template'      => 'user_email',
            'title'         => __( 'E-mail', 'wpuf-pro' ),
            'icon'          => 'envelope-o',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'    => 'email',
                'template'      => 'user_email',
                'required'      => 'yes',
                'label'         => __( 'E-mail', 'wpuf-pro' ),
                'name'          => 'user_email',
                'is_meta'       => 'no',
                'help'          => '',
                'css'           => '',
                'placeholder'   => '',
                'default'       => '',
                'size'          => 40,
                'id'            => 0,
                'is_new'        => true,
                'wpuf_cond'     => WPUF_Form_Builder_Field_Settings::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Password field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function user_url() {
        $settings = WPUF_Form_Builder_Field_Settings::get_common_properties( false );
        $settings = array_merge( $settings, WPUF_Form_Builder_Field_Settings::get_common_text_properties() );

        return array(
            'template'      => 'user_url',
            'title'         => __( 'Website', 'wpuf-pro' ),
            'icon'          => 'link',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'    => 'url',
                'template'      => 'user_url',
                'required'      => 'yes',
                'label'         => __( 'Website', 'wpuf-pro' ),
                'name'          => 'user_url',
                'is_meta'       => 'no',
                'help'          => '',
                'css'           => '',
                'placeholder'   => '',
                'default'       => '',
                'size'          => 40,
                'id'            => 0,
                'is_new'        => true,
                'wpuf_cond'     => WPUF_Form_Builder_Field_Settings::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Biographical Info field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function user_bio() {
        $settings = WPUF_Form_Builder_Field_Settings::get_common_properties( false );
        $settings = array_merge( $settings, WPUF_Form_Builder_Field_Settings::get_common_textarea_properties() );

        return array(
            'template'      => 'user_bio',
            'title'         => __( 'Biographical Info', 'wpuf-pro' ),
            'icon'          => 'text-width',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'       => 'textarea',
                'template'         => 'user_bio',
                'required'         => 'yes',
                'label'            => __( 'Biographical Info', 'wpuf-pro' ),
                'name'             => 'description',
                'is_meta'          => 'yes',
                'help'             => '',
                'css'              => '',
                'rows'             => 5,
                'cols'             => 25,
                'placeholder'      => '',
                'default'          => '',
                'rich'             => 'no',
                'word_restriction' => '',
                'id'               => 0,
                'is_new'           => true,
                'wpuf_cond'        => WPUF_Form_Builder_Field_Settings::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Password field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function password() {
        $settings = WPUF_Form_Builder_Field_Settings::get_common_properties( false );
        $settings = array_merge( $settings, WPUF_Form_Builder_Field_Settings::get_common_text_properties() );

        $settings = array_merge( $settings, array(
            array(
                'name'          => 'min_length',
                'title'         => __( 'Minimum password length', 'wpuf-pro' ),
                'type'          => 'text',
                'section'       => 'advanced',
                'priority'      => 23,
            ),
            array(
                'name'          => 'repeat_pass',
                'title'         => __( 'Password Re-type', 'wpuf-pro' ),
                'type'          => 'checkbox',
                'options'       => array( 'yes' => __( 'Require Password repeat', 'wpuf-pro' ) ),
                'is_single_opt' => true,
                'section'       => 'advanced',
                'priority'      => 24,
            ),
            array(
                'name'          => 're_pass_label',
                'title'         => __( 'Re-type password label', 'wpuf-pro' ),
                'type'          => 'text',
                'section'       => 'advanced',
                'priority'      => 25,
            ),
            array(
                'name'          => 'pass_strength',
                'title'         => __( 'Password Strength Meter', 'wpuf-pro' ),
                'type'          => 'checkbox',
                'options'       => array( 'yes' => __( 'Show password strength meter', 'wpuf-pro' ) ),
                'is_single_opt' => true,
                'section'       => 'advanced',
                'priority'      => 26,
            ),
        ) );

        return array(
            'template'      => 'password',
            'title'         => __( 'Password', 'wpuf-pro' ),
            'icon'          => 'lock',
            'settings'      => $settings,
            'is_full_width' => true,
            'field_props'   => array(
                'input_type'    => 'password',
                'template'      => 'password',
                'required'      => 'yes',
                'label'         => __( 'Password', 'wpuf-pro' ),
                'name'          => 'password',
                'is_meta'       => 'no',
                'help'          => '',
                'css'           => '',
                'placeholder'   => '',
                'default'       => '',
                'size'          => 40,
                'id'            => 0,
                'is_new'        => true,
                'min_length'    => 5,
                'repeat_pass'   => 'yes',
                're_pass_label' => 'Confirm Password',
                'pass_strength' => 'yes',
                'wpuf_cond'     => WPUF_Form_Builder_Field_Settings::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * Avatar field settings
     *
     * @since 2.5
     *
     * @return array
     */
    public static function avatar() {
        $settings = WPUF_Form_Builder_Field_Settings::get_common_properties( false );

        $settings = array_merge( $settings, array(
            array(
                'name'          => 'max_size',
                'title'         => __( 'Max. file size', 'wpuf-pro' ),
                'type'          => 'text',
                'section'       => 'advanced',
                'priority'      => 20,
                'help_text'     => __( 'Enter maximum upload size limit in KB', 'wpuf-pro' ),
            ),

            array(
                'name'          => 'count',
                'title'         => __( 'Max. files', 'wpuf-pro' ),
                'type'          => 'text',
                'section'       => 'advanced',
                'priority'      => 21,
                'help_text'     => __( 'Number of images can be uploaded', 'wpuf-pro' ),
            ),
        ) );

        return array(
            'template'      => 'avatar',
            'title'         => __( 'Avatar', 'wpuf-pro' ),
            'icon'          => 'file-image-o',
            'settings'      => $settings,
            'field_props'   => array(
                'input_type'    => 'image_upload',
                'template'      => 'avatar',
                'required'      => 'yes',
                'label'         => __( 'Avatar', 'wpuf-pro' ),
                'name'          => 'avatar',
                'is_meta'       => 'no',
                'help'          => '',
                'css'           => '',
                'max_size'      => '1024',
                'count'         => '1',
                'id'            => 0,
                'is_new'        => true,
                'wpuf_cond'     => WPUF_Form_Builder_Field_Settings::get_wpuf_cond_prop()
            )
        );
    }

    /**
     * i18n strings specially for Post Forms
     *
     * @since 2.5
     *
     * @param array $i18n
     *
     * @return array
     */
    public function i18n( $i18n ) {
        return array_merge( $i18n, array(
            'email_needed' => __( 'Profile Forms must have Email field', 'wpuf-pro' )
            ) );
    }

}
