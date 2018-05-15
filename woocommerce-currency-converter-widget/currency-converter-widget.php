<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Currency Converter Widget
 *
 * @package		WooCommerce
 * @category	Widgets
 * @author		WooThemes
 */
class WooCommerce_Widget_Currency_Converter extends WP_Widget {

	/** @var string Widgets ID */
	private $widget_id_base = 'woocommerce_currency_converter';

	/**
	 * Register the Widget with WP
	 */
	public static function register() {
		register_widget( 'WooCommerce_Widget_Currency_Converter' );
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( $this->widget_id_base, __( 'WooCommerce Currency Converter', 'woocommerce-currency-converter-widget' ), array(
			'classname'   => 'widget_currency_converter',
			'description' =>  __( 'Allow users to choose a currency for prices to be displayed in.', 'woocommerce-currency-converter-widget' )
		) );
	}

	/**
	 * Output the widget content
	 * @param  array $args
	 * @param  array $instance
	 */
	public function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->widget_id_base );

		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		do_action( 'woocommerce_currency_converter', $instance, true );

		echo $after_widget;
	}

	/**
	 * Save settings form
	 * @param  array $new_instance
	 * @param  array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance['title']            = empty( $new_instance['title'] ) ? '' : wc_clean( $new_instance['title'] );
		$instance['currency_codes']   = empty( $new_instance['currency_codes'] ) ? '' : implode( "\n", array_map( 'wc_clean', explode( "\n", $new_instance['currency_codes'] ) ) );
		$instance['message']          = empty( $new_instance['message'] ) ? '' : wc_clean( $new_instance['message'] );
		$instance['show_reset']       = empty( $new_instance['show_reset'] ) ? '' : wc_clean( $new_instance['show_reset'] );
		$instance['show_symbols']     = empty( $new_instance['show_symbols'] ) ? '' : wc_clean( $new_instance['show_symbols'] );
		$instance['currency_display'] = empty( $new_instance['currency_display'] ) ? '' : wc_clean( $new_instance['currency_display'] );
		$instance['disable_location'] = empty( $new_instance['disable_location'] ) ? false : (bool) $new_instance['disable_location'];

		// We want to save the "disable_location" to options table
		// as this setting should be global and easily retrieved to
		// be set in cookie. TODO: move setting away from widget to
		// products tab in WC setting.
		update_option( 'wc_currency_converter_disable_location', empty( $new_instance['disable_location'] ) ? false : (bool) $new_instance['disable_location'] );

		update_option( 'wc_currency_converter_allowed_currencies', empty( $new_instance['currency_codes'] ) ? '' : implode( "\n", array_map( 'wc_clean', explode( "\n", $new_instance['currency_codes'] ) ) ) );

		return $instance;
	}

	/**
	 * Settings Form
	 * @param  array $instance
	 */
	public function form( $instance ) {
		$instance['currency_display'] = empty( $instance['currency_display'] ) ? '' : $instance['currency_display'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'woocommerce-currency-converter-widget' ) ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php if ( isset( $instance['title'] ) ) echo esc_attr( $instance['title'] ); else echo __( 'Currency converter', 'woocommerce-currency-converter-widget' ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'currency_codes' ); ?>"><?php _e( 'Currency codes:', 'woocommerce-currency-converter-widget' ); ?> <small>(<?php _e( '1 per line', 'woocommerce-currency-converter-widget' ) ?>)</small></label>
			<textarea class="widefat" rows="5" cols="20" name="<?php echo $this->get_field_name( 'currency_codes' ); ?>" id="<?php echo $this->get_field_id( 'currency_codes' ); ?>"><?php if ( ! empty( $instance['currency_codes'] ) ) echo esc_attr( $instance['currency_codes'] ); else echo "USD\nEUR"; ?></textarea>
			<p class="description"><?php esc_html_e( "Use * to control how the amounts and currency symbols are displayed. Example: SEK* becomes 999kr. USD * becomes 999 $. If you omit * and just provide the currency (USD, EUR), WooCommerce's default currency position will be used.", 'woocommerce-currency-converter-widget' ); ?></p>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'currency_display' ); ?>"><?php _e( 'Currency Display Mode:', 'woocommerce-currency-converter-widget' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'currency_display' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'currency_display' ) ); ?>">
				<option value="" <?php selected( $instance['currency_display'], '' ) ?>><?php _e( 'Buttons', 'woocommerce-currency-converter-widget' ); ?></option>
				<option value="select" <?php selected( $instance['currency_display'], 'select' ) ?>><?php _e( 'Select Box', 'woocommerce-currency-converter-widget' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'message' ); ?>"><?php _e( 'Widget message:', 'woocommerce-currency-converter-widget' ) ?></label>
			<textarea class="widefat" rows="5" cols="20" name="<?php echo $this->get_field_name( 'message' ); ?>" id="<?php echo $this->get_field_id( 'message' ); ?>"><?php if ( isset ( $instance['message'] ) ) echo esc_attr( $instance['message'] ); else _e( "Currency conversions are estimated and should be used for informational purposes only.", 'woocommerce-currency-converter-widget' ); ?></textarea>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_symbols' ); ?>"><?php _e( 'Show currency symbols in widget:', 'woocommerce-currency-converter-widget' ) ?></label>
			<input type="checkbox" class="" id="<?php echo esc_attr( $this->get_field_id( 'show_symbols' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_symbols') ); ?>" value="1" <?php if ( isset( $instance['show_symbols'] ) ) checked( $instance['show_symbols'], 1 ); ?> />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_reset' ); ?>"><?php _e( 'Show reset link:', 'woocommerce-currency-converter-widget' ) ?></label>
			<input type="checkbox" class="" id="<?php echo esc_attr( $this->get_field_id( 'show_reset' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_reset' ) ); ?>" value="1" <?php if ( isset( $instance['show_reset'] ) ) checked( $instance['show_reset'], 1 ); ?> />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'disable_location' ); ?>"><?php _e( 'Disable location detection:', 'woocommerce-currency-converter-widget' ) ?></label>
			<input type="checkbox" class="" id="<?php echo esc_attr( $this->get_field_id( 'disable_location' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'disable_location' ) ); ?>" value="1" <?php if ( isset( $instance['disable_location'] ) ) checked( $instance['disable_location'], 1 ); ?> />
			<p class="description"><?php esc_html_e( "The currency converter widget will default to the currency for a user's currenct location. Check this box to disable location detection and default to the store's currency.", 'woocommerce-currency-converter-widget' ); ?></p>
		</p>
		<?php
	}
}

WooCommerce_Widget_Currency_Converter::register();
