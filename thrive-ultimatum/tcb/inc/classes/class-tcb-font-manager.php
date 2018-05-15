<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Main class for handling the editor page related stuff
 *
 * Class TCB_Editor_Page
 */
class TCB_Font_Manager {
	/**
	 * Instance
	 *
	 * @var TCB_Font_Manager
	 */
	private static $instance;

	/**
	 * Google Fonts api link
	 *
	 * @var string
	 */
	private static $api = 'https://www.googleapis.com/webfonts/v1/webfonts?key=';

	/**
	 * Google Fonts Api Developer key
	 *
	 * @var string
	 */
	private static $key = 'AIzaSyDJhU1bXm2YTz_c4VpWZrAyspOS37Nn-kI';

	/**
	 * Option name that will be used to save the fonts
	 *
	 * @var string
	 */
	private static $google_fonts_option = 'tve_google_fonts';

	/**
	 * Singleton instance method
	 *
	 * @return TCB_Font_Manager
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Return all fonts needed for font manager
	 *
	 * @return array
	 */
	public function all_fonts() {
		return array(
			'google' => array(
				'label' => __( 'Google Fonts', 'thrive-cb' ),
				'fonts' => array(),// $this->google_fonts() - we'll get those from js
			),
			'safe'   => array(
				'label' => __( 'Web Safe Fonts', 'thrive-cb' ),
				'fonts' => self::safe_fonts(),
			),
			'custom' => array(
				'label' => __( 'Custom Fonts', 'thrive-cb' ),
				'fonts' => $this->custom_fonts(),
			),

		);
	}

	/**
	 * Get google fonts. By default we get it from the option table, but if we don't find something there we take from the api.
	 *
	 * @param bool $force Force a new api call.
	 *
	 * @return array
	 */
	public function google_fonts( $force = false ) {

		$fonts = get_option( self::$google_fonts_option, array() );

		if ( empty( $fonts ) || $force ) {
			$fonts = $this->get_google_fonts();

			update_option( self::$google_fonts_option, $fonts );
		}

		return $fonts;
	}

	/**
	 * Make api call to google fonts to retrieve the fonts. In case of error return empty array
	 *
	 * @return array
	 */
	private function get_google_fonts() {
		try {
			$request = tve_dash_api_remote_get( self::$api . self::$key );

			$response = json_decode( wp_remote_retrieve_body( $request ), true );

			$fonts = $response['items'];
		} catch ( Exception $e ) {
			$fonts = array();
		}

		return $fonts;
	}

	/**
	 * Return array of custom fonts
	 *
	 * @return array
	 */
	public function custom_fonts() {
		$custom_fonts   = json_decode( get_option( 'thrive_font_manager_options' ), true );
		$imported_fonts = Tve_Dash_Font_Import_Manager::getImportedFonts();

		if ( ! is_array( $custom_fonts ) ) {
			$custom_fonts = array();
		}

		$imported_keys = array();
		foreach ( $imported_fonts as $imp_font ) {
			$imported_keys[] = $imp_font['family'];
		}

		$return = array();
		foreach ( $custom_fonts as $font ) {
			$return[] = array(
				'family'         => $font['font_name'],
				'regular_weight' => intval( $font['font_style'] ),
				'class'          => $font['font_class'],
			);
		}

		return $return;
	}

	/**
	 * Return safe fonts array
	 *
	 * @return array
	 */
	public static function safe_fonts() {
		return $safe_fonts = array(
			array(
				'family'   => 'Georgia, serif',
				'variants' => array( 'regular', 'italic', '600' ),
				'subsets'  => array( 'latin' ),
			),
			array(
				'family'   => 'Palatino Linotype, Book Antiqua, Palatino, serif',
				'variants' => array( 'regular', 'italic', '600' ),
				'subsets'  => array( 'latin' ),
			),
			array(
				'family'   => 'Times New Roman, Times, serif',
				'variants' => array( 'regular', 'italic', '600' ),
				'subsets'  => array( 'latin' ),
			),
			array(
				'family'   => 'Arial, Helvetica, sans-serif',
				'variants' => array( 'regular', 'italic', '600' ),
				'subsets'  => array( 'latin' ),
			),
			array(
				'family'   => 'Arial Black, Gadget, sans-serif',
				'variants' => array( 'regular', 'italic', '600' ),
				'subsets'  => array( 'latin' ),
			),
			array(
				'family'   => 'Comic Sans MS, cursive, sans-serif',
				'variants' => array( 'regular', 'italic', '600' ),
				'subsets'  => array( 'latin' ),
			),
			array(
				'family'   => 'Impact, Charcoal, sans-serif',
				'variants' => array( 'regular', 'italic', '600' ),
				'subsets'  => array( 'latin' ),
			),
			array(
				'family'   => 'Lucida Sans Unicode, Lucida Grande, sans-serif',
				'variants' => array( 'regular', 'italic', '600' ),
				'subsets'  => array( 'latin' ),
			),
			array(
				'family'   => 'Tahoma, Geneva, sans-serif',
				'variants' => array( 'regular', 'italic', '600' ),
				'subsets'  => array( 'latin' ),
			),
			array(
				'family'   => 'Trebuchet MS, Helvetica, sans-serif',
				'variants' => array( 'regular', 'italic', '600' ),
				'subsets'  => array( 'latin' ),
			),
			array(
				'family'   => 'Verdana, Geneva, sans-serif',
				'variants' => array( 'regular', 'italic', '600' ),
				'subsets'  => array( 'latin' ),
			),
			array(
				'family'   => 'Courier New, Courier, monospace',
				'variants' => array( 'regular', 'italic', '600' ),
				'subsets'  => array( 'latin' ),
			),
			array(
				'family'   => 'Lucida Console, Monaco, monospace',
				'variants' => array( 'regular', 'italic', '600' ),
				'subsets'  => array( 'latin' ),
			),
		);
	}
}
