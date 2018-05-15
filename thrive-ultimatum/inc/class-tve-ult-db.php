<?php
/**
 * Handles database operations
 */

/**
 * Global instance to be used allover
 */
global $tve_ult_db;

/**
 * Encapsulates the global $wpdb object
 *
 * Class Thrive_Ultimatum_DB
 */
class Tve_Ult_Db {
	/**
	 * @var $wpdb wpdb
	 */
	protected $wpdb = null;

	/**
	 * Class constructor
	 */
	public function __construct() {
		global $wpdb;

		$this->wpdb = $wpdb;
	}

	/**
	 * get the ID from the $object
	 * If $object is scalar, it will be returned
	 * If object, it assumes a WP_Post object
	 * If array it will return the id field
	 *
	 * @param array|WP_Post|int|string $object
	 * @param string $id_field
	 *
	 * @return int it will always return an integer (if no ID found, it will return zero)
	 */
	protected function _object_id( $object, $id_field = 'ID' ) {
		if ( is_scalar( $object ) ) {
			return (int) $object;
		}
		if ( is_array( $object ) ) {
			return isset( $object[ $id_field ] ) ? $object[ $id_field ] : 0;
		}
		if ( is_object( $object ) ) {
			return isset( $object->{$id_field} ) ? $object->{$id_field} : 0;
		}

		return 0;
	}

	/**
	 * Shorthand method for tve_ult_table_name
	 *
	 * @see tve_ult_table_name
	 *
	 * @param string $table
	 *
	 * @return string
	 */
	protected function t( $table ) {
		return tve_ult_table_name( $table );
	}

	/**
	 * Unserialize fields from an array
	 *
	 * @param $array array where to search the fields
	 * @param $fields array fields to be unserialized
	 *
	 * @return array modified, containing the unserialized fields
	 */
	protected function _unserialize_fields( $array, $fields = array() ) {

		foreach ( $fields as $field ) {
			/* the serialized fields should be trigger_config and tcb_fields */
			$array[ $field ] = empty( $array[ $field ] ) ? array() : unserialize( $array[ $field ] );
			$array[ $field ] = wp_unslash( $array[ $field ] );

			/* extra checks to ensure we'll have consistency */
			if ( ! is_array( $array[ $field ] ) ) {
				$array[ $field ] = array();
			}
		}

		return $array;
	}

	/**
	 * assign all fields from the fields array to the main array
	 *
	 * @param array $array
	 * @param array $fields
	 *
	 * @return array
	 */
	protected function _assign_fields( $array, $fields = array() ) {

		foreach ( $fields as $field ) {
			if ( ! isset( $array[ $field ] ) || ! is_array( $array[ $field ] ) ) {
				continue;
			}
			/**
			 * assign each field from the $fields in the main item array, so they can be accessed directly
			 */
			foreach ( $array[ $field ] as $k => $v ) {
				if ( ! isset( $array[ $k ] ) ) {
					$array[ $k ] = $v;
				}
			}
		}

		return $array;
	}

	/**
	 * Wrapper over wpdb prepare() function
	 *
	 * @param $sql string
	 * @param $params array
	 *
	 * @return string SQL query to be ready to be executed
	 */
	public function prepare( $sql, $params = array() ) {
		$prefix = tve_ult_table_name( '' );
		$sql    = preg_replace( '/\{(.+?)\}/', '`' . $prefix . '$1' . '`', $sql );

		if ( strpos( $sql, '%' ) === false ) {
			return $sql;
		}

		return $this->wpdb->prepare( $sql, $params );
	}

