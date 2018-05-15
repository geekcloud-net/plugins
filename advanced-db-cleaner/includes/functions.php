<?php
/** Cleans all elements in the current site and in MU according to the selected type */
function aDBc_clean_all_elements_type($type){
	global $wpdb;
	if(function_exists('is_multisite') && is_multisite()){
		$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach($blogs_ids as $blog_id){
			switch_to_blog($blog_id);
			aDBc_clean_elements_type($type);
			restore_current_blog();
		}
	}else{
		aDBc_clean_elements_type($type);
	}
}

/** Cleans all elements in the current site according to the selected type */
function aDBc_clean_elements_type($type){
	global $wpdb;
	switch($type){
		case "revision":
			$wpdb->query("DELETE FROM $wpdb->posts WHERE post_type = 'revision'");
			break;
		case "draft":
			$wpdb->query("DELETE FROM $wpdb->posts WHERE post_status = 'draft'");
			break;
		case "auto-draft":
			$wpdb->query("DELETE FROM $wpdb->posts WHERE post_status = 'auto-draft'");
			break;
		case "trash-posts":
			$wpdb->query("DELETE FROM $wpdb->posts WHERE post_status = 'trash'");
			break;					
		case "moderated-comments":
			$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = '0'");
			break;
		case "spam-comments":
			$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = 'spam'");
			break;
		case "trash-comments":
			$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_approved = 'trash'");
			break;
		case "orphan-postmeta":
			$wpdb->query("DELETE pm FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL");
			break;
		case "orphan-commentmeta":
			$wpdb->query("DELETE FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_id FROM $wpdb->comments)");
			break;
		case "orphan-relationships":
			$wpdb->query("DELETE FROM $wpdb->term_relationships WHERE term_taxonomy_id=1 AND object_id NOT IN (SELECT id FROM $wpdb->posts)");
			break;
		case "dashboard-transient-feed":
			$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_site_transient_browser_%' OR option_name LIKE '_site_transient_timeout_browser_%' OR option_name LIKE '_transient_feed_%' OR option_name LIKE '_transient_timeout_feed_%'");
			break;
	}
}

/** Cleans all elements in the current site and in MU (used by the scheduler) */
function aDBc_clean_all_elements(){
	global $wpdb;
	if(function_exists('is_multisite') && is_multisite()){
		$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach($blogs_ids as $blog_id){
			switch_to_blog($blog_id);
			aDBc_clean_elements();
			restore_current_blog();
		}
	}else{
		aDBc_clean_elements();
	}
}

/** Cleans all elements in the current site */
function aDBc_clean_elements(){
	global $wpdb;
	$wpdb->query("DELETE 	FROM $wpdb->posts WHERE post_type = 'revision'");
	$wpdb->query("DELETE 	FROM $wpdb->posts WHERE post_status = 'draft'");
	$wpdb->query("DELETE 	FROM $wpdb->posts WHERE post_status = 'auto-draft'");
	$wpdb->query("DELETE 	FROM $wpdb->posts WHERE post_status = 'trash'");
	$wpdb->query("DELETE 	FROM $wpdb->comments WHERE comment_approved = '0'");
	$wpdb->query("DELETE 	FROM $wpdb->comments WHERE comment_approved = 'spam'");
	$wpdb->query("DELETE 	FROM $wpdb->comments WHERE comment_approved = 'trash'");
	$wpdb->query("DELETE pm FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL");
	$wpdb->query("DELETE 	FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_id FROM $wpdb->comments)");
	$wpdb->query("DELETE 	FROM $wpdb->term_relationships WHERE term_taxonomy_id=1 AND object_id NOT IN (SELECT id FROM $wpdb->posts)");
	$wpdb->query("DELETE 	FROM $wpdb->options WHERE option_name LIKE '_site_transient_browser_%' OR option_name LIKE '_site_transient_timeout_browser_%' OR option_name LIKE '_transient_feed_%' OR option_name LIKE '_transient_timeout_feed_%'");
}


/** Counts all elements to clean (in the current site or MU) */
function aDBc_count_all_elements_to_clean(){
	global $wpdb;
	$aDBc_unused["revision"]['name'] 					= __('Revisions','advanced-db-cleaner');
	$aDBc_unused["draft"]['name'] 						= __('Drafts','advanced-db-cleaner');
	$aDBc_unused["auto-draft"]['name'] 					= __('Auto Drafts','advanced-db-cleaner');
	$aDBc_unused["trash-posts"]['name'] 				= __('Trash posts','advanced-db-cleaner');
	$aDBc_unused["moderated-comments"]['name'] 			= __('Pending comments','advanced-db-cleaner');
	$aDBc_unused["spam-comments"]['name'] 				= __('Spam Comments','advanced-db-cleaner');
	$aDBc_unused["trash-comments"]['name'] 				= __('Trash comments','advanced-db-cleaner');
	$aDBc_unused["orphan-postmeta"]['name'] 			= __('Orphan Postmeta','advanced-db-cleaner');
	$aDBc_unused["orphan-commentmeta"]['name'] 			= __('Orphan Commentmeta','advanced-db-cleaner');
	$aDBc_unused["orphan-relationships"]['name'] 		= __('Orphan Relationships','advanced-db-cleaner');
	$aDBc_unused["dashboard-transient-feed"]['name'] 	= __('Dashboard Transient Feed','advanced-db-cleaner');
	// Initialize counts to 0
	foreach($aDBc_unused as $aDBc_type => $element_info){
		$aDBc_unused[$aDBc_type]['count'] = 0;
	}

	if(function_exists('is_multisite') && is_multisite()){
		$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach($blogs_ids as $blog_id){
			switch_to_blog($blog_id);
			aDBc_count_elements_to_clean($aDBc_unused);	
			restore_current_blog();
		}
	}else{
		aDBc_count_elements_to_clean($aDBc_unused);
	}
	return $aDBc_unused;
}

