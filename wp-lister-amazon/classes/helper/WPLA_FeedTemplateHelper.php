<?php

class WPLA_FeedTemplateHelper extends WPLA_Core {
	
	var $logger;
	var $tpl_id;
	var $site_id;
	var $site_code;
	public $imported_count = 0;
	public $temporary_files = array();

	const TABLENAME = 'amazon_feeds';
	const UPDATEURL = 'http://update.wplab.de/wpla/data/1.0/';


	public function importTemplatesForCategory( $category_name, $site_code ) {
		WPLA()->logger->info("importTemplatesForCategory( {$category_name} , {$site_code} )");

		$file_index = WPLA_FeedTemplateIndex::get_file_index();
		$this->imported_count = 0;

		if ( ! isset( $file_index[ $site_code ] ) ) return;
		$site = $file_index[ $site_code ];
		// echo "<pre>";print_r($category_name);echo"</pre>";
		// echo "<pre>";print_r($site);echo"</pre>";
		$this->market    = WPLA_AmazonMarket::getMarketByCountyCode( $site_code );
		$this->site_id   = $this->market->id;
		$this->site_code = $site_code;

		if ( ! isset( $site['categories'][ $category_name ] ) ) return;
		$category = $site['categories'][ $category_name ];
		// echo "<pre>";print_r($category);echo"</pre>";

		// $template_files = $category['templates']; 
		// $btguides_files = $category['btguides']; 

		// echo "<pre>";print_r($template_files);echo"</pre>";
		// echo "<pre>";print_r($btguides_files);echo"</pre>";

		WPLA()->logger->info("importing files for {$this->site_id} / {$this->site_code}");
		$this->importTemplates( $category['templates'], $site_code );
		$this->importBrowseTreeGuides( $category['btguides'], $site_code );

		// remove data files
		$this->cleanupTempFiles();

		return $this->imported_count;
	} // importTemplatesForCategory()

	function cleanupTempFiles() {
		foreach ($this->temporary_files as $file) {
			unlink( $file );
			WPLA()->logger->info("removed ".basename($file));			
		}
		WPLA()->logger->info("-------------------------------");			
	}


	/**
	 * Fetch data file from remote URL
	 */

	public function fetchRemoteFile( $url ) {
		// echo "<pre>fetching URL ";print_r($url);echo"</pre>";
		WPLA()->logger->info("fetching URL: {$url}");

		// get uploads folder
		$upload_dir  = wp_upload_dir();
		$upload_path = $upload_dir['basedir'];

		// fetch file
		$response = wp_remote_get( $url, array( 'timeout' => 15 ) );
		// echo "<pre>";print_r($response);echo"</pre>";die();

		if ( is_wp_error( $response ) ) {
			// echo "<pre>";print_r($response);echo"</pre>";	
			$this->showMessage( "Couldn't fetch URL $url - ".$response->get_error_message(), 1, 1 );
			WPLA()->logger->error("Couldn't fetch URL $url - ".$response->get_error_message());
			return false;			
		}

		if ( wp_remote_retrieve_response_code( $response ) != 200 ) {
			// echo "<pre>Couldn't fetch URL $url - server returned error code ".$response['response']['code']."</pre>";
			$this->showMessage( "Couldn't fetch URL $url - server returned error code ". wp_remote_retrieve_response_code( $response ), 1, 1 );
			WPLA()->logger->error("Couldn't fetch URL $url - server returned error code ". wp_remote_retrieve_response_code( $response ) );
			return false;
		}

		// save file in uploads folder
		$local_file = trailingslashit( $upload_path ) . basename( $url );
		if ( ! file_put_contents( $local_file, wp_remote_retrieve_body( $response ) ) ) {
			$this->showMessage( "Couldn't write file $local_file - please check upload folder permissions.", 1, 1 );
			WPLA()->logger->error("Couldn't write file $local_file");
			return false;			
		}

		// remember local file for cleanup
		$this->temporary_files[] = $local_file;

		return $local_file;
	} // fetchRemoteFile()