	/**
	 * Inserts a new design row or updates an existing one based on $data param
	 *
	 * @param $data
	 *
	 * @return false|int the id of the new or existing design
	 */
	public function save_design( $data ) {
		$columns = array(
			'id',
			'post_parent',
			'post_status',
			'post_type',
			'post_title',
			'content',
			'tcb_fields',
			'parent_id',
		);

		/** removed non column keys */
		foreach ( $data as $key => $value ) {
			if ( ! in_array( $key, $columns ) ) {
				unset( $data[ $key ] );
			}
		}

		if ( ! isset( $data['tcb_fields'] ) || ! is_array( $data['tcb_fields'] ) ) {
			$data['tcb_fields'] = array();
		}

		$data['tcb_fields'] = serialize( $data['tcb_fields'] );
		if ( empty( $data['content'] ) ) {
			$data['content'] = '';
		}
		$data['content'] = wp_unslash( $data['content'] );

		if ( empty( $data['id'] ) ) {
			$result = $this->wpdb->insert( tve_ult_table_name( 'designs' ), $data );

			return $result !== false ? $this->wpdb->insert_id : false;
		}

		$results = $this->wpdb->update( tve_ult_table_name( 'designs' ), $data, array( 'id' => $data['id'] ) );

		return $results !== false ? $data['id'] : false;
	}

	/**
	 * Returns an array with designs from DB based on $filters
	 * This can be used for counting
	 *
	 * @param array $filters
	 *          post_parent int
	 *          post_status string
	 *          order string "{column_name} {DESC|ASC}"
	 *          offset integer used for limit; where to start
	 *          limit int max number of returned rows
	 * @param bool $return_count
	 *
	 * @return array|string
	 */
	public function get_designs( $filters = array(), $return_count = false ) {
		$select = $return_count ? 'COUNT( `id` )' : '*';
		$sql    = 'SELECT ' . $select . ' FROM {designs} WHERE 1';
		$params = array();

		if ( ! empty( $filters['post_parent'] ) ) {
			$sql .= ' AND `post_parent` = %d';
			$params [] = $filters['post_parent'];
		}

		if ( ! empty( $filters['post_status'] ) ) {
			if ( ! is_array( $filters['post_status'] ) ) {
				$filters['post_status'] = array( $filters['post_status'] );
			}
			$sql .= ' AND ( ';
			foreach ( $filters['post_status'] as $post_status ) {
				$sql .= isset( $first ) ? ' OR ' : '';
				$sql .= '`post_status` = %s';
				$params [] = $post_status;
				$first     = true;
			}
			$sql .= ' )';
		}

		if ( isset( $filters['parent_id'] ) ) {
			$sql .= ' AND parent_id = %d';
			$params [] = $filters['parent_id'];
		}

		if ( ! empty( $filters['order'] ) ) {
			list( $col, $dir ) = explode( ' ', $filters['order'] );
			if ( strpos( $col, '.' ) ) {
				list( $table, $col ) = explode( '.', $col );
				$table = $table ? '`' . str_replace( '`', '', '{' . $table . '}' ) . '`' : '`' . tve_ult_table_name( 'designs' ) . '`';
			} else {
				$table = tve_ult_table_name( 'designs' );
			}
			$col = '`' . str_replace( '`', '', $col ) . '`';
			$sql .= " ORDER BY {$table}.{$col} {$dir}";
		}

		if ( ! empty( $filters['limit'] ) ) {
			$sql .= ' LIMIT ' . ( ! empty( $filters['offset'] ) ? intval( $filters['offset'] ) . ',' : '' );
			$sql .= intval( $filters['limit'] );
		}

		if ( $return_count ) {
			return $this->wpdb->get_var( $this->prepare( $sql, $params ) );
		}

		$results = $this->wpdb->get_results( $this->prepare( $sql, $params ), ARRAY_A );
		if ( empty( $results ) ) {
			return array();
		}

		foreach ( $results as $i => $item ) {
			$item          = $this->_unserialize_fields( $item, array( 'tcb_fields' ) );
			$item          = $this->_assign_fields( $item, array( 'tcb_fields' ) );
			$results[ $i ] = $item;
		}

		return $results;
	}

	/**
	 * Find the design row in DB and returns it as array
	 *
	 * @param int $design_id
	 * @param string $return_type by default ARRAY_A
	 *
	 * @return array|null Array with the design fields or null if the design is not found
	 */
	public function get_design( $design_id, $return_type = null ) {

		if ( empty( $return_type ) ) {
			$return_type = ARRAY_A;
		}

		$sql = 'SELECT * FROM {designs} WHERE `id` = %d';

		$item = $this->wpdb->get_row( $this->prepare( $sql, array( $design_id ) ), $return_type );

		if ( empty( $item ) ) {
			return null;
		}

		$item = $this->_unserialize_fields( $item, array( 'tcb_fields' ) );
		$item = $this->_assign_fields( $item, array( 'tcb_fields' ) );

		return $item;
	}

