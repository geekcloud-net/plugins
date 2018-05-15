<?php

/**
 * A parent class that can be used to create new taxonomies
 * for the theme. Check out /modules/states.php
 * for an example on how to extend this class.
 *
 * @package Quality_Control
 * @since Quality Control 0.2
 */
class QC_Taxonomy {

	// Wether a ticket must have a value set for this taxonomy
	protected $mandatory = true;

	protected static $attr_changes = array();

	function __construct( $taxonomy, $taxonomy_slug = '', $taxonomy_labels = array() ) {
		$this->taxonomy = $taxonomy;
		$this->taxonomy_slug = $taxonomy_slug;
		$this->taxonomy_labels = $taxonomy_labels;

		$this->actions();

		add_action( 'wp_insert_comment', array( __CLASS__, 'store_attr_changes' ) );
	}

	function actions() {
		add_action( 'init', array( $this, 'register_taxonomy' ), 8 );

		add_action( 'qc_navigation_after', array( $this, 'add_navigation' ), 10, 1 );
		add_action( 'qc_ticket_fields_between', array( $this, 'ticket_meta' ), 10, 1 );
		add_action( 'qc_ticket_form_advanced_fields', array( $this, 'add_to_form' ), 10, 1 );

		add_action( 'admin_menu', array( $this, 'meta_box' ) );
		add_action( 'save_post', array( $this, 'save_taxonomy' ) );

		add_action( 'qc_create_ticket', array( $this, 'save_taxonomy_frontend' ), 10, 2 );
		add_action( 'pre_comment_on_post', array( $this, 'update_taxonomy_frontend' ), 9 );

		add_filter( 'manage_edit-ticket_columns', array( $this, 'manage_column_titles' ), 11 );
		add_action( 'manage_posts_custom_column', array( $this, 'manage_columns' ), 11 );

		add_action( 'right_now_content_table_end', array( $this, 'right_now' ) );
	}

	function register_taxonomy() {
		register_taxonomy(
			$this->taxonomy,
			array( QC_TICKET_PTYPE ),
			$this->taxonomy_args()
		);
	}

	function taxonomy_args() {
		$args = array(
			'labels' => apply_filters(
				"qc_{$this->taxonomy}_labels",
				$this->taxonomy_labels
			),
			'hierarchical' => true,
			'show_tagcloud' => false,
			'show_ui' => true,
			'rewrite' => apply_filters(
				"qc_{$this->taxonomy}_rewrite",
				array(
					'slug' => $this->taxonomy_slug
				)
			),
			'update_count_callback' => apply_filters(
				"qc_{$this->taxonomy}_callback",
				'_update_post_term_count'
			)
		);

		return $args;
	}

	function add_navigation() {
		$tax_object = get_taxonomy( $this->taxonomy );

		$statuses = get_categories( array(
			'taxonomy' => $this->taxonomy,
			'hide_empty' => 0,
			'orderby' => 'name'
		) );

		if ( empty( $statuses ) ) {
			return;
		}

		echo '<li' . ( is_tax( $this->taxonomy ) ? ' class="current-tab"' : '' ) . '>';
		echo '<a href="#">' . $tax_object->labels->singular_name . '</a>';

		echo '<ul class="second-level children">';

		foreach ( $statuses as $status ) {
			$content = $status->name;

			if ( qc_can_view_all_tickets() )
				$content = html( 'span', $status->count ) . $content;

			echo html( 'li', html( 'a', array(
				'href' => get_term_link( $status, $this->taxonomy ),
				'title' => sprintf( __( 'View all tickets marked %s', APP_TD ), $status->name )
			), $content ) );
		}

		echo '</ul>';

		echo '</li>';
	}

	function ticket_meta( $exclude ) {
		global $post;

		$tax_object = get_taxonomy( $this->taxonomy );

		echo '<li>
				<small>' . $tax_object->labels->singular_name. '</small>';
				if ( get_the_term_list( $post->ID, $this->taxonomy, '', ', ', '' ) ) {
					echo get_the_term_list( $post->ID, $this->taxonomy, '', ', ', '' );
				} else {
					echo'&mdash;';
				}
		echo '</li>';
	}

