<?php
/**
 * @package Internals
 * @since      1.6.0
 * @version    1.6.0
 */

// Avoid direct calls to this file.
if ( ! class_exists( 'WPSEO_Video_Sitemap' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/**
 *****************************************************************
 * Option: wpseo_video
 */
if ( ! class_exists( 'WPSEO_Option_Video' ) ) {

	/**
	 * Class WPSEO_Option_Video
	 */
	class WPSEO_Option_Video extends WPSEO_Option {

		/**
		 * @var string  option name
		 */
		public $option_name = 'wpseo_video';

		/**
		 * @var bool  whether to include the option in the return for WPSEO_Options::get_all()
		 */
		public $include_in_all = false;

		/**
		 * @var bool  whether this option is only for when the install is multisite
		 */
		public $multisite_only = false;

		/**
		 * @var array  Array of defaults for the option
		 *             Shouldn't be requested directly, use $this->get_defaults();
		 */
		protected $defaults = array(
			// Non-form fields, set via validation routine / license activation method.
			// Leave default as 0 to ensure activation/upgrade works.
			'dbversion'               => 0,

			// Form fields.
			'cloak_sitemap'           => false,
			'disable_rss'             => false,
			'custom_fields'           => '',
			'facebook_embed'          => true, // N.B.: The name of this property is outdated, should be `allow_external_embeds`.
			'fitvids'                 => false,
			'content_width'           => '',
			'wistia_domain'           => '',
			'embedly_api_key'         => '',
			'videositemap_posttypes'  => array(),
			'videositemap_taxonomies' => array(),
		);


		/**
		 * Add the actions and filters for the option
		 *
		 * @return \WPSEO_Option_Video
		 */
		protected function __construct() {
			parent::__construct();
		}


		/**
		 * Get the singleton instance of this class
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( ! ( self::$instance instanceof self ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}


		/**
		 * Add dynamically created default option based on available post types
		 *
		 * @return void
		 */
		public function enrich_defaults() {
			$this->defaults['videositemap_posttypes'] = get_post_types( array( 'public' => true ) );
		}


		/**
		 * Validate the option
		 *
		 * @param  array $dirty New value for the option.
		 * @param  array $clean Clean value for the option, normally the defaults.
		 * @param  array $old   Old value of the option.
		 *
		 * @return array        Validated clean value for the option to be saved to the database
		 */
		protected function validate_option( $dirty, $clean, $old ) {

			foreach ( $clean as $key => $value ) {
				switch ( $key ) {
					case 'dbversion':
						$clean[ $key ] = WPSEO_VIDEO_VERSION;
						break;

					case 'videositemap_posttypes':
						$clean[ $key ]    = array();
						$valid_post_types = get_post_types( array( 'public' => true ) );
						if ( isset( $dirty[ $key ] ) && ( is_array( $dirty[ $key ] ) && $dirty[ $key ] !== array() ) ) {
							foreach ( $dirty[ $key ] as $k => $v ) {
								if ( in_array( $k, $valid_post_types, true ) ) {
									$clean[ $key ][ $k ] = $v;
								}
								elseif ( sanitize_title_with_dashes( $k ) === $k ) {
									// Allow post types which may not be registered yet.
									$clean[ $key ][ $k ] = $v;
								}
							}
						}
						break;

					case 'videositemap_taxonomies':
						$clean[ $key ]    = array();
						$valid_taxonomies = get_taxonomies( array( 'public' => true ) );
						if ( isset( $dirty[ $key ] ) && ( is_array( $dirty[ $key ] ) && $dirty[ $key ] !== array() ) ) {
							foreach ( $dirty[ $key ] as $k => $v ) {
								if ( in_array( $k, $valid_taxonomies, true ) ) {
									$clean[ $key ][ $k ] = $v;
								}
								elseif ( sanitize_title_with_dashes( $k ) === $k ) {
									// Allow taxonomies which may not be registered yet.
									$clean[ $key ][ $k ] = $v;
								}
							}
						}
						break;

					// Text field - may not be in form.
					// @todo - validate custom fields against meta table?
					case 'custom_fields':
						if ( isset( $dirty[ $key ] ) && $dirty[ $key ] !== '' ) {
							$clean[ $key ] = sanitize_text_field( $dirty[ $key ] );
						}
						break;

					// @todo - validate domains in some way?
					case 'wistia_domain':
						if ( isset( $dirty[ $key ] ) && $dirty[ $key ] !== '' ) {
							$clean[ $key ] = sanitize_text_field( urldecode( $dirty[ $key ] ) );
							$clean[ $key ] = preg_replace( array( '`^http[s]?://`', '`^//`', '`/$`' ), '', $clean[ $key ] );
						}
						break;

					case 'embedly_api_key':
						if ( isset( $dirty[ $key ] ) && $dirty[ $key ] !== '' && preg_match( '`^[a-f0-9]{32}$`', $dirty[ $key ] ) ) {
							$clean[ $key ] = sanitize_text_field( $dirty[ $key ] );
						}
						break;

					// Numeric text field - may not be in form.
					case 'content_width':
						if ( isset( $dirty[ $key ] ) && $dirty[ $key ] !== '' ) {
							$int = WPSEO_Video_Wrappers::yoast_wpseo_video_validate_int( $dirty[ $key ] );

							if ( $int !== false && $int > 0 ) {
								$clean[ $key ] = $int;
							}
						}
						break;

					// Boolean (checkbox) field - may not be in form.
					case 'cloak_sitemap':
					case 'disable_rss':
					case 'facebook_embed':
					case 'fitvids':
						if ( isset( $dirty[ $key ] ) ) {
							$clean[ $key ] = WPSEO_Video_Wrappers::validate_bool( $dirty[ $key ] );
						}
						else {
							$clean[ $key ] = false;
						}
						break;
				}
			}

			return $clean;
		}
	} /* End of class WPSEO_Option_Video */

} /* End of class-exists wrapper */