	/**
	 * Deletes a design from DB
	 * Also deletes its children
	 *
	 * @param int $design_id
	 *
	 * @return false|int number of deleted rows
	 */
	public function delete_design( $design_id ) {

		$design_id = (int) $design_id;

		$deleted = $this->wpdb->delete( tve_ult_table_name( 'designs' ), array( 'parent_id' => $design_id ) );
		if ( $deleted !== false ) {
			$deleted = $this->wpdb->delete( tve_ult_table_name( 'designs' ), array( 'id' => $design_id ) );
			if ( $deleted !== false ) {
				$deleted ++;
			}
		}

		return $deleted;
	}

	/**
	 * Read from DB events table and returns rows based on filters
	 *
	 * @param $filters
	 *      - campaign_id implemented, more to come.
	 *      - order array [column => asc|desc]
	 *
	 * @return array|null|object
	 */
	public function get_events( $filters ) {
		$sql    = 'SELECT * FROM {events} WHERE 1=1';
		$params = array();

		if ( ! empty( $filters['campaign_id'] ) ) {
			$sql .= ' AND campaign_id = %d';
			$params[] = $filters['campaign_id'];
		}

		if ( ! empty( $filters['type'] ) ) {

			if ( ! is_array( $filters['type'] ) ) {
				$filters['type'] = array( $filters['type'] );
			}

			$sql .= ' AND (';
			foreach ( $filters['type'] as $key => $type ) {
				$sql .= $key == 0 ? 'type = %s' : ' OR type = %s';
				$params[] = $type;
			}
			$sql .= ')';
		}

		if ( ! empty( $filters['duration'] ) ) {
			$sql .= ' AND days * 24 + hours > %d';
			$params[] = $filters['duration'];
		}

		if ( ! empty( $filters['order'] ) && is_array( $filters['order'] ) ) {
			$sql .= ' ORDER BY';
			foreach ( $filters['order'] as $column => $dir ) {
				$dir = strtoupper( $dir );
				$dir = in_array( $dir, array( 'ASC', 'DESC' ) ) ? $dir : 'DESC';
				$sql .= " {$column} {$dir},";
			}
			$sql = rtrim( $sql, ',' );
		}

		return $this->wpdb->get_results( $this->prepare( $sql, $params ), ARRAY_A );
	}

	/**
	 * Saves an event data into DB
	 *
	 * @param $model
	 *
	 * @return false|int id of the model on success or false on error
	 */
	public function save_event( $model ) {
		$results = null;

		$columns = array(
			'id',
			'campaign_id',
			'days',
			'hours',
			'trigger_options',
			'actions',
			'type',
		);

		foreach ( $model as $key => $value ) {
			if ( ! in_array( $key, $columns ) ) {
				unset( $model[ $key ] );
			}
		}

		if ( empty( $model['id'] ) ) {

			$results = $this->wpdb->insert( tve_ult_table_name( 'events' ), $model );

			return $results !== false ? $this->wpdb->insert_id : false;
		}

		$results = $this->wpdb->update( tve_ult_table_name( 'events' ), $model, array( 'id' => $model['id'] ) );

		return $results !== false ? $model['id'] : false;
	}

	/**
	 * Get event by ID.
	 *
	 * @param mixed $id
	 *
	 * @return array|null|object|void
	 */
	public function get_event( $id ) {

		$sql  = 'SELECT * FROM {events} WHERE `id` = %d';
		$item = $this->wpdb->get_row( $this->prepare( $sql, array( $id ) ), ARRAY_A );

		if ( empty( $item ) ) {
			return null;
		}

		$item = $this->_unserialize_fields( $item, array( 'actions', 'trigger_options' ) );

		return $item;
	}


	/**
	 * Gets last error
	 *
	 * @return string
	 */
	public function last_error() {
		return $this->wpdb->last_error;
	}

	/**
	 * Delete an event.
	 *
	 * @param mixed $id
	 *
	 * @return false|int
	 */
	public function delete_event( $id ) {
		return $this->wpdb->delete( $this->t( 'events' ), array( 'id' => $id ) );
	}