	function add_to_form( $context ) {
		global $qc_options;

		$tax_object = get_taxonomy( $this->taxonomy );

		$args = array(
			'name' => $this->taxonomy,
			'hide_empty' => 0,
			'taxonomy' => $this->taxonomy,
			'hierarchical' => 1,
		);

		if ( ! $this->mandatory ) {
			$args['show_option_none'] = '&mdash;' . __( 'none', APP_TD ) . '&mdash;';
		}

		if ( 'create' == $context ) {
			$args['selected'] = $qc_options->get( "default_{$this->taxonomy}" );
		} else {
			$args['selected'] = qc_taxonomy( $this->taxonomy );
		}

		echo '<p class="inline-input">
			<label>' . $tax_object->labels->singular_name . ':</label>';
				wp_dropdown_categories( $args );
		echo '</p>';
	}

	function meta_box() {
		$taxonomy = get_taxonomy( $this->taxonomy );

		if ( $taxonomy->hierarchical ) {
			remove_meta_box( "{$this->taxonomy}div", "ticket", "side" );
		} else {
			remove_meta_box( "tagsdiv-{$this->taxonomy}", "ticket", "side" );
		}

		add_meta_box( $this->taxonomy, $taxonomy->labels->singular_name, array( $this, 'create_meta_box' ), QC_TICKET_PTYPE, 'side', 'low' );
	}

	function create_meta_box() {
		global $post, $qc_options;

		echo '<div class="input-text-wrap" style="margin:5px 0 0">';

		wp_dropdown_categories( array(
			'taxonomy' => $this->taxonomy,
			'hide_empty' => 0,
			'name' => "quality[{$this->taxonomy}]",
			'selected' => ( qc_taxonomy( $this->taxonomy ) ? qc_taxonomy( $this->taxonomy ) : $qc_options->ticket_status_new )
		) );

		echo '</div>';
	}

