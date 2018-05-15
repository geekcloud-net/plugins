<?php

class Test extends PHPUnit_Framework_TestCase {
	/** @test */
	public function it_adds_shipping_method_to_shipping_methods_array()
	{
		$shipping = new Yoast_WCSEO_Local_Shipping();

		$return = $shipping->add_shipping_method( array() );
		$this->assertContains( 'Yoast_WCSEO_Local_Shipping_Method', $return );
	}
}
