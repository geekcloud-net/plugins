<?php

class ADBC_Clean_Relationship extends WP_List_Table {

	private $aDBc_message = "";
	private $aDBc_class_message = "updated";
	private $aDBc_relationships_to_display = array();

    /**
     * Constructor
     */
    function __construct(){

        parent::__construct(array(
            'singular'  => __('Relationship', 'advanced-db-cleaner'),		//singular name of the listed records
            'plural'    => __('Relationships', 'advanced-db-cleaner'),	//plural name of the listed records
            'ajax'      => false	//does this table support ajax?
		));

		$this->aDBc_prepare_relationships_to_clean();
		$this->aDBc_print_page_content();
    }

	/** Prepare elements to display */
	function aDBc_prepare_relationships_to_clean(){
		global $wpdb;
		// Process bulk action if any before preparing relationships to clean
		$this->process_bulk_action();
		// Get all relationships
		if(function_exists('is_multisite') && is_multisite()){
			$blogs_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
			foreach($blogs_ids as $blog_id){
				switch_to_blog($blog_id);
				$aDBc_all_relationships = $wpdb->get_results("SELECT * from $wpdb->term_relationships WHERE term_taxonomy_id=1 AND object_id NOT IN (SELECT id FROM $wpdb->posts)");
				foreach($aDBc_all_relationships as $relationship){
					array_push($this->aDBc_relationships_to_display, array(
						'object_id' 		=> $relationship->object_id,
						'term_taxonomy_id' 	=> $relationship->term_taxonomy_id,
						'term_order'		=> $relationship->term_order,
						'term_site_id'		=> $blog_id
						)
					);
				}
				restore_current_blog();
			}
		}else{
			$aDBc_all_relationships = $wpdb->get_results("SELECT * from $wpdb->term_relationships WHERE term_taxonomy_id=1 AND object_id NOT IN (SELECT id FROM $wpdb->posts)");
			foreach($aDBc_all_relationships as $relationship){
				array_push($this->aDBc_relationships_to_display, array(
					'object_id' 		=> $relationship->object_id,
					'term_taxonomy_id' 	=> $relationship->term_taxonomy_id,
					'term_order'		=> $relationship->term_order,
					'term_site_id'		=> '1'
					)
				);
			}
		}
		// Call WP prepare_items function
		$this->prepare_items();
	}

	/** WP: Get columns */
	function get_columns(){
		$columns = array(
			'cb'       			=> '<input type="checkbox" />',
			'object_id' 		=> __('Object id','advanced-db-cleaner'),
			'term_taxonomy_id' 	=> __('Term taxonomy id','advanced-db-cleaner'),
			'term_order'   		=> __('Term order','advanced-db-cleaner'),
			'term_site_id'   	=> __('Site id','advanced-db-cleaner')
		);
		return $columns;
	}

	/** WP: Column default */
	function column_default($item, $column_name){
		switch($column_name){
			case 'object_id':
			case 'term_taxonomy_id':
			case 'term_order':
			case 'term_site_id':
				return $item[$column_name];
			default:
			  return print_r($item, true) ; //Show the whole array for troubleshooting purposes
		}
	}

	/** WP: Get columns that should be hidden */
    function get_hidden_columns(){
		// If MU, nothing to hide, else hide Side ID column
		if(function_exists('is_multisite') && is_multisite()){
			return array();
		}else{
			return array('term_site_id');
		}
    }

