<?php

/**
 * Menu restriction class
 * 
 * @since 2.6
 */
class WPUF_Menu_Restriction {

    public function __construct() {
        add_filter( 'wp_setup_nav_menu_item', array( __CLASS__, 'merge_item_data' ) );
        add_filter( 'wp_edit_nav_menu_walker', array( __CLASS__, 'nav_menu_walker' ), 999999999 );
        add_action( 'admin_head-nav-menus.php', array( __CLASS__, 'register_metaboxes' ) );
        add_filter( 'wp_get_nav_menu_items', array( __CLASS__, 'exclude_menu_items' ) );
        add_action( 'wp_nav_menu_item_custom_fields', array( __CLASS__, 'fields' ), 10, 4 );
        add_action( 'wp_update_nav_menu_item', array( __CLASS__, 'save' ), 10, 2 );
    }

    /**
     * @param int $item_id
     *
     * @return array
     */
    public static function get_options( $item_id = 0 ) {
        $item_options = get_post_meta( $item_id, '_wpuf_nav_item_options', true );

        return static::parse_options( $item_options );
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public static function parse_options( $options = array() ) {
        if ( ! is_array( $options ) ) {
            $options = array();
        }

        if ( isset( $options['which_users'] ) && isset( $options['roles'] ) ) {
            $options['wpuf_which_users'] = $options['which_users'];
            $options['wpuf_roles'] = $options['roles'];
        }

        return wp_parse_args( $options, array(
            'wpuf_which_users'   => '',
            'wpuf_roles'         => array(),
        ) );
    }


    private static $current_item;

    /**
     * Merge Item data into the $item object.
     *
     * @param $item
     *
     * @return mixed
     */
    public static function merge_item_data( $item ) {
        self::$current_item = self::get_options();

        // Merge Rules.
        foreach ( self::get_options( $item->ID ) as $key => $value ) {
            $item->$key = $value;
        }

        // User text replacement.
        if ( ! is_admin() ) {
            $item->title = static::user_titles( $item->title );
        }

        return $item;
    }

    /**
     * @param string $title
     *
     * @return mixed|string
     */
    public static function user_titles( $title = '' ) {
        preg_match_all( '/{(.*?)}/', $title, $found );

        if ( count( $found[1] ) ) {

            foreach ( $found[1] as $key => $match ) {

                $title = static::text_replace( $title, $match );

            }
        }

        return $title;
    }

    public static function text_replace( $title = '', $match = '' ) {

        if ( empty( $match ) ) {
            return $title;
        }

        if ( strpos( $match, '||' ) !== false ) {
            $matches = explode( '||', $match );
        } else {
            $matches = array( $match );
        }

        $current_user = wp_get_current_user();

        $replace = '';

        foreach ( $matches as $string ) {

            if ( $current_user->ID == 0 || ! array_key_exists( $string, self::valid_codes() ) ) {

                $replace = '';

            } else {

                switch ( $string ) {

                    case 'avatar':
                        $replace = get_avatar( $current_user, self::$current_item->avatar_size );
                        break;

                    case 'first_name':
                        $replace = $current_user->user_firstname;
                        break;

                    case 'last_name':
                        $replace = $current_user->user_lastname;
                        break;

                    case 'username':
                        $replace = $current_user->user_login;
                        break;

                    case 'display_name':
                        $replace = $current_user->display_name;
                        break;

                    case 'nickname':
                        $replace = $current_user->nickname;
                        break;

                    case 'email':
                        $replace = $current_user->user_email;
                        break;

                    default:
                        $replace = $string;
                        break;

                }

            }

            // If we found a replacement stop the loop.
            if ( ! empty( $replace ) ) {
                break;
            }

        }

        return str_replace( '{' . $match . '}', $replace, $title );
    }

    public static function valid_codes() {
        return array(
            'avatar'       => __( 'Avatar', 'user-menus' ),
            'first_name'   => __( 'First Name', 'user-menus' ),
            'last_name'    => __( 'Last Name', 'user-menus' ),
            'username'     => __( 'Username', 'user-menus' ),
            'display_name' => __( 'Display Name', 'user-menus' ),
            'nickname'     => __( 'Nickname', 'user-menus' ),
            'email'        => __( 'Email', 'user-menus' ),
        );
    }


    /**
     * Override the Admin Menu Walker
     */
    public static function nav_menu_walker( $walker ) {
        global $wp_version;

        if ( doing_filter( 'plugins_loaded' ) ) {
            return $walker;
        }

        if ( $walker == 'Walker_Nav_Menu_Edit_Custom_Fields' ) {
            return $walker;
        }

        if ( ! class_exists( 'Walker_Nav_Menu_Edit_Custom_Fields' ) ) {
            if ( version_compare( $wp_version, '3.6', '>=' ) ) {
                require_once WPUF_PRO_INCLUDES . '/libs/wpuf-nav-walker/wpuf-nav-menu-edit-custom-fields.php';
            } else {
                require_once WPUF_PRO_INCLUDES . '/libs/wpuf-nav-walker/wpuf-nav-menu-edit-custom-fields-deprecated.php';
            }
        }

        return 'Walker_Nav_Menu_Edit_Custom_Fields';
    }

    public static function register_metaboxes() {
        add_meta_box( 'wpuf_user_menus', __( 'User Links', 'wpuf-pro' ), array( __CLASS__, 'nav_menu_metabox', ), 'nav-menus', 'side', 'default' );
    }

    /**
     * @param $object
     */
    public static function nav_menu_metabox( $object ) {
        global $_nav_menu_placeholder, $nav_menu_selected_id;

        $link_types = array(
            array(
                'object' => 'login',
                'title'  => __( 'Login', 'wpuf-pro' ),
            ),
            array(
                'object' => 'logout',
                'title'  => __( 'Logout', 'wpuf-pro' ),
            ),
        );

        foreach ( $link_types as $key => $link ) {

            $i = isset( $i ) ? $i + 1 : 1;

            $link_types[ $key ] = (object) array_replace_recursive( array(
                'type'             => '',
                'object'           => '',
                'title'            => '',
                'ID'               => $i,
                'object_id'        => $i,
                'db_id'            => 0,
                'post_parent'      => 0,
                'menu_item_parent' => 0,
                'url'              => '',
                'target'           => '',
                'attr_title'       => '',
                'description'      => '',
                'classes'          => array(),
                'xfn'              => '',
            ), $link );

        }

        $walker = new Walker_Nav_Menu_Checklist();

        $removed_args = array(
            'action',
            'customlink-tab',
            'edit-menu-item',
            'menu-item',
            'page-tab',
            '_wpnonce',
        );

        ?>

        <div id="user-menus-div" class="user-menus">
            <div id="tabs-panel-user-menus-all" class="tabs-panel tabs-panel-active">
                <ul id="user-menus-checklist-all" class="categorychecklist form-no-clear">
                    <?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $link_types ), 0, (object) array( 'walker' => $walker ) ); ?>
                </ul>

