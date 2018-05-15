<?php
/**
 * Term Counts
 *
 * @package Framework\Term-Counts
 */

add_filter( 'get_terms', '_appthemes_pad_term_counts', 10, 3 );
add_action( 'edited_term_taxonomy', '_appthemes_update_post_term_count', 10, 2 );

/**
 * Add count of children to parent count.
 * Recalculates term counts by including items from child terms.
 * Assumes all relevant children are already in the $terms argument.
 *
 * @return array Terms
 */
function _appthemes_pad_term_counts( $terms, $taxonomies, $args ) {
	global $wpdb;

	if ( !current_theme_supports( 'app-term-counts' ) )
		return $terms;

	if ( !isset($args['app_pad_counts']) || !$args['app_pad_counts'] || !is_array($terms) )
		return $terms;

	$taxonomy = $taxonomies[0];
	if ( !is_taxonomy_hierarchical( $taxonomy ) )
		return $terms;

	$term_hier = _get_term_hierarchy($taxonomy);
	if ( empty($term_hier) )
		return $terms;

	list( $options ) = get_theme_support( 'app-term-counts' );

	$key = md5( serialize( compact(array_keys($args)) ) . serialize( $taxonomies ) );
	$last_changed = wp_cache_get('last_changed', 'app_terms');
	if ( !$last_changed ) {
		$last_changed = time();
		wp_cache_set('last_changed', $last_changed, 'app_terms');
	}
	$cache_key = "app_get_terms:$key:$last_changed";
	$cache = wp_cache_get( $cache_key, 'app_terms' );
	if ( false !== $cache )
		return $cache;

	$term_items = array();

	foreach ( (array) $terms as $key => $term ) {
		$terms_by_id[$term->term_id] = & $terms[$key];
		$term_ids[$term->term_taxonomy_id] = $term->term_id;
	}

	$post_types = esc_sql( $options['post_type'] );
	$post_statuses = esc_sql( $options['post_status'] );
	$results = $wpdb->get_results("SELECT object_id, term_taxonomy_id FROM $wpdb->term_relationships INNER JOIN $wpdb->posts ON object_id = ID WHERE term_taxonomy_id IN (" . implode(',', array_keys($term_ids)) . ") AND post_type IN ('" . implode("', '", $post_types) . "') AND post_status IN ('" . implode("', '", $post_statuses) . "') ");
	foreach ( $results as $row ) {
		$id = $term_ids[$row->term_taxonomy_id];
		$term_items[$id][$row->object_id] = isset($term_items[$id][$row->object_id]) ? ++$term_items[$id][$row->object_id] : 1;
	}

	// Touch every ancestor's lookup row for each post in each term
	foreach ( $term_ids as $term_id ) {
		$child = $term_id;
		while ( !empty( $terms_by_id[$child] ) && $parent = $terms_by_id[$child]->parent ) {
			if ( !empty( $term_items[$term_id] ) )
				foreach ( $term_items[$term_id] as $item_id => $touches ) {
					$term_items[$parent][$item_id] = isset($term_items[$parent][$item_id]) ? ++$term_items[$parent][$item_id]: 1;
				}
			$child = $parent;
		}
	}

	// Transfer the touched cells
	foreach ( (array) $term_items as $id => $items )
		if ( isset($terms_by_id[$id]) )
			$terms_by_id[$id]->count = count($items);

	wp_cache_add( $cache_key, $terms_by_id, 'app_terms', 86400 ); // one day

	return $terms_by_id;
}


function _appthemes_update_post_term_count( $term, $taxonomy ) {
	global $wpdb;

	if ( ! current_theme_supports( 'app-term-counts' ) )
		return;

	list( $options ) = get_theme_support( 'app-term-counts' );

	$post_types = esc_sql( $options['post_type'] );
	$post_statuses = esc_sql( $options['post_status'] );

	if ( is_object( $taxonomy ) && in_array( $taxonomy->name, $options['taxonomy'] ) ) {
		$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships INNER JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->term_relationships.object_id WHERE post_type IN ('" . implode( "', '", $post_types ) . "') AND post_status IN ('" . implode( "', '", $post_statuses ) . "') AND term_taxonomy_id = %d", $term ) );
		$wpdb->update( $wpdb->term_taxonomy, array( 'count' => $count ), array( 'term_taxonomy_id' => $term ) );
	}

}

