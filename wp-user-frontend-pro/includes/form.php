<?php
/**
 * Pro features for wpuf_forms builder
 *
 * @since 2.5
 */
class WPUF_Admin_Form_Pro {

    /**
     * Class constructor
     *
     * @since 2.5
     *
     * @return void
     */
    public function __construct() {
        add_action( 'wpuf-form-builder-init-type-wpuf_forms', array( $this, 'init_pro' ) );
    }

    /**
     * Initialize the framework
     *
     * @since 2.5
     *
     * @return void
     */
    public function init_pro() {
        add_filter( 'wpuf-get-post-types', array( $this, 'add_custom_post_types' ) );

        require_once WPUF_PRO_ROOT . '/admin/form-builder/class-wpuf-form-builder-pro.php';
        new WPUF_Admin_Form_Builder_Pro();
    }

    /**
     * Filter to add custom post types to wpuf_get_post_types
     *
     * @since 2.5
     *
     * @param array $post_types
     *
     * @return array
     */
    public function add_custom_post_types( $post_types ) {
        $args = array( '_builtin' => false );

        $custom_post_types = get_post_types( $args );

        $ignore_post_types = array(
            'wpuf_forms', 'wpuf_profile', 'wpuf_input'
        );

        foreach ( $custom_post_types as $key => $val ) {
            if ( in_array( $val, $ignore_post_types ) ) {
                unset( $custom_post_types[$key] );
            }
        }

        return array_merge( $post_types, $custom_post_types );
    }
}
