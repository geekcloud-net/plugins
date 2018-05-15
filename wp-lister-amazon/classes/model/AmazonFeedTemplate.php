<?php
/**
 * WPLA_AmazonFeedTemplate class
 *
 */

// class WPLA_AmazonFeedTemplate extends WPLA_NewModel {
class WPLA_AmazonFeedTemplate {

	const TABLENAME = 'amazon_feed_templates';

	var $id;
	var $data;
	var $fieldnames;

	function __construct( $id = null ) {
		
		$this->init();

		if ( $id ) {

			$this->id = $id;
			
			// load data into object
			$feed_template = self::getFeedTemplate( $id );
			foreach( $feed_template AS $key => $value ){
			    $this->$key = $value;
			}

			return $this;

		} else {

			foreach( $this->fieldnames AS $key ){
			    $this->$key = null;
			}

		}

	}

	function init()	{

		$this->fieldnames = array(
			'id',
			'name',
			'title',
			'version',
			'site_id'
		);

	}

	// get single feed_template
	static function getFeedTemplate( $id )	{
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;
		
		$item = $wpdb->get_row( $wpdb->prepare("
			SELECT *
			FROM $table
			WHERE id = %d
		", $id
		), OBJECT);

		return $item;
	}

	// get all feed_templates
	static function getAll() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$items = $wpdb->get_results("
			SELECT *
			FROM $table
			ORDER BY title ASC
		", OBJECT_K);

		return $items;
	}

	// get all feed_tpl_data
	public function getFieldData() {
		global $wpdb;
		$data_table = $wpdb->prefix . 'amazon_feed_tpl_data';

		$items = $wpdb->get_results( $wpdb->prepare("
			SELECT *
			FROM $data_table
			WHERE tpl_id = %d
			ORDER BY id ASC
		", $this->id
		), ARRAY_A);

		return $items;
	}

	// get all field names from feed_tpl_data
	public function getFieldNames() {
		global $wpdb;
		$data_table = $wpdb->prefix . 'amazon_feed_tpl_data';

		$items = $wpdb->get_col( $wpdb->prepare("
			SELECT field
			FROM $data_table
			WHERE tpl_id = %d
			ORDER BY id ASC
		", $this->id ));

		return apply_filters( 'wpla_feed_template_column_names', $items, $this );
	}

	// get all feed_tpl_values
	public function getFieldValues() {
		global $wpdb;
		$data_table = $wpdb->prefix . 'amazon_feed_tpl_values';

		$items = $wpdb->get_results( $wpdb->prepare("
			SELECT *
			FROM $data_table
			WHERE tpl_id = %d
			ORDER BY id ASC
		", $this->id
		), ARRAY_A);

		// convert to assoc array with field names as keys
		$values = array();
		foreach ($items as $item) {
			if ( ! isset( $values[ $item['field'] ] ) ) {
				// default: simply add new field
				$values[ $item['field'] ] = $item;
			} else {
				// if field is already set, merge new values (for variation_theme, etc.)
				$existing_values = explode( '|', $values[ $item['field'] ]['values'] );
				// WPLA()->logger->info("existing values: ".print_r($existing_values,1));
				$new_values      = explode( '|', $item['values'] );
				// WPLA()->logger->info("adding values: ".print_r($new_values,1));
				$combined_values = array_unique(array_merge( $existing_values, $new_values ));
				// WPLA()->logger->info("combined values: ".print_r($combined_values,1));
				// store combined values
				$values[ $item['field'] ]['values'] = join( $combined_values, '|' );
			}
		}

		return $values;
	}


	// add feed_template
	function add() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;
		// echo "<pre>";print_r($this);echo"</pre>";die();

		$data = array();
		foreach ( $this->fieldnames as $key ) {
			if ( isset( $this->$key ) && ! is_null( $this->$key ) ) {
				$data[ $key ] = $this->$key;
			} 
		}

		if ( sizeof( $data ) > 0 ) {
			$result = $wpdb->insert( $table, $data );
			echo $wpdb->last_error;

			return $wpdb->insert_id;		
		}

	} // add()

	// update feed_template
	function update() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$data = array();
		foreach ( $this->fieldnames as $key ) {
			if ( isset( $this->$key ) && ! is_null( $this->$key ) ) {
				$data[ $key ] = $this->$key;
			} 
		}

		// check if MySQL server has gone away and reconnect if required - WP 3.9+
		if ( method_exists( $wpdb, 'check_connection') ) $wpdb->check_connection();
		

		if ( sizeof( $data ) > 0 ) {
			$result = $wpdb->update( $table, $data, array( 'id' => $this->id ) );
			echo $wpdb->last_error;
		}

	} // update()



	// populate feed_template fields from data array
	function fillFromArray( $data ) {

		foreach ( $this->fieldnames as $key ) {
			if ( isset( $data[$key] ) ) {
				$this->$key = $data[ $key ];
			} 
		}

	} // fillFromArray()


	function delete() {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		if ( ! $this->id ) return;

		$wpdb->delete( $table, array( 'id' => $this->id ), array( '%d' ) );
		echo $wpdb->last_error;

	} // delete()


	// can be removed...
	function getPageItems( $current_page, $per_page ) {
		global $wpdb;
		$table = $wpdb->prefix . self::TABLENAME;

		$orderby  = (!empty($_REQUEST['orderby'])) ? esc_sql( $_REQUEST['orderby'] ) : 'name'; //If no sort, default to title
		$order    = (!empty($_REQUEST['order']))   ? esc_sql( $_REQUEST['order']   ) : 'asc'; //If no order, default to asc
		$offset   = ( $current_page - 1 ) * $per_page;
		$per_page = esc_sql( $per_page );

        // get items
		$items = $wpdb->get_results("
			SELECT *
			FROM $table
			ORDER BY $orderby $order
            LIMIT $offset, $per_page
		", ARRAY_A);

		// get total items count - if needed
		if ( ( $current_page == 1 ) && ( count( $items ) < $per_page ) ) {
			$this->total_items = count( $items );
		} else {
			$this->total_items = $wpdb->get_var("
				SELECT COUNT(*)
				FROM $table
				ORDER BY $orderby $order
			");			
		}

		return $items;
	} // getPageItems()


} // WPLA_AmazonFeedTemplate()


