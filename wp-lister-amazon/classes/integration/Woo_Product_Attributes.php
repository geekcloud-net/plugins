<?php
/**
 * Display product condition and notes as attributes in the product page
 */
class WPLA_Product_Attributes {

    /**
     * Register hooks
     */
    public function __construct() {
        if ( WPLA_SettingsPage::getOption( 'display_condition_and_notes', 0 ) == 1 ) {
            add_filter( 'woocommerce_product_get_attributes', array( $this, 'addProductAttributes' ) );
        }
    }

    /**
     * Add item condition and item note as attributes
     * @param array $attributes
     * @return array
     */
    public function addProductAttributes( $attributes = array() ) {
        global $product;

        if ( !is_object( $product ) ) {
            return $attributes;
        }

        $condition  = get_post_meta( wpla_get_product_meta( $product, 'id' ), '_amazon_condition_type', true );
        $note       = get_post_meta( wpla_get_product_meta( $product, 'id' ), '_amazon_condition_note', true );

        if ( $condition ) {
            $condition = self::getConditionString( $condition );
            $attributes[] = $this->addAttribute( __('Condition', 'wpla'), $condition );
        }

        if ( $note ) {
            $attributes[] = $this->addAttribute( __('Note', 'wpla'), $note );
        }

        return $attributes;
    }

    /**
     * Return a readable string from the given $conditionType
     *
     * @param string $conditionType
     * @return string
     */
    public static function getConditionString( $conditionType ) {
        $string = $conditionType;
        $map = array(
            'New'                   => __('New', 'wpla'),
            'UsedLikeNew'           => __('Used - Like New', 'wpla'),
            'UsedVeryGood'          => __('Used - Very Good', 'wpla'),
            'UsedGood'              => __('Used - Good', 'wpla'),
            'UsedAcceptable'        => __('Used - Acceptable', 'wpla'),
            'Refurbished'           => __('Refurbished', 'wpla'),
            'CollectibleLikeNew'    => __('Collectible - Like New', 'wpla'),
            'CollectibleVeryGood'   => __('Collectible - Very Good', 'wpla'),
            'CollectibleGood'       => __('Collectible - Good', 'wpla'),
            'CollectibleAcceptable' => __('Collectible - Acceptable', 'wpla'),
        );

        if ( isset( $map[ $conditionType ] ) ) {
            $string = $map[ $conditionType ];
        }

        return $string;
    }

    /**
     * @param $name
     * @param string $value
     * @param bool $is_visible
     * @return WC_Product_Attribute|array
     */
    private function addAttribute( $name, $value = '', $is_visible = true ) {
        if ( class_exists( 'WC_Product_Attribute' ) ) {
            $attr = new WC_Product_Attribute();
            $attr->set_name( $name );
            $attr->set_visible( $is_visible );

            if ( ! empty( $value ) ) {
                $attr->set_options( array( $value ) );
            }

            return $attr;
        } else {
            return  array(
                'is_visible'    => $is_visible,
                'is_taxonomy'   => false,
                'name'          => $name,
                'value'         => $value
            );
        }
    }

}