	/**
	 * Template Files
	 */

	public function importTemplates( $template_files, $site_code ) {
		$this->tpl_id = 0;

		$b2b_enabled = get_option('wpla_load_b2b_templates',0);
		if ( ! in_array( $site_code, array('UK','DE') ) ) $b2b_enabled = false; // only enable for UK and DE for now
		$sclc = strtolower( $site_code ); // site code lower case

		foreach ( $template_files as $filename ) {
			if ( $b2b_enabled ) $filename = str_replace( '.'.$sclc, '_b2b.'.$sclc, $filename ); // Baby.uk.csv -> Baby_b2b.uk.csv
			$local_file = $this->fetchRemoteFile( self::UPDATEURL . 'tpl/' . $site_code .'/'. $filename );
			if ( ! $local_file ) continue;
			$count = $this->importTplFile( $local_file );
			// $count = $this->importTplFile( WPLA_PATH . '/includes/data/tpl/' . $filename );
			$this->imported_count++;
		}

		return $this->imported_count;
	} // importTemplates()

	/**
	 * importTplFile()
	 *
	 * import the specified $file - which can be the Template, Data Definitions or Valid Values
	 * runs the appropiate parser based on file type
	 */
	function importTplFile( $file ) {
		WPLA()->logger->info("importTplFile(): ".basename($file));

		// detect file type
		$mode = 'template';
		$filename = strtolower(basename($file));
		if ( strpos( $filename, 'data'   ) > 0 ) $mode = 'data';
		if ( strpos( $filename, 'values' ) > 0 ) $mode = 'values';

		// detect feed type
		$filename = str_replace('Flat.File.','',basename($file));    						// remove Flat.File.
		$filename = str_replace('Flat_File_','',$filename); 		   						// remove Flat_File_
		$feed_type = substr($filename,  0, strpos($filename, '-') ); 						// remove -Template.csv
		if (strpos($feed_type, '.')) list( $feed_type, $dummy ) = explode('.',$feed_type);	// remove .de (if present)
		if (strpos($feed_type, '_')) list( $feed_type, $dummy ) = explode('_',$feed_type);	// remove _de (if present)
		if ( $feed_type == 'ListingLoader') $feed_type = 'Offer';
		if ( $feed_type == 'Listingloader') $feed_type = 'Offer';
		if ( $feed_type == 'CE')            $feed_type = 'ConsumerElectronics';
		if ( $feed_type == 'SWVG')          $feed_type = 'SoftwareVideoGames';
		if ( $feed_type == 'sports')        $feed_type = 'Sports';
		if ( $feed_type == 'SexSensuality') $feed_type = 'Custom';
		WPLA()->logger->info("detected type: {$feed_type}");

		switch ($mode) {
			case 'template':

				// read CSV file
				$tpl = $this->readFeedTemplateCSV( $file );
				// echo "<pre>";print_r($tpl);echo"</pre>";#die();

				// parse browse tree guide data
				$this->tpl_id = $this->parseFeedTemplate( $tpl );
				$result = 1;
				break;
			
			case 'data':

				// read CSV file
				$field_data = $this->readFeedDataCSV( $file );
				// echo "<pre>";print_r($field_data);echo"</pre>";#die();

				// parse feed data defintions
				$result = $this->parseFeedData( $field_data, $feed_type );
				$result = 1;
				break;
			
			case 'values':

				// read CSV file
				$field_data = $this->readFeedValuesCSV( $file );
				// echo "<pre>";print_r($field_data);echo"</pre>";#die();

				// parse feed valid values
				$result = $this->parseFeedValues( $field_data, $feed_type );
				$result = 1;
				break;
			
		}

		return $result;
	} // importTplFile()


