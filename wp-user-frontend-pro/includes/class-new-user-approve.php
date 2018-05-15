<?php

/**
 * New user approve class for registration
 *
 * @since 2.8
 *
 * @package WP User Frontend
 */
class WPUF_New_User_Approve {

    /**
     * Class constructor.
     *
     */
    public function __construct() {
        add_action( 'wpuf_profile_setting', array( $this, 'add_form_settings' ), 10, 2 );
        add_action( 'wpuf_after_register', array( $this, 'insert_user_status' ), 10, 3 );
        add_filter( 'manage_users_columns', array( $this, 'new_modify_user_table' ), 10, 1 );
        add_filter( 'manage_users_custom_column', array( $this, 'new_modify_user_table_row' ), 10, 3 );
        add_filter( 'user_row_actions', array( $this, 'user_row_action_links' ), 10, 2 );
        add_filter( 'wp_authenticate_user', array( $this, 'validate_login'), 10, 2 );
        add_action( 'restrict_manage_users', array( $this, 'status_filter' ), 10, 1 );
        add_action( 'pre_user_query', array( $this, 'filter_by_status' ) );
        add_action( 'admin_footer-users.php', array( $this, 'admin_footer' ) );
        add_action( 'load-users.php', array( $this, 'bulk_action' ) );
        add_filter( 'wpuf_settings_fields', array( $this, 'add_global_settings' ), 10, 1 );
    }

    /**
     * Get the valid statuses.
     *
     * @return array
     */
    public function get_valid_statuses() {
        return array( 'pending', 'approved', 'denied' );
    }

    /**
     * Add setting to registration form
     *
     * @return void
     */
    public function add_form_settings( $form_settings, $post ) {
        $status_selected    = isset( $form_settings['wpuf_user_status'] ) ? $form_settings['wpuf_user_status'] : 'approved';
        $statuses           = $this->get_valid_statuses();
        ?>

        <tr class="wpuf-post-type">
            <th><?php _e( 'New User Status', 'wpuf-pro' ); ?></th>
            <td>
                <select name="wpuf_settings[wpuf_user_status]">
                    <?php
                        foreach ( $statuses as $status ) {
                            printf('<option value="%s"%s>%s</option>', $status, selected( $status_selected, $status, false ), ucfirst( $status ) );
                        }
                    ?>
                </select>

                <p class="description"><?php _e( 'The user status of the newly registered user.', 'wpuf-pro' ); ?></p>
            </td>
        </tr>

        <?php
    }

    /**
     * Add setting to registration form
     *
     * @return void
     */
    public function insert_user_status( $user_id, $form_id ) {

        $form_settings  = wpuf_get_form_settings( $form_id );
        $status         = isset( $form_settings['wpuf_user_status'] ) ? $form_settings['wpuf_user_status'] : 'approved';
        $key            = 'wpuf_user_status';

        update_user_meta( $user_id, $key, $status );
    }

    /**
     * Update user status
     *
     * @return void
     */
    public function update_user_status() {

        $wp_list_table  = _get_list_table('WP_Users_List_Table');
        $key            = 'wpuf_user_status';
        $user_id        = isset( $_GET['user'] ) ? $_GET['user'] : '';
        $status         = '';

        switch ( $wp_list_table->current_action() ) {
            case 'wpuf-approve-user':
                $status = 'approved';
                break;
            case 'wpuf-pending-user':
                $status = 'pending';
                break;
            case 'wpuf-deny-user':
                $status = 'denied';
                break;
            default:
        }

        if ( update_user_meta( $user_id, $key, $status ) ) {
            WPUF_Frontend_Form_Profile::user_email_notification( $status, $user_id );
        }

    }

    /**
     * Add status column to users table
     *
     * @return array
     */
    public function new_modify_user_table( $column ) {
        $column['status'] = __( 'Status', 'wpuf-pro' );
        return $column;
    }

    /**
     * Pull user status
     *
     * @return string
     */
    public function new_modify_user_table_row( $value, $column_name, $user_id ) {
        $this->update_user_status();

        switch ($column_name) {
            case 'status' :
                return ucfirst( $this->get_user_status( $user_id ) );
                break;
            default:
        }

        return $value;
    }