	/**
	 * Based on params this function gets an event from DB and returns it
	 * The difference between how many hours the campaign exists from nou on and param $hours
	 * determine which event should be returned
	 *
	 * @param $campaign_id
	 * @param int $hours how many ours until the campaign ends
	 *
	 * @return array|null|object|void
	 */
	public function get_closest_event( $campaign_id, $hours ) {
		$params[] = $campaign_id;
		$params[] = $hours;
		$params[] = 'conv';
		$params[] = 'start';

		$sql = 'SELECT *, days * 24 + hours as event_duration FROM ' . tve_ult_table_name( 'events' ) . ' WHERE campaign_id = %d AND (days * 24 + hours >= %f AND `type` != %s OR `type` = %s) ORDER BY event_duration ASC LIMIT 1';

		return $this->wpdb->get_row( $this->prepare( $sql, $params ), ARRAY_A );
	}

	/**
	 * Inserts a row into the event_log table
	 *
	 * @param array $data event type - conversion or impression
	 *
	 * @return int|string $log_id the inserted log ID or a string error message in case of failure
	 */
	public function insert_event_log( $data ) {
		$defaults = array(
			'date' => tve_ult_current_time( 'mysql' ),
		);
		$data     = wp_parse_args( $data, $defaults );

		if ( $this->wpdb->insert( tve_ult_table_name( 'event_log' ), $data ) ) {
			return $this->wpdb->insert_id;
		}

		return $this->wpdb->last_error;

	}

	/**
	 * count event logs based on a filter
	 *
	 * @param array $filter
	 *
	 * @return int
	 */
	public function count_logs( $filter = array() ) {
		$sql    = 'SELECT COUNT( e.id ) FROM `' . tve_ult_table_name( 'event_log' ) . '` AS `e` WHERE 1';
		$params = array();

		if ( ! empty( $filter['campaign_id'] ) ) {
			$sql .= ' AND `campaign_id` = %d';
			$params [] = $filter['campaign_id'];
		}

		if ( ! empty( $filter['type'] ) ) {
			$sql .= ' AND `type` = %s';
			$params [] = $filter['type'];
		}

		return $this->wpdb->get_var( $this->prepare( $sql, $params ) );
	}