	// store allowed values in amazon_feed_tpl_values
	function parseFeedValues( $fields, $feed_type ) {
		WPLA()->logger->info("parseFeedValues(): {$feed_type}");
		global $wpdb;
		$templates_table = $wpdb->prefix . 'amazon_feed_templates';
		$values_table    = $wpdb->prefix . 'amazon_feed_tpl_values';
		$data_table      = $wpdb->prefix . 'amazon_feed_tpl_data';

		// get template id 
		$tpl_id = $wpdb->get_var( "SELECT id FROM $templates_table WHERE name = '$feed_type' and site_id = '{$this->site_id}' ");
		WPLA()->logger->info("TPL_ID: {$tpl_id}");

		// get all template field names
		$data_field_index = $wpdb->get_col( "SELECT field FROM $data_table WHERE tpl_id = '$tpl_id' and site_id = '{$this->site_id}' ");

		foreach ( $fields as $field_data ) {

			// build sql columns
			$data = $field_data;
			$data['values']  = join( '|', $data['values'] );
			$data['tpl_id']  = $tpl_id;
			$data['site_id'] = $this->site_id;

			// insert to db...
			$result = $wpdb->insert( $values_table, $data );

			// if the field does not exist, check for enumerated fields - like target_audience_keywords1..3
			if ( ! in_array($data['field'], $data_field_index) ) {

				$field_root_name = $data['field'];
				for ($i=1; $i < 10; $i++) { 

					// try all suffixes from 1..9 - and insert record if match is found
					$enum_field_name = $field_root_name . $i;
					if ( in_array( $enum_field_name, $data_field_index) ) {
						$data['field'] = $enum_field_name;
						$result = $wpdb->insert( $values_table, $data );
					}					

				}

			} // if field not found

		} // foreach field

		return $tpl_id;
	} // parseFeedValues()

	function readFeedValuesCSV( $filename, $delimiter = ',' ) {

	    if ( ! file_exists($filename) || ! is_readable($filename) ) {
	    	echo "<pre>Could not read $filename</pre>";
    	    return false;
	    }

		$fields        = array();
		$line          = 0;
		$is_lilo       = false;

	    // open file
	    if ( ( $handle = fopen($filename, 'r') ) !== false ) {

	    	// read lines
	        while ( ( $row = fgetcsv($handle, 0, $delimiter) ) !== false ) {
	        	// echo "<pre>line $line: ";print_r($row);echo"</pre>";#die();

	        	// ListingLoader: skip empty lines at start
	        	if ( empty($row[0]) && empty($row[1]) && empty($row[2]) ) {
	        		if ( $line == 0 ) $line++;
	        		$is_lilo = true;
	        		continue;
	        	}


	            if ( $line == 0 ) {

	            	// first row contains field labels
	            	for ($i=0; $i < sizeof($row); $i++) { 

		                $fields[$i]['label'] = $row[$i];

		            	// extract type - if exists
						if ( preg_match("/(.*) - \\[ \\(?(.*)\\)? \\]/uiUs", $row[$i], $matches ) ) {
							// echo "<pre>";print_r($matches);echo"</pre>";die();
			                $fields[$i]['label'] = $matches[1];
			                $fields[$i]['type'] = str_replace('(','',$matches[2]);
						} else {					
			                $fields[$i]['type'] = '';
						}

	            	}

	            } elseif ( $line == 1 ) {

	            	// second row contains field names
	            	for ($i=0; $i < sizeof($row); $i++) { 
		                $fields[$i]['field'] = $row[$i];
		                $fields[$i]['values'] = array();

		            	// generate dummy column labels for ListingLoader
		            	if ( $is_lilo ) $fields[$i]['label'] = self::generate_label_from_fieldname( $row[$i] );
	            	}

	            } else {

	            	// other row contain allowed values
	            	for ($i=0; $i < sizeof($row); $i++) { 
		                if ( $row[$i] )
		                	$fields[$i]['values'][] = $row[$i];
	            	}

	            }

	            $line++;
	        }
	        fclose($handle);
	    }

	    return $fields;
	} // readFeedValuesCSV