    /**
     * Add setting to registration form
     *
     * @return array
     */
    public function user_row_action_links( $actions, $user_object ) {

        $user_id        = $user_object->ID;
        $user_status    = $this->get_user_status( $user_id );

        $approve_user   = "<a href='" . admin_url( "users.php?action=wpuf-approve-user&amp;user=$user_id") . "'>" . __( 'Approve', 'wpuf-pro' ) . "</a>";
        $pending_user   = "<a href='" . admin_url( "users.php?action=wpuf-pending-user&amp;user=$user_id") . "'>" . __( 'Pending', 'wpuf-pro' ) . "</a>";
        $deny_user      = "<a href='" . admin_url( "users.php?action=wpuf-deny-user&amp;user=$user_id") . "'>" . __( 'Deny', 'wpuf-pro' ) . "</a>";

        switch ($user_status) {
            case 'approved' :
                $actions['wpuf-deny-user']      = $deny_user;
                $actions['wpuf-pending-user']   = $pending_user;
                break;
            case 'pending' :
                $actions['wpuf-approve-user']   = $approve_user;
                $actions['wpuf-deny-user']      = $deny_user;
                break;
            case 'denied' :
                $actions['wpuf-approve-user']   = $approve_user;
                $actions['wpuf-pending-user']   = $pending_user;
                break;
            default:
        }

        return $actions;
    }

    /**
     * Get the status of a user.
     *
     * @param int $user_id
     *
     * @return string the status of the user
     */
    public function get_user_status( $user_id ) {
        $user_status = get_user_meta( $user_id, 'wpuf_user_status', true );

        if ( empty( $user_status ) ) {
            $user_status = 'approved';
        }

        return $user_status;
    }


    /**
     * The default message that is shown to a user depending on their status when trying to sign in.
     *
     * @return string
     */
    public function get_authentication_message( $status ) {
        $message = '';

        $pending_user_message = wpuf_get_option( 'pending_user_message', 'wpuf_profile' );
        $denied_user_message = wpuf_get_option( 'denied_user_message', 'wpuf_profile' );

        if ( $status == 'pending' ) {
            $message = $pending_user_message;
        } else if ( $status == 'denied' ) {
            $message = $denied_user_message;
        }

        return $message;
    }

    /**
     * Generate error for login form based on user status
     *
     * @return string
     */
    public function validate_login ($user, $password) {
        $status = $this->get_user_status( $user->ID );

        switch ($status) {
            case 'pending' :
                $pending_message = $this->get_authentication_message( 'pending' );
                $user = new WP_Error( 'wpuf_pending_user_error', $pending_message );
                break;
            case 'denied' :
                $denied_message = $this->get_authentication_message( 'denied' );
                $user = new WP_Error( 'wpuf_denied_user_error', $denied_message );
                break;
            default:
        }

        return $user;
    }

