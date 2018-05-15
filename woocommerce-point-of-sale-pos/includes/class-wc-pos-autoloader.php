<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_POS Autoloader
 *
 * @class 		WC_POS_Autoloader
 * @package		WC_POS/Classes/
 * @category	Class
 * @since       3.0.5
 */
class WC_POS_Autoloader {

	/**
	 * Path to the includes directory
	 * @var string
	 */
	private $include_path = '';

	/**
	 * The Constructor
	 */
	public function __construct() {
		if ( function_exists( "__autoload" ) ) {
			spl_autoload_register( "__autoload" );
		}

		spl_autoload_register( array( $this, 'autoload' ) );

		$this->include_path = untrailingslashit( plugin_dir_path( WC_POS_FILE ) ) . '/includes/';
	}

	/**
	 * Take a class name and turn it into a file name
	 * @param  string $class
	 * @return string
	 */
	private function get_file_name_from_class( $class ) {
		return 'class-' . str_replace( '_', '-', $class ) . '.php';
	}

	/**
	 * Include a class file
	 * @param  string $path
	 * @return bool successful or not
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			include_once( $path );
			return true;
		}
		return false;
	}

	/**
	 * Auto-load WC_POS classes on demand to reduce memory consumption.
	 *
	 * @param string $class
	 */
	public function autoload( $class ) {
		$class = strtolower( $class );
		$file  = $this->get_file_name_from_class( $class );
		$path  = '';

		if ( strpos( $class, 'wc_pos_screen' ) === 0 ) {
			$path = $this->include_path . 'screen/';
		}else if ( strpos( $class, 'wc_pos_table' ) === 0 ) {
			$path = $this->include_path . 'tables/';
		} elseif ( strpos( $class, 'wc_pos_meta_box' ) === 0 ) {
			$path = $this->include_path . 'admin/meta-boxes/';
		} elseif ( strpos( $class, 'wc_pos_admin' ) === 0 ) {
			$path = $this->include_path . 'admin/';
		}

		if ( empty( $path ) || ( ! $this->load_file( $path . $file ) && strpos( $class, 'wc_pos_' ) === 0 ) ) {
			$this->load_file( $this->include_path . $file );
		}
	}
}

new WC_POS_Autoloader();
