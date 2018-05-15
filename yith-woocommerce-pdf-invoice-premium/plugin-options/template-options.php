<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$current_date = getdate();

$general_options = array(

	'template' => array(

		'section_template' => array(
			'name' => __( 'Template settings', 'yith-woocommerce-pdf-invoice' ),
			'type' => 'title',
		),

		'company_name'              => array(
			'name'    => __( 'Company name', 'yith-woocommerce-pdf-invoice' ),
			'type'    => 'text',
			'id'      => 'ywpi_company_name',
			'desc'    => __( 'Set company name to be shown on invoices', 'yith-woocommerce-pdf-invoice' ),
			'default' => __( 'Your company name', 'yith-woocommerce-pdf-invoice' ),
		),
		'company_logo'              => array(
			'name' => __( 'Your company logo', 'yith-woocommerce-pdf-invoice' ),
			'type' => 'ywpi_logo',
			'id'   => 'ywpi_company_logo',
			'desc' => __( 'Set a default logo to be shown', 'yith-woocommerce-pdf-invoice' ),
		),
		'company_details'           => array(
			'name'    => __( 'Company details', 'yith-woocommerce-pdf-invoice' ),
			'type'    => 'textarea',
			'id'      => 'ywpi_company_details',
			'css'     => 'width:80%; height: 90px;',
			'desc'    => __( 'Set company details to use in the invoice', 'yith-woocommerce-pdf-invoice' ),
			'default' => __( 'Your company details
Address
City, State' ),
		),
		'show_company_name'         => array(
			'name'          => __( 'Visible sections', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => 'start',
			'id'            => 'ywpi_show_company_name',
			'desc'          => __( 'Show company name', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'show_company_logo'         => array(
			'name'          => __( 'Show company logo', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_show_company_logo',
			'desc'          => __( 'Show company logo', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'show_company_details'      => array(
			'name'          => __( 'Show company details', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => 'end',
			'id'            => 'ywpi_show_company_details',
			'desc'          => __( 'Show company details', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'customer_billing_details'  => array(
			'name'    => __( 'Customer invoice details', 'yith-woocommerce-pdf-invoice' ),
			'type'    => 'textarea',
			'id'      => 'ywpi_customer_billing_details',
			'css'     => 'width:80%; height: 90px;',
			'desc'    => __( 'Set the customer details to use in the invoice. Use the postmeta metakeys as placeholders within double curly
			brackets, for example {{_billing_first_name}} for showing the order billing first name', 'yith-woocommerce-pdf-invoice' ),
			'default' => __( '{{_billing_first_name}} {{_billing_last_name}}
{{_billing_address_1}}
{{_billing_postcode}}{{_billing_city}}
{{_billing_country}}
SSN: {{_billing_vat_ssn}}
VAT: {{_billing_vat_number}}
{{_billing_phone}}
{{_billing_email}}', 'yith-woocommerce-pdf-invoice' ),
		),
		'customer_shipping_details' => array(
			'name'    => __( 'Customer packing slip details', 'yith-woocommerce-pdf-invoice' ),
			'type'    => 'textarea',
			'id'      => 'ywpi_customer_shipping_details',
			'css'     => 'width:80%; height: 90px;',
			'desc'    => __( 'Set the customer details to use in the invoice. Use the postmeta metakeys as placeholders within double curly
			brackets, for example {{_shipping_first_name}} for showing the order shipping first name', 'yith-woocommerce-pdf-invoice' ),
			'default' => '{{_shipping_first_name}} {{_shipping_last_name}}
{{_shipping_address_1}}
{{_shipping_postcode}}{{_shipping_city}}
{{_shipping_country}}',
		),
		'section_template_end'      => array(
			'type' => 'sectionend',
			'id'   => 'ywpi_template_end',
		),
		'section_template_invoice'  => array(
			'name' => __( 'Invoice and Pro-Forma invoice template settings', 'yith-woocommerce-pdf-invoice' ),
			'type' => 'title',
			'id'   => 'ywpi_section_template_invoice',
		),

		'invoice_notes'  => array(
			'name' => __( 'Invoice notes', 'yith-woocommerce-pdf-invoice' ),
			'type' => 'textarea',
			'id'   => 'ywpi_invoice_notes',
			'css'  => 'width:80%; height: 90px;',
			'desc' => __( 'Type the text to show as notes on invoices.', 'yith-woocommerce-pdf-invoice' ),
		),
		'invoice_footer' => array(
			'name' => __( 'Invoice footer', 'yith-woocommerce-pdf-invoice' ),
			'type' => 'textarea',
			'id'   => 'ywpi_invoice_footer',
			'css'  => 'width:80%; height: 90px;',
			'desc' => __( 'Type the text to show as footer on invoices.', 'yith-woocommerce-pdf-invoice' ),
		),

		'pro_forma_notes'  => array(
			'name' => __( 'Pro-Forma Invoice notes', 'yith-woocommerce-pdf-invoice' ),
			'type' => 'textarea',
			'id'   => 'ywpi_pro_forma_notes',
			'css'  => 'width:80%; height: 90px;',
			'desc' => __( 'Type the text to show as notes on Pro-Forma invoices.', 'yith-woocommerce-pdf-invoice' ),

		),
		'pro_forma_footer' => array(
			'name' => __( 'Pro-Forma invoice footer', 'yith-woocommerce-pdf-invoice' ),
			'type' => 'textarea',
			'id'   => 'ywpi_pro_forma_footer',
			'css'  => 'width:80%; height: 90px;',
			'desc' => __( 'Type the text to show as footer on Pro-Forma invoices.', 'yith-woocommerce-pdf-invoice' ),
		),

		'show_invoice_notes'                    => array(
			'name'          => __( 'Visible sections', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => 'start',
			'id'            => 'ywpi_show_invoice_notes',
			'desc'          => __( 'Show notes', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'show_invoice_footer'                   => array(
			'name'          => __( 'Show footer', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_show_invoice_footer',
			'desc'          => __( 'Show footer', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'ywpi_subtotal_inclusive_discount'      => array(
			'name'          => __( 'Discount on subtotal', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_subtotal_inclusive_discount',
			'desc'          => __( 'Show order subtotal inclusive of order discount', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'ywpi_show_discount'                    => array(
			'name'          => __( 'Show discount', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => 'end',
			'id'            => 'ywpi_show_discount',
			'desc'          => __( 'Show the order discount in the invoice summary amounts', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'show_invoice_column_picture'           => array(
			'name'            => __( 'Visible columns', 'yith-woocommerce-pdf-invoice' ),
			'type'            => 'checkbox',
			'checkboxgroup'   => 'start',
			'show_if_checked' => 'option',
			'id'              => 'ywpi_invoice_column_picture',
			'css'             => 'width:80%; height: 90px;',
			'desc'            => __( 'Product picture', 'yith-woocommerce-pdf-invoice' ),
			'default'         => 'yes',
		),
		'show_invoice_column_SKU'               => array(
			'name'          => __( 'Invoice visible columns', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_invoice_column_SKU',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Product SKU', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'show_invoice_column_short_description' => array(
			'name'          => __( 'Invoice visible columns', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_invoice_column_short_description',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Short description', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'no',
		),
		'show_invoice_column_variation'         => array(
			'name'          => __( 'Invoice visible columns', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_invoice_column_variation',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Product variation', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'show_invoice_column_quantity'          => array(
			'name'          => __( 'Invoice visible columns', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_invoice_column_quantity',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Quantity', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'show_invoice_column_product_price'     => array(
			'name'          => __( 'Invoice visible columns', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_invoice_column_product_price',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Product price', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'no',
		),
		'show_invoice_column_regular_price'     => array(
			'name'          => __( 'Invoice visible columns', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_invoice_column_regular_price',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Regular price', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'show_invoice_column_sale_price'        => array(
			'name'          => __( 'Invoice visible columns', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_invoice_column_sale_price',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'On sale price', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),

		'show_invoice_column_line_total'       => array(
			'name'          => __( 'Invoice visible columns', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_invoice_column_line_total',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Line total', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'show_invoice_column_tax'              => array(
			'name'          => __( 'Invoice visible columns', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_invoice_column_tax',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Tax', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'show_invoice_column_percentage_tax'              => array(
			'name'          => __( 'Invoice visible columns', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_invoice_column_percentage_tax',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Tax percentage', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'no',
		),
		'ywpi_invoice_column_total_taxed'      => array(
			'name'          => __( 'Invoice visible columns', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_invoice_column_total_taxed',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Total (inc. tax)', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'no',
		),
		'ywpi_invoice_column_percentage'       => array(
			'name'          => __( 'Invoice visible columns', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => 'end',
			'id'            => 'ywpi_invoice_column_percentage',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Show discount percentage', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'no',
		),
		'section_section_template_invoice_end' => array(
			'type' => 'sectionend',
			'id'   => 'ywpi_section_template_invoice_end',
		),

		array(
			'name' => __( 'Credit note template settings', 'yith-woocommerce-pdf-invoice' ),
			'type' => 'title',
		),
		'ywpi_credit_note_notes'               => array(
			'name' => __( 'Notes on credit note', 'yith-woocommerce-pdf-invoice' ),
			'type' => 'textarea',
			'id'   => 'ywpi_credit_note_notes',
			'css'  => 'width:80%; height: 90px;',
			'desc' => __( 'Type the text to show as notes on credit notes.', 'yith-woocommerce-pdf-invoice' ),
		),
		'ywpi_credit_note_footer'              => array(
			'name' => __( 'Credit note footer', 'yith-woocommerce-pdf-invoice' ),
			'type' => 'textarea',
			'id'   => 'ywpi_credit_note_footer',
			'css'  => 'width:80%; height: 90px;',
			'desc' => __( 'Type the text to show as footer on credit notes.', 'yith-woocommerce-pdf-invoice' ),
		),
		'ywpi_credit_note_refund_text'         => array(
			'name' => __( 'Refund text', 'yith-woocommerce-pdf-invoice' ),
			'type' => 'text',
			'id'   => 'ywpi_credit_note_refund_text',
			'css'  => 'width:50%;',
			'desc' => __( 'Type the refund text to show in the credit note.', 'yith-woocommerce-pdf-invoice' ),
		),
		'ywpi_show_credit_note_notes'          => array(
			'name'          => __( 'Visible sections', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => 'start',
			'id'            => 'ywpi_show_credit_note_notes',
			'desc'          => __( 'Show notes', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'ywpi_show_credit_note_footer'         => array(
			'name'          => __( 'Show footer', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => 'end',
			'id'            => 'ywpi_show_credit_note_footer',
			'desc'          => __( 'Show footer', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'ywpi_credit_note_reason_column'       => array(
			'name'            => __( 'Visible columns', 'yith-woocommerce-pdf-invoice' ),
			'type'            => 'checkbox',
			'checkboxgroup'   => 'start',
			'show_if_checked' => 'option',
			'id'              => 'ywpi_credit_note_reason_column',
			'css'             => 'width:80%; height: 90px;',
			'desc'            => __( 'Show refund reason, if available.', 'yith-woocommerce-pdf-invoice' ),
			'default'         => 'yes',
		),
		'ywpi_credit_note_subtotal_column'     => array(
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_credit_note_subtotal_column',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Show subtotal', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'ywpi_credit_note_total_tax_column'    => array(
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_credit_note_total_tax_column',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Show total tax', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'ywpi_credit_note_total_percentage_tax_column'    => array(
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_credit_note_percentage_tax_column',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Show percentage tax', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'no',
		),
		'ywpi_credit_note_total_column'        => array(
			'type'          => 'checkbox',
			'checkboxgroup' => 'end',
			'id'            => 'ywpi_credit_note_total_column',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Show total', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'no',
		),
		array(
			'type' => 'sectionend',
		),

		'section_template_packing_slip' => array(
			'name' => __( 'Packing slip template settings', 'yith-woocommerce-pdf-invoice' ),
			'type' => 'title',
			'id'   => 'ywpi_section_template_packing_slip',
		),

		'packing_slip_notes'  => array(
			'name' => __( 'Notes', 'yith-woocommerce-pdf-invoice' ),
			'type' => 'textarea',
			'id'   => 'ywpi_packing_slip_notes',
			'css'  => 'width:80%; height: 90px;',
			'desc' => __( 'Type the text to show as notes on packing slip.', 'yith-woocommerce-pdf-invoice' ),
		),
		'packing_slip_footer' => array(
			'name' => __( 'Footer', 'yith-woocommerce-pdf-invoice' ),
			'type' => 'textarea',
			'id'   => 'ywpi_packing_slip_footer',
			'css'  => 'width:80%; height: 90px;',
			'desc' => __( 'Type the text to show as footer on packing slip.', 'yith-woocommerce-pdf-invoice' ),
		),

		'packing_slip_show_order_totals'            => array(
			'name'          => __( 'Visible sections', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => 'start',
			'id'            => 'ywpi_packing_slip_show_order_totals',
			'desc'          => __( 'Show order totals.', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'packing_slip_show_notes'                   => array(
			'name'          => __( 'Show notes', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_packing_slip_show_notes',
			'desc'          => __( 'Show notes', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'packing_slip_show_footer'                  => array(
			'name'          => __( 'Show footer', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => 'end',
			'id'            => 'ywpi_packing_slip_show_footer',
			'desc'          => __( 'Show footer', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'packing_slip_column_picture'               => array(
			'name'            => __( 'Visible columns', 'yith-woocommerce-pdf-invoice' ),
			'type'            => 'checkbox',
			'checkboxgroup'   => 'start',
			'show_if_checked' => 'option',
			'id'              => 'ywpi_packing_slip_column_picture',
			'css'             => 'width:80%; height: 90px;',
			'desc'            => __( 'Product picture', 'yith-woocommerce-pdf-invoice' ),
			'default'         => 'yes',
		),
		'packing_slip_column_SKU'                   => array(
			'name'          => __( 'Visible columns in the packing slip', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_packing_slip_column_SKU',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Product SKU', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'ywpi_packing_slip_column_weight'                   => array(
			'name'          => __( 'Visible columns in the packing slip', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_packing_slip_column_weight',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Weight and dimension', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'no',
		),
		'packing_slip_column_short_description'     => array(
			'name'          => __( 'Visible columns in the packing slip', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_packing_slip_column_short_description',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Short description', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'no',
		),
		'packing_slip_column_variation'             => array(
			'name'          => __( 'Visible columns in the packing slip', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_packing_slip_column_variation',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Product variation', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'packing_slip_column_quantity'              => array(
			'name'          => __( 'Visible columns in the packing slip', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_packing_slip_column_quantity',
			'css'           => 'width:80%; hywpi_invoice_column_regular_priceeight: 90px;',
			'desc'          => __( 'Quantity', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'packing_slip_column_product_price'         => array(
			'name'          => __( 'Visible columns in the packing slip', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_packing_slip_column_product_price',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Product price', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'no',
		),
		'packing_slip_column_regular_price'         => array(
			'name'          => __( 'Visible columns in the packing slip', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_packing_slip_column_regular_price',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Regular price', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'packing_slip_column_sale_price'            => array(
			'name'          => __( 'Visible columns in the packing slip', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_packing_slip_column_sale_price',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Sale price', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'packing_slip_column_line_total'            => array(
			'name'          => __( 'Visible columns in the packing slip', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_packing_slip_column_line_total',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Line total', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'packing_slip_column_tax'                   => array(
			'name'          => __( 'Visible columns in the packing slip', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_packing_slip_column_tax',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Tax', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'yes',
		),
		'packing_slip_column_percentage_tax'                   => array(
			'name'          => __( 'Visible columns in the packing slip', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_packing_slip_column_percentage_tax',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Percentage tax', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'no',
		),
		'ywpi_packing_slip_column_total_taxed'      => array(
			'name'          => __( 'Visible columns in the packing slip', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => '',
			'id'            => 'ywpi_packing_slip_column_total_taxed',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Total (inc. tax)', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'no',
		),
		'ywpi_packing_slip_column_percentage'       => array(
			'name'          => __( 'Visible columns in the packing slip', 'yith-woocommerce-pdf-invoice' ),
			'type'          => 'checkbox',
			'checkboxgroup' => 'end',
			'id'            => 'ywpi_packing_slip_column_percentage',
			'css'           => 'width:80%; height: 90px;',
			'desc'          => __( 'Show discount percentage', 'yith-woocommerce-pdf-invoice' ),
			'default'       => 'no',
		),
		'section_section_template_packing_slip_end' => array(
			'type' => 'sectionend',
		),
	),
);

return $general_options;