	// store data defitions in amazon_feed_tpl_data
	function parseFeedData( $fields, $feed_type ) {
		WPLA()->logger->info("parseFeedData(): {$feed_type}");
		global $wpdb;
		$templates_table = $wpdb->prefix . 'amazon_feed_templates';
		$fields_table    = $wpdb->prefix . 'amazon_feed_tpl_data';

		// get template id 
		$tpl_id = $wpdb->get_var( "SELECT id FROM $templates_table WHERE name = '$feed_type' and site_id = '{$this->site_id}' ");
		WPLA()->logger->info("TPL_ID: {$tpl_id}");

		foreach ( $fields as $key => $field_data ) {

			// build sql columns
			// $data['tpl_id']  = $tpl_id;

			// update db...
			$result = $wpdb->update( $fields_table, $field_data, array( 'field' => $key, 'tpl_id' => $tpl_id ) );
			// WPLA()->logger->info("SQL: ".print_r($wpdb->last_query,1));
			if ( ! $result ) {
				WPLA()->logger->error("Failed to store field {$key} - MySQL result: ".print_r($result,1));
				WPLA()->logger->error("MySQL query: ".print_r($wpdb->last_query,1));
				WPLA()->logger->error("MySQL error: ".print_r($wpdb->last_error,1));
			}
		}

		return $tpl_id;
	} // parseFeedData()