/** Counts elements to clean in the current site */
function aDBc_count_elements_to_clean(&$aDBc_unused){
	global $wpdb;
	$aDBc_unused["revision"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'revision'");
	$aDBc_unused["draft"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'draft'");
	$aDBc_unused["auto-draft"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'auto-draft'");
	$aDBc_unused["trash-posts"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'trash'");
	$aDBc_unused["moderated-comments"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '0'");
	$aDBc_unused["spam-comments"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = 'spam'");
	$aDBc_unused["trash-comments"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = 'trash'");
	$aDBc_unused["orphan-postmeta"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL");
	$aDBc_unused["orphan-commentmeta"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_id FROM $wpdb->comments)");
	$aDBc_unused["orphan-relationships"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id=1 AND object_id NOT IN (SELECT id FROM $wpdb->posts)");
	$aDBc_unused["dashboard-transient-feed"]['count'] += $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '_site_transient_browser_%' OR option_name LIKE '_site_transient_timeout_browser_%' OR option_name LIKE '_transient_feed_%' OR option_name LIKE '_transient_timeout_feed_%'");
}

/** Optimizes all tables having lost space (data_free > 0). Used by the scheduled task */
function aDBc_optimize_tables(){
	global $wpdb;
	$adbc_sql = "SELECT table_name, data_free FROM information_schema.tables WHERE table_schema = '" . DB_NAME ."' and Engine <> 'InnoDB' and data_free > 0";
	$result = $wpdb->get_results($adbc_sql);
	foreach($result as $row){
		$wpdb->query('OPTIMIZE TABLE ' . $row->table_name);
	}
}

/***********************************************************************************
*
* Common function to: options, tables and scheduled tasks processes
*
***********************************************************************************/

/** Prepares items (options, tables or tasks) to display + message*/
function aDBc_prepare_items_to_display(
	$deleted_items,
	&$items_to_display,
	&$aDBc_items_categories_info,
	&$aDBc_which_button_to_show,
	&$aDBc_message,
	&$aDBc_class_message,
	$items_type){

	// Prepare categories info
	switch($items_type){
		case 'tasks' :
			$aDBc_all_items = aDBc_get_all_scheduled_tasks();
			$aDBc_saved_items = get_option("aDBc_tasks_status");
			$aDBc_items_categories_info = array(
					'all' 	=> array('name' => __('All tasks', 'advanced-db-cleaner'),		'color' => '#4E515B',  	'count' => 0),
					'o'		=> array('name' => __('Orphan tasks','advanced-db-cleaner'),	'color' => '#E97F31', 	'count' => 0),
					'p'		=> array('name' => __('Plugins tasks', 'advanced-db-cleaner'),	'color' => '#00BAFF', 	'count' => 0),
					't'		=> array('name' => __('Themes tasks', 'advanced-db-cleaner'),	'color' => '#45C966', 	'count' => 0),
					'w'		=> array('name' => __('WP tasks', 'advanced-db-cleaner'),		'color' => '#D091BE', 	'count' => 0)
					);
			break;
		case 'options' :
			$aDBc_all_items = aDBc_get_all_options();
			$aDBc_saved_items = get_option("aDBc_options_status");
			$aDBc_items_categories_info = array(
					'all' 	=> array('name' => __('All options', 'advanced-db-cleaner'),	'color' => '#4E515B',  	'count' => 0),
					'o'		=> array('name' => __('Orphan options','advanced-db-cleaner'),	'color' => '#E97F31', 	'count' => 0),
					'p'		=> array('name' => __('Plugins options', 'advanced-db-cleaner'),'color' => '#00BAFF', 	'count' => 0),
					't'		=> array('name' => __('Themes options', 'advanced-db-cleaner'),	'color' => '#45C966', 	'count' => 0),
					'w'		=> array('name' => __('WP options', 'advanced-db-cleaner'),		'color' => '#D091BE', 	'count' => 0)
					);
			break;
		case 'tables' :
			$aDBc_all_items = aDBc_get_all_tables();
			$aDBc_saved_items = get_option("aDBc_tables_status");
			$aDBc_items_categories_info = array(
					'all' 	=> array('name' => __('All tables', 'advanced-db-cleaner'),		'color' => '#4E515B',  	'count' => 0),
					'o'		=> array('name' => __('Orphan tables','advanced-db-cleaner'),	'color' => '#E97F31', 	'count' => 0),
					'p'		=> array('name' => __('Plugins tables', 'advanced-db-cleaner'),	'color' => '#00BAFF', 	'count' => 0),
					't'		=> array('name' => __('Themes tables', 'advanced-db-cleaner'),	'color' => '#45C966', 	'count' => 0),
					'w'		=> array('name' => __('WP tables', 'advanced-db-cleaner'),		'color' => '#D091BE', 	'count' => 0)
					);
			break;
	}

	// Add info to items (blongs_to + type) + count them
	$aDBc_items_status = empty($aDBc_saved_items['items_status']) ? "" : $aDBc_saved_items['items_status'];
	$aDBc_last_file_path = empty($aDBc_saved_items['last_file_path']) ? "" : $aDBc_saved_items['last_file_path'];
	$aDBc_search_iteration = empty($aDBc_saved_items['search_iteration']) ? "" : $aDBc_saved_items['search_iteration'];

	if(!empty($aDBc_items_status)){
		$item_info = explode(",", $aDBc_items_status);
		foreach($item_info as $item){
			$columns = explode(":", $item);
			// Prevent adding an item that was cleaned (maybe by other plugins) but not updated in DB
			if(array_key_exists($columns[0], $aDBc_all_items)) {
				$aDBc_all_items[$columns[0]]['belongs_to'] = $columns[1];
				$aDBc_all_items[$columns[0]]['type'] = $columns[2];
				$aDBc_all_items[$columns[0]]['maybe_belongs_to'] = $columns[4];
				$site_ids_array = explode("|", $columns[3]);
				if(!empty($aDBc_items_categories_info[$columns[2]])){
					$aDBc_items_categories_info[$columns[2]]['count'] += count($site_ids_array);
				}	
			}
		}

		// Adjust counts of items after a clean-up in Multisite. This is necessary because items status in DB are not updated yet after a clean-up
		if(function_exists('is_multisite') && is_multisite()){
			foreach($deleted_items as $item_name => $total_deleted){
				if(!empty($aDBc_all_items[$item_name])){
					$aDBc_items_categories_info[$aDBc_all_items[$item_name]['type']]['count'] -= $total_deleted;
				}
			}
		}
	}

	// Prepare items to display
	foreach($aDBc_all_items as $item_name => $item_info){
		$aDBc_items_categories_info['all']['count'] += count($item_info['sites']);
		if($_GET['aDBc_cat'] != "all" && $item_info['type'] != $_GET['aDBc_cat']){
			continue;
		}
		switch($item_info['type']){
			case '' :
				$belongs_to = '<span style="color:#ccc">' . __('Uncategorised!', 'advanced-db-cleaner') . '</span>';
				break;
			case 'o' :
				$belongs_to = '<span style="color:#E97F31">' . __('Orphan!', 'advanced-db-cleaner') . '</span>';
				break;
			case 'w' :
				$belongs_to = '<span style="color:#D091BE">' . __('Wordpress core', 'advanced-db-cleaner');
				// Add percent % if any
				$belongs_to .= $item_info['belongs_to'] == "w" ? "" : " ".$item_info['belongs_to'];
				$belongs_to .= '</span>';
				break;
			case 'p' :
				$belongs_to = '<span style="color:#00BAFF">' . $item_info['belongs_to'] . '</span>';
				break;
			case 't' :
				$belongs_to = '<span style="color:#45C966">' . $item_info['belongs_to'] . '</span>';
				break;
		}
		foreach($item_info['sites'] as $site_id => $site_item_info){
			switch($items_type){
				case 'tasks' :
					array_push($items_to_display, array(
							'hook_name' 		=> $item_name,
							'site_id' 			=> $site_id,
							'next_run' 			=> $site_item_info['next_run'] . ' - ' . $site_item_info['frequency'],
							'hook_belongs_to'	=> $belongs_to
					));
					break;
				case 'options' :
					array_push($items_to_display, array(
							'option_name' 		=> $item_name,
							'option_value' 		=> esc_html($site_item_info['value']),
							'option_autoload' 	=> $site_item_info['autoload'],
							'site_id' 			=> $site_id,
							'option_belongs_to' => $belongs_to
					));
					break;
				case 'tables' :
					array_push($items_to_display, array(
							'table_name' 		=> $item_name,
							'table_prefix' 		=> $site_item_info['prefix'],
							'table_rows' 		=> $site_item_info['rows'],
							'table_size' 		=> $site_item_info['size'],
							'site_id' 			=> $site_id,
							'table_belongs_to' 	=> $belongs_to
					));
					break;
			}
		}
	}

	// Should we update items in DB? This is necessary after deleting some tasks
	if(!empty($deleted_items) && !empty($aDBc_items_status)){
		aDBc_save_items_status_to_DB($aDBc_all_items, $aDBc_last_file_path, $aDBc_search_iteration, $items_type);
	}

	// Select which button to show, is it "new search" or "continue search"?
	// If $aDBc_saved_items['last_file_path'] contains a path, then we conclude that the last search has failed => display "continue searching" button
	if(empty($aDBc_last_file_path)){
		$aDBc_which_button_to_show = "new_search";
	}else{
		$aDBc_which_button_to_show = "continue_search";
		// Calculate remaining uncategorised items
		$aDBc_remaining_items = 0;
		foreach($aDBc_all_items as $item_name => $item_info){
			if(empty($item_info['belongs_to'])){
				$aDBc_remaining_items++;
			}
		}
		switch($items_type){
			case 'tasks' :
				$aDBc_last_search_msg = __('Your last search for orphan tasks has been stopped due to your server timeout', 'advanced-db-cleaner');
				$aDBc_remaining_msg = __('Remaining uncategorised tasks:', 'advanced-db-cleaner');
				break;
			case 'options' :
				$aDBc_last_search_msg = __('Your last search for orphan options has been stopped due to your server timeout', 'advanced-db-cleaner');
				$aDBc_remaining_msg = __('Remaining uncategorised options:', 'advanced-db-cleaner');
				break;
			case 'tables' :
				$aDBc_last_search_msg = __('Your last search for orphan tables has been stopped due to your server timeout', 'advanced-db-cleaner');
				$aDBc_remaining_msg = __('Remaining uncategorised tables:', 'advanced-db-cleaner');
				break;
		}		
		// Prepare message to show if last search was stopped due to timeout
		$aDBc_message  = '<font color="orange">- ' . $aDBc_last_search_msg . '</font>';
		$aDBc_message .= '<font color="black">';
		$aDBc_message .= '<br/>- ' . __('Please click "continue searching" button to continue your search.', 'advanced-db-cleaner');
		$aDBc_message .= '<br/>- ' . $aDBc_remaining_msg . " <b>". $aDBc_remaining_items .'</b>';
		$aDBc_message .= '</font>';
		$aDBc_class_message = "error";
	}
}


/* 
* Searches for any item name in the "$items_to_search_for" in all files of WordPress
* Saves results in "$items_to_search_for" itself
*/
function aDBc_run_search_for_items($aDBc_core_items, &$aDBc_success_message, $items_type){

	/*
	* $aDBc_affect_unknown_to_wp : when an item name is found in a file but it is not a plugin nor a theme, then should we affect it to WP?
	* $aDBc_affect_unknown_to_wp is used by aDBc_find_items() function
	*/
	$aDBc_affect_unknown_to_wp = 1;

	// Prepare variables
	// $items_to_search_for contains all items (options, tables or tasks) that we should search for in files
	// $aDBc_saved_items contains all items that were saved in DB in the previous search
	switch($items_type){
		case 'tasks' :
			$items_to_search_for = aDBc_get_all_scheduled_tasks();
			$aDBc_saved_items = get_option("aDBc_tasks_status");
			$aDBc_message = __('Searching for tasks was performed successfully!', 'advanced-db-cleaner');
			break;
		case 'options' :
			$items_to_search_for = aDBc_get_all_options();
			$aDBc_saved_items = get_option("aDBc_options_status");
			$aDBc_message = __('Searching for options was performed successfully!', 'advanced-db-cleaner');
			break;
		case 'tables' :
			$items_to_search_for = aDBc_get_all_tables();
			$aDBc_saved_items = get_option("aDBc_tables_status");
			$aDBc_affect_unknown_to_wp = 0;
			$aDBc_message = __('Searching for tables was performed successfully!', 'advanced-db-cleaner');
			break;
	}

	// If an item name belongs to WP core, modify its type and belong_to
	foreach($aDBc_core_items as $item_name){
		if(array_key_exists($item_name, $items_to_search_for)) {
			$items_to_search_for[$item_name]['belongs_to'] = "w";
			$items_to_search_for[$item_name]['type'] = "w";
		}
	}

	/*
	* $aDBc_last_file_path contains (if not empty) the last path processed before server timeout.
	* It is used mainly by aDBc_find_items() function to detect if we should run a new search or continue search after timeout failed
	* If empty then start search from scratch, else skip files until finding That last file then continue searching from that position
	*/
	$aDBc_last_file_path = empty($aDBc_saved_items['last_file_path']) ? "" : $aDBc_saved_items['last_file_path'];

	// If we are continuing search after timeout, affect also already detected items
	if(!empty($aDBc_last_file_path)){
		$aDBc_items_status = empty($aDBc_saved_items['items_status']) ? "" : $aDBc_saved_items['items_status'];
		$items_info = explode(",", $aDBc_items_status);
		foreach($items_info as $item){
			$columns = explode(":", $item);
			// Prevent adding a item that was cleaned (maybe by other plugins) but not updated in DB
			if(array_key_exists($columns[0], $items_to_search_for)) {
				$items_to_search_for[$columns[0]]['belongs_to'] = $columns[1];
				$items_to_search_for[$columns[0]]['type'] = $columns[2];
				$items_to_search_for[$columns[0]]['maybe_belongs_to'] = $columns[4];
			}
		}
	}

	// Search starting time, to prevent timeout in aDBc_find_items() function
	$starting_time = time();

	// $aDBc_search_should_stop is used by aDBc_find_items() function to see if we should stop search either for timeout or once detecting all tasks
	$aDBc_search_should_stop = 0;

	// $aDBc_search_iteration indicates if we are running a search for the hole item name in files (1), or if we are trying to find a percent% of that name (2)
	$aDBc_search_iteration = empty($aDBc_saved_items['search_iteration']) ? "1" : $aDBc_saved_items['search_iteration'];

	// For each none categorized item, search for it in all possible directories of WP installation.
	$search_state = aDBc_find_items_in_all_paths($items_to_search_for, $aDBc_last_file_path, $aDBc_affect_unknown_to_wp, $aDBc_search_should_stop, $aDBc_search_iteration, $starting_time);
	// If there are any remaining orphan items after iteration 1, make a second search iteration to find percent %
	if($search_state != '0' && $aDBc_search_iteration == "1"){
		// Test if all items has been detected to stop search and not waste time searching in iteration 2
		foreach($items_to_search_for as $aDBc_item => $aDBc_info){
			// If at least one item is remaining then we should launch iteration 2
			if(empty($aDBc_info['belongs_to'])){
				$aDBc_search_iteration = "2";
				$search_state = aDBc_find_items_in_all_paths($items_to_search_for, $aDBc_last_file_path, $aDBc_affect_unknown_to_wp, $aDBc_search_should_stop, $aDBc_search_iteration, $starting_time);
				// Break since we have processed all remaining items in iteration 2
				break;
			}
		}
	}

	// If search iteration 2 has been completed, then transform "maybe_belongs_to" to "belongs_to". Also do the last search
	if($search_state != '0' && $aDBc_search_iteration == "2"){
		// For items which have a prediction with percent but not have belongs_to, set their belongs_to with the percent % data
		foreach($items_to_search_for as $aDBc_item => $aDBc_info){
			if(empty($aDBc_info['belongs_to']) && !empty($aDBc_info['maybe_belongs_to'])){
				$aDBc_maybe_belongs_to_parts = explode("/", $aDBc_info['maybe_belongs_to']);
				// If the part1 is not empty, we will use it, else use the part 2
				if(!empty($aDBc_maybe_belongs_to_parts[0])){

					$aDBc_maybe_belongs_to_info = explode("|", $aDBc_maybe_belongs_to_parts[0]);
					$items_to_search_for[$aDBc_item]['belongs_to'] = $aDBc_maybe_belongs_to_info[0] == "w" ? "" : $aDBc_maybe_belongs_to_info[0];
					$items_to_search_for[$aDBc_item]['belongs_to'] .= " (".$aDBc_maybe_belongs_to_info[2]."%)";
					$items_to_search_for[$aDBc_item]['type'] = $aDBc_maybe_belongs_to_info[1];	

				}else if(!empty($aDBc_maybe_belongs_to_parts[1])){

					$aDBc_maybe_belongs_to_info = explode("|", $aDBc_maybe_belongs_to_parts[1]);
					$items_to_search_for[$aDBc_item]['belongs_to'] = $aDBc_maybe_belongs_to_info[0] == "w" ? "" : $aDBc_maybe_belongs_to_info[0];
					$items_to_search_for[$aDBc_item]['belongs_to'] .= " (".$aDBc_maybe_belongs_to_info[2]."%)";
					$items_to_search_for[$aDBc_item]['type'] = $aDBc_maybe_belongs_to_info[1];

				}
			}
		}

		// As final step, make all items to orphan if they have an empty "belong_to"
		foreach($items_to_search_for as $aDBc_item => $aDBc_info){
			if(empty($aDBc_info['belongs_to'])){
				$items_to_search_for[$aDBc_item]['belongs_to'] = "o";
				$items_to_search_for[$aDBc_item]['type'] = "o";
			}
			// Empty maybe_belongs_to
			$items_to_search_for[$aDBc_item]['maybe_belongs_to'] = "";
		}
	}

	// Update the message to show to the user
	if($search_state != '0'){
		$aDBc_search_iteration = "";
		$aDBc_success_message = $aDBc_message;
	}

	// Save new items status to DB
	aDBc_save_items_status_to_DB($items_to_search_for, $aDBc_last_file_path, $aDBc_search_iteration, $items_type);
}

/*
* Searches for any item name in the "$items_to_search_for" in all files in all possibles paths of plugins, themes, wp-content...
* Saves results in "$items_to_search_for" itself
*/
function aDBc_find_items_in_all_paths(
		&$items_to_search_for,
		&$aDBc_last_file_path,
		$aDBc_affect_unknown_to_wp,
		&$aDBc_search_should_stop,
		$aDBc_search_iteration,
		$starting_time){

	// Prepare WP Themes directories paths (useful to detect if an item belongs to a theme and detect the theme name)
	global $wp_theme_directories;
	$aDBc_themes_paths_array = array();
	foreach($wp_theme_directories as $aDBc_theme_path){
		array_push($aDBc_themes_paths_array, str_replace('\\' ,'/', $aDBc_theme_path));
	}

	// For every none categorized task, search for it in all WP files. $search_state = '0' if the search failed due to time-out
	$search_state = aDBc_find_items(ADBC_ABSPATH, $items_to_search_for, $aDBc_last_file_path, $aDBc_themes_paths_array, $aDBc_affect_unknown_to_wp, $aDBc_search_should_stop, $aDBc_search_iteration, $starting_time);

	// Search also in WP-content if it is outside ADBC_ABSPATH
	if($search_state != '0' && is_dir(ADBC_WP_CONTENT_DIR_PATH)){
		if(strpos(ADBC_WP_CONTENT_DIR_PATH, ADBC_ABSPATH) === false){
			$search_state = aDBc_find_items(ADBC_WP_CONTENT_DIR_PATH, $items_to_search_for, $aDBc_last_file_path, $aDBc_themes_paths_array, $aDBc_affect_unknown_to_wp, $aDBc_search_should_stop, $aDBc_search_iteration, $starting_time);
		}
	}

	// Search also in MU must have plugins if it is outside ADBC_ABSPATH and ADBC_WP_CONTENT_DIR_PATH
	if(function_exists('is_multisite') && is_multisite()){
		if($search_state != '0' && is_dir(ADBC_WPMU_PLUGIN_DIR_PATH)){
			if(strpos(ADBC_WPMU_PLUGIN_DIR_PATH, ADBC_ABSPATH) === false && strpos(ADBC_WPMU_PLUGIN_DIR_PATH, ADBC_WP_CONTENT_DIR_PATH) === false){
				$search_state = aDBc_find_items(ADBC_WPMU_PLUGIN_DIR_PATH, $items_to_search_for, $aDBc_last_file_path, $aDBc_themes_paths_array, $aDBc_affect_unknown_to_wp, $aDBc_search_should_stop, $aDBc_search_iteration, $starting_time);
			}
		}
	}

	// Search in plugins directory if it is outside ADBC_WP_CONTENT_DIR_PATH and ADBC_ABSPATH
	if($search_state != '0' && is_dir(ADBC_WP_PLUGINS_DIR_PATH)){
		if(strpos(ADBC_WP_PLUGINS_DIR_PATH, ADBC_ABSPATH) === false && strpos(ADBC_WP_PLUGINS_DIR_PATH, ADBC_WP_CONTENT_DIR_PATH) === false){
			$search_state = aDBc_find_items(ADBC_WP_PLUGINS_DIR_PATH, $items_to_search_for, $aDBc_last_file_path, $aDBc_themes_paths_array, $aDBc_affect_unknown_to_wp, $aDBc_search_should_stop, $aDBc_search_iteration, $starting_time);
		}
	}

	// Search in themes directories if they are outside ADBC_WP_CONTENT_DIR_PATH and ADBC_ABSPATH
	if($search_state != '0'){
		foreach($aDBc_themes_paths_array as $aDBc_theme_path){
			if(is_dir($aDBc_theme_path)){
				if(strpos($aDBc_theme_path, ADBC_ABSPATH) === false && strpos($aDBc_theme_path, ADBC_WP_CONTENT_DIR_PATH) === false){
					$search_state = aDBc_find_items($aDBc_theme_path, $items_to_search_for, $aDBc_last_file_path, $aDBc_themes_paths_array, $aDBc_affect_unknown_to_wp, $aDBc_search_should_stop, $aDBc_search_iteration, $starting_time);
				}
			}
		}
	}

	return $search_state;
}

/* 
* Searches for any item name in the "$items_to_search_for" in all WP files
* $path_to_start_from : path to start searching from
* $items_to_search_for : array containing items to search for, will hold also result of searching
*/
function aDBc_find_items(
		$path_to_start_from,
		&$items_to_search_for,
		&$aDBc_last_file_path,
		$aDBc_themes_paths_array,
		$aDBc_affect_unknown_to_wp,
		&$aDBc_search_should_stop,
		$aDBc_search_iteration,
		$starting_time){

		$aDBc_fp = opendir($path_to_start_from);

		while($aDBc_f = readdir($aDBc_fp)){

			// Before anything, test if search should stop due to server timeout. 
			// If $aDBc_search_should_stop is equal to 0, we should not leave yet, continue processing.
			// If $aDBc_search_should_stop is equal to 1, then we should leave because of timeout.
			// If $aDBc_search_should_stop is equal to 2 then we should leave because all items has been found.
			if($aDBc_search_should_stop){
				if($aDBc_search_should_stop == "1"){
					return 0;
				}else if($aDBc_search_should_stop == "2"){
					return 1;
				}
			}

			// Ignore symbolic links
			if(preg_match("#^\.+$#", $aDBc_f)){
				continue;
			}

			// Create the full path for the current file/folder
			$full_path = $path_to_start_from . "/" . $aDBc_f;

			// If the current path is a folder, then call recursive function
			if(is_dir($full_path)) {

				// Skip upload directory while searching
				if(strpos($full_path, ADBC_UPLOAD_DIR_PATH) !== false){
					continue;
				}
				aDBc_find_items($full_path, $items_to_search_for, $aDBc_last_file_path, $aDBc_themes_paths_array, $aDBc_affect_unknown_to_wp, $aDBc_search_should_stop, $aDBc_search_iteration, $starting_time);

			}else{

				// Test execution time to prevent timeout fatal error
				if((time() - $starting_time) >= ADBC_PLUGIN_TIMEOUT){
					$aDBc_search_should_stop = 1;
					$aDBc_last_file_path = $full_path;
					return 0;
				}

				// Test if all items has been detected to stop search and not waste time searching for none items
				$aDBc_total_ramining = 0;
				foreach($items_to_search_for as $aDBc_item => $aDBc_info){
					if(empty($aDBc_info['belongs_to'])){
						$aDBc_total_ramining++;
						break;
					}
				}
				if($aDBc_total_ramining == 0){
					$aDBc_search_should_stop = 2;
					$aDBc_last_file_path = "";
					break;
				}

				// If we are continuing search after a timeout, skip files until finding last one before timeout
				if(!empty($aDBc_last_file_path) && $aDBc_last_file_path == $full_path){
					$aDBc_last_file_path = "";
				}

				// Ignore all files that are not php
				if(strpos($aDBc_f, ".php") === false){
					continue;
				}

				// Process file only if $aDBc_last_file_path is empty (if we are running new search or after finding the last file processed after timeout)
				if(empty($aDBc_last_file_path)){

					// If the current path is a valid php file then get its content
					$aDBc_file_content = file_get_contents($full_path);

					// Convert the content to lower case if we are in iteration 2
					if($aDBc_search_iteration == "2"){
						$aDBc_file_content = strtolower($aDBc_file_content);
					}

					foreach($items_to_search_for as $item_name => $item_info){

						// If the item is not localized yet
						if(empty($item_info['belongs_to'])){

							// Convert the item name to lower case if we are in iteration 2
							$aDBc_item_name = $item_name;
							if($aDBc_search_iteration == "2"){
								$aDBc_item_name = strtolower($item_name);
							}
							
							// If search iteration is 1 then search for the hole name of item
							if($aDBc_search_iteration == "1"){

								if(strpos($aDBc_file_content, $aDBc_item_name) !== false){
									$aDBc_type_detected = 0;
									// Is a plugin?
									if(strpos($full_path, ADBC_WP_PLUGINS_DIR_PATH) !== false){
										$aDBc_path = str_replace(ADBC_WP_PLUGINS_DIR_PATH."/", "", $full_path);
										$plugin_name = explode("/", $aDBc_path, 2);
										$items_to_search_for[$item_name]['belongs_to'] = $plugin_name[0];
										$items_to_search_for[$item_name]['type'] = "p";
										$aDBc_type_detected = 1;
									}
									// If not a plugin, then is a theme?
									if(!$aDBc_type_detected){
										foreach($aDBc_themes_paths_array as $aDBc_theme_path){
											if(strpos($full_path, $aDBc_theme_path) !== false){
												$aDBc_path = str_replace($aDBc_theme_path."/", "", $full_path);
												$theme_name = explode("/", $aDBc_path, 2);
												$items_to_search_for[$item_name]['belongs_to'] = $theme_name[0];
												$items_to_search_for[$item_name]['type'] = "t";
												$aDBc_type_detected = 1;
												break;
											}
										}
									}
									// If not a plugin and not a theme, then affect it to WP?
									if(!$aDBc_type_detected && $aDBc_affect_unknown_to_wp){
											$items_to_search_for[$item_name]['belongs_to'] = "w";
											$items_to_search_for[$item_name]['type'] = "w";
									}
								}

							}else{

								// If the hole item name does not belong to the current file, then try to get a percent of its presence
								$aDBc_maybe_belongs_to = empty($item_info['maybe_belongs_to']) ? "/" : $item_info['maybe_belongs_to'];
								$aDBc_maybe_belongs_to_parts = explode("/", $aDBc_maybe_belongs_to);

								$aDBc_item_name_len = strlen($aDBc_item_name);
								$aDBc_is_new_score_found = 0;

								$aDBc_percent1 = 35;
								$aDBc_item_part1 = substr($aDBc_item_name, 0, (($aDBc_percent1 * $aDBc_item_name_len) / 100));
								$aDBc_percent2 = 75;
								$aDBc_item_part2 = substr($aDBc_item_name, -(($aDBc_percent2 * $aDBc_item_name_len) / 100));

								// If aDBc_item_part1 appears in the file content
								if(strpos($aDBc_file_content, $aDBc_item_part1) !== false){
									
									$aDBc_maybe_belongs_to_info_part1 = explode("|", $aDBc_maybe_belongs_to_parts[0]);
									$aDBc_maybe_best_score_found = empty($aDBc_maybe_belongs_to_info_part1[2]) ? $aDBc_percent1 : $aDBc_maybe_belongs_to_info_part1[2];
									// Search for all combinations starting from the beginning of the item name
									for ($i = $aDBc_item_name_len; $i > 1; $i--) {
										$aDBc_substring = substr($aDBc_item_name, 0, $i);
										$aDBc_percent = (strlen($aDBc_substring) * 100) / $aDBc_item_name_len;
										if($aDBc_percent > $aDBc_maybe_best_score_found){
											if(strpos($aDBc_file_content, $aDBc_substring) !== false){
												// Bingo, we have find a percent %
												$aDBc_maybe_best_score_found = round($aDBc_percent, 2);
												$aDBc_is_new_score_found = 1;
												// Break after the first item found, since it is the longest
												break;
											}
										}else{
											break;
										}
									}
									// Test execution time to prevent timeout fatal error
									if((time() - $starting_time) >= ADBC_PLUGIN_TIMEOUT){
										$aDBc_search_should_stop = 1;
										$aDBc_last_file_path = $full_path;
										return 0;
									}

								}

								// If aDBc_item_part2 appears in the file content
								if(strpos($aDBc_file_content, $aDBc_item_part2) !== false){

									$aDBc_maybe_belongs_to_info_part2 = explode("|", $aDBc_maybe_belongs_to_parts[1]);
									$aDBc_maybe_best_score_found = empty($aDBc_maybe_belongs_to_info_part2[2]) ? $aDBc_percent2 : $aDBc_maybe_belongs_to_info_part2[2];
									// Search for all combinations starting from the end of the item name
									for ($i = 0; $i < $aDBc_item_name_len; $i++) {
										$aDBc_substring = substr($aDBc_item_name, $i);
										$aDBc_percent = (strlen($aDBc_substring) * 100) / $aDBc_item_name_len;
										if($aDBc_percent > $aDBc_maybe_best_score_found){
											if(strpos($aDBc_file_content, $aDBc_substring) !== false){
												// Bingo, we have find a percent %
												$aDBc_maybe_best_score_found = round($aDBc_percent, 2);
												$aDBc_is_new_score_found = 2;
												// Break after the first item found, since it is the longest
												break;
											}
										}else{
											break;
										}
									}
									// Test execution time to prevent timeout fatal error
									if((time() - $starting_time) >= ADBC_PLUGIN_TIMEOUT){
										$aDBc_search_should_stop = 1;
										$aDBc_last_file_path = $full_path;
										return 0;
									}

								}

								

								// Test is new score was found in order to update data
								if($aDBc_is_new_score_found){
									$aDBc_type_detected = 0;
									// Is a plugin?
									if(strpos($full_path, ADBC_WP_PLUGINS_DIR_PATH) !== false){
										$aDBc_path = str_replace(ADBC_WP_PLUGINS_DIR_PATH."/", "", $full_path);
										$plugin_name = explode("/", $aDBc_path, 2);
										// If the new score is >= 100%, fill belongs_to directly instead of maybe_belongs_to to win time
										if($aDBc_maybe_best_score_found >= 100){
											$items_to_search_for[$item_name]['belongs_to'] = $plugin_name[0];
											$items_to_search_for[$item_name]['type'] = "p";
											$items_to_search_for[$item_name]['maybe_belongs_to'] = "";
										}else{
											$aDBc_new_part = $plugin_name[0] . "|p|" . $aDBc_maybe_best_score_found;
											if($aDBc_is_new_score_found == "1"){
												$items_to_search_for[$item_name]['maybe_belongs_to'] = $aDBc_new_part . "/" . $aDBc_maybe_belongs_to_parts[1];
											}else{
												$items_to_search_for[$item_name]['maybe_belongs_to'] = $aDBc_maybe_belongs_to_parts[0] . "/" . $aDBc_new_part;
											}
										}
										$aDBc_type_detected = 1;
									}
									// If not a plugin, then is a theme?
									if(!$aDBc_type_detected){
										foreach($aDBc_themes_paths_array as $aDBc_theme_path){
											if(strpos($full_path, $aDBc_theme_path) !== false){
												$aDBc_path = str_replace($aDBc_theme_path."/", "", $full_path);
												$theme_name = explode("/", $aDBc_path, 2);
												// If the new score is >= 100%, fill belongs_to directly instead of maybe_belongs_to to win time
												if($aDBc_maybe_best_score_found >= 100){
													$items_to_search_for[$item_name]['belongs_to'] = $theme_name[0];
													$items_to_search_for[$item_name]['type'] = "t";
													$items_to_search_for[$item_name]['maybe_belongs_to'] = "";
												}else{
													$aDBc_new_part = $theme_name[0] . "|t|" . $aDBc_maybe_best_score_found;
													if($aDBc_is_new_score_found == "1"){
														$items_to_search_for[$item_name]['maybe_belongs_to'] = $aDBc_new_part . "/" . $aDBc_maybe_belongs_to_parts[1];
													}else{
														$items_to_search_for[$item_name]['maybe_belongs_to'] = $aDBc_maybe_belongs_to_parts[0] . "/" . $aDBc_new_part;
													}
												}
												$aDBc_type_detected = 1;
												break;
											}
										}
									}
									// If not a plugin and not a theme, then affect it to WP?
									if(!$aDBc_type_detected && $aDBc_affect_unknown_to_wp){
										// If the new score is >= 100%, fill belongs_to directly instead of maybe_belongs_to to win time
										if($aDBc_maybe_best_score_found >= 100){
											$items_to_search_for[$item_name]['belongs_to'] = "w";
											$items_to_search_for[$item_name]['type'] = "w";
											$items_to_search_for[$item_name]['maybe_belongs_to'] = "";
										}else{
											$aDBc_new_part = "w|w|" . $aDBc_maybe_best_score_found;
											if($aDBc_is_new_score_found == "1"){
												$items_to_search_for[$item_name]['maybe_belongs_to'] = $aDBc_new_part . "/" . $aDBc_maybe_belongs_to_parts[1];
											}else{
												$items_to_search_for[$item_name]['maybe_belongs_to'] = $aDBc_maybe_belongs_to_parts[0] . "/" . $aDBc_new_part;
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
}

/**
* $aDBc_last_file_path holds the last file path processed before timeout. If empty => the search has finished without timeout
* If the search has been stopped due to timeout, $aDBc_search_iteration indicates in which iteration the search has stopped: itaration1 or iteration2
*/
function aDBc_save_items_status_to_DB($new_items_array, $aDBc_last_file_path, $aDBc_search_iteration, $items_type){
	$aDBc_items_status = "";
	foreach($new_items_array as $item_name => $item_info){
		// If empty($item_info['belongs_to']) and empty($item_info['maybe_belongs_to']) => do not save
		if(!empty($item_info['belongs_to']) || !empty($item_info['maybe_belongs_to'])){
			$aDBc_items_status .= $item_name . ":" . $item_info['belongs_to'] . ":" . $item_info['type'] . ":";
			$sites_ids = "";
			foreach($item_info['sites'] as $site_id => $site_item_info){
				$sites_ids .= $site_id . "|";
			}
			$sites_ids = rtrim($sites_ids, "|");
			$aDBc_items_status .= $sites_ids;
			$aDBc_items_status .= ":" . $item_info['maybe_belongs_to'] . ",";
		}
	}
	$aDBc_items_status = rtrim($aDBc_items_status, ",");
	$aDBc_new_status = array(
		'items_status' 		=> $aDBc_items_status,
		'last_file_path' 	=> $aDBc_last_file_path,
		'search_iteration'	=> $aDBc_search_iteration
		
	);
	switch($items_type){
		case 'tasks' :
			update_option('aDBc_tasks_status', $aDBc_new_status);
			break;
		case 'options' :
			update_option('aDBc_options_status', $aDBc_new_status);
			break;
		case 'tables' :
			update_option('aDBc_tables_status', $aDBc_new_status);
			break;
	}
}

/***********************************************************************************
*
* Function proper to options processes
*
***********************************************************************************/

/** Prepares all options for all sites (if any) in a multidimensional array */
function aDBc_get_all_options() {
	$aDBc_all_options = array();
	global $wpdb;
	if(function_exists('is_multisite') && is_multisite()){
		$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach($blogs_ids as $blog_id){
			switch_to_blog($blog_id);
				aDBc_add_options($aDBc_all_options, $blog_id);
			restore_current_blog();
		}
	}else{
		aDBc_add_options($aDBc_all_options, "1");
	}
	return $aDBc_all_options;
}

/** Prepares options for one single site (Used by aDBc_get_all_options() function) */
function aDBc_add_options(&$aDBc_all_options, $blog_id) {
	global $wpdb;
	// Get the list of all options from the current WP database
	$aDBc_options_in_db = $wpdb->get_results("SELECT option_name, option_value, autoload FROM $wpdb->options WHERE option_name NOT LIKE '%transient%' and option_name NOT LIKE '%session%expire%'");
	foreach($aDBc_options_in_db as $option){
		// If the option has not been added yet, add it and initiate its info
		if(empty($aDBc_all_options[$option->option_name])){
			$aDBc_all_options[$option->option_name] = array('belongs_to' => '', 'maybe_belongs_to' => '', 'type' => '', 'sites' => array());
		}
		// Add info of the option according to the current site
		$aDBc_all_options[$option->option_name]['sites'][$blog_id] = array(
										'value' => strlen($option->option_value) > 30 ? substr($option->option_value, 0, 30) . " ..." : $option->option_value,
										'autoload' => $option->autoload
																	);	
	}
}

/***********************************************************************************
*
* Function proper to tables processes
*
***********************************************************************************/

/** Prepares all tables for all sites (if any) in a multidimensional array */
function aDBc_get_all_tables() {
	global $wpdb;
	// First, prepare an array containing rows and sizes of tables
	$aDBc_tables_rows_sizes = array();
	$aDBc_result = $wpdb->get_results('SHOW TABLE STATUS FROM `'.DB_NAME.'`');
	foreach($aDBc_result as $aDBc_row){
		$aDBc_table_size = ($aDBc_row->Data_length + $aDBc_row->Index_length) / 1024;
		$aDBc_table_size = round($aDBc_table_size, 1) . " KB";
		$aDBc_tables_rows_sizes[$aDBc_row->Name] = array('rows' => $aDBc_row->Rows, 'size' => $aDBc_table_size);
	}

	// Prepare ana array to hold all info about tables
	$aDBc_all_tables = array();
	$aDBc_prefix_list = array();
	// If is Multisite then we retrieve the list of all prefixes
	if(function_exists('is_multisite') && is_multisite()){
		$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach($blogs_ids as $blog_id){
			$aDBc_prefix_list[$wpdb->get_blog_prefix($blog_id)] = $blog_id;
		}
	}else{
		$aDBc_prefix_list[$wpdb->prefix] = "1";
	}
	// Get the names of all tables in the database
	$aDBc_all_tables_names = $wpdb->get_results("SELECT table_name FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");

	foreach($aDBc_all_tables_names as $aDBc_table){
		// Holds the possible prefixes found for the current table
		$aDBc_found_prefixes = array();
		// Test if the table name starts with a valid prefix
		foreach($aDBc_prefix_list as $prefix => $site_id){
			if(substr($aDBc_table->table_name, 0, strlen($prefix)) === $prefix){
				$aDBc_found_prefixes[$prefix] = $site_id;
			}
		}
		// If the table do not start with any valid prefix, we add it as it is
		if(count($aDBc_found_prefixes) == 0){
			$aDBc_table_name_without_prefix = $aDBc_table->table_name;
			$aDBc_table_prefix = "";
			$aDBc_table_site = "1";
		}else if(count($aDBc_found_prefixes) == 1){
			// If the number of possible prefixes found is 1, we add the table name with its data
			// Get the first element in $aDBc_found_prefixes
			reset($aDBc_found_prefixes);
			$aDBc_table_prefix = key($aDBc_found_prefixes);
			$aDBc_table_site = current($aDBc_found_prefixes);
			$aDBc_table_name_without_prefix = substr($aDBc_table->table_name, strlen($aDBc_table_prefix));
		}else{
			// If the number of possible prefixes found >= 2, we choose the longest prefix as valid one
			$aDBc_table_prefix = "";
			$aDBc_table_site = "";
			$aDBc_table_name_without_prefix = "";
			foreach($aDBc_found_prefixes as $aDBc_prefix => $aDBc_site){
				if(strlen($aDBc_prefix) >= strlen($aDBc_table_prefix)){
					$aDBc_table_prefix = $aDBc_prefix;
					$aDBc_table_site = $aDBc_site;
					$aDBc_table_name_without_prefix = substr($aDBc_table->table_name, strlen($aDBc_table_prefix));
				}
			}
		}
		// Add table information to the global array
		// If the table has not been added yet, add it and initiate its info
		if(empty($aDBc_all_tables[$aDBc_table_name_without_prefix])){
			$aDBc_all_tables[$aDBc_table_name_without_prefix] = array('belongs_to' => '', 'maybe_belongs_to' => '', 'type' => '', 'sites' => array());
		}
		// Add info of the task according to the current site
		$aDBc_all_tables[$aDBc_table_name_without_prefix]['sites'][$aDBc_table_site] = array('prefix' => $aDBc_table_prefix,
																						 'rows'	=> $aDBc_tables_rows_sizes[$aDBc_table->table_name]['rows'],
																						 'size'	=> $aDBc_tables_rows_sizes[$aDBc_table->table_name]['size'],
																						);		
	}
	return $aDBc_all_tables;
}

/***********************************************************************************
*
* Function proper to scheduled tasks processes
*
***********************************************************************************/

/** Prepares all scheduled tasks for all sites (if any) in a multidimensional array */
function aDBc_get_all_scheduled_tasks() {
	$aDBc_all_tasks = array();
	if(function_exists('is_multisite') && is_multisite()){
		global $wpdb;
		$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach($blogs_ids as $blog_id){
			switch_to_blog($blog_id);
				aDBc_add_scheduled_tasks($aDBc_all_tasks, $blog_id);
			restore_current_blog();
		}
	}else{
		aDBc_add_scheduled_tasks($aDBc_all_tasks, "1");
	}
	return $aDBc_all_tasks;
}

/** Prepares scheduled tasks for one single site (Used by aDBc_get_all_scheduled_tasks() function) */
function aDBc_add_scheduled_tasks(&$aDBc_all_tasks, $blog_id) {
	$cron = _get_cron_array();
	$schedules = wp_get_schedules();
	foreach((array) $cron as $timestamp => $cronhooks){
		foreach( (array) $cronhooks as $hook => $events){
			foreach( (array) $events as $event){
				// If the frequency exist
				if($event['schedule']){
					if(!empty($schedules[$event['schedule']])){
						$aDBc_frequency = $schedules[$event['schedule']]['display'];
					}else{
						$aDBc_frequency = __('Unknown!', 'advanced-db-cleaner');
					}
				}else{
					$aDBc_frequency = "<em>" . __('One-off event', 'advanced-db-cleaner') ."</em>";
				}
				// If the task has not been added yet, add it and initiate its info
				if(empty($aDBc_all_tasks[$hook])){
					$aDBc_all_tasks[$hook] = array('belongs_to' => '', 'maybe_belongs_to' => '', 'type' => '', 'sites' => array());
				}
				// Add info of the task according to the current site
				$aDBc_all_tasks[$hook]['sites'][$blog_id] = array('frequency' => $aDBc_frequency,
																  'next_run' => get_date_from_gmt(date('Y-m-d H:i:s', $timestamp), 'M j, Y @ H:i:s'));

			}
		}
	}
}

?>