<?php
/*
Plugin Name: Blog Description on Blog Creation
Description: Allows new bloggers to be able to set their tagline when they create a blog in Multisite
Version: 1.1
Author: Aaron Edwards & Andrew Billits (Incsub)
Author URI: http://premium.wpmudev.org
Network: true
WDP ID: 104
Contributors - Umesh Kumar, Marcin Pietrzak
 */

/*
Copyright 2007-2017 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ub_signup_blog_description' ) ) {

	class ub_signup_blog_description extends ub_helper {

		public function __construct() {
			add_action( 'ultimatebranding_settings_multisite', array( $this, 'admin_options_page' ) );
			add_filter( 'add_signup_meta', array( $this, 'meta_filter' ) );
			add_filter( 'bp_signup_usermeta', array( $this, 'meta_filter' ) );
			add_action( 'signup_blogform', array( $this, 'signup_form' ) );
			add_action( 'bp_blog_details_fields', array( $this, 'signup_form' ) );
			add_filter( 'blog_template_exclude_settings', array( $this, 'nbt' ) );
			add_filter( 'wpmu_new_blog', array( $this, 'nbt' ) );
		}

		protected function set_options() {
			$this->options = array(
				'description' => array(
					'title' => __( 'Description', 'ub' ),
					'description' => __( 'There are no settings for this module. It simply added ability to setup site tagline during creation.', 'ub' ),
				),
			);
		}

		/**
		 * Save the blogdescription value in meta
		 * @param type $meta
		 * @return type $meta
		 */
		public function meta_filter( $meta ) {
			if ( ! empty( $_POST['blog_description'] ) ) {
				$meta['blogdescription'] = $_POST['blog_description'];
			}
			return $meta;
		}

		/**
		 * Exclude option from New Site Template plugin copy
		 * @param string $and
		 * @return string
		 */
		public function nbt( $and ) {
			$and .= " AND `option_name` != 'blogdescription'";
			return $and;
		}

		/**
		 * Adds an additional field for Blog description,
		 * on signup form for WordPress or Buddypress
		 * @param type $errors
		 */
		public function signup_form( $errors ) {
			if ( ! empty( $errors ) ) {
				$error = $errors->get_error_message( 'blog_description' );
			}
			$desc = isset( $_POST['blog_description'] ) ? esc_attr( $_POST['blog_description'] ) : '';
?>

        <label for="blog_description"><?php _e( 'Site Tagline', 'ub' ); ?>:</label>
        <input name="blog_description" type="text" id="blog_description" value="<?php echo $desc; ?>" autocomplete="off" maxlength="50" /><br />
        <?php _e( 'In a few words, explain what this site is about. Default will be used if left blank.', 'ub' ) ?>
<?php
		}
	}
}

new ub_signup_blog_description();