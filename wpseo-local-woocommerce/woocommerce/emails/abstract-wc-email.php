<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

abstract class WPSEO_Local_WooCommerce_Email extends WC_Email {

	/**
	 * Set email defaults
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// Call parent constructor to load any other defaults not explicity defined here
		parent::__construct();

		// this sets the recipient to the current customer
		$this->customer_email = true;
		$this->template_base = WPSEO_LOCAL_WOOCOMMERCE_PATH . '/woocommerce/templates/';

	}

	/**
	 * Determine if the email should actually be sent and setup email merge variables
	 *
	 * @since 0.1
	 * @param int $order_id
	 */
	public function trigger( $order_id ) {

		// bail if no order ID is present
		if ( ! $order_id )
			return;

		// setup order object
		$this->object = new WC_Order( $order_id );

		//set mail recipient to the current customer
		$this->recipient = $this->object->billing_email;

		// replace variables in the subject/headings
		$this->find[] = '{order_date}';
		$this->replace[] = date_i18n( wc_date_format(), strtotime( $this->object->order_date ) );

		$this->find[] = '{order_number}';
		$this->replace[] = $this->object->get_order_number();

		if ( ! $this->is_enabled() )
			return;

		// woohoo, send the email!
		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * get_content_html function.
	 *
	 * @since 0.1
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html( $this->template_html, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => false,
			'email'			=> $this
		) );

	}

	/**
	 * get_content_plain function.
	 *
	 * @since 0.1
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => true,
			'email'			=> $this
		) );
	}

	/**
	 * Initialize Settings Form Fields
	 *
	 * @since 2.0
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'    => array(
				'title'   => __( 'Enable/Disable', 'yoast-local-seo-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'yoast-local-seo-woocommerce' ),
				'default' => 'yes'
			),
			'subject'    => array(
				'title'       => __( 'Subject', 'yoast-local-seo-woocommerce' ),
				'type'        => 'text',
				'description' => sprintf( __('This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'yoast-local-seo-woocommerce' ), $this->subject ),
				'placeholder' => '',
				'default'     => ''
			),
			'heading'    => array(
				'title'       => __( 'Email Heading', 'yoast-local-seo-woocommerce'),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'yoast-local-seo-woocommerce' ), $this->heading ),
				'placeholder' => '',
				'default'     => ''
			),
			'email_type' => array(
				'title'       => __( 'Email type', 'yoast-local-seo-woocommerce'),
				'type'        => 'select',
				'description' => __('Choose which format of email to send.', 'yoast-local-seo-woocommerce'),
				'default'     => 'html',
				'class'       => 'email_type',
				'options'     => array(
					'plain'	    => __( 'Plain text', 'yoast-local-seo-woocommerce' ),
					'html' 	    => __( 'HTML', 'yoast-local-seo-woocommerce' ),
					'multipart' => __( 'Multipart', 'yoast-local-seo-woocommerce' ),
				)
			)
		);
	}

}