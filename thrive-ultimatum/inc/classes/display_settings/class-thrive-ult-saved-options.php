<?php

class Thrive_Ult_Saved_Options {
	private $table_name = 'settings_templates';
	private $db;
	private $name;
	private $description;
	public $show_options;
	public $hide_options;

	public function __construct( $name = '', $show_options = '', $hide_options = '', $description = '' ) {
		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;
		$this->db           = $wpdb;
		$this->table_name   = tve_ult_table_name( $this->table_name );
		$this->name         = $name;
		$this->description  = $description;
		$this->show_options = $show_options;
		$this->hide_options = $hide_options;
	}

	protected function _processPreSave( $jsonOptions ) {
		$options = @json_decode( stripcslashes( $jsonOptions ), true );
		if ( empty( $options ) || empty( $options['tabs'] ) ) {
			return json_encode( array( 'identifier' => $jsonOptions['identifier'] ) );
		}

		$clean_options = array();

		foreach ( $options['tabs'] as $index => $tabOptions ) {
			$clean_options['tabs'][ $index ]['options'] = $tabOptions;
		}

		return json_encode( $clean_options );
	}

	public function save() {
		$this->delete();
		$this->db->suppress_errors();

		$show_options = $this->_processPreSave( $this->show_options );
		$hide_options = $this->_processPreSave( $this->hide_options );

		return $this->db->insert( $this->table_name, array(
			'name'         => $this->name,
			'description'  => $this->description,
			'show_options' => $show_options,
			'hide_options' => $hide_options
		) ) !== false ? true : $this->db->last_error;
	}

	public function delete() {
		$this->db->delete( $this->table_name, array( 'name' => $this->name ) );
	}

	/**
	 * Read options from database
	 * @return $this
	 */
	public function initOptions( $byId = false, $from_data = null ) {
		if ( $from_data !== null ) {
			$row = $from_data;
		} else {
			$where = $byId === false ? "name = '{$this->name}'" : "id = {$byId}";
			$sql   = "SELECT * FROM {$this->table_name} WHERE {$where}";
			$row   = $this->db->get_row( $sql );
		}
		if ( $row ) {
			$this->show_options = $row->show_options;
			$this->hide_options = $row->hide_options;
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function getShowGroupOptions() {
		return $this->show_options;
	}

	/**
	 * @return string
	 */
	public function getHideGroupOptions() {
		return $this->hide_options;
	}

	public function getAll() {
		$sql     = "SELECT * FROM {$this->table_name} ORDER BY name";
		$results = $this->db->get_results( $sql );

		return $results;
	}
}
