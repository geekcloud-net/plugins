<?php
/**
 * @package WPSEO_Local\Frontend
 * @since   7.1
 */

if ( ! class_exists( 'WPSEO_Local_Search' ) ) {

	/**
	 * Class WPSEO_Local_Search
	 *
	 * Add functionality for enhancing the search engine and page
	 */
	class WPSEO_Local_Search {

		/**
		 * @var array $options Stores the options for this plugin.
		 */
		private $search_fields = array();

		/**
		 * @var bool
		 */
		private $enhanced_search_enabled = true;

		/**
		 * @var bool
		 */
		private $enhanced_search_result_enabled = true;

		/**
		 * @var $wpdb \WPDB global wpdb variable.
		 */
		private $wpdb;

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->run();
		}

		/**
		 * Run all the needed actions.
		 */
		public function run() {
			add_action( 'pre_get_posts', array( $this, 'enhance_search' ) );
			add_filter( 'the_excerpt', array( $this, 'enhance_location_search_results' ) );
		}

		/**
		 * Enhance the WordPress search to search in WPSEO Local locations meta data.
		 */
		public function enhance_search() {
			if ( $this->is_enhanced_search_enabled() && is_search() && ! is_admin() && ( ! isset( $_GET['post_type'] ) || 'wpseo_locations' === $_GET['post_type'] ) ) {
				global $wpdb;
				$this->wpdb = $wpdb;

				$this->set_search_fields();

				add_filter( 'posts_where', array( $this, 'where' ), 99, 1 );
				add_filter( 'posts_join', array( $this, 'join' ), 99, 1 );
				add_filter( 'posts_groupby', array( $this, 'groupby' ), 99, 1 );
			}
		}

		/**
		 * @param string $where the WHERE clause for the search query.
		 *
		 * @return mixed
		 */
		public function where( $where ) {
			$meta_query = '';
			$where      = $this->wpdb->remove_placeholder_escape( $where );

			foreach ( $this->search_fields as $field ) {
				$meta_query .= '((' . $this->wpdb->postmeta . ".meta_key = '" . $field . "')";
				$meta_query .= ' AND (' . $this->wpdb->postmeta . ".meta_value  LIKE '%" . get_search_query() . "%')) OR ";
			}

			$where = str_replace( '(((' . $this->wpdb->posts . ".post_title LIKE '%", '( ' . $meta_query . ' ((' . $this->wpdb->posts . ".post_title LIKE '%", $where );

			$where = $this->wpdb->remove_placeholder_escape( $where );

			return $where;
		}

		/**
		 * @param string $join the JOIN clause for the search query.
		 *
		 * @return mixed
		 */
		public function join( $join ) {
			$join .= ' INNER JOIN ' . $this->wpdb->postmeta . ' ON (' . $this->wpdb->posts . '.ID = ' . $this->wpdb->postmeta . '.post_id)';

			return $join;
		}

		/**
		 * @param string $groupby the GROUPBY clause for the search query.
		 *
		 * @return mixed
		 */
		public function groupby( $groupby ) {
			$groupby .= $this->wpdb->posts . '.ID';

			return $groupby;
		}

		/**
		 * Add address to locations in search results
		 *
		 * @since 1.3.8
		 *
		 * @param string $excerpt The excerpt which will be changed by this method.
		 *
		 * @return string
		 */
		public function enhance_location_search_results( $excerpt ) {
			if ( is_search() && $this->is_enhanced_search_result_enabled() ) {
				global $post;

				if ( 'wpseo_locations' === get_post_type( $post->ID ) ) {
					$excerpt .= '<div class="wpseo-local-search-details">';
					$excerpt .= wpseo_local_show_address( array(
						'id'           => $post->ID,
						'hide_name'    => true,
						'hide_json_ld' => true,
					) );
					$excerpt .= '</div>';
				}
			}

			return $excerpt;
		}

		/**
		 * Set the default fields to search in.
		 */
		private function set_search_fields() {
			$this->search_fields = array( '_wpseo_business_address', '_wpseo_business_city', '_wpseo_business_zipcode' );
			$this->search_fields = apply_filters( 'wpseo_local_search_custom_fields', $this->search_fields );
		}

		/**
		 * Check if enhanced search is enabled.
		 *
		 * @todo: Create an option in the Yoast Local SEO options page to disable the enhanced search.
		 *
		 * @return true|false
		 */
		private function is_enhanced_search_enabled() {
			return apply_filters( 'yoast_local_seo_enhanced_search_enabled', $this->enhanced_search_enabled );
		}

		/**
		 * Check if enhanced search result is enabled.
		 *
		 * @todo: Create an option in the Yoast Local SEO options page to disable the enhanced search.
		 *
		 * @return true|false
		 */
		private function is_enhanced_search_result_enabled() {
			return apply_filters( 'yoast_local_seo_enhanced_search_result_enabled', $this->enhanced_search_result_enabled );
		}
	}
}
