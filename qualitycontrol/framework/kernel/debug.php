<?php
/**
 * Framework debugging
 *
 * @package Framework\Debug
 */

add_action( 'all', 'appthemes_bad_method_visibility' );

/**
 * Prints warning about any hooked method with bad visibility (that are either protected or private)
 * @return void
 */
function appthemes_bad_method_visibility(){
	global $wp_filter;

	$arguments = func_get_args();
	$tag = array_shift( $arguments );

	$errors = new WP_Error;
	if( ! isset( $wp_filter[ $tag ] ) )
		return;

	foreach( $wp_filter[ $tag ] as $prioritized_callbacks ){
		foreach( $prioritized_callbacks as $callback ){
			$function = $callback['function'];
			if( is_array( $function ) ){
				$method = new ReflectionMethod( $function[0], $function[1] );
				if( $method->isPrivate() || $method->isProtected() ){
					$class = get_class( $function[ 0 ] );
					if( ! $class )
						$class = $function[ 0 ];

					$errors->add( 'visiblity', $class . '::' . $function[1] . ' was hooked into "'. $tag .'", but is either protected or private.' );
				}
			}

		}
	}

	if( $errors->get_error_messages() ){
		foreach( $errors->get_error_messages() as $message )
			echo $message;
	}
}
