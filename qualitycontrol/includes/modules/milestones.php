<?php
/**
 * @package Quality_Control
 * @subpackage Ticket Taxonomies
 * @since Quality Control 0.2
 */

class QC_Ticket_Milestone extends QC_Taxonomy {

	protected $mandatory = false;

	function __construct() {
		parent::__construct(
			'ticket_milestone',
			'milestone',
			array(
				'name' => __( 'Milestones', APP_TD ),
				'singular_name' => __( 'Milestone', APP_TD ),
				'search_items' => __( 'Search Milestones', APP_TD ),
				'popular_items' => __( 'Popular Milestones', APP_TD ),
				'all_items' => __( 'All Milestones', APP_TD ),
				'update_item' => __( 'Update Milestone', APP_TD ),
				'add_new_item' => __( 'Add New Milestone', APP_TD ),
				'new_item_name' => __( 'New Milestone Name', APP_TD ),
				'edit_item' => __( 'Edit Milestone', APP_TD )
			)
		);
	}
}

appthemes_add_instance( 'QC_Ticket_Milestone' );

