<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * WPML Compatibility Class
 *
 * @class   YITH_WCBEP_Badge_Management_Compatibility
 * @package Yithemes
 * @since   1.1.2
 * @author  Yithemes
 *
 */
class YITH_WCBEP_Badge_Management_Compatibility {

    /**
     * Single instance of the class
     *
     * @var \YITH_WCBEP_Badge_Management_Compatibility
     */
    protected static $_instance;

    /**
     * @var array
     */
    public $badge_array;

    /**
     * Returns single instance of the class
     *
     * @return \YITH_WCBEP_Badge_Management_Compatibility
     */
    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Constructor
     */
    protected function __construct() {
        add_filter( 'yith_wcbep_default_columns', array( $this, 'add_badge_column' ) );
        add_filter( 'yith_wcbep_manage_custom_columns', array( $this, 'manage_badge_column' ), 10, 3 );
        add_filter( 'yith_wcbep_variation_not_editable_and_empty', array( $this, 'edit_not_editable_and_empty_in_variations' ) );
        add_filter( 'yith_wcbep_td_extra_class_select', array( $this, 'add_extra_class_select_in_js' ) );

        add_action( 'yith_wcbep_update_product', array( $this, 'save_badge_meta' ), 10, 4 );
        add_action( 'yith_wcbep_extra_general_bulk_fields', array( $this, 'add_extra_bulk_fields' ) );
        add_filter( 'yith_wcbep_extra_bulk_columns_select', array( $this, 'add_extra_bulk_columns_select' ) );
    }

    /**
     * @param $columns
     *
     * @return mixed
     */
    public function add_badge_column( $columns ) {
        $columns[ 'yith_wcbm_badge' ] = __( 'Badge', 'yith-wcmb' );

        return $columns;
    }

    /**
     * @param $value
     * @param $column_name
     * @param $post
     */
    public function manage_badge_column( $value, $column_name, $post ) {
        if ( $column_name == 'yith_wcbm_badge' ) {
            $badge_array = $this->get_badge_array();
            $bm_meta     = get_post_meta( $post->ID, '_yith_wcbm_product_meta', true );
            $id_badge    = ( isset( $bm_meta[ 'id_badge' ] ) ) ? $bm_meta[ 'id_badge' ] : '';

            $value = '<select class="yith-wcbep-editable-select"><option value="none"> ' . __( 'None', 'yith-wcbm' ) . '</option>';

            foreach ( $badge_array as $id => $title ) {
                $value .= '<option value="' . $id . '" ' . ( ( $id_badge == $id ) ? 'selected' : '' ) . '>' . $title . '</option>';
            }

            $value .= '</select>';
            $value .= '<input type="hidden" class="yith-wcbep-hidden-select-value" value="' . $id_badge . '"/>';
        }

        return $value;
    }

    /**
     * @return array
     */
    public function get_badge_array() {
        if ( isset( $this->badge_array ) )
            return $this->badge_array;

        $this->badge_array = array();

        $args   = array(
            'posts_per_page' => -1,
            'post_type'      => 'yith-wcbm-badge',
            'orderby'        => 'title',
            'order'          => 'ASC',
            'post_status'    => 'publish',
        );
        $badges = get_posts( $args );

        if ( $badges ) {
            foreach ( $badges as $badge ) {
                $this->badge_array[ $badge->ID ] = $badge->post_title;
            }
        }

        return $this->badge_array;
    }

    /**
     * @param $values
     *
     * @return array
     */
    public function edit_not_editable_and_empty_in_variations( $values ) {
        $values[] = 'yith_wcbm_badge';

        return $values;
    }

    /**
     * @param $values
     *
     * @return array
     */
    public function add_extra_bulk_columns_select( $values ) {
        $values[] = 'yith_wcbm_badge';

        return $values;
    }

    /**
     * @param $extra_classes
     *
     * @return array
     */
    public function add_extra_class_select_in_js( $extra_classes ) {
        $extra_classes[] = 'td.yith_wcbm_badge';

        return $extra_classes;
    }

    /**
     * @param $product
     * @param $matrix_keys
     * @param $single_modify
     */
    public function save_badge_meta( $product, $matrix_keys, $single_modify, $is_variation ) {
        $badge_index = array_search( 'yith_wcbm_badge', $matrix_keys );
        if ( !empty( $single_modify[ $badge_index ] ) ) {
            if ( !$is_variation ) {
                $new_badge                  = $single_modify[ $badge_index ] != 'none' ? $single_modify[ $badge_index ] : '';
                $product_meta               = yit_get_prop( $product, '_yith_wcbm_product_meta', true );
                $product_meta[ 'id_badge' ] = $new_badge;

                yit_save_prop( $product, '_yith_wcbm_product_meta', $product_meta );
            }
        }

    }

    public function add_extra_bulk_fields() {
        ?>
        <tr>
            <td class="yith-wcbep-bulk-form-label-col">
                <label><?php _e( 'Badge', 'yith-woocommerce-bulk-product-editing' ) ?></label>
            </td>
            <td class="yith-wcbep-bulk-form-content-col">
                <select id="yith-wcbep-yith_wcbm_badge-bulk-select" name="yith-wcbep-yith_wcbm_badge-bulk-select" class="yith-wcbep-miniselect is_resetable">
                    <option value="skip"></option>
                    <option value="none"><?php _e( 'None', 'yith-wcbm' ) ?></option>
                    <?php
                    foreach ( $this->get_badge_array() as $key => $value ) {
                        ?>
                        <option value="<?php echo $key ?>"><?php echo $value ?></option> <?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        <?php
    }
}

/**
 * Unique access to instance of YITH_WCBEP_Badge_Management_Compatibility class
 *
 * @return YITH_WCBEP_Badge_Management_Compatibility
 * @since 1.0.11
 */
function YITH_WCBEP_Badge_Management_Compatibility() {
    return YITH_WCBEP_Badge_Management_Compatibility::get_instance();
}