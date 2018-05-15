<?php
/*
Class Name: UB_Module_List_Table
Class URI: http://iworks.pl/
Description: UB Modules table.
Version: 1.0.0
Author: Marcin Pietrzak
Author URI: http://iworks.pl/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Copyright 2018 Incsub (http://incsub.com)

this program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class UB_Module_List_Table extends WP_List_Table {

	private $modules;
	private $totals = array(
		'all' => 0,
		'active' => 0,
		'inactive' => 0,
	);
	private $configuration;

	public function __construct( $args = array() ) {
		global $status;
		parent::__construct( array(
			'singular' => 'module',
			'plural' => 'modules',
			'screen' => isset( $args['screen'] ) ? $args['screen'] : null,
			'ajax' => true,
		) );
		$status = 'all';
		if ( isset( $_REQUEST['module_status'] ) && in_array( $_REQUEST['module_status'], array( 'active', 'inactive' ) ) ) {
			$status = $_REQUEST['module_status'];
		}
		ub_enqueue_switch_button();
	}

	public function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'title' => __( 'Module Name', 'ub' ),
			'status' => __( 'Status', 'ub' ),
		);
		return $columns;
	}

	public function prepare_items() {
		$columns = $this->get_columns();
		$this->_column_headers = array( $columns, array(), array() );
		$this->configuration = ub_get_modules_list();
		/**
		 * set fake navigation
		 */
		$total_items = $per_page = $this->totals['all'];
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}

	public function set_modules( $modules ) {
		global $status;
		$search = ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : false;
		$this->process_bulk_action();
		/**
		 * Do not translate this table, it have file placeholders to read
		 */
		$default_headers = array(
			'author_uri' => 'Author URI',
			'author' => 'Author',
			'description' => 'Description',
			'name' => 'Plugin Name',
		);
		$this->modules = $modules;
		$this->items = array();
		/**
		 * prepare search
		 */
		if ( $search ) {
			$search = stripslashes( $search );
			$search = sprintf( '/%s/i', preg_replace( '@/@', '\\/', $search ) );
		}
		foreach ( $this->modules as $module => $plugin ) {
			$file = ub_files_dir( 'modules/' . $module );
			if ( ! is_file( $file ) || ! is_readable( $file ) ) {
				continue;
			}
			$this->totals['all']++;
			$module_data = get_file_data( $file, $default_headers, 'plugin' );
			$module_data['module'] = $module;
			$module_data['plugin'] = $plugin;
			$module_data['is_active'] = ub_is_active_module( $module );
			if ( $module_data['is_active'] ) {
				$this->totals['active']++;
			} else {
				$this->totals['inactive']++;
			}
			if (
				'all' === $status
				|| ( 'active' === $status && $module_data['is_active'] )
				|| ( 'inactive' === $status && ! $module_data['is_active'] )
			) {
				if ( $search ) {
					if (
						preg_match( $search, $module_data['name'] )
						|| preg_match( $search, $module_data['description'] )
						|| preg_match( $search, $module_data['author'] )
						|| preg_match( $search, $module_data['author_uri'] )
					) {
						$this->items[ $module_data['name'] ] = $module_data;
					}
				} else {
					$this->items[ $module_data['name'] ] = $module_data;
				}
			}
		}
		ksort( $this->items );
	}

	public function column_title( $item ) {
		$content = '<strong>';
		$content .= $item['name'];
		$content .= '</strong>';
		$content .= sprintf(
			'<p class="Description">%s</p>',
			$item['description']
		);
		return $content;
	}

	public function column_cb( $item ) {
		$replace_by = $this->check_replaced_by( $item );
		if ( $replace_by ) {
			return '&nbsp;';
		}
		return sprintf( '<input type="checkbox" name="module[]" value="%s" />', esc_attr( $item['module'] ) );
	}

	private function check_replaced_by( $item ) {
		$key = $item['module'];
		if (
			isset( $this->configuration[ $key ] )
			&& isset( $this->configuration[ $key ]['replaced_by'] )
		) {
			$replace_by = $this->configuration[ $key ]['replaced_by'];
			$is_active = ub_is_active_module( $replace_by );
			if ( $is_active ) {
				return true;
			}
		}
		return false;
	}

	public function column_status( $item ) {
		/**
		 * check replaced by module
		 */
		$replace_by = $this->check_replaced_by( $item );
		if ( $replace_by ) {
			$content = sprintf(
				__( 'Module "%s" can not be activated, becouse module "%s" is active.', 'ub' ),
				$item['name'],
				$this->configuration[ $this->configuration[ $item['module'] ]['replaced_by'] ]['title']
			);
			return $content;
		}
		$content = sprintf(
			'<input type="checkbox" name="status[]" value="%s" %s class="switch-button" data-nonce="%s" />',
			esc_attr( $item['module'] ),
			checked( $item['is_active'], true, false ),
			wp_create_nonce( $item['module'] )
		);
		return $content;
	}

	public function get_bulk_actions() {
		$actions = array(
			'activate' => _x( 'Activate', 'Bulk action to activate modules', 'ub' ),
			'deactivate' => _x( 'Deactivate', 'Bulk action to deactivate modules', 'ub' ),
		);
		return $actions;
	}

	/**
	 *
	 * @global array $totals
	 * @global string $status
	 * @return array
	 */
	protected function get_views() {
		global $status;
		$status_links = array();
		$status_links = array();
		foreach ( $this->totals as $type => $count ) {
			if ( ! $count ) {
				continue;
			}
			switch ( $type ) {
				case 'all':
					$text = _nx( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $count, 'ub' );
				break;
				case 'active':
					$text = _n( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', $count, 'ub' );
				break;
				case 'inactive':
					$text = _n( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', $count, 'ub' );
				break;
			}
			$status_links[ $type ] = sprintf( "<a href='%s'%s>%s</a>",
				add_query_arg( 'module_status', $type ),
				( $type === $status ) ? ' class="current" aria-current="page"' : '',
				sprintf( $text, number_format_i18n( $count ) )
			);
		}
		return $status_links;
	}
}