	function readFeedDataCSV( $filename, $delimiter = ',' ) {

	    if ( ! file_exists($filename) || ! is_readable($filename) ) {
	    	echo "<pre>Could not read $filename</pre>";
    	    return false;
	    }

		$fields        = array();
		$current_group = '';
		$line          = 0;
		$is_lilo       = false;

	    // open file
	    if ( ( $handle = fopen($filename, 'r') ) !== false ) {

	    	// read lines
	        while ( ( $row = fgetcsv($handle, 0, $delimiter) ) !== false ) {
	        	// echo "<pre>line $line: ";print_r($row);echo"</pre>";#die();

            	// first two row contains nothing of interest
	            if ( $line < 2 ) {
	            	$line++;
	            	continue;
	            }

	            // parse row
				$group      = $row[0];
				$field      = $row[1];
				$label      = $row[2];
				$definition = $row[3];
				$accepted   = $row[4];
				$example    = $row[5];
				$required   = $row[6];

				// ListingLoader: has neither group no label, so we'll simply fake those
				if ( $line == 2 && in_array( $row[0], array('Label Name','Attribut Name') ) ) {
					$is_lilo = true;
					$line++;
					continue;
				}
				if ( $is_lilo ) {
					$group      = '';
					$field      = strtolower( str_replace( ' ', '-', $row[0] ) ); // B2B fields have their label here, not their fieldname
					$label      = self::generate_label_from_fieldname( $row[0] );
					$definition = $row[1];
					$accepted   = $row[2];
					$example    = $row[3];
					$required   = $row[4];

					// fix B2B field names
					$field = str_replace( 'quantity-price-',       'quantity-price',       $field );
					$field = str_replace( 'quantity-lower-bound-', 'quantity-lower-bound', $field );
					if ( $field == 'quantity-pricetype' ) 									$field = 'quantity-price-type';
					if ( $field == 'quantity_price_type' ) 									$field = 'quantity-price-type';
					if ( $field == 'national-stock-number' ) 								$field = 'national_stock_number';
					if ( $field == 'united-nations-standard-products-and-services-code' ) 	$field = 'unspsc_code';
					if ( $field == 'pricing-action' ) 										$field = 'pricing_action';
				}

	            if ( $group ) {

	            	// parse group title
					$current_group    = $group;
	            	if ( strpos( $group, ' - ' ) > 0 ) {
						$current_group_id = substr($group, 0, strpos($group,' - ') );
					} else {
						$current_group_id = 'Ungrouped';
					}

	            } else {

	            	// fix too long required fields
					if ( 'Optional'  == substr( $required, 0, 8 ) ) $required = 'Optional';
					if ( 'MANDATORY' == substr( $required, 0, 9 ) ) $required = 'Optional';
					if ( 'Automat'   == substr( $required, 0, 7 ) ) $required = 'Optional';
					if ( strlen($required) > 30 ) $required = trim( substr( $row[4], 0, 30 ) );

	            	// check for multi-fields like 'bullet_point1 - bullet_point5'
	            	if ( strpos( $field, '1 - ' ) > 0 ) {

	            		$base_field = substr( $field, 0, strpos($field,'1 - ') );
	            		$base_label = substr( $label, 0, strpos($label,'1 - ') );
	            		$last_index = substr( $field, -1, 1 );

	            		// create all fields
	            		for ($i=1; $i <= $last_index; $i++) { 

	            			$field = $base_field . $i;
	            			$label = $base_label .' '. $i;
			            	$fields[ $field ] = array(
								'field'      => $field,
								'label'      => $label,
								'definition' => $definition,
								'accepted'   => $accepted,
								'example'    => $example,
								'required'   => $required,
								'group'      => $current_group,
								'group_id'   => $current_group_id,
			            	);
	            			
	            		}

	            	// check for multi-fields like 'ghs_classification_class1-ghs_classification_class3'
	            	} elseif ( strpos( $field, '1-' ) > 0 ) {

	            		$base_field = substr( $field, 0, strpos($field,'1-') );
	            		$base_label = substr( $label, 0, strpos($label,'1 ') );
	            		$last_index = substr( $field, -1, 1 );

	            		// create all fields
	            		for ($i=1; $i <= $last_index; $i++) { 

	            			$field = $base_field . $i;
	            			$label = $base_label .' '. $i;
			            	$fields[ $field ] = array(
								'field'      => $field,
								'label'      => $label,
								'definition' => $definition,
								'accepted'   => $accepted,
								'example'    => $example,
								'required'   => $required,
								'group'      => $current_group,
								'group_id'   => $current_group_id,
			            	);
	            			
	            		}

	            	} else {

		            	// parse field information
		            	$fields[ $field ] = array(
							'field'      => $field,
							'label'      => $label,
							'definition' => $definition,
							'accepted'   => $accepted,
							'example'    => $example,
							'required'   => $required,
							'group'      => $current_group,
							'group_id'   => $current_group_id,
		            	);

	            	} // if single field

	            } // if group header

	            $line++;
	        }
	        fclose($handle);
	    }

	    return $fields;
	} // readFeedDataCSV



