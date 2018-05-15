<?php
/**
 * @package Quality_Control
 * @subpackage Ticket Taxonomies
 * @since Quality Control 0.2
 */

class QC_Ticket_Tags extends QC_Taxonomy {

	const TAXONOMY = 'ticket_tag';

	function __construct() {
		parent::__construct(
			self::TAXONOMY,
			'ticket-tag',
			array(
				'name' => __( 'Tags', APP_TD ),
				'singular_name' => __( 'Tag', APP_TD ),
				'search_items' => __( 'Search Tags', APP_TD ),
				'popular_items' => __( 'Popular Tags', APP_TD ),
				'all_items' => __( 'All Tags', APP_TD ),
				'update_item' => __( 'Update Tag', APP_TD ),
				'add_new_item' => __( 'Add New Tag', APP_TD ),
				'new_item_name' => __( 'New Tag Name', APP_TD ),
				'edit_item' => __( 'Edit Tag', APP_TD )
			)
		);
	}

	/**
	 * We don't want to add all the actions/filters as the
	 * other taxonomies
	 */
	function actions() {
		add_action( 'init', array( $this, 'register_taxonomy' ), 8 );

		add_action( 'qc_ticket_fields_between', array( $this, 'ticket_meta' ) );

		add_action( 'qc_create_ticket', array( $this, 'save_taxonomy_frontend' ), 10, 2 );
		add_action( 'pre_comment_on_post', array( $this, 'update_taxonomy_frontend' ), 9 );

		add_filter( 'manage_edit-ticket_columns', array( $this, 'manage_column_titles' ), 11 );
		add_action( 'manage_posts_custom_column', array( $this, 'manage_columns' ), 11 );
	}

	function taxonomy_args() {
		$args = wp_parse_args( array( 'hierarchical' => false ), parent::taxonomy_args() );

		return $args;
	}

	function save_taxonomy_frontend( $ticket_id, $ticket ) {
		wp_set_post_terms( $ticket_id, $ticket['ticket_tags'], $this->taxonomy );
	}

	/**
	 * When a comment has been created, the tags have to be checked slightly
	 * differently.
	 */
	function update_taxonomy_frontend( $ticket_id ) {
		$ticket = get_post( $ticket_id );

		if ( ! $ticket || $ticket->post_type != QC_TICKET_PTYPE ) {
			return;
		}

		$old_terms = wp_get_post_terms( $ticket_id, self::TAXONOMY, array( 'fields' => 'names' ) );

		wp_set_post_terms( $ticket_id, $_POST['ticket_tags'], self::TAXONOMY, false );

		$new_terms = wp_get_post_terms( $ticket_id, self::TAXONOMY, array( 'fields' => 'names' ) );

		$added = array_diff( $new_terms, $old_terms );
		$deleted = array_diff( $old_terms, $new_terms );

		$msg = _qc_get_message_diff( $added, $deleted );

		if ( empty( $msg ) ) {
			return;
		}

		$msg = '<strong>' . __( 'Tags', APP_TD ) . '</strong> ' . implode( '; ', $msg ) . '.';

		// Store message for when we have a comment id
		self::$attr_changes[] = apply_filters( "qc_ticket_update_{$this->taxonomy}",
			$msg, $old_terms, $new_terms
		);

		add_filter( 'qc_did_change_ticket', '__return_true' );
	}

	/**
	 * Override to add the tags class
	 */
	function ticket_meta( $exclude ) {
		global $post;

		// Only display on single ticket pages
		if ( ! is_singular( QC_TICKET_PTYPE ) ) {
			return;
		}

		$tax_object = get_taxonomy( $this->taxonomy );

		echo '<li class="tags">
				<small>' . $tax_object->labels->singular_name . '</small>';
				if ( get_the_term_list( $post->ID, $this->taxonomy, '', ', ', '' ) ) {
					echo get_the_term_list( $post->ID, $this->taxonomy, '', ', ', '' );
				} else {
					echo '&mdash;';
				}
		echo '</li>';
	}
}

appthemes_add_instance( 'QC_Ticket_Tags' );

