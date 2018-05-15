<?php

/**
 * An array of flat rate boxes sizes for USPS
 */
return apply_filters( 'wc_usps_flat_rate_boxes', array(
	// Priority Mail Express
	"d13"     => array(
		"name"     => "Priority Mail Express Flat Rate Envelope",
		"length"   => "12.5",
		"width"    => "9.5",
		"height"   => "0.25",
		"weight"   => "70",
		"type"     => "envelope",
		"box_type" => "express"
	),
	"d30"     => array(
		"name"     => "Priority Mail Express Legal Flat Rate Envelope",
		"length"   => "9.5",
		"width"    => "15",
		"height"   => "0.25",
		"weight"   => "70",
		"type"     => "envelope",
		"box_type" => "express"
	),
	"d63"     => array(
		"name"     => "Priority Mail Express Padded Flat Rate Envelope",
		"length"   => "12.5",
		"width"    => "9.5",
		"height"   => "1",
		"weight"   => "70",
		"type"     => "envelope",
		"box_type" => "express"
	),

	// Priority Mail
	"d16"     => array(
		"name"     => "Priority Mail Flat Rate Envelope",
		"length"   => "12.5",
		"width"    => "9.5",
		"height"   => "0.25",
		"weight"   => "70",
		"type"     => "envelope",
		"box_type" => "priority"
	),
	"d17"     => array(
		"name"     => "Priority Mail Flat Rate Medium Box - 2",
		"length"   => "13.625",
		"width"    => "11.875",
		"height"   => "3.375",
		"weight"   => "70",
		"box_type" => "priority"
	),
	"d17b"     => array(
		"name"     => "Priority Mail Flat Rate Medium Box - 1",
		"length"   => "11",
		"width"    => "8.5",
		"height"   => "5.5",
		"weight"   => "70",
		"box_type" => "priority"
	),
	"d22"     => array(
		"name"     => "Priority Mail Flat Rate Large Box",
		"length"   => "12",
		"width"    => "12",
		"height"   => "5.5",
		"weight"   => "70",
		"box_type" => "priority"
	),
	"d22a"     => array(
		"name"     => "Priority Mail Large Flat Rate Board Game Box",
		"length"   => "23.69",
		"width"    => "11.75",
		"height"   => "3",
		"weight"   => "70",
		"box_type" => "priority"
	),
	"d28"     => array(
		"name"     => "Priority Mail Flat Rate Small Box",
		"length"   => "5.375",
		"width"    => "8.625",
		"height"   => "1.625",
		"weight"   => "70",
		"box_type" => "priority"
	),
	"d29"     => array(
		"name"     => "Priority Mail Padded Flat Rate Envelope",
		"length"   => "12.5",
		"width"    => "9.5",
		"height"   => "1",
		"weight"   => "70",
		"type"     => "envelope",
		"box_type" => "priority"
	),
	"d38"     => array(
		"name"     => "Priority Mail Gift Card Flat Rate Envelope",
		"length"   => "10",
		"width"    => "7",
		"height"   => "0.25",
		"weight"   => "70",
		"type"     => "envelope",
		"box_type" => "priority"
	),
	"d40"     => array(
		"name"     => "Priority Mail Window Flat Rate Envelope",
		"length"   => "5",
		"width"    => "10",
		"height"   => "0.25",
		"weight"   => "70",
		"type"     => "envelope",
		"box_type" => "priority"
	),
	"d42"     => array(
		"name"     => "Priority Mail Small Flat Rate Envelope",
		"length"   => "6",
		"width"    => "10",
		"height"   => "0.25",
		"weight"   => "70",
		"type"     => "envelope",
		"box_type" => "priority"
	),
	"d44"     => array(
		"name"     => "Priority Mail Legal Flat Rate Envelope",
		"length"   => "9.5",
		"width"    => "15",
		"height"   => "0.5",
		"weight"   => "70",
		"type"     => "envelope",
		"box_type" => "priority"
	),

	// International Priority Mail Express
	"i13"     => array(
		"name"     => "Priority Mail Express Flat Rate Envelope",
		"length"   => "12.5",
		"width"    => "9.5",
		"height"   => "0.25",
		"weight"   => "4",
		"type"     => "envelope",
		"box_type" => "express"
	),
	"i30"     => array(
		"name"     => "Priority Mail Express Legal Flat Rate Envelope",
		"length"   => "9.5",
		"width"    => "15",
		"height"   => "0.25",
		"weight"   => "4",
		"type"     => "envelope",
		"box_type" => "express"
	),
	"i63"     => array(
		"name"     => "Priority Mail Express Padded Flat Rate Envelope",
		"length"   => "12.5",
		"width"    => "9.5",
		"height"   => "1",
		"weight"   => "4",
		"type"     => "envelope",
		"box_type" => "express"
	),

	// International Priority Mail
	"i8"      => array(
		"name"     => "Priority Mail Flat Rate Envelope",
		"length"   => "12.5",
		"width"    => "9.5",
		"height"   => "0.25",
		"weight"   => "4",
		"type"     => "envelope",
		"box_type" => "priority"
	),
	"i29"     => array(
		"name"     => "Priority Mail Padded Flat Rate Envelope",
		"length"   => "12.5",
		"width"    => "9.5",
		"height"   => "1",
		"weight"   => "4",
		"type"     => "envelope",
		"box_type" => "priority"
	),
	"i16"     => array(
		"name"     => "Priority Mail Flat Rate Small Box",
		"length"   => "5.375",
		"width"    => "8.625",
		"height"   => "1.625",
		"weight"   => "4",
		"box_type" => "priority"
	),
	"i9"      => array(
		"name"     => "Priority Mail Flat Rate Medium Box",
		"length"   => "11.875",
		"width"    => "13.625",
		"height"   => "3.375",
		"weight"   => "20",
		"box_type" => "priority"
	),
	"i9b"      => array(
		"name"     => "Priority Mail Flat Rate Medium Box",
		"length"   => "11",
		"width"    => "8.5",
		"height"   => "5.5",
		"weight"   => "70",
		"box_type" => "priority"
	),
	"i11"     => array(
		"name"     => "Priority Mail Flat Rate Large Box",
		"length"   => "12",
		"width"    => "12",
		"height"   => "5.5",
		"weight"   => "20",
		"box_type" => "priority"
	)
) );
