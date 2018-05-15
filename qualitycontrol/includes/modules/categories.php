<?php
/**
 * @package Quality_Control
 * @subpackage Ticket Taxonomies
 * @since Quality Control 0.2
 */

class QC_Ticket_Category extends QC_Taxonomy {

	const TAXONOMY = 'ticket_category';

	function __construct() {
		parent::__construct(
			self::TAXONOMY,
			'ticket-category',
			array(
				'name' => __( 'Categories', APP_TD ),
				'singular_name' => __( 'Category', APP_TD ),
				'search_items' => __( 'Search Categories', APP_TD ),
				'popular_items' => __( 'Popular Categories', APP_TD ),
				'all_items' => __( 'All Categories', APP_TD ),
				'update_item' => __( 'Update Category', APP_TD ),
				'add_new_item' => __( 'Add New Category', APP_TD ),
				'new_item_name' => __( 'New Category Name', APP_TD ),
				'edit_item' => __( 'Edit Category', APP_TD )
			)
		);
	}

	function actions() {
		add_action( 'init', array( $this, 'register_taxonomy' ), 8 );

		add_action( 'qc_navigation_after', array( $this, 'add_navigation' ) );
		add_action( 'qc_ticket_fields_between', array( $this, 'ticket_meta' ) );
		add_action( 'qc_ticket_form_advanced_fields', array( $this, 'add_to_form' ), 10, 1 );

		add_action( 'qc_create_ticket', array( $this, 'save_taxonomy_frontend' ), 10, 2 );
		add_action( 'pre_comment_on_post', array( $this, 'update_taxonomy_frontend' ), 9 );

		add_action( 'manage_posts_custom_column', array( $this, 'manage_columns' ), 11 );
		add_filter( 'manage_edit-ticket_columns', array( $this, 'manage_column_titles' ), 11 );
	}

}

appthemes_add_instance( 'QC_Ticket_Category' );

