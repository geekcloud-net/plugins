<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * DLM_CI_Import_Version
 */
class DLM_CI_Import_Version {

	/**
	 * @var string
	 */
	private $version = '1.0';

	/**
	 * @var int
	 */
	private $download_count = 0;

	/**
	 * @var DateTime
	 */
	private $date = null;

	/**
	 * @var string
	 */
	private $url = '';

	/** @var int  */
	private $order = 0;

	/**
	 * Construct. Set now as date.
	 *
	 * @param String $url
	 */
	public function __construct( $url ) {
		$this->date = new DateTime(  current_time( 'mysql' ) );
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * @param string $version
	 */
	public function set_version( $version ) {
		$this->version = $version;
	}

	/**
	 * @return int
	 */
	public function get_download_count() {
		return $this->download_count;
	}

	/**
	 * @param int $download_count
	 */
	public function set_download_count( $download_count ) {
		$this->download_count = $download_count;
	}

	/**
	 * @return null
	 */
	public function get_date() {
		return $this->date;
	}

	/**
	 * @param null $date
	 */
	public function set_date( $date ) {
		$this->date = $date;
	}

	/**
	 * @return string
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * @param string $url
	 */
	public function set_url( $url ) {
		$this->url = $url;
	}

	/**
	 * @return int
	 */
	public function get_order() {
		return $this->order;
	}

	/**
	 * @param int $order
	 */
	public function set_order( $order ) {
		$this->order = $order;
	}

}