	function save_taxonomy( $post_id ) {
		global $pagenow;

		if ( $pagenow != 'post.php' ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		$taxonomy = isset( $_POST['quality'][ $this->taxonomy ] ) ? $_POST['quality'][ $this->taxonomy ] : 0;

		$taxonomy = wp_set_object_terms( $post_id, intval( $taxonomy ), $this->taxonomy, false );

		return $taxonomy;
	}

	/**
	 * Assign the taxonomy terms (status, milestone) to the object (ticket)
	 * when a ticket is created via the frontend form.
	 */
	function save_taxonomy_frontend( $ticket_id, $ticket ) {
		wp_set_object_terms( $ticket_id, array( intval( $ticket[ $this->taxonomy ] ) ), $this->taxonomy );
	}

	/**
	 * When a comment is posted, check to see if they are updating the taxonomy.
	 * If they are, actually update the tax, but then also provide a string which
	 * says what is updated.
	 */
	function update_taxonomy_frontend( $ticket_id ) {
		$ticket = get_post( $ticket_id );

		if ( ! $ticket || $ticket->post_type != QC_TICKET_PTYPE ) {
			return;
		}

		$old_term = qc_taxonomy( $this->taxonomy, 'term_id', $ticket_id );

		$new_term = get_term( $_POST[ $this->taxonomy ], $this->taxonomy );
		$new_term = is_null( $new_term ) ? 0 : (int) $new_term->term_id;

		if ( $old_term == $new_term ) {
			return false;
		}

		wp_set_object_terms( $ticket_id, array( $new_term ), $this->taxonomy, false );

		// Store message for when we have a comment id
		self::$attr_changes[] = apply_filters( "qc_ticket_update_{$this->taxonomy}",
			$this->get_message( $old_term, $new_term ), $old_term, $new_term
		);

		add_filter( 'qc_did_change_ticket', '__return_true' );
	}

	function update_ticket( $ticket_id, $terms, $append = false ) {
		$term_ids = array();

		foreach ( (array) $terms as $key => $term_name ) {
			if ( 'none' == $term_name ) {
				$term_ids = null;
				break;
			}

			// skip non-existing terms, except tags
			if ( ! term_exists( $term_name, $this->taxonomy ) ) {
				if ( $this->taxonomy == 'ticket_tag' ) {
					wp_insert_term( $term_name, $this->taxonomy );
				} else {
					continue;
				}
			}

			$term_by = ( is_string( $term_name ) ) ? 'slug' : 'id';
			$term = get_term_by( $term_by, $term_name, $this->taxonomy );

			$term_ids[] = (int) $term->term_id;
		}

		if ( ( count( $term_ids ) < 1 ) && ( $term_ids !== null ) ) {
			return;
		}

		$old_terms = wp_get_post_terms( $ticket_id, $this->taxonomy, array( 'fields' => 'names' ) );

		wp_set_post_terms( $ticket_id, $term_ids, $this->taxonomy, $append );

		$new_terms = wp_get_post_terms( $ticket_id, $this->taxonomy, array( 'fields' => 'names' ) );

		// Store message for when we have a comment id
		self::$attr_changes[] = apply_filters( "qc_ticket_update_{$this->taxonomy}", 
			$this->get_comment_diff_message( $old_terms, $new_terms ), $old_terms, $new_terms
		);

		add_filter( 'qc_did_change_ticket', '__return_true' );
	}

	function get_comment_diff_message( $old_terms, $new_terms ) {
		$added = array_diff( $new_terms, $old_terms );
		$deleted = array_diff( $old_terms, $new_terms );

		if ( ( count( $added ) > 1 ) || ( count( $deleted ) > 1 ) ) {
			$tax_name = get_taxonomy( $this->taxonomy )->labels->singular_name;
			$msg = _qc_get_message_diff( $added, $deleted );

			if ( empty( $msg ) ) {
				return false;
			}

			$msg = '<strong>' . $tax_name . '</strong> ' . implode( '; ', $msg ) . '.';
		} else {
			$old_term = get_term_by( 'name', array_shift( $old_terms ), $this->taxonomy );
			$old_term = ( $old_term ) ? (int) $old_term->term_id : 0;

			$new_term = get_term_by( 'name', array_shift( $new_terms ), $this->taxonomy );
			$new_term = ( $new_term ) ? (int) $new_term->term_id : 0;

			if ( $old_term == $new_term ) {
				return false;
			}

			$msg = $this->get_message( $old_term, $new_term );
		}

		return $msg;
	}

	protected function get_message( $old_term, $new_term ) {
		$tax_name = get_taxonomy( $this->taxonomy )->labels->singular_name;

		if ( ! $old_term ) {
			return sprintf( __( '<strong class="taxonomy">%1$s</strong> set to <em>%2$s</em>.', APP_TD ), $tax_name, get_term_field( 'name', $new_term, $this->taxonomy ) );
		}

		if ( ! $new_term ) {
			return sprintf( __( '<strong class="taxonomy">%1$s</strong> deleted.', APP_TD ), $tax_name );
		}

		return sprintf(
			__( '<strong class="taxonomy">%1$s</strong> changed from <em>%2$s</em> to <em>%3$s</em>.', APP_TD ),
			$tax_name,
			get_term_field( 'name', $old_term, $this->taxonomy ),
			get_term_field( 'name', $new_term, $this->taxonomy )
		);
	}

	static function store_attr_changes( $comment_id ) {
		foreach ( self::$attr_changes as $update ) {
			add_comment_meta( $comment_id, 'ticket_updates', $update );
		}
	}

	/**
	 * Add this taxonomy to the array of column titles.
	 */
	function manage_column_titles( $columns ) {
		$taxonomy = get_taxonomy( $this->taxonomy );

		$columns[ $this->taxonomy ] = $taxonomy->labels->singular_name;

		return $columns;
	}

	/**
	 * Create the callback for the column headers.
	 *
	 * To override this method, simply redeclare it in the child class.
	 *
	 * @since Quality Control 0.2
	 * @param array columns The columns
	 */
	function manage_columns( $column ) {
		global $post;

		switch( $column ) {
			case $this->taxonomy :

				$tax = get_the_term_list( $post->ID, $this->taxonomy, '', ', ', '' );

				if ( ! empty( $tax ) ) {
					echo $tax;
				} else {
					_e( '&mdash;', APP_TD );
				}

			break;
		}
	}

	function right_now() {
		$num_taxes = wp_count_terms( $this->taxonomy );
		$num = number_format_i18n( $num_taxes );
		$tax_object = get_taxonomy( $this->taxonomy );

		echo "<tr>";
		$text = _n( $tax_object->labels->singular_name, $tax_object->labels->name, $num_taxes );
		if ( current_user_can( 'manage_categories' ) ) {
			$num = "<a href='edit-tags.php?taxonomy={$this->taxonomy}&post_type=ticket'>$num</a>";
			$text = "<a href='edit-tags.php?taxonomy={$this->taxonomy}&post_type=ticket'>$text</a>";
		}
		echo '<td class="first b b-tags">' . $num . '</td>';
		echo '<td class="t tags">' . $text . '</td>';
		echo "</tr>";
	}
}