    /**
     * Add a filter to the user table to filter by user status
     *
     * @uses restrict_manage_users
     */
    public function status_filter( $which ) {
        $id              = 'wpuf_user_approve_filter-' . $which;

        $filter_button   = submit_button( __( 'Filter Users by Status', 'wpuf-user-approve' ), 'button', 'wpuf-status-query-submit', false, array( 'id' => 'wpuf-status-query-submit' ) );
        $filtered_status = $this->selected_status();
        ?>

        <label class="screen-reader-text" for="<?php echo $id ?>"><?php _e( 'View all users', 'wpuf-user-approve' ); ?></label>
        <select id="<?php echo $id ?>" name="<?php echo $id ?>" style="float: none; margin: 0 0 0 15px;">
            <option value=""><?php _e( 'View all users', 'new-user-approve' ); ?></option>
            <?php foreach ( $this->get_valid_statuses() as $status ) : ?>
                <option value="<?php echo esc_attr( $status ); ?>"<?php selected( $status, $filtered_status ); ?>><?php echo esc_html( ucfirst( $status ) ); ?></option>
            <?php endforeach; ?>
        </select>

        <style>
            #wpuf-status-query-submit {
                float: right;
                margin: 0 0 0 5px;
            }
        </style>
    <?php
    }

    /**
     * Modify the user query if the status filter is being used.
     *
     * @uses pre_user_query
     * @param $query
     */
    public function filter_by_status( $query ) {
        global $wpdb;

        if ( !is_admin() && !did_action( 'admin_init' ) ) {
            return;
        }

        $screen = get_current_screen();
        if ( isset( $screen ) && 'users' != $screen->id ) {
            return;
        }

        if ( $this->selected_status() != null ) {
            $filter = $this->selected_status();

            $query->query_from .= " INNER JOIN {$wpdb->usermeta} ON ( {$wpdb->users}.ID = $wpdb->usermeta.user_id )";

            if ( 'approved' == $filter ) {
                $query->query_fields = "DISTINCT SQL_CALC_FOUND_ROWS {$wpdb->users}.ID";
                $query->query_from  .= " LEFT JOIN {$wpdb->usermeta} AS mt1 ON ({$wpdb->users}.ID = mt1.user_id AND mt1.meta_key = 'wpuf_user_status')";
                $query->query_where .= " AND ( ( $wpdb->usermeta.meta_key = 'wpuf_user_status' AND CAST($wpdb->usermeta.meta_value AS CHAR) = 'approved' ) OR mt1.user_id IS NULL )";
            } else {
                $query->query_where .= " AND ( ($wpdb->usermeta.meta_key = 'wpuf_user_status' AND CAST($wpdb->usermeta.meta_value AS CHAR) = '{$filter}') )";
            }
        }
    }

    /**
     * Selected Status
     *
     */
    public function selected_status() {
        if ( ! empty( $_REQUEST['wpuf_user_approve_filter-top'] ) || ! empty( $_REQUEST['wpuf_user_approve_filter-bottom'] ) ) {
            return esc_attr( ( ! empty( $_REQUEST['wpuf_user_approve_filter-top'] ) ) ? $_REQUEST['wpuf_user_approve_filter-top'] : $_REQUEST['wpuf_user_approve_filter-bottom'] );
        }

        return null;
    }

    /**
     * Use javascript to add the ability to bulk modify the status of users.
     *
     * @uses admin_footer-users.php
     */
    public function admin_footer() {
        $screen = get_current_screen();

        if ( $screen->id == 'users' ) : ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('<option>').val('approve').text('<?php _e( 'Approve', 'wpuf-pro' )?>').appendTo("select[name='action']");
                    $('<option>').val('approve').text('<?php _e( 'Approve', 'wpuf-pro' )?>').appendTo("select[name='action2']");

                    $('<option>').val('pending').text('<?php _e( 'Pending', 'wpuf-pro' )?>').appendTo("select[name='action']");
                    $('<option>').val('pending').text('<?php _e( 'Pending', 'wpuf-pro' )?>').appendTo("select[name='action2']");

                    $('<option>').val('deny').text('<?php _e( 'Deny', 'wpuf-pro' )?>').appendTo("select[name='action']");
                    $('<option>').val('deny').text('<?php _e( 'Deny', 'wpuf-pro' )?>').appendTo("select[name='action2']");
                });
            </script>
        <?php endif;
    }

    /**
     * Process the bulk status updates
     *
     * @uses load-users.php
     */
    public function bulk_action() {
        $screen = get_current_screen();

        if ( $screen->id == 'users' ) {

            // get the action
            $wp_list_table = _get_list_table( 'WP_Users_List_Table' );
            $action = $wp_list_table->current_action();

            $allowed_actions = array( 'approve', 'pending', 'deny' );
            if ( !in_array( $action, $allowed_actions ) ) {
                return;
            }

            // security check
            check_admin_referer( 'bulk-users' );

            // make sure ids are submitted
            if ( isset( $_REQUEST['users'] ) ) {
                $user_ids = array_map( 'intval', $_REQUEST['users'] );
            }

            if ( empty( $user_ids ) ) {
                return;
            }

            $sendback = remove_query_arg( array( 'approved', 'pending', 'denied', 'deleted', 'ids', 'new_user_approve_filter', 'new_user_approve_filter2', 'pw-status-query-submit', 'new_role' ), wp_get_referer() );
            if ( !$sendback ) {
                $sendback = admin_url( 'users.php' );
            }

            $pagenum  = $wp_list_table->get_pagenum();
            $sendback = add_query_arg( 'paged', $pagenum, $sendback );
            $status   = '';

            switch ( $action ) {
                case 'approve':
                    $approved = 0;
                    foreach ( $user_ids as $user_id ) {
                        if ( update_user_meta( $user_id, 'wpuf_user_status', 'approved' ) ) {
                            WPUF_Frontend_Form_Profile::user_email_notification( 'approved', $user_id );
                            $approved++;
                        }
                    }

                    $sendback = add_query_arg( array( 'approved' => $approved, 'ids' => join( ',', $user_ids ) ), $sendback );
                    break;

                case 'pending':
                    $pending = 0;
                    foreach ( $user_ids as $user_id ) {
                        if ( update_user_meta( $user_id, 'wpuf_user_status', 'pending' ) ) {
                            WPUF_Frontend_Form_Profile::user_email_notification( 'pending', $user_id );
                            $pending++;
                        }
                    }

                    $sendback = add_query_arg( array( 'pending' => $pending, 'ids' => join( ',', $user_ids ) ), $sendback );
                    break;

                case 'deny':
                    $denied = 0;
                    foreach ( $user_ids as $user_id ) {
                        if ( update_user_meta( $user_id, 'wpuf_user_status', 'denied' ) ) {
                            WPUF_Frontend_Form_Profile::user_email_notification( 'denied', $user_id );
                            $denied++;
                        }
                    }

                    $sendback = add_query_arg( array( 'denied' => $denied, 'ids' => join( ',', $user_ids ) ), $sendback );
                    break;

                default:
                    return;
            }

            $sendback = remove_query_arg( array( 'action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view' ), $sendback );

            wp_redirect( $sendback );
            exit();
        }
    }

    public function add_global_settings( $settings_fields ) {

        $settings_fields['wpuf_mails'][] = array(
            'name'    => 'new_user_admin_email',
            'label'   => __( '<span class="dashicons dashicons-admin-users"></span> Admin Email', 'wpuf-pro' ),
            'type'    => 'html',
            'class'   => 'admin-new-user-email',
        );

        $settings_fields['wpuf_mails'][] = array(
            'name'     => 'new_user_email_subject_admin',
            'label'    => __( 'New User Email Subject for Admin', 'wpuf-pro' ),
            'desc'     => __( 'This sets the subject of the emails sent to site administrator.', 'wpuf-pro' ),
            'default'  => 'New user registered on your site',
            'type'     => 'text',
            'class'    => 'admin-new-user-email-option'
        );

        $settings_fields['wpuf_mails'][] = array(
            'name'     => 'new_user_email_body_admin',
            'label'    => __( 'New User Email Body for Admin', 'wpuf-pro' ),
            'desc'     => __( 'This sets the body of the emails sent to site administrator. <br><strong>You may use: </strong><code>%username%</code><code>%user_email%</code><code>%display_name%</code><br><code>%user_status%</code><code>%pending_users%</code><code>%approved_users%</code><br><code>%denied_users%</code>', 'wpuf-pro' ),
            'default'  => 'Username: %username% (%user_email%) has requested a username. \r\n\r\nTo approve or deny this user access go to \r\n\r\n%pending_users%',
            'type'     => 'wysiwyg',
            'class'    => 'admin-new-user-email-option'
        );

        $settings_fields['wpuf_mails'][] = array(
            'name'    => 'pending_user_email',
            'label'   => __( '<span class="dashicons dashicons-groups"></span> Pending User Email', 'wpuf-pro' ),
            'type'    => 'html',
            'class'   => 'pending-user-email',
        );

        $settings_fields['wpuf_mails'][] = array(
            'name'     => 'pending_user_email_subject',
            'label'    => __( 'Pending Email Subject for User', 'wpuf-pro' ),
            'desc'     => __( 'This sets the subject of the emails sent to newly registered user.', 'wpuf-pro' ),
            'default'  => 'Thank you for registering',
            'type'     => 'text',
            'class'   => 'pending-user-email-option',
        );

        $settings_fields['wpuf_mails'][] = array(
            'name'     => 'pending_user_email_body',
            'label'    => __( 'Pending Email Body for User', 'wpuf-pro' ),
            'desc'     => __( 'This sets the body of the emails sent to newly registered user. <br><strong>You may use: </strong><code>%username%</code><code>%user_email%</code><code>%display_name%</code><br><code>%user_status%</code><code>%pending_users%</code><code>%approved_users%</code><br><code>%denied_users%</code>', 'wpuf-pro' ),
            'default'  => 'Hi %username%, \r\n\r\n\r\n\r\nAn email has been sent to the site administrator. The administrator will review the information that has been submitted and either approve or deny your request. You will receive an email with instructions on what you will need to do next. \r\n\r\nThanks for your patience.',
            'type'     => 'wysiwyg',
            'class'   => 'pending-user-email-option',
        );

        $settings_fields['wpuf_mails'][] = array(
            'name'    => 'denied_user_email',
            'label'   => __( '<span class="dashicons dashicons-dismiss"></span> Denied User Email', 'wpuf-pro' ),
            'type'    => 'html',
            'class'   => 'denied-user-email',
        );

        $settings_fields['wpuf_mails'][] = array(
            'name'     => 'denied_user_email_subject',
            'label'    => __( 'Denied Email Subject for User', 'wpuf-pro' ),
            'desc'     => __( 'This sets the subject of the emails sent to denied user.', 'wpuf-pro' ),
            'default'  => '',
            'type'     => 'text',
            'class'    => 'denied-user-email-option'
        );

        $settings_fields['wpuf_mails'][] = array(
            'name'     => 'denied_user_email_body',
            'label'    => __( 'Denied Email Body for User', 'wpuf-pro' ),
            'desc'     => __( 'This sets the body of the emails sent to denied user. <br><strong>You may use: </strong><code>%username%</code><code>%user_email%</code><code>%display_name%</code><br><code>%user_status%</code><code>%pending_users%</code><code>%approved_users%</code><br><code>%denied_users%</code>', 'wpuf-pro' ),
            'default'  => '',
            'type'     => 'wysiwyg',
            'class'    => 'denied-user-email-option'
        );

        $settings_fields['wpuf_mails'][] = array(
            'name'    => 'approved_user_email',
            'label'   => __( '<span class="dashicons dashicons-smiley"></span> Approved User Email', 'wpuf-pro' ),
            'type'    => 'html',
            'class'   => 'approved-user-email',
        );

        $settings_fields['wpuf_mails'][] = array(
            'name'     => 'approved_user_email_subject',
            'label'    => __( 'Approved Email Subject for User', 'wpuf-pro' ),
            'desc'     => __( 'This sets the subject of the emails sent to approved user.', 'wpuf-pro' ),
            'default'  => '',
            'type'     => 'text',
            'class'   => 'approved-user-email-option',
        );

        $settings_fields['wpuf_mails'][] = array(
            'name'     => 'approved_user_email_body',
            'label'    => __( 'Approved Email Body for User', 'wpuf-pro' ),
            'desc'     => __( 'This sets the body of the emails sent to approved user. <br><strong>You may use: </strong><code>%username%</code><code>%user_email%</code><code>%display_name%</code><br><code>%user_status%</code><code>%pending_users%</code><code>%approved_users%</code><br><code>%denied_users%</code>', 'wpuf-pro' ),
            'default'  => '',
            'type'     => 'wysiwyg',
            'class'   => 'approved-user-email-option',
        );

        $settings_fields['wpuf_profile'][] = array(
            'name'     => 'pending_user_message',
            'label'    => __( 'Pending User Message', 'wpuf-pro' ),
            'desc'     => __( 'Pending user will see this message when try to log in.', 'wpuf-pro' ),
            'default'  => '<strong>ERROR:</strong> Your account has to be approved by an administrator before you can login.',
            'type'     => 'textarea',
        );

        $settings_fields['wpuf_profile'][] = array(
            'name'     => 'denied_user_message',
            'label'    => __( 'Denied User Message', 'wpuf-pro' ),
            'desc'     => __( 'Denied user will see this message when try to log in.', 'wpuf-pro' ),
            'default'  => '<strong>ERROR:</strong> Your account has been denied by an administrator, please contact admin to approve your account.',
            'type'     => 'textarea',
        );

        return $settings_fields;
    }

}