                <p class="button-controls">
                    <span class="list-controls">
                        <a href="<?php
                        echo esc_url( add_query_arg( array(
                            'user-menus-all' => 'all',
                            'selectall'      => 1,
                        ), remove_query_arg( $removed_args ) ) );
                        ?>#user-menus-div" class="select-all"><?php _e( 'Select All' ); ?></a>
                    </span>

                    <span class="add-to-menu">
                        <input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-user-menus-menu-item" id="submit-user-menus-div" />
                        <span class="spinner"></span>
                    </span>
                </p>
            </div>
        </div>

        <?php

    }

    /**
     * Exclude menu items via wp_get_nav_menu_items filter.
     *
     * Guarantees compatibility with nearly any theme.
     */
    public static function exclude_menu_items( $items = array() ) {

        if ( empty( $items ) ) {
            return $items;
        }

        $logged_in = is_user_logged_in();

        $excluded = array();

        foreach ( $items as $key => $item ) {

            $exclude = in_array( $item->menu_item_parent, $excluded );

            if ( $item->object == 'logout' ) {
                $exclude = ! $logged_in;
            } elseif ( $item->object == 'login' ) {
                $exclude = $logged_in;
            } else {
                switch ( $item->wpuf_which_users ) {
                    case 'logged_in':
                        if ( ! $logged_in ) {
                            $exclude = true;
                        } elseif ( ! empty( $item->wpuf_roles && ! is_admin() ) ) {

                            // Checks all roles, should not exclude if any are active.
                            $valid_role = false;

                            foreach ( $item->wpuf_roles as $role ) {
                                if ( current_user_can( $role ) ) {
                                    $valid_role = true;
                                    break;
                                }
                            }

                            if ( ! $valid_role ) {
                                $exclude = true;
                            }
                        }
                        break;

                    case 'logged_out':
                        if ( ! is_admin() ) {
                            $exclude = $logged_in;
                            break;
                        }  

                    case 'sub_packs':
                        $vis_pack_meta = get_post_meta( $item->ID, '_menu_item_visibility', false );
                        if ( !empty($vis_pack_meta) && !is_admin()) {
                            extract( $vis_pack_meta );

                            $sub_pack = WPUF_Subscription::get_user_pack( get_current_user_id() );

                            if ( !$sub_pack ) {
                                $exclude = true;
                            }

                            if ( ! self::is_valid_subscription( $sub_pack ) ) {
                                $exclude = true;
                            }

                            $pack_id = is_array( $sub_pack ) ? intval( $sub_pack['pack_id'] ) : 0;

                            foreach ($vis_pack_meta as $vis_pack) {
                                if ( ! in_array( $pack_id, $vis_pack ) ) {
                                    $exclude = true;
                                    break;
                                }
                            } 
                        } else {
                            $exclude = false;
                        }
                        break;
                }
            }

            $exclude = apply_filters( 'wpuf_should_exclude_item', $exclude, $item );

            // unset non-visible item
            if ( $exclude ) {
                $excluded[] = $item->ID; // store ID of item
                unset( $items[ $key ] );
            }
        }

        return $items;
    }

    /**
     * @param $item_id
     * @param $item
     * @param $depth
     * @param $args
     */
    public static function fields( $item_id, $item, $depth, $args ) {

        $allowed_user_roles     = static::allowed_user_roles();

        wp_nonce_field( 'wpuf-menu-editor-nonce', 'wpuf-menu-editor-nonce' ); ?>

        <script type="text/javascript">
            (function ($, $document) {
    
                function wpuf_which_users() {
                    var $this = $(this),
                    $item = $this.parents('.menu-item'),
                    $roles = $item.find('.nav_item_options-wpuf_roles'),
                    $subs = $item.find('.nav_item_options-sub-packs');

                    if ($this.val() === 'logged_in') {
                        $roles.slideDown();
                        $subs.slideUp();
                        $item.addClass('show-insert-button');
                        $item.removeClass('.nav_item_options-sub-packs');
  
                    } else if ($this.val() === 'sub_packs') {
                        $roles.slideUp();
                        $subs.slideDown();
                        $item.removeClass('.nav_item_options-wpuf_roles');
                    } else {
                        $roles.slideUp();
                        $subs.slideUp();
                        $item.removeClass('.nav_item_options-sub-packs');
                        $item.removeClass('.nav_item_options-wpuf_roles');
                    }
                }

                function refresh_all_items() {
                    $('.nav_item_options-wpuf_which_users select').each( wpuf_which_users );
                }

                $document
                .on( 'change', '.nav_item_options-wpuf_which_users select', wpuf_which_users )
                .ready( refresh_all_items );

                // Add click event directly to submit buttons to prevent being prevented by default action.
                $('.submit-add-to-menu').click( function () {
                    setTimeout( refresh_all_items, 1000 );
                    $this.removeClass('.nav_item_options-wpuf_roles');
                    $this.removeClass('.nav_item_options-sub-packs');

                });

            }(jQuery, jQuery(document)));
        </script>

        <?php
            $which_users_options = array(
                ''           => __( 'Everyone', 'wpuf-pro' ),
                'logged_out' => __( 'Logged Out Users', 'wpuf-pro' ),
                'logged_in'  => __( 'Logged In Users', 'wpuf-pro' ),
                'sub_packs'  => __( 'Subscription Packs', 'wpuf-pro' ),
            ); ?>

            <p class="nav_item_options-wpuf_which_users  description  description-wide">

                <label for="wpuf_nav_item_options-wpuf_which_users-<?php echo $item->ID; ?>">

                    <?php _e( 'Who can see this link?', 'wpuf-pro' ); ?><br />

                    <select name="wpuf_nav_item_options[<?php echo $item->ID; ?>][wpuf_which_users]" id="wpuf_nav_item_options-wpuf_which_users-<?php echo $item->ID; ?>" class="widefat">
                        <?php foreach ( $which_users_options as $option => $label ) : ?>
                            <option value="<?php echo $option; ?>" <?php selected( $option, $item->wpuf_which_users ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                </label>

            </p>

            <p class="nav_item_options-wpuf_roles  description  description-wide">

                <?php _e( 'Choose which roles can see this link', 'wpuf-pro' ); ?><br />

                <?php foreach ( $allowed_user_roles as $option => $label ) : ?>
                    <label>
                        <input type="checkbox" name="wpuf_nav_item_options[<?php echo $item->ID; ?>][wpuf_roles][]" value="<?php echo $option; ?>" <?php checked( in_array( $option, $item->wpuf_roles ), true ); ?>/>
                        <?php echo esc_html( $label ); ?>
                    </label>
                <?php endforeach; ?>

            </p>

            <p class="nav_item_options-sub-packs  description  description-wide">

                <?php _e( 'Choose which subscription pack users can see this link', 'wpuf-pro' ); ?><br />
                <?php
                $all_packs      = self::all_sub_packs();
                $subscriptions  = WPUF_Subscription::init()->get_subscriptions();
                if ( $subscriptions ) {
                    $pack_count = count( $all_packs['id']);
                }
                $allowed_packs  = array();
                if ( get_post_meta( $item->ID, '_menu_item_visibility', false ) ) {
                    $allowed_packs  = get_post_meta( $item->ID, '_menu_item_visibility', false );
                    $allowed_packs  = $allowed_packs[0];
                }
                $i = 0;
                foreach ( $subscriptions as $pack ) { 
                    if ( $i < $pack_count ) {
                        ?>
                        <label>
                            <input type="hidden" name="wpuf_nav_item_options[<?php echo $item->ID; ?>][pack][]" value="false" />
                            <input type="checkbox" name="wpuf_nav_item_options[<?php echo $item->ID; ?>][pack][]" value="<?php echo $pack->ID; ?>" <?php checked( in_array( $pack->ID, $allowed_packs ) ); ?> />
                            <?php echo esc_html( $pack->post_title ); ?>
                        </label>
                        <?php 
                        $i+=1;
                    } 
                } ?>
            </p>

        <?php
    }

    /**
     * @return array|mixed|void
     */
    public static function allowed_user_roles() {
        global $wp_roles;

        static $roles;

        if ( ! isset( $roles ) ) {
            $roles = apply_filters( 'wpuf_user_roles', $wp_roles->role_names );

            if ( ! is_array( $roles ) || empty( $roles ) ) {
                $roles = array();
            }
        }

        return $roles;
    }

    /**
     * @return array|mixed|void
     */
    public static function all_sub_packs() {

        static $sub_packs = array();
        $subscriptions = WPUF_Subscription::init()->get_subscriptions();
        if ( $subscriptions ) {
            foreach ($subscriptions as $pack) {
                $sub_packs['id'][] = $pack->ID;
                $sub_packs['name'][] = $pack->post_title;
            }
        }

        return $sub_packs;
    }

    /**
     * @param $menu_id
     * @param $item_id
     */
    public static function save( $menu_id, $item_id ) {

        $allowed_roles = static::allowed_user_roles();
        $all_packs     = static::all_sub_packs();


        if ( empty( $_POST['wpuf_nav_item_options'][ $item_id ] ) || ! isset( $_POST['wpuf-menu-editor-nonce'] ) || ! wp_verify_nonce( $_POST['wpuf-menu-editor-nonce'], 'wpuf-menu-editor-nonce' ) ) {
            return;
        }

        $item_options = static::parse_options( $_POST['wpuf_nav_item_options'][ $item_id ] );

        if ( $item_options['wpuf_which_users'] == 'logged_in' ) {
            // Validate chosen roles and remove non-allowed roles.
            foreach ( (array) $item_options['wpuf_roles'] as $key => $role ) {
                if ( ! array_key_exists( $role, $allowed_roles ) ) {
                    unset( $item_options['wpuf_roles'][ $key ] );
                }
            }
        } elseif ( isset( $_POST['wpuf_nav_item_options'][$item_id]['pack'] ) ) {
            if ( $_POST['wpuf_nav_item_options'][$item_id]['pack'] !== 'false' ) {
                foreach ( (array) $_POST['wpuf_nav_item_options'][$item_id]['pack'] as $sub_pack ) {
                    $checked_packs[] = $sub_pack;
                }

                if ( !empty( $checked_packs ) ) {
                    $checked_packs = array_unique( $checked_packs );
                    update_post_meta( $item_id, '_menu_item_visibility', $checked_packs );
                }
            } else {
                delete_post_meta( $item_id, '_menu_item_visibility');
            }
        } else {
            unset( $item_options['wpuf_roles'] );
            unset( $item_options['sub_packs'] );
            delete_post_meta( $item_id, '_menu_item_visibility');
        }

        // Remove empty options to save space.
        $item_options = array_filter( $item_options );

        if ( ! empty( $item_options ) ) {
            update_post_meta( $item_id, '_wpuf_nav_item_options', $item_options );
        } else {
            delete_post_meta( $item_id, '_wpuf_nav_item_options' );
        }
    }

    /**
     * Check if the subscription is valid
     *
     * @param  array  $package
     *
     * @return boolean
     */
    public static function is_valid_subscription( $package ) {
        $pack_id = is_array( $package ) ? intval( $package['pack_id'] ) : 0;

        if ( !$pack_id ) {
            return false;
        }

        // check expiration
        $expire = isset( $package['expire'] ) ? $package['expire'] : 0;

        if ( strtolower( $expire ) == 'unlimited' || empty( $expire ) ) {
            $has_expired = false;
        } else if ( ( strtotime( date( 'Y-m-d', strtotime( $expire ) ) ) >= strtotime( date( 'Y-m-d', time() ) ) ) ) {
            $has_expired = false;
        } else {
            $has_expired = true;
        }

        if ( $has_expired ) {
            return false;
        }

        return true;
    }

}
