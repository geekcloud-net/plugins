<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Text Plugin Admin View
 *
 * @package    Yithemes
 * @author     Emanuela Castorina <emanuela.castorina@yithemes.it>
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


$id    = $this->_panel->get_id_field( $option['id'] );
$name  = $this->_panel->get_name_field( $option['id'] );
$class = isset( $option['class'] ) ? $option['class'] : '';

$deps_html = yith_field_deps_data( $option );

$currencies       = array();
$default_currency = get_woocommerce_currency();
array_push( $currencies, $default_currency );

//filter to multi currencies integration
$currencies = apply_filters( 'ywpar_get_active_currency_list', $currencies );


foreach ( $currencies as $current_currency ) :

		$name = $this->_panel->get_name_field( $option['id'] ) . '[' . $current_currency . ']';
		$id = $this->_panel->get_id_field( $option['id'] ) . '[' . $current_currency . ']';
		$points = ( isset( $db_value[ $current_currency ]['points'] ) ) ? $db_value[ $current_currency ]['points'] : $db_value['points'];
        $money = ( isset( $db_value[ $current_currency ]['money'] ) ) ? $db_value[ $current_currency ]['money'] : $db_value['money'];

		?>
        <div id="<?php echo $id ?>-container" <?php echo $deps_html; ?>
             class="yit_options rm_option rm_input rm_text conversion-options <?php echo $class ?>">
            <div class="option">
                <input type="number" name="<?php echo $name ?>[points]" step="1" min="0" id="<?php echo $id ?>-points"
                       value="<?php echo esc_attr( $points ) ?>"/>
                <span><?php _e( 'Points', 'yith-woocommerce-points-and-rewards' ) ?></span>
                <input type="number" name="<?php echo $name ?>[money]" step="1" min="0" id="<?php echo $id ?>-money"
                       value="<?php echo esc_attr( $money ) ?>"/>
                <span><?php echo get_woocommerce_currency_symbol( $current_currency ) ?></span>
            </div>
            <span class="description"><?php echo $option['desc'] ?></span>
            <div class="clear"></div>
        </div>

<?php endforeach; ?>

