<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TD_DB_Migration {

	/**
	 * @var array
	 */
	protected $_queries = array();

	/**
	 * @var string
	 */
	protected $_table_prefix;

	/**
	 * @var wpdb
	 */
	protected $_wpdb;

	/**
	 * TD_DB_Migration constructor.
	 *
	 * Each migration works with prefixed tables
	 *
	 * @param $table_prefix string plugin prefix
	 */
	public function __construct( $table_prefix ) {

		global $wpdb;

		$this->_wpdb         = $wpdb;
		$this->_table_prefix = $this->_wpdb->prefix . trim( $table_prefix, '_ ' ) . '_';
	}

	/**
	 * Based on the prefix sent on initialization returns the name of the table
	 * with {wp_prefix}_{plugin_prefix}_{name}
	 *
	 * @param $name
	 *
	 * @return string
	 */
	public function get_table_name( $name ) {

		return $this->_table_prefix . $name;
	}

	/**
	 * Adds and sql query to the queue for later execution
	 *
	 * @param $sql
	 */
	public function add( $sql, $collate = true ) {

		$this->_queries[] = trim( $sql ) . ( $collate ? ' ' . $this->_wpdb->get_charset_collate() : '' );
	}

	/**
	 * Loops through the queries and executes them
	 *
	 * @return bool
	 */
	public function run() {

		$success = true;

		if ( defined( 'THRIVE_DB_UPGRADING' ) === false ) {
			$this->_wpdb->last_error = 'Cannot run migrations outside of Database Manager';
			$success                 = false;
		}

		foreach ( $this->_queries as $query ) {
			if ( $success && $this->_wpdb->query( $query ) === false ) {
				$success = false;
				break;
			}
		}

		return $success;
	}
}
