<?php
/**
 * @package    Internals
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
 * Meta value addition for Video SEO
 */
if ( ! class_exists( 'WPSEO_Meta_Video' ) ) {

	/**
	 * Add the VideoSEO metabox fields and associated validation to the WPSEO-meta management
	 *
	 * {@internal WPSEO_Meta is a class with only static methods, so we will not extend it with
	 * a child class, we will only add to it by hooking in.}}
	 */
	class WPSEO_Meta_Video {

		/**
		 * @static
		 *
		 * @var array $meta_fields Meta box field definitions for the meta box form
		 *
		 *        Array format:
		 *        (required)    'type'      => (string) field type. i.e. text / textarea / checkbox /
		 *                          radio / select / multiselect / upload / snippetpreview etc
		 *        (required)    'title'      => (string) table row title
		 *        (recommended)  'default_value' => (string) default value for the field
		 *                          IMPORTANT:
		 *                          - if the field has options, the default has to be the
		 *                            key of one of the options
		 *                          - if the field is a text field, the default **has** to be
		 *                            an empty string as otherwise the user can't save
		 *                            an empty value/delete the meta value
		 *                          - if the field is a checkbox, the only valid values
		 *                            are 'on' or 'off'
		 *        (semi-required)  'options'    => (array) options for used with (multi-)select and radio
		 *                          fields, required if that's the field type
		 *                          key = (string) value which will be saved to db
		 *                          value = (string) text label for the option
		 *        (optional)    'autocomplete'  => (bool) whether auto complete is on for text fields,
		 *                          defaults to true
		 *        (optional)    'class'      => (string) classname(s) to add to the actual <input> tag
		 *        (optional)    'description'  => (string) description to show underneath the field
		 *        (optional)    'expl'      => (string) label for a checkbox
		 *        (optional)    'help'      => (string) help text to show on mouse over ? image
		 *        (optional)    'rows'      => (int) number of rows for a textarea, defaults to 3
		 *
		 *        (optional)    'placeholder'  => (string) currently not used in this class
		 *
		 * {@internal
		 * - Titles, help texts, description text and option labels are added via a translate_meta_boxes() method
		 *   in the relevant child classes (WPSEO_Metabox and WPSEO_Social_admin) as they are only needed there.
		 * - Beware: even though the meta keys are divided into subsets, they still have to be uniquely named!}}
		 */
		public static $meta_fields = array(
			'video'    => array(
				'videositemap-disable'             => array(
					'type'          => 'checkbox',
					'title'         => '', // Translation added later.
					'default_value' => 'off',
					'expl'          => '', // Translation added later.
				),
				'videositemap-thumbnail'           => array(
					'type'          => 'upload',
					'title'         => '', // Translation added later.
					'default_value' => '',
					'description'   => '',
					'placeholder'   => '', // Translation added later.
				),
				'videositemap-duration'            => array(
					'type'          => 'number',
					'title'         => '', // Translation added later.
					'default_value' => '0',
					'description'   => '', // Translation added later.
					'options'       => array(
						'min_value' => '0',
						'max_value' => null,
						'step'      => '1',
					),
				),

				/*
				 * {@internal Not used directly anywhere except for storage and retrieval of the meta box
				 *            form values. The real use is in the video_meta key which retrieves the value
				 *            of this from $_POST.
				 *            However removing this would cause the meta box field to go blank as the info
				 *            can no longer be retrieved, so leaving it as is for now.}}
				 */
				'videositemap-tags'                => array(
					'type'          => 'text',
					'title'         => '', // Translation added later.
					'default_value' => '',
					'description'   => '', // Translation added later.
				),

				/*
				 * {@internal Not used directly anywhere except for storage and retrieval of the meta box
				 *            form values. The real use is in the video_meta key which retrieves the value
				 *            of this from $_POST.
				 *            However removing this would cause the meta box field to go blank as the info
				 *            can no longer be retrieved, so leaving it as is for now.}}
				 */
				'videositemap-category'            => array(
					'type'          => 'text',
					'title'         => '', // Translation added later.
					'default_value' => '',
					'description'   => '', // Translation added later.
				),
				'videositemap-rating'              => array(
					'type'          => 'number',
					'title'         => '', // Translation added later.
					'default_value' => 0,
					'description'   => '', // Translation added later.
					'options'       => array(
						'min_value' => '0',
						'max_value' => '5',
						'step'      => '0.1',
					),
				),
				'videositemap-not-family-friendly' => array(
					'type'          => 'checkbox',
					'title'         => '', // Translation added later.
					'default_value' => 'off',
					'expl'          => '', // Translation added later.
					'description'   => '', // Translation added later.
				),
			),


			/* Fields we should validate & save, but not show on any form */
			'non_form' => array(
				'video_meta' => array(
					'type'          => null,
					'default_value' => 'none',
					'serialized'    => true,
				),
			),
		);


		/**
		 * Hook into WPSEO_Meta
		 *
		 * @static
		 *
		 * @return void
		 */
		public static function init() {
			add_filter( 'add_extra_wpseo_meta_fields', array( __CLASS__, 'register_video_meta_fields' ) );
			add_filter( 'wpseo_metabox_entries_video', array( __CLASS__, 'adjust_video_meta_field_defs' ), 10, 2 );

			add_filter( 'wpseo_sanitize_post_meta_' . WPSEO_Meta::$meta_prefix . 'videositemap-duration', array( __CLASS__, 'sanitize_duration' ), 10, 3 );
			add_filter( 'wpseo_sanitize_post_meta_' . WPSEO_Meta::$meta_prefix . 'videositemap-rating', array( __CLASS__, 'sanitize_rating' ), 10, 3 );
			add_filter( 'wpseo_sanitize_post_meta_' . WPSEO_Meta::$meta_prefix . 'videositemap-thumbnail', array( __CLASS__, 'sanitize_thumbnail_upload' ), 10, 3 );
			add_filter( 'wpseo_sanitize_post_meta_' . WPSEO_Meta::$meta_prefix . 'video_meta', array( __CLASS__, 'sanitize_video_meta' ), 10, 2 );
		}


		/**
		 * Add the video meta fields to the WPSEO_Meta::$meta_fields definitions
		 *
		 * @static
		 *
		 * @param  array $fields Fields already in place (possibly from other add-on plugins).
		 *
		 * @return array
		 */
		public static function register_video_meta_fields( $fields ) {
			return WPSEO_Meta::array_merge_recursive_distinct( $fields, self::$meta_fields );
		}

		/**
		 * Prepare the video meta field definitions for display in the metabox
		 *
		 * @static
		 *
		 * @param  string $field_defs Field definitions for the requested tab.
		 * @param  string $post_type  Post type of the current post.
		 *
		 * @return array              Array containing the meta box field definitions
		 */
		public static function adjust_video_meta_field_defs( $field_defs, $post_type ) {
			$post = ( ! empty( $GLOBALS['post'] ) ) ? $GLOBALS['post'] : null;

			$field_defs['videositemap-disable']['expl'] = sprintf( $field_defs['videositemap-disable']['expl'], $post_type );

			$video = array();
			if ( isset( $post->ID ) ) {
				$video = WPSEO_Meta::get_value( 'video_meta', $post->ID );
			}

			if ( ( ! isset( $post->ID ) || '' === WPSEO_Meta::get_value( 'videositemap-thumbnail', $post->ID ) ) && ( isset( $video['thumbnail_loc'] ) && $video['thumbnail_loc'] !== '' ) ) {
				$field_defs['videositemap-thumbnail']['description'] = sprintf( $field_defs['videositemap-thumbnail']['description'], '<a target="_blank" href="' . esc_url( $video['thumbnail_loc'] ) . '">', '</a>' );
			}
			else {
				$field_defs['videositemap-thumbnail']['description'] = '';
			}

			if ( isset( $video['duration'] ) ) {
				$field_defs['videositemap-duration']['default_value'] = $video['duration'];
			}

			return $field_defs;
		}

		/**
		 * Sanitize the video thumbnail upload post meta
		 *
		 * @static
		 *
		 * @param  mixed  $clean      Potentially pre-cleaned version of the new meta value.
		 * @param  mixed  $meta_value The new value.
		 * @param  string $field_def  The field definition for the current meta field.
		 *
		 * @return string             Cleaned value
		 */
		public static function sanitize_thumbnail_upload( $clean, $meta_value, $field_def ) {
			// Validate as url.
			$clean = $field_def['default_value'];

			$url = WPSEO_Video_Wrappers::yoast_wpseo_video_sanitize_url( $meta_value );

			if ( $url !== '' ) {
				$clean = $url;
			}

			return $clean;
		}

		/**
		 * Sanitize the video duration post meta
		 *
		 * @static
		 *
		 * @param  mixed  $clean      Potentially pre-cleaned version of the new meta value.
		 * @param  mixed  $meta_value The new value.
		 * @param  string $field_def  The field definition for the current meta field.
		 *
		 * @return string             Cleaned value
		 */
		public static function sanitize_duration( $clean, $meta_value, $field_def ) {
			$field_def = WPSEO_Meta::get_meta_field_defs( 'video' );
			$field_def = $field_def['videositemap-duration'];
			$clean     = $field_def['default_value'];

			$int = WPSEO_Video_Wrappers::yoast_wpseo_video_validate_int( $meta_value );

			if ( $int !== false && $int > 0 ) {
				$clean = strval( $int );
			}

			return $clean;
		}


		/**
		 * Sanitize the video rating post meta
		 *
		 * @static
		 *
		 * @param  mixed  $clean      Potentially pre-cleaned version of the new meta value.
		 * @param  mixed  $meta_value The new value.
		 * @param  string $field_def  The field definition for the current meta field.
		 *
		 * @return string             Cleaned value
		 */
		public static function sanitize_rating( $clean, $meta_value, $field_def ) {
			$clean = $field_def['default_value'];
			if ( is_numeric( $meta_value ) && ( $meta_value >= 0 && $meta_value <= 5 ) ) {
				$clean = $meta_value;
			}

			return $clean;
		}


		/**
		 * Sanitize the video meta post meta - set in function, not from user input so no extra validation done
		 *
		 * @static
		 *
		 * @param  mixed $clean      Potentially pre-cleaned version of the new meta value.
		 * @param  mixed $meta_value The new value.
		 *
		 * @return string            Cleaned value
		 */
		public static function sanitize_video_meta( $clean, $meta_value ) {
			if ( is_array( $meta_value ) && $meta_value !== array() ) {
				$clean = $meta_value;
			}

			return $clean;
		}


		/**
		 * Upgrade routine to deal with the fall-out of issue #102
		 */
		public static function re_add_durations() {
			global $wpdb;

			$query       = $wpdb->prepare(
				"SELECT post_id, meta_value
					FROM {$wpdb->postmeta}
					WHERE meta_key = %s",
				WPSEO_Meta::$meta_prefix . 'video_meta'
			);
			$video_metas = $wpdb->get_results( $query );

			$query           = $wpdb->prepare(
				"SELECT post_id
					FROM {$wpdb->postmeta}
					WHERE meta_key = %s",
				WPSEO_Meta::$meta_prefix . 'videositemap-duration'
			);
			$known_durations = $wpdb->get_col( $query );

			if ( is_array( $video_metas ) && $video_metas !== array() ) {
				foreach ( $video_metas as $video ) {
					if ( $known_durations === array() || ! in_array( $video->post_id, $known_durations, true ) ) {
						$meta = maybe_unserialize( $video->meta_value );
						if ( isset( $meta['duration'] ) ) {
							WPSEO_Meta::set_value( 'videositemap-duration', $meta['duration'], $video->post_id );
						}
						unset( $meta );
					}
				}
			}
			unset( $query, $video_metas, $known_durations, $video );
		}
	} /* End of class WPSEO_Meta_Video */

} /* End of class-exists wrapper */