	/** WP: Prepare items to display */
	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$per_page = 50;
		$current_page = $this->get_pagenum();
		// Prepare sequence of elements to display
		$display_data = array_slice($this->aDBc_relationships_to_display,(($current_page-1) * $per_page), $per_page);
		$this->set_pagination_args( array(
			'total_items' => count($this->aDBc_relationships_to_display),
			'per_page'    => $per_page
		));
		$this->items = $display_data;
	}

	/** WP: Column cb for check box */
	function column_cb($item) {
		return sprintf('<input type="checkbox" name="aDBc_relationships_to_clean[]" value="%s" />', $item['term_site_id']."|".$item['object_id']);
	}

	/** WP: Get bulk actions */
	function get_bulk_actions() {
		$actions = array(
			'clean'    => __('Clean','advanced-db-cleaner')
		);
		return $actions;
	}

	/** WP: Message to display when no items found */
	function no_items() {
		_e('No elements found!','advanced-db-cleaner');
	}

	/** WP: Process bulk actions */
    public function process_bulk_action() {
        // security check!
        if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])){
            $nonce  = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];
            if (!wp_verify_nonce( $nonce, $action))
                wp_die('Security check failed!');
        }
        $action = $this->current_action();
        if($action == 'clean'){
			// If the user wants to clean the elements he/she selected
			if(isset($_POST['aDBc_relationships_to_clean'])){
				global $wpdb;
				if(function_exists('is_multisite') && is_multisite()){
					// Prepare relationships to delete
					$relationships_to_delete = array();
					foreach($_POST['aDBc_relationships_to_clean'] as $relationship){
						$relationship_info = explode("|", $relationship);
						if(empty($relationships_to_delete[$relationship_info[0]])){
							$relationships_to_delete[$relationship_info[0]] = array();
						}
						array_push($relationships_to_delete[$relationship_info[0]], $relationship_info[1]);
					}
					// Delete relationships
					foreach($relationships_to_delete as $site_id => $object_ids){
						switch_to_blog($site_id);
						foreach($object_ids as $object_id) {
							$wpdb->query("DELETE FROM $wpdb->term_relationships WHERE term_taxonomy_id=1 AND object_id = $object_id");
						}
						restore_current_blog();
					}
				}else{
					foreach($_POST['aDBc_relationships_to_clean'] as $relationship) {
						$relationship_info = explode("|", $relationship);
						$wpdb->query("DELETE FROM $wpdb->term_relationships WHERE term_taxonomy_id=1 AND object_id = " . $relationship_info[1]);
					}
				}
				// Update the message to show to the user
				$this->aDBc_message = __('Selected orphan relationships successfully cleaned!', 'advanced-db-cleaner');
			}
        }
    }

	/** Print the page content */
	function aDBc_print_page_content(){
		// Print a message if any
		if($this->aDBc_message != ""){
			echo '<div id="aDBc_message" class="' . $this->aDBc_class_message . ' notice is-dismissible"><p>' . $this->aDBc_message . '</p></div>';
		}
		?>
		<div class="aDBc-content-max-width">
			<div class="aDBc-float-left">
				<a style="text-decoration: none" href="?page=advanced_db_cleaner&aDBc_tab=general">
					<img src="<?php echo ADBC_PLUGIN_DIR_PATH . '/images/go_back.png'?>"/>
				</a>
			</div>
			<div class="aDBc-float-right aDBc-custom-clean-text">
				<?php echo __('Custom cleaning : <b>Orphan relationships</b>','advanced-db-cleaner') . " (" . count($this->aDBc_relationships_to_display) . ")"; ?>
			</div>
			<div>
				<form id="aDBc_form" action="" method="post">
					<?php
					// Print the elements to clean
					$this->display();
					?>
				</form>
			</div>
		</div>
		<div id="aDBc_dialog1" title="<?php _e('Cleaning...','advanced-db-cleaner'); ?>" class="aDBc-jquery-dialog">
			<p class="aDBc-box-warning">
				<?php _e('You are about to clean some of your orphan relationships. This operation is irreversible. Don\'t forget to make a backup first.','advanced-db-cleaner'); ?>
			</p>
			<p>
				<?php _e('Are you sure to continue?','advanced-db-cleaner'); ?>
			</p>
		</div>		
		<div id="aDBc_dialog2" title="<?php _e('Action required','advanced-db-cleaner'); ?>" class="aDBc-jquery-dialog">
			<p class="aDBc-box-info">
				<?php _e('Please select an action!','advanced-db-cleaner'); ?>
			</p>
		</div>		
	<?php
	}
}

new ADBC_Clean_Relationship();
?>