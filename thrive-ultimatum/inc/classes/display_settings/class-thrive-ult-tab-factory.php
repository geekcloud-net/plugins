<?php

/**
 * Class Thrive_Ult_Tab_Factory
 * Based on $type a specific tab object is returned
 */
class Thrive_Ult_Tab_Factory {
	public static function build( $type ) {
		$class  = "Thrive_Ult_";
		$chunks = explode( "_", $type );
		foreach ( $chunks as $chunk ) {
			$class .= ucfirst( $chunk ) . "_";
		}
		$class .= "Tab";

		return new $class;
	}
}
