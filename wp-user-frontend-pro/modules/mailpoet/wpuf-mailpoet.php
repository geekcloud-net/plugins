<?php
/*
Plugin Name: Mailpoet
Plugin URI: http://wedevs.com/
Thumbnail Name: wpuf-mailpoet.png
Description: Add subscribers to mailpoet mailing list when they registers via WP User Frontend Pro
Version: 0.1
Author: weDevs
Author URI: http://wedevs.com/
License: GPL2
*/

/**
 * Copyright (c) 2014 weDevs (email: info@wedevs.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WPUF_Mailpoet class
 *
 * @class WPUF_Mailpoet The class that holds the entire WPUF_Mailpoet plugin
 */
class WPUF_Mailpoet {

    /**
     * Constructor for the WPUF_Mailpoet class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses add_action()
     */
    public function __construct() {

        add_action( 'wpuf_profile_form_tab', array( $this, 'add_tab_profile_form') );
        add_action( 'wpuf_profile_form_tab_content', array( $this, 'add_tab_content_profile_form') );

        add_action( 'wpuf_after_register', array( $this, 'subscribe_user_after_registration'), 10, 3 );

        // Loads frontend scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

    }

    /**
     * Initializes the WPUF_Mailpoet() class
     *
     * Checks for an existing WPUF_Mailpoet() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new WPUF_Mailpoet();
        }

        return $instance;
    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_enqueue_style
     */
    public function enqueue_scripts() {

        /**
         * All styles goes here
         */
        wp_enqueue_style( 'wpuf-mc-styles', plugins_url( 'css/style.css', __FILE__ ), false, date( 'Ymd' ) );

        /**
         * All scripts goes here
         */
        wp_enqueue_script( 'wpuf-mc-scripts', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ), false, true );
    }


    /**
     * Add Mailpoet tab in Each form
     *
     * @return void
     */
    public function add_tab_profile_form() {
        ?>
            <a href="#wpuf-metabox-mailpoet" class="nav-tab" id="wpuf_mailpoet-tab"><?php _e( 'Mailpoet', 'wpuf-pro' ); ?></a>
        <?php
    }

    /**
     * Display settings option in tab content
     *
     * @return void
     */
    public function add_tab_content_profile_form() {
        ?>
        <div id="wpuf-metabox-mailpoet" class="group">
            <?php require_once dirname( __FILE__ ) . '/templates/mailpoet-settigs-tab.php'; ?>
        </div>
        <?php
    }

    /**
     * Send Subscribe request in Mailpoet
     *
     * @param  integer $user_id
     * @param  integer $form_id
     * @param  array $form_settings
     */
    public function subscribe_user_after_registration( $user_id, $form_id, $form_settings ) {

        if ( !class_exists( 'WYSIJA' ) ) {
            return;
        }

        if ( $form_settings['enable_mailpoet'] == 'no' ) {
            return;
        }

        $user = get_user_by( 'id', $user_id );

        // Populate data submitted.
        if ( $user->first_name && 'false' !== $user->first_name )
            $userData = array( 'email' => $user->user_email, 'firstname' => $user->first_name );
        else
            $userData = array( 'email' => $user->user_email );

        $data = array(
          'user'      => $userData,
          'user_list' => array( 'list_ids' => array( $form_settings['mailpoet_list'] ) )
        );

        // Add subscriber to MailPoet.
        $userHelper = WYSIJA::get( 'user', 'helper' );
        $userHelper->addSubscriber( $data );
    }

} // WPUF_Mailpoet

$baseplugin = WPUF_Mailpoet::init();