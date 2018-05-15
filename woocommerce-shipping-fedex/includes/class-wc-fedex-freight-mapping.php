<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Map freight classes to shipping classes
 */
class WC_Fedex_Freight_Mapping {
	public $fedex_freight_class;
	public $display_fedex_freight_class;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->classes = include( dirname( __FILE__ ) . '/data/data-freight-classes.php' );

		add_filter( 'woocommerce_get_shipping_classes', array( $this, 'get_shipping_classes' ) );
		add_filter( 'woocommerce_shipping_classes_columns', array( $this, 'add_shipping_class_column' ) );
		add_action( 'woocommerce_shipping_classes_column_fedex-freight-class', array( $this, 'display_freight_class_column' ) );
		add_action( 'woocommerce_shipping_classes_save_class', array( $this, 'save_shipping_class' ), 10, 2 );
	}

	/**
	 * Change shipping classes data
	 * @param  array
	 * @return array
	 */
	public function get_shipping_classes( $classes ) {
		foreach ( $classes as $class ) {
			$class->fedex_freight_class         = get_woocommerce_term_meta( $class->term_id, 'fedex_freight_class', true );
			$class->display_fedex_freight_class = $class->fedex_freight_class ? $this->classes[ $class->fedex_freight_class ] : '-';
		}

		return $classes;
	}

	/**
	 * Change columns on shipping clases screen.
	 * @param  array
	 * @return array
	 */
	public function add_shipping_class_column( $columns ) {
		$columns['fedex-freight-class'] = __( 'FedEx Freight Class', 'woocommerce-shipping-fedex' );

		return $columns;
	}

	/**
	 * Output html for column
	 */
	public function display_freight_class_column() {
		?>
		<div class="view">{{ data.display_fedex_freight_class }}</div>
		<div class="edit">
			<select name="fedex_freight_class" data-attribute="fedex_freight_class" data-value="{{ data.fedex_freight_class }}">
				<option value=""><?php esc_html_e( 'Default', 'woocommerce-shipping-fedex' ); ?></option>
				<?php foreach ( $this->classes as $key => $value ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
	}

	/**
	 * Save class during ajax save event.
	 */
	public function save_shipping_class( $term_id, $data ) {
		if ( ! empty( $term_id ) && isset( $data['fedex_freight_class'] ) ) {
			// $term_id is an array when add new class and its int
			// when editing the class.
			if ( is_array( $term_id ) ) {
				$term_id = $term_id['term_id'];
			}

			update_term_meta( $term_id, 'fedex_freight_class', sanitize_text_field( $data['fedex_freight_class'] ) );
		}
	}
}

new WC_Fedex_Freight_Mapping();
