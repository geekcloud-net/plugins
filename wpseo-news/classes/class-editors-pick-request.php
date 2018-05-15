<?php
/**
 * Yoast SEO: News plugin file.
 *
 * @package WPSEO_News
 */

/**
 * Represents the editors pick.
 */
class WPSEO_News_Editors_Pick_Request {

	const REWRITE_RULE = '^editors-pick.rss$';

	/**
	 * Setup this class.
	 */
	public function __construct() {
		$this->setup();
	}

	/**
	 * Setup the Rewrite Rule Hooks.
	 */
	private function setup() {
		add_filter( 'rewrite_rules_array', array( $this, 'add_rewrite_rule' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_action( 'wp_loaded', array( $this, 'flush_rules' ) );
		add_action( 'template_redirect', array( $this, 'catch_request' ), 1 );
	}

	/**
	 * Add custom query variables to WordPress query variables.
	 *
	 * @param array $vars The current query vars.
	 *
	 * @return array query_vars
	 */
	public function add_query_vars( $vars ) {
		array_push( $vars, 'wpseo-news-editors-pick' );

		return $vars;
	}

	/**
	 * Add Editors' Picks rewrite rules to WordPress rewrite rules.
	 *
	 * @param array $rules The rules to extend.
	 *
	 * @return array rules
	 */
	public function add_rewrite_rule( $rules ) {
		$newrules                       = array();
		$newrules[ self::REWRITE_RULE ] = 'index.php?wpseo-news-editors-pick=all';

		return ( $newrules + $rules );
	}

	/**
	 * Flush rules if they're not set yet.
	 */
	public function flush_rules() {
		$rules = get_option( 'rewrite_rules' );

		if ( ! isset( $rules[ self::REWRITE_RULE ] ) ) {
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
		}
	}

	/**
	 * Catch the Editors' Picks request.
	 */
	public function catch_request() {
		global $wp_query;

		if ( $wp_query->get( 'wpseo-news-editors-pick' ) ) {

			$editors_pick = new WPSEO_News_Sitemap_Editors_Pick();
			$editors_pick->generate_rss();

			exit;

		}
	}
}
