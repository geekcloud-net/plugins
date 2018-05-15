<?php

/*
 * This file belongs to the YITH Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'YITH_COG_PATH' ) ) {
    exit( 'Direct access forbidden.' );
}

/**
 * @class      YITH_COG_Admin
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     Francisco Mendoza
 *
 */
if ( ! class_exists( 'YITH_COG_Admin' ) ) {
    /**
     * Class YITH_COG_Admin
     *
     * @author
     */
    class YITH_COG_Admin {

        /**
         * Main Instance
         *
         * @var YITH_COG_Admin
         * @since 1.0
         */
        protected static $_instance = null;

        public $options = null;
        protected $_panel = null;
        protected $_panel_page = 'fm_my_admin_users_options';
        protected $_main_panel_option;

        /**
         * Construct
         *
         * @since 1.0
         */
        public function __construct(){}

        /**
         * Main plugin Instance
         * @return stdClass
         * @var YITH_COG_Admin instance
         * @author
         */
        public static function get_instance()
        {
            $self = __CLASS__ . (class_exists(__CLASS__ . '_Premium') ? '_Premium' : '');

            if (is_null($self::$_instance)) {
                $self::$_instance = new $self;
            }
            return $self::$_instance;
        }


    }
}



















