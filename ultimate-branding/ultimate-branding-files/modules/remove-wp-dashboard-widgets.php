<?php
/*
  Plugin Name: Remove WP Dashboard Widgets
  Description: Removes the wordpress dashboard widgets
  Author: Marko Miljus (Incsub), Barry (Incsub), Andrew Billits, Ulrich Sossou, Marcin Pietrzak (Incsub)

  Copyright 2007-2018 Incsub (http://incsub.com)

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


if ( ! class_exists( 'ub_rwpwidgets' ) ) {
	class ub_rwpwidgets {

		public function __construct() {
			add_action( 'wp_dashboard_setup', array( $this, 'remove_wp_dashboard_widgets' ), 99 );
			add_action( 'ultimatebranding_settings_widgets', array( $this, 'manage_output' ) );
			add_filter( 'ultimatebranding_settings_widgets_process', array( $this, 'process_save' ) );
			/**
			 * export
			 */
			add_filter( 'ultimate_branding_export_data', array( $this, 'export' ) );
		}

		public function process_save( $status ) {
			$active = array();
			if ( isset( $_POST['active'] ) ) {
				foreach ( (array) $_POST['active'] as $key => $value ) {
					if ( ! isset( $active[ $value ] ) ) {
						$active[ $value ] = $value;
					}
				}
			}
			ub_update_option( 'rwp_active_dashboard_widgets', $active );
			if ( $status === false ) {
				return $status;
			} else {
				return true;
			}
		}

		public function manage_output() {
			global $wpdb, $current_site, $page;
			global $wp_meta_boxes;
			$all_available_widgets = ub_get_option( 'ub_rwp_all_active_dashboard_widgets', array() );
			$detected_widgets = $this->detect();
			$available_widgets = $all_available_widgets + $detected_widgets;
			$active = ub_get_option( 'rwp_active_dashboard_widgets', array() );
?>
    <div class="postbox">
        <h2 class="hndle" style='cursor:auto;'><span><?php _e( 'Remove WordPress Dashboard Widgets ', 'ub' ); ?></span></h2>
        <div class="inside">
            <p class='description'><?php _e( 'Select which widgets you want to remove from all dashboards on your network from the list below. If you do not see a desired widget on this list, please visit Dashboard page and come back on this page.', 'ub' ); ?>
<?php
if ( empty( $available_widgets ) ) {
	echo wpautop( __( 'Currently there are no admin dashboard widgets to manage.', 'ub' ) );
	echo wpautop( __( 'Try to check on Admin Dashboard and back here.', 'ub' ) );
} else {
	foreach ( $available_widgets as $key => $title ) {
		echo '<ul class="availablewidgets">';
?>
	<li><label><input type='checkbox' name='active[]' value='<?php echo $key; ?>' <?php if ( in_array( $key, $active ) ) { echo "checked='checked'"; } ?> />&nbsp;<?php echo $this->remove_tags( $title ); ?></label></li>
<?php
	}
	echo '</ul>';
}
?>
        </div>
    </div>
<?php
		}

		/**
		 * Detect dashboard widgets
		 *
		 * @since 1.9.8
		 */
		private function detect() {
			global $wp_meta_boxes;
			/* Detect active widgets and save the array (only possible from the dashboard page) */
			$detected_widgets = array();
			if ( ! isset( $wp_meta_boxes['dashboard'] ) ) {
				return $detected_widgets;
			}
			$boxes = $wp_meta_boxes['dashboard'];
			$positions  = array( 'normal', 'side' );
			$priorities = array( 'core', 'low', 'high' );
			foreach ( $positions as $position ) {
				foreach ( $priorities as $priority ) {
					if ( isset( $boxes[ $position ][ $priority ] ) && is_array( $boxes[ $position ][ $priority ] ) ) {
						foreach ( array_keys( $boxes[ $position ][ $priority ] ) as $name ) {
							$detected_widgets[ $name ] = $boxes[ $position ][ $priority ][ $name ]['title'];
						}
					}
				}
			}
			return $detected_widgets;
		}

		public function remove_wp_dashboard_widgets() {
			$detected_widgets = $this->detect();
			ub_update_option( 'ub_rwp_all_active_dashboard_widgets', $detected_widgets );
			$active = ub_get_option( 'rwp_active_dashboard_widgets', array() );
			$contexts = array( 'normal', 'advanced', 'side' );
			foreach ( $active as $key => $value ) {
				foreach ( $contexts as $context ) {
					remove_meta_box( $key, 'dashboard', $context );
				}
			}
		}

		private function remove_tags( $string ) {
			// ----- remove HTML TAGs -----
			$string = preg_replace( '/<[^>]*>/', ' ', $string );
			// ----- remove control characters -----
			$string = str_replace( "\r", '', $string );    // --- replace with empty space
			$string = str_replace( "\n", ' ', $string );   // --- replace with space
			$string = str_replace( "\t", ' ', $string );   // --- replace with space
			// ----- remove multiple spaces -----
			$string = trim( preg_replace( '/ {2,}/', ' ', $string ) );
			return $string;
		}

		/**
		 * Export data.
		 *
		 * @since 1.8.6
		 */
		public function export( $data ) {
			$options = array(
				'rwp_active_dashboard_widgets',
				'ub_rwp_all_active_dashboard_widgets',
			);
			foreach ( $options as $key ) {
				$data['modules'][ $key ] = ub_get_option( $key );
			}
			return $data;
		}
	}
}
new ub_rwpwidgets();