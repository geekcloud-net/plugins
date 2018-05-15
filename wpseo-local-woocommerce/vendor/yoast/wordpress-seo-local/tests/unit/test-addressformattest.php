<?php
/**
 * @package WPSEO_Local\Tests
 */

/**
 * Class AddressFormatTest
 *
 * This class contains all the tests regarding to the address formats.
 *
 * @since 3.3.1
 */
class AddressFormatTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var array $address_formats Contains all the possible address formats that can be chosen in the options.
	 */
	public $address_formats = array(
		'address-state-postal',
		'address-state-postal-comma',
		'address-postal-city-state',
		'address-postal',
		'address-postal-comma',
		'address-city',
		'postal-address',
	);

	/** @test */
	public function multiple_line_no_state_non_escaped_with_tags() {
		$street        = 'Steenuilstraat 25';
		$one_line      = false;
		$zipcode       = '7523 BP';
		$city          = 'Enschede';
		$state         = '';
		$show_state    = false;
		$escape_output = false;
		$use_tags      = true;

		$format = new WPSEO_Local_Address_Format();
		$formatted = $format->get_address_format( 'address-postal-city-state', array(
			'business_address'  => $street,
			'oneline'          => $one_line,
			'business_zipcode' => $zipcode,
			'business_city'    => $city,
			'business_state'   => $state,
			'show_state'       => $show_state,
			'escape_output'    => $escape_output,
			'use_tags'         => $use_tags,
		) );

		$expected  = '<div class="street-address">Steenuilstraat 25</div><span class="postal-code">7523 BP</span><span class="locality"> Enschede</span>';

		$this->assertEquals( $expected, $formatted );
	}

	/** @test */
	public function multiple_line_with_state_non_escaped_with_tags() {
		$street        = 'Steenuilstraat 25';
		$one_line      = false;
		$zipcode       = '7523 BP';
		$city          = 'Enschede';
		$state         = 'Overijssel';
		$show_state    = true;
		$escape_output = false;
		$use_tags      = true;

		$format = new WPSEO_Local_Address_Format();
		$formatted = $format->get_address_format( 'address-postal-city-state', array(
			'business_address'  => $street,
			'oneline'          => $one_line,
			'business_zipcode' => $zipcode,
			'business_city'    => $city,
			'business_state'   => $state,
			'show_state'       => $show_state,
			'escape_output'    => $escape_output,
			'use_tags'         => $use_tags,
		) );

		$expected  = '<div class="street-address">Steenuilstraat 25</div><span class="postal-code">7523 BP</span><span class="locality"> Enschede</span>, <span  class="region">Overijssel</span>';

		$this->assertEquals( $expected, $formatted );
	}

	/** @test */
	public function one_line_doesnt_contain_double_commas() {
		$street        = '';
		$one_line      = true;
		$zipcode       = '';
		$city          = '';
		$state         = '';
		$show_state    = true;
		$escape_output = false;
		$use_tags      = true;

		foreach ( $this->address_formats as $address_format ) {
			$format = new WPSEO_Local_Address_Format();
			$formatted = $format->get_address_format( $address_format, array(
				'business_address'  => $street,
				'oneline'          => $one_line,
				'business_zipcode' => $zipcode,
				'business_city'    => $city,
				'business_state'   => $state,
				'show_state'       => $show_state,
				'escape_output'    => $escape_output,
				'use_tags'         => $use_tags,
			) );

			// Strip down all spaces.
			$formatted = str_replace( ' ', '', $formatted );

			$this->assertFalse( strstr( $formatted, ',,' ) );
		}
	}

	/** @test */
	public function one_line_address_doesnt_contain_leading_spaces() {
		$street        = '';
		$one_line      = true;
		$zipcode       = '';
		$city          = '';
		$state         = '';
		$show_state    = true;
		$escape_output = false;
		$use_tags      = true;

		foreach ( $this->address_formats as $address_format ) {
			$format    = new WPSEO_Local_Address_Format();
			$formatted = $format->get_address_format( $address_format, array(
				'business_address' => $street,
				'oneline'          => $one_line,
				'business_zipcode' => $zipcode,
				'business_city'    => $city,
				'business_state'   => $state,
				'show_state'       => $show_state,
				'escape_output'    => $escape_output,
				'use_tags'         => $use_tags,
			) );

			$this->assertFalse( strstr( $formatted, ' ,' ) );
		}
	}
}

/**
 * Global functions that need to be mocked.
 *
 * @param string $string The string to be escaped.
 *
 * @return mixed
 */
function esc_html( $string ) {
	return $string;
}