	function parseFeedTemplate( $tpl ) {
		WPLA()->logger->info("parseFeedTemplate( tpl {$tpl->type} )");

		global $wpdb;
		$templates_table = $wpdb->prefix . 'amazon_feed_templates';
		$fields_table    = $wpdb->prefix . 'amazon_feed_tpl_data';
		$values_table    = $wpdb->prefix . 'amazon_feed_tpl_values';

		// remove old data 
		WPLA()->logger->info("trying to find existing template WHERE name = '{$tpl->type}' AND site_id = '{$this->site_id}'");
		$tpl_id = $wpdb->get_var( "SELECT id FROM $templates_table WHERE name = '$tpl->type' AND site_id = '$this->site_id' ");
		if ( $tpl_id ) {
			WPLA()->logger->info("removing data for tpl_id {$tpl_id}");
			// $wpdb->delete( $templates_table, array( 'id'     => $tpl_id, 'site_id' => $this->site_id ) );
			$wpdb->delete( $fields_table,    array( 'tpl_id' => $tpl_id, 'site_id' => $this->site_id ) );
			$wpdb->delete( $values_table,    array( 'tpl_id' => $tpl_id, 'site_id' => $this->site_id ) );
		}

		// build sql columns
		$data = array();
		$data['name']   	= $tpl->type;
		$data['title']   	= $this->spacify( $tpl->type );
		$data['version']   	= $tpl->version;
		$data['site_id']    = $this->site_id;

		// insert to wp_amazon_feed_templates - or update
		if ( $tpl_id ) {
			$result = $wpdb->update( $templates_table, $data, array( 'id' => $tpl_id, 'site_id' => $this->site_id ) );
			WPLA()->logger->info("updated existing template - id {$tpl_id}");
		} else {
			$result = $wpdb->insert( $templates_table, $data );
			$tpl_id = $wpdb->insert_id;
			WPLA()->logger->info("added new feed template - id {$tpl_id}");
		}

		// store fields
		foreach ( $tpl->fields as $field_name => $field_title ) {

			// fix column name in ListingLoader DE B2B
			if ( $field_name == 'quantity_price_type' ) $field_name = 'quantity-price-type';

			// build sql columns
			$data = array();
			$data['field']   = $field_name;
			$data['label']   = $field_title;
			$data['tpl_id']  = $tpl_id;
			$data['site_id'] = $this->site_id;

			// insert to amazon_feed_tpl_data
			$result = $wpdb->insert( $fields_table, $data );
		}

		return $tpl_id;
	} // parseFeedTemplate()

	function readFeedTemplateCSV( $filename, $delimiter = ',' ) {
		WPLA()->logger->info("readFeedTemplateCSV(): {$filename}");

	    if ( ! file_exists($filename) || ! is_readable($filename) ) {
	    	echo "<pre>Could not read $filename</pre>";
    	    return false;
	    }

		$tpl         = new stdClass();
		$tpl->fields = array();
		$header      = false;
		$line        = 0;

	    // open file
	    if ( ( $handle = fopen($filename, 'r') ) !== false ) {

	    	// read lines
	        while ( ( $row = fgetcsv($handle, 0, $delimiter) ) !== false ) {
	        	// echo "<pre>line $line: ";print_r($row);echo"</pre>";#die();

	            if ( $line == 0 ) {

	            	// first row contains template type and version
	                $tpl->type    = str_replace( 'TemplateType=', '', $row[0] );
	                $tpl->version = str_replace( 'Version=', '', $row[1] );
					if ( $tpl->type == 'sports') $tpl->type = 'Sports'; // fix Sports UK tpl
					WPLA()->logger->info("TemplateType: {$tpl->type}");
					WPLA()->logger->info("Version: {$tpl->version}");

	            } elseif ( $line == 1 ) {

	            	// second row contains field labels
	                $header_labels = $row;
					// WPLA()->logger->info("header labels: ".print_r($header_labels,1));

	                // fix column labels for new ListingLoader
					if ( $row[0] == 'sku' ) {
		            	for ($i=0; $i < sizeof($row); $i++) { 
							$header_labels[$i] = self::generate_label_from_fieldname( $row[$i] );
						}
					}

	            } elseif ( $line == 2 ) {

	            	// third row contains field names
	                $tpl->fields = array_combine( $row, $header_labels );

	            }

	            $line++;
	        }
	        fclose($handle);
	    }

	    return $tpl;
	} // readFeedTemplateCSV



	/**
	 * Browse Tree Guides
	 */

	public function importBrowseTreeGuides( $btg_files = false, $site_code ) {

		// clean previously imported BTG for current tpl_id
		$this->cleanBTG( $this->tpl_id );

		foreach ( $btg_files as $filename ) {
			$local_file = $this->fetchRemoteFile( self::UPDATEURL . 'btg/' . $site_code .'/'. $filename );
			if ( ! $local_file ) continue;
			$count = $this->importBTG( $local_file );
			// $count = $this->importBTG( WPLA_PATH . '/includes/data/btg/' . $filename );
			$this->imported_count++;
		}

		return $this->imported_count;
	} // importBrowseTreeGuides()


