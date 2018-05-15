<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ultimatum
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TU_Shortcodes
 *
 * Main wrapper for all shortcodes that are/will be implemented in TU plugin
 */
class TU_Shortcodes {

	protected static $campaigns_ids = array();

	/**
	 * Usually called on {init} WP hook
	 * Ads the required hooks for shortcodes
	 */
	public static function init() {
		$shortcodes = array(
			'tu_countdown' => __CLASS__ . '::countdown',
		);

		foreach ( $shortcodes as $code => $function ) {
			add_shortcode( $code, $function );
		}
	}

	/**
	 * Save the shortcode's campaign in list and
	 * renders the required placeholder
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public static function countdown( $args ) {

		/**
		 * if not post or page do not render shortcode and do not save the campaign globally
		 * cos we don't want to render the shortcode in lists
		 */
		if ( ! is_single() && ! is_page() ) {
			return '';
		}

		if ( empty( $args['id'] ) || empty( $args['design'] ) ) {
			return '';
		}

		/**
		 * check if the campaigns is running
		 */
		$status = get_post_meta( $args['id'], TVE_Ult_Const::META_NAME_FOR_STATUS, true );
		if ( $status !== TVE_Ult_Const::CAMPAIGN_STATUS_RUNNING ) {
			return '';
		}

		self::push_campaign( $args['id'] );

		require_once trailingslashit( dirname( __FILE__ ) ) . 'shortcodes/class-tu-shortcode-countdown.php';

		ob_start();
		echo TU_Shortcode_Countdown::instance()->placeholder( $args['design'] );

		return ob_get_clean();
	}

	/**
	 * Returns the list of campaign ids used by all shortcodes in current request
	 *
	 * @return array
	 */
	public static function get_campaigns() {
		return self::$campaigns_ids;
	}

	/**
	 * Push an campaign id into global array if it does not exists
	 *
	 * @param $id
	 *
	 * @return bool pushed or not
	 */
	protected static function push_campaign( $id ) {
		$id = intval( $id );

		if ( empty( $id ) || in_array( $id, self::$campaigns_ids ) ) {
			return false;
		}

		self::$campaigns_ids[] = (int) $id;

		return true;
	}

	/**
	 * Generates code for countdown shortcode
	 *
	 * @param int $campaign
	 * @param int $design
	 *
	 * @return string
	 */
	public static function get_countdown_shortcode( $campaign, $design ) {

		if ( empty( $campaign ) || empty( $design ) ) {
			return '';
		}

		require_once trailingslashit( dirname( __FILE__ ) ) . 'shortcodes/class-tu-shortcode-countdown.php';

		return TU_Shortcode_Countdown::instance()->get_code( $campaign, $design );
	}
}