	/**
	 * count impressions and conversions for a specific campaign
	 *
	 * @param int $campaign_id ID of the campaign for which the data is retrieved
	 *
	 * @return array
	 */
	public function count_event_logs_chart_data( $campaign_id ) {
		$no_results = array(
			'impressions' => array(),
			'conversions' => array(),
		);
		if ( empty( $campaign_id ) ) {
			return $no_results;
		}
		$sql    = 'SELECT COUNT( e.id ) AS _total, e.type, {group_field} FROM {event_log} AS `e` WHERE `campaign_id` = %d';
		$params = array();

		$params [] = $campaign_id;

		/**
		 * Get the minimum and maximum date for which a campaign has impressions / conversions
		 */
		$date_sql = 'SELECT MIN( `date` ) AS _min, MAX( `date` ) AS _max FROM {event_log} WHERE campaign_id = %d';
		$row      = $this->wpdb->get_row( $this->prepare( $date_sql, $params ), ARRAY_A );
		if ( empty( $row ) || empty( $row['_min'] ) ) {
			return $no_results;
		}

		/* this can only mean there's one impression logged */
		if ( $row['_min'] === $row['_max'] ) {
			return array(
				'impressions' => array( 1 ),
				'conversions' => array( 0 ),
				'labels'      => array( $row['_min'] ),
			);
		}
		/* store the start and end dates for the chart : we need to show the minimum and maximum dates at which an impression / conversion took place */
		$start = $real_start_label = strtotime( $row['_min'] );
		$end   = $real_end_label = strtotime( $row['_max'] );

		/**
		 * the interval length should be computed dynamically based on the total length of the time difference
		 *
		 * show max 12 intervals in the graph
		 */
		$interval_length = floor( ( $end - $start ) / 12 );
		/* rebuild $start so that it conforms with the mysql-saved intervals */
		$start = $interval_length * floor( $start / $interval_length );

		/**
		 * by dividing this, we make sure we always have 13 values reported from the query
		 *
		 * TODO: anybody has a better idea on how to do this ?
		 */
		$group_by = "UNIX_TIMESTAMP( `date` ) DIV {$interval_length}";

		$sql = str_replace( '{group_field}', $group_by . ' AS _grp', $sql );

		$sql .= ' GROUP BY _grp, e.`type` ORDER BY e.`date` ASC';

		/**
		 * make sure each interval has it's own entry for conversions, impressions and labels
		 */
		$impressions = $conversions = $labels = array();
		foreach ( $this->wpdb->get_results( $this->prepare( $sql, $params ), ARRAY_A ) as $item ) {
			if ( ! isset( $previous_group ) ) {
				$previous_group = $item['_grp'];
			}
			/**
			 * fill in all the gaps - it's possible that some of the intervals do not have any impressions / conversions - we still need those in the chart
			 * with zero-filled values
			 */
			for ( $grp = $previous_group + 1; $grp < $item['_grp']; $grp ++ ) {
				$impressions[ $grp ] = $conversions[ $grp ] = 0;
			}
			$previous_group = $item['_grp'];
			if ( ! isset( $impressions[ $item['_grp'] ] ) ) {
				$impressions[ $item['_grp'] ] = 0;
			}
			if ( ! isset( $conversions[ $item['_grp'] ] ) ) {
				$conversions[ $item['_grp'] ] = 0;
			}
			if ( $item['type'] == TVE_Ult_Const::LOG_TYPE_IMPRESSION ) {
				$impressions[ $item['_grp'] ] = (int) $item['_total'];
			} else {
				$conversions[ $item['_grp'] ] = (int) $item['_total'];
			}
		}

		$date_format = 'M jS, Y H:i:s';

		/**
		 * we can get rid of non-numeric indexes
		 */
		$impressions = array_values( $impressions );
		$conversions = array_values( $conversions );
		$total       = count( $impressions );

		$dates[ - 1 ] = date( 'Y-m-d H:i:s', $start );

		/**
		 * each label will store a time interval - which will vary from campaign to campaign
		 *
		 * TODO: not sure if this is the best approach - maybe we should separate the intervals into more clearer groups ?
		 * at this point, the interval precision is up until the seconds part of a date - maybe we can get rid of seconds / minutes / hours / days (?) for longer time intervals
		 *
		 * previous commit shows another variation of this - by having specific time intervals approximated based on the $interval_length
		 */
		foreach ( $impressions as $i => $count ) {
			$dates[ $i ] = date( 'Y-m-d H:i:s', strtotime( $dates[ $i - 1 ] . ' +' . $interval_length . ' seconds' ) );
			if ( $i === 0 ) {
				/* the label should display the first date / time when this campaign has registered an impression */
				$labels[ $i ] = date( $date_format, $real_start_label ) . ' - ' . date( $date_format, strtotime( $dates[ $i ] ) );
				continue;
			}
			if ( $i < $total - 1 ) {
				$labels[ $i ] = date( $date_format, strtotime( $dates[ $i - 1 ] ) ) . ' - ' . date( $date_format, strtotime( $dates[ $i ] ) );
				continue;
			}
			/* the last label should have the real last date at which an impression / conversion has been made */
			$labels[ $i ] = date( $date_format, strtotime( $dates[ $i - 1 ] ) ) . ' - ' . date( $date_format, $real_end_label );
		}

		return array(
			'impressions' => $impressions,
			'conversions' => $conversions,
			'labels'      => $labels,
		);
	}

	/**
	 * Delete all events from timeline that are outside the campaign's duration.
	 *
	 * @param mixed $campaign
	 * @param mixed $duration
	 *
	 * @return false|int
	 */
	public function clear_timeline( $campaign, $duration ) {
		$sql = 'DELETE FROM {events} WHERE campaign_id = %d AND days * 24 + hours >= %d AND `type` = %s';

		$params[] = $campaign;
		$params[] = $duration;
		$params[] = 'time';

		$sql = $this->prepare( $sql, $params );

		return $this->wpdb->query( $sql );
	}

