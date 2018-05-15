<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * PT: dlm_download
 */
class DLM_CI_Import_Download {

	/** @var int */
	private $id = 0;

	/**
	 * @var string
	 */
	private $title = '';

	/**
	 * @var string
	 */
	private $content = '';

	/**
	 * @var string
	 */
	private $short_description = '';

	/**
	 * @var array<String>
	 */
	private $categories = array();

	/**
	 * @var array<String>
	 */
	private $tags = array();

	/**
	 * @var array<DLM_CI_Import_Version>
	 */
	private $versions = array();

	/**
	 * @var bool
	 */
	private $featured = false;

	/**
	 * @var bool
	 */
	private $members_only = false;

	/**
	 * @var bool
	 */
	private $redirect = false;

	/**
	 * @var array
	 */
	private $meta = array();

	/**
	 * @param String $title
	 * @param String $content
	 * @param String $short_description
	 * @param array $categories
	 * @param array $tags
	 */
	function __construct( $title, $content, $short_description, $categories, $tags ) {
		$this->title             = $title;
		$this->content           = $content;
		$this->short_description = $short_description;
		$this->categories        = $categories;
		$this->tags              = $tags;
	}

	/**
	 * @return int
	 */
	public function get_id() {
		return absint( $this->id );
	}

	/**
	 * @param int $id
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function get_content() {
		return $this->content;
	}

	/**
	 * @param string $content
	 */
	public function set_content( $content ) {
		$this->content = $content;
	}

	/**
	 * @return string
	 */
	public function get_short_description() {
		return $this->short_description;
	}

	/**
	 * @param string $short_description
	 */
	public function set_short_description( $short_description ) {
		$this->short_description = $short_description;
	}

	/**
	 * @return array
	 */
	public function get_categories() {
		return $this->categories;
	}

	/**
	 * @param array $categories
	 */
	public function set_categories( $categories ) {
		$this->categories = $categories;
	}

	/**
	 * @return array
	 */
	public function get_tags() {
		return $this->tags;
	}

	/**
	 * @param array $tags
	 */
	public function set_tags( $tags ) {
		$this->tags = $tags;
	}

	/**
	 * @return array
	 */
	public function get_versions() {
		return $this->versions;
	}

	/**
	 * @param array $versions
	 */
	public function set_versions( $versions ) {
		$this->versions = $versions;
	}

	/**
	 * Add version to download
	 *
	 * @param DLM_CI_Import_Version $version
	 */
	public function add_version( $version ) {
		$this->versions[] = $version;
	}

	/**
	 * @return boolean
	 */
	public function is_featured() {
		return $this->featured;
	}

	/**
	 * @param boolean $featured
	 */
	public function set_featured( $featured ) {
		$this->featured = $featured;
	}

	/**
	 * @return boolean
	 */
	public function is_members_only() {
		return $this->members_only;
	}

	/**
	 * @param boolean $members_only
	 */
	public function set_members_only( $members_only ) {
		$this->members_only = $members_only;
	}

	/**
	 * @return boolean
	 */
	public function is_redirect() {
		return $this->redirect;
	}

	/**
	 * @param boolean $redirect
	 */
	public function set_redirect( $redirect ) {
		$this->redirect = $redirect;
	}

	/**
	 * @return array
	 */
	public function get_meta() {
		return $this->meta;
	}

	/**
	 * @param array $meta
	 */
	public function set_meta( $meta ) {
		$this->meta = $meta;
	}

	/**
	 * @param string $meta_key
	 * @param string $meta_value
	 */
	public function add_meta( $meta_key, $meta_value ) {
		$this->meta[ $meta_key ] = $meta_value;
	}

}