	function importBTG( $file ) {

		// read CSV file to array
		$csv_data = $this->readBTG( $file );
		// echo "<pre>";print_r($csv_data);echo"</pre>";#die();

		// parse browse tree guide data
		$result = $this->parseBTG( $csv_data );

		return $result;
	} // importBTG()


	// clean previously imported BTG for current tpl_id
	function cleanBTG( $tpl_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'amazon_btg';

		// first remove all nodes for current tpl_id (v2)
		// (to clean incorrectly imported data automatically)
		$wpdb->delete( $table, array('tpl_id' => $tpl_id, 'site_id' => $this->site_id ) );
		$wpdb->delete( $table, array('tpl_id' => 0,       'site_id' => $this->site_id ) ); // remove unassigned/stale items from old version

	} // cleanBTG()


	function parseBTG( $csv_rows ) {
		global $wpdb;
		// $table = $wpdb->prefix . self::TABLENAME;
		$table = $wpdb->prefix . 'amazon_btg';
		$path_cache  = array();
		$child_cache = array();
		$warnings    = '';

		// first remove all nodes with this file's top id (v1) (obsolete?)
		$wpdb->delete( $table, array('top_id' => $csv_rows[0][0], 'site_id' => $this->site_id ) );
		$wpdb->delete( $table, array('node_id' => $csv_rows[0][0], 'site_id' => $this->site_id ) );
		// echo "<pre>";print_r( $csv_rows[0][0] );echo"</pre>";die();

		// // first remove all nodes for current tpl_id (v2) - moved to cleanBTG()
		// $wpdb->delete( $table, array('tpl_id' => $this->tpl_id, 'site_id' => $this->site_id ) );
		// $wpdb->delete( $table, array('tpl_id' => 0, 'site_id' => $this->site_id ) ); // remove unassigned/stale items from old version


		// make sure the CSV array is properly sorted
		foreach ($csv_rows as $key => $row) {
		    $sort_order[$key] = $row[1];
		}
		array_multisort( $sort_order, SORT_ASC, $csv_rows );


		foreach ($csv_rows as $line => $row) {

			// basic values
			$node_id   = $row[0]; // Node ID
			$node_path = $row[1]; // Node Path
			$node_name = basename( $node_path );
			$level     = substr_count( $node_path, '/' );
			$top_id    = 0;
			$parent_id = 0;
			$leaf      = 1;
			$keyword   = '';

			// extract keyword
			if ( preg_match( '/item_type_keyword:([[:alnum:]_-]*)/', @$row[2], $matches ) ) {
				$keyword = $matches[1];
			}
			if ( preg_match( '/item_type_keyword:([[:alnum:]_-]*)/', @$row[3], $matches ) ) {
				$keyword = $matches[1];
			}

			// analyze hierarchy
			$parent_path = dirname( $node_path );
			if ( isset( $path_cache[ $parent_path ] ) ) {
				
				$parent_id = $path_cache[ $parent_path ];
				$top_name = substr( $node_path, 0, strpos($node_path,'/') );
				$top_id = isset( $path_cache[ $top_name ] ) ? $path_cache[ $top_name ] : 0;

			} elseif ( $level ) {
				// echo "<pre>Warning: could not find parent node: ";print_r($parent_path);echo"</pre>";#die();				

				// use "grand parent" - fix nodes like 'PS/2 Cables' and 'I/O Adapters'
				$parent_path = dirname( $parent_path );
				if ( isset( $path_cache[ $parent_path ] ) ) {
					$parent_id = $path_cache[ $parent_path ];
					$top_name = substr( $node_path, 0, strpos($node_path,'/') );
					$top_id = isset( $path_cache[ $top_name ] ) ? $path_cache[ $top_name ] : 0;
				}

			}
			
			// update leaf cache
			if ( $parent_id ) {
				$child_cache[ $parent_id ] = true;
			}

			// self check
			if ( $level && ! $parent_id ) {
				// $warnings .= "Warning: skipped node without parent: $node_path <br>";
				WPLA()->logger->warn("skipped node without parent: $node_path (parent node: $parent_path)");
				continue;
			}

			// duplicate check
			// if ( in_array( $node_id, $path_cache ) && $path_cache[$node_path] == $node_id ) { // reverted because duplicate NodeIDs will cause duplicate child entries to be shown in BTG selector
			if ( in_array( $node_id, $path_cache ) ) {
				WPLA()->logger->info("skipped duplicate node: $node_path ($node_id)");
				continue;
			}


			// build sql columns
			$data = array();
			$data['node_id']   = $node_id;
			$data['node_path'] = $node_path;
			$data['node_name'] = $node_name;
			$data['keyword']   = $keyword;
			$data['parent_id'] = $parent_id;
			$data['top_id']    = $top_id;
			$data['level']     = $level;
			$data['leaf']      = $leaf;
			$data['tpl_id']    = $this->tpl_id;
			$data['site_id']   = $this->site_id;

			// echo "<pre>";print_r($data);echo"</pre>";#die();

			// insert to db...
			$result = $wpdb->insert( $table, $data );
			WPLA()->logger->info("inserted browse tree node $node_id: $node_path");

			// add to cache
			$path_cache[ $node_path ] = $node_id;
		}

		// process parent categories - set leaf to 0
		foreach ( $child_cache as $node_id => $has_childs ) {
			$data   = array( 'leaf' => 0 );
			$where  = array( 'node_id' => $node_id );
			$result = $wpdb->update( $table, $data, $where );
		}

		if ( $warnings ) {
			wpla_show_message( $warnings, 'warn' );
		}

		WPLA()->logger->info("browse tree nodes processed: ".sizeof($path_cache));
		return sizeof($path_cache);
	} // parseBTG()


