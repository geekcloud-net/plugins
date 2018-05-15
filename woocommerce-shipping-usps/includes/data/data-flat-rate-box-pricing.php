<?php

/**
 * An array of flat rate box pricing - 2016
 * As of Jan 2016 USPS has removed all discounts for Click N Ship/Business/Online rate.  All rates are returning retail rates now.
 * We're keeping both just in case they change their minds later but for now will make the rates the same for both.
 * Priority mail flat rate envelope prices updated on 04/13/2017 according to https://www.usps.com/ship/priority-mail.htm
 */
return apply_filters( 'wc_usps_flat_rate_box_pricing', array(

	// Priority Mail Express

		// Priority Mail Express Flat Rate Envelope
		"d13"     => array(
			"retail" => "24.70",
			"online" => "24.70",
		),
		// Priority Mail Express Legal Flat Rate Envelope
		"d30"     => array(
			"retail" => "24.90",
			"online" => "24.90",
		),
		// Priority Mail Express Padded Flat Rate Envelope
		"d63"     => array(
			"retail" => "25.40",
			"online" => "25.40",
		),

	// Priority Mail Boxes

		// Priority Mail Flat Rate Medium Box
		"d17"     => array(
			"retail" => "13.65",
			"online" => "13.65",
		),
		// Priority Mail Flat Rate Medium Box
		"d17b"     => array(
			"retail" => "13.65",
			"online" => "13.65",
		),
		// Priority Mail Flat Rate Large Box
		"d22"     => array(
			"retail" => "18.90",
			"online" => "18.90",
		),

		// Priority Mail Flat Rate Large Box
		"d22a"     => array(
			"retail" => "18.90",
			"online" => "18.90",
		),
		// Priority Mail Flat Rate Small Box
		"d28"     => array(
			"retail" => "7.20",
			"online" => "7.20",
		),

	// Priority Mail Envelopes

		// Priority Mail Flat Rate Envelope
		"d16"     => array(
			"retail" => "6.70",
			"online" => "6.70",
		),
		// Priority Mail Padded Flat Rate Envelope
		"d29"     => array(
			"retail" => "7.25",
			"online" => "7.25",
		),
		// Priority Mail Gift Card Flat Rate Envelope
		"d38"     => array(
			"retail" => "6.70",
			"online" => "6.70",
		),
		// Priority Mail Window Flat Rate Envelope
		"d40"     => array(
			"retail" => "6.70",
			"online" => "6.70",
		),
		// Priority Mail Small Flat Rate Envelope
		"d42"     => array(
			"retail" => "6.70",
			"online" => "6.70",
		),
		// Priority Mail Legal Flat Rate Envelope
		"d44"     => array(
			"retail" => "7.00",
			"online" => "7.00",
		),

	// International Priority Mail Express

		// Priority Mail Express Flat Rate Envelope
		"i13"     => array(
			"retail"    => array(
				'*'  => "63.95",
				'CA' => "43.00"
			)
		),
		// Priority Mail Express Legal Flat Rate Envelope
		"i30"     => array(
			"retail"    => array(
				'*'  => "63.95",
				'CA' => "43.00"
			)
		),
		// Priority Mail Express Padded Flat Rate Envelope
		"i63"     => array(
			"retail"    => array(
				'*'  => "63.95",
				'CA' => "43.00"
			)
		),

	// International Priority Mail

		// Priority Mail Flat Rate Envelope
		"i8"      => array(
			"retail"    => array(
				'*'  => "35.25",
				'CA' => "24.95"
			)
		),
		// Priority Mail Padded Flat Rate Envelope
		"i29"      => array(
			"retail"    => array(
				'*'  => "35.25",
				'CA' => "24.95"
			)
		),
		// Priority Mail Flat Rate Small Box
		"i16"     => array(
			"retail"    => array(
				'*'  => "36.25",
				'CA' => "25.95"
			)
		),
		// Priority Mail Flat Rate Medium Box
		"i9"      => array(
			"retail"    => array(
				'*'  => "78.95",
				'CA' => "47.75"
			)
		),
		// Priority Mail Flat Rate Medium Box
		"i9b"      => array(
			"retail"    => array(
				'*'  => "78.95",
				'CA' => "47.75"
			)
		),
		// Priority Mail Flat Rate Large Box
		"i11"     => array(
			"retail"    => array(
				'*'  => "99.75",
				'CA' => "62.35"
			)
		),
) );
