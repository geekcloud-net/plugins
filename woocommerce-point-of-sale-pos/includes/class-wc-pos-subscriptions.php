<?php
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class WC_Pos_Subscriptions{

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
        add_filter('wc_pos_edit_product_types',   array($this, 'edit_product_types'), 10);
        add_filter('wc_pos_enqueue_scripts',   array($this, 'pos_enqueue_scripts'), 10, 1);
        add_filter('wc_pos_i18n_js',   array($this, 'include_i18n_js'), 20, 1);
        add_filter('wc_pos_inline_js',   array($this, 'add_inline_js'), 20, 1);
        #add_action('wc_pos_tmpl_cart_product_item_thead',   array($this, 'tmpl_cart_product_item_thead'), 1);
        #add_action('wc_pos_tmpl_cart_product_item_col',   array($this, 'tmpl_cart_product_item_col'), 1);
        add_action('wc_pos_modal_add_product_custom_meta',   array($this, 'modal_add_product_custom_meta'), 30, 1);
	}

	public function edit_product_types($types)
	{
		if( !is_array($types)){
			$types = array();
		}
		$types['subscription'] = __('Subscription', 'wc_point_of_sale');
		return $types;
	}
	public function pos_enqueue_scripts($sctipts){
		$sctipts['wc-pos-subscriptions'] = WC_POS()->plugin_url() . '/assets/js/register/subscriptions.js';
		return $sctipts;
	}

	public function include_i18n_js($i18n){
		$i18n['subscriptions_i18n'] = include_once WC_POS()->plugin_path() . '/i18n/subscriptions.php';
		return $i18n;
	}
	public function add_inline_js($inline_js){
		$options = array(
			'multiple_subscriptions' => WC_Subscriptions_Payment_Gateways::one_gateway_supports( 'multiple_subscriptions' ),
			'accept_manual_renewals' => ( 'yes' == get_option( WC_Subscriptions_Admin::$option_prefix . '_accept_manual_renewals', 'no' ) ) ? true : false,
			'multiple_purchase'      => ( 'yes' == get_option( WC_Subscriptions_Admin::$option_prefix . '_multiple_purchase', 'no' ) ) ? true : false,			
			'syncing_enabled'        => ( 'yes' == get_option( WC_Subscriptions_Admin::$option_prefix . '_sync_payments', 'no' ) ) ? true : false,
			'months'                 => $this->get_months(),
			'weekdays'               => array(
					WC_Subscriptions_Synchroniser::get_weekday( 0 ),
					WC_Subscriptions_Synchroniser::get_weekday( 1 ),
					WC_Subscriptions_Synchroniser::get_weekday( 2 ),
					WC_Subscriptions_Synchroniser::get_weekday( 3 ),
					WC_Subscriptions_Synchroniser::get_weekday( 4 ),
					WC_Subscriptions_Synchroniser::get_weekday( 5 ),
					WC_Subscriptions_Synchroniser::get_weekday( 6 ),
					WC_Subscriptions_Synchroniser::get_weekday( 7 )
				),
			);
		$array = json_encode( $options );
		$WCSubscriptions = json_encode( array(
			'subscriptionLengths'            => wcs_get_subscription_ranges(),
			) );
		$inline_js['subscriptions_options'] = '<script type="text/javascript" class="wc_pos_subscriptions_options" > var wc_pos_subscriptions_options = '. $array .'; </script>';
		$inline_js['WCSubscriptions']       = '<script type="text/javascript" class="wc_pos_WCSubscriptions" >       var WCSubscriptions = '. $WCSubscriptions .'; </script>';

		return $inline_js;
	}

	private function get_months()
	{
		global $wp_locale;
		$months = array();
		for ($i=1; $i <= 12; $i++) { 	
			$l = $i;
			if( strlen( $l ) == 1){
				$l = '0'.$i;
			}
			$months[] = $wp_locale->month[ $l ];
		}
		return $months;
	}
	
	public function tmpl_cart_product_item_thead()
	{
		?>
		<th class="line_cost line_fee"><?php _e('Fee', 'wc_point_of_sale'); ?></th>
		<?php
	}
	public function tmpl_cart_product_item_col()
	{
		?>
		<td class="line_cost line_fee">
			<div class="view">
            {{#if cart_item_data.sign_up_fee}}
	            {{#if editable}}
	                <input type="text" class="product_sign_up_fee" placeholder="{{cart_item_data.sign_up_fee}}" value="{{cart_item_data.sign_up_fee}}" data-discountsymbol="currency_symbol" data-percent="0" >
	            {{else}}
					<input type="text" class="product_sign_up_fee" placeholder="{{cart_item_data.sign_up_fee}}" value="{{cart_item_data.sign_up_fee}}" data-discountsymbol="currency_symbol" data-percent="0" disabled="disabled">
	            {{/if}}
            {{/if}}
			</div>
		</td>
		<?php
	}

	public function modal_add_product_custom_meta($type)
	{
		?>
		<div id="<?php echo $type; ?>_subscription_fields">
		<h3><?php _e('Subscription', 'wc_point_of_sale'); ?></h3>
		<table class="subscription_pricing_table" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th><?php printf(__('Sign-up Fee (%s)', 'wc_point_of_sale'), get_woocommerce_currency_symbol()); ?></th>
					<th><?php _e('Interval', 'wc_point_of_sale'); ?></th>
					<th><?php _e('Period', 'wc_point_of_sale'); ?></th>
					<th><?php _e('Length', 'wc_point_of_sale'); ?></th>
				</tr>
			</thead>
			<tbody>
		        <tr>
		            <td><input type="text" class="_subscription_sign_up_fee"></td>
		            <td>
		                <select class="_subscription_period_interval">
		                    <?php foreach ( wcs_get_subscription_period_interval_strings() as $key => $value) {
		                        echo "<option value='" . $key . "'>".$value."</option>";
		                    }?>
		                </select>
	                </td>
	                <td>
		                <select class="_subscription_period">
		                    <?php foreach ( wcs_get_subscription_period_strings() as $key => $value) {
		                        echo "<option value='" . $key . "'>".$value."</option>";
		                    }?>
		                </select>
	                </td>
	                <td>
		                <select class="_subscription_length">
		                    <?php foreach ( wcs_get_subscription_ranges( 'month' ) as $key => $value) {
		                        echo "<option value='" . $key . "'>".$value."</option>";
		                    }?>
		                </select>
		            </td>
		        </tr>				
			</tbody>
		</table>
		</div>
		<?php
	}
    /**
	 * Main WC_Pos_Subscriptions Instance
	 *
	 * Ensures only one instance of WC_Pos_Subscriptions is loaded or can be loaded.
	 *
	 * @since 1.9
	 * @static
	 * @return WC_Pos_Subscriptions Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.9
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '1.9' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.9
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '1.9' );
	}

}

return new WC_Pos_Subscriptions();