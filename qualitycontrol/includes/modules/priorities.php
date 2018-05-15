<?php
/**
 * @package Quality_Control
 * @subpackage Ticket Taxonomies
 * @since Quality Control 0.5
 */

class QC_Ticket_Priority extends QC_Taxonomy {

	function __construct() {
		parent::__construct(
			'ticket_priority',
			'priority',
			array(
				'name' => __( 'Priorities', APP_TD ),
				'singular_name' => __( 'Priority', APP_TD ),
				'search_items' => __( 'Search Priorities', APP_TD ),
				'popular_items' => __( 'Popular Priorities', APP_TD ),
				'all_items' => __( 'All Priorities', APP_TD ),
				'update_item' => __( 'Update Priority', APP_TD ),
				'add_new_item' => __( 'Add New Priority', APP_TD ),
				'new_item_name' => __( 'New Priority Name', APP_TD ),
				'edit_item' => __( 'Edit Priority', APP_TD )
			)
		);

		add_action( 'post_class', array( $this, 'post_classes' ), 10, 3 );
	}


	function post_classes( $classes, $class, $post_id ) {
		$terms = get_the_terms( (int) $post_id, $this->taxonomy );
		if ( ! empty( $terms ) ) {
			foreach ( (array) $terms as $term ) {
				$new_class = $this->taxonomy . '-' . $term->slug;
				if ( ! in_array( $new_class, $classes ) )
					$classes[] = $new_class;
			}
		}
		return $classes;
	}
}

appthemes_add_instance( 'QC_Ticket_Priority' );

