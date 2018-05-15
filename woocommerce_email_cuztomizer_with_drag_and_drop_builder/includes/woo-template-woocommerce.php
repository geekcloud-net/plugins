<?php
/**
 * WooCommerce Email Customizer with Drag and Drop Email Builder
 * Create awesome transactional emails with a drag and drop email builder
 * @author Flycart Technologies LLP
 * @license GNU GPL V3 or later
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 *
 * Main Class.
 */
class WooMailBuilder{

	public function __construct() {

		/* Register Email Template */
		add_action( 'register_email_template',	array( $this, 'register_email_template' ) );
	}

	public function register_email_template() {

		woo_mb_register_email_template(
			'woocommerce',
			array(
				'name'                         => 'WooCommerce',
				'description'                  => '',
				'template_folder'              => WOO_ECPB_DIR . '/templates',
				'settings'                     => '',
				'woocoomerce_required_version' => '2.5',
			)
		);
	}
}