	/**
	 * get the list of saved templates to be used in display settings
	 *
	 * @return array
	 */
	public function get_display_settings_templates() {
		$sql = 'SELECT * FROM {settings_templates} ORDER BY `name` ASC';

		return $this->wpdb->get_results( $this->prepare( $sql, array() ) );
	}

	/**
	 * get display settings template by ID
	 *
	 * @param int|string $id
	 *
	 * @return array|null|object|void
	 */
	public function get_display_settings_template( $id ) {
		$sql    = 'SELECT * FROM {settings_templates} WHERE id = %d';
		$params = array( $id );

		return $this->wpdb->get_row( $this->prepare( $sql, $params ) );
	}

	/**
	 * delete all events related to a campaign
	 *
	 * @param WP_Post|string|int $campaign
	 *
	 * @return int|false
	 */
	public function delete_events( $campaign ) {
		return $this->wpdb->delete( tve_ult_table_name( 'settings_campaign' ), array(
			'campaign_id' => $this->_object_id( $campaign ),
		) );
	}

	/**
	 * Delete the display settings for a campaign
	 *
	 * @param WP_Post|string $campaign
	 *
	 * @return false|int
	 */
	public function delete_display_settings( $campaign ) {
		return $this->wpdb->delete( tve_ult_table_name( 'settings_campaign' ), array(
			'campaign_id' => $this->_object_id( $campaign ),
		) );
	}

	/**
	 * delete all designs belonging to a campaign
	 *
	 * @param WP_Post|string|int $campaign
	 *
	 * @return int|false
	 */
	public function delete_designs( $campaign ) {
		return $this->wpdb->delete( $this->t( 'designs' ), array(
			'post_parent' => $this->_object_id( $campaign ),
		) );
	}

	/**
	 * delete all event logs for a campaign
	 *
	 * @param WP_Post|string|int $campaign
	 *
	 * @return int|false
	 */
	public function delete_event_logs( $campaign ) {
		return $this->wpdb->delete( $this->t( 'events' ), array(
			'campaign_id' => $this->_object_id( $campaign ),
		) );
	}

	/**
	 * delete all saved emails for a campaign
	 *
	 * @param WP_Post|string|int $campaign
	 *
	 * @return int|false
	 */
	public function delete_email_logs( $campaign ) {
		return $this->wpdb->delete( $this->t( 'emails' ), array(
			'campaign_id' => $this->_object_id( $campaign ),
		) );
	}

	/**
	 * Save email log in database
	 *
	 * @param mixed $model
	 *
	 * @return false|int
	 */
	public function save_email_log( $model ) {
		$results = null;

		$columns = array(
			'id',
			'campaign_id',
			'email',
			'started',
			'type',
			'end',
			'has_impression',
		);

		foreach ( $model as $key => $value ) {
			if ( ! in_array( $key, $columns ) ) {
				unset( $model[ $key ] );
			}
		}

		if ( empty( $model['id'] ) ) {
			$results = $this->wpdb->insert( tve_ult_table_name( 'emails' ), $model );

			return $results !== false ? $this->wpdb->insert_id : false;
		}

		$results = $this->wpdb->update( tve_ult_table_name( 'emails' ), $model, array( 'id' => $model['id'] ) );

		return $results !== false ? $model['id'] : false;
	}

	/**
	 * Get a specific email log based on campaign and email address
	 *
	 * @param $campaign_id
	 * @param $email
	 *
	 * @return array
	 */
	public function get_email_log( $campaign_id, $email ) {
		$sql    = 'SELECT * FROM {emails} WHERE campaign_id = %d AND email = %s';
		$params = array(
			'campaign_id' => $campaign_id,
			'email'       => $email,
		);

		return $this->wpdb->get_row( $this->prepare( $sql, $params ), ARRAY_A );
	}

	/**
	 * Gets a row from DB by id and returns it
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public function get_email_log_by_id( $id ) {
		$sql    = 'SELECT * FROM {emails} WHERE id = %d';
		$params = array( $id );

		return $this->wpdb->get_row( $this->prepare( $sql, $params ), ARRAY_A );
	}
}

/**
 * This should exists only in data.php file
 */
$tve_ult_db = new Tve_Ult_Db();