	function readBTG( $filename, $delimiter = ',' ) {

	    if ( ! file_exists($filename) || ! is_readable($filename) ) {
	    	echo "<pre>Could not read $filename</pre>";
    	    return false;
	    }

	    $header = NULL;
	    $data = array();
	    if ( ( $handle = fopen($filename, 'r') ) !== false )
	    {
	        while ( ( $row = fgetcsv($handle, 1000, $delimiter) ) !== false )
	        {
	        	// echo "<pre>";print_r($row);echo"</pre>";#die();

	            if ( ! $header )
	                $header = $row;
	            else
	                // $data[] = array_combine( $header, $row );
	                $data[] = $row;
	        }
	        fclose($handle);
	    }

	    return $data;
	} // readBTG


	// remove feed template
	function removeFeedTemplate( $tpl_id ) {
		global $wpdb;
		$templates_table = $wpdb->prefix . 'amazon_feed_templates';
		$fields_table    = $wpdb->prefix . 'amazon_feed_tpl_data';
		$values_table    = $wpdb->prefix . 'amazon_feed_tpl_values';
		$tpl_id          = intval($tpl_id);
		if ( ! $tpl_id ) return;

		$wpdb->query("DELETE FROM $values_table    WHERE tpl_id = '$tpl_id' ");
		$wpdb->query("DELETE FROM $fields_table    WHERE tpl_id = '$tpl_id' ");
		$wpdb->query("DELETE FROM $templates_table WHERE     id = '$tpl_id' ");
	}

	// un-CamelCase string
	function spacify( $str ) {
		return preg_replace('/([a-z])([A-Z])/', '$1 $2', $str);
	}

	// generate field label from fieldname (sale-price -> Sale Price)
	static function generate_label_from_fieldname( $str ) {
		$str = str_replace( '-', ' ', $str );
		$str = str_replace( '_', ' ', $str );
		$str = ucwords( $str );
		$str = str_replace( 'Id', 'ID', $str );
		$str = str_replace( 'Sku', 'SKU', $str );
		return $str;
	}


} // class WPLA_FeedTemplateHelper
