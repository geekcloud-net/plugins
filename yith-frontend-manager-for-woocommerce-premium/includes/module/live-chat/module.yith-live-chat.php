<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * @class      YITH_Frontend_Manager_Live_Chat
 * @package    Yithemes
 * @since      Version 1.2.2
 * @author     Your Inspiration Themes
 *
 */
if ( ! class_exists( 'YITH_Frontend_Manager_Live_Chat' ) ) {

	/**
	 * YITH_Frontend_Manager_Live_Chat Class
	 */
	class YITH_Frontend_Manager_Live_Chat {

		/**
		 * Main instance
		 */
		private static $_instance = null;

		/**
		 * Main plugin Instance
		 *
		 * @static
		 * @return YITH_Frontend_Manager_Live_Chat Main instance
		 *
		 * @since  1.2.2
		 * @author Alberto Ruggiero
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Construct
		 */
		public function __construct() {
			
		}

	}
}

/**
 * Main instance of plugin
 *
 * @return /YITH_Frontend_Manager_Live_Chat
 * @since  1.2.2
 * @author Alberto Ruggiero
 */
if ( ! function_exists( 'YITH_Frontend_Manager_Live_Chat' ) ) {
	function YITH_Frontend_Manager_Live_Chat() {
		return YITH_Frontend_Manager_Live_Chat::instance();
	}
}