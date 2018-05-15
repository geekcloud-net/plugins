<?php
/**
 * @package WPSEO_LOCAL\Import
 */

if ( ! defined( 'WPSEO_LOCAL_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

if ( ! class_exists( 'WPSEO_Local_Import_Export' ) ) {

	/**
	 * Class that holds the functionality for the WPSEO Local Import and Export functions
	 *
	 * @since 3.9
	 */
	class WPSEO_Local_Import_Export {

		/**
		 * @var string WPSEO Upload Directory.
		 */
		protected $wpseo_upload_dir;

		/**
		 * @var string Error and succes messages.
		 */
		protected $messages;

		/**
		 * @var string Holds the WPSEO Local option name.
		 */
		protected $option_name;

		/**
		 * @var array Holds the predefined column names.
		 */
		protected $columns;

		/**
		 * Class constructor.
		 */
		public function __construct() {
			$this->set_option_name();
			$this->set_upload_dir();
			$this->set_messages();
			$this->set_columns();

			add_action( 'admin_notices', array( $this, 'show_notices' ) );
		}

		/**
		 * Set WPSEO Local option name.
		 */
		private function set_option_name() {
			$this->option_name = 'wpseo_local';
		}

		/**
		 * Set the WPSEO Upload Dir
		 */
		private function set_upload_dir() {
			$wp_upload_dir          = wp_upload_dir();
			$this->wpseo_upload_dir = trailingslashit( $wp_upload_dir['basedir'] . '/wpseo/import' );
		}

		/**
		 * Set message array.
		 */
		private function set_messages() {
			$this->messages = array();
		}

		/**
		 * Set predefined columns for importing and exporting.
		 */
		private function set_columns() {
			$this->columns = array(
				'name',
				'address',
				'address_2',
				'city',
				'zipcode',
				'state',
				'country',
				'phone',
				'phone2nd',
				'fax',
				'email',
				'description',
				'image',
				'category',
				'url',
				'vat_id',
				'tax_id',
				'coc_id',
				'notes_1',
				'notes_2',
				'notes_3',
				'business_type',
				'location_logo',
				'is_postal_address',
				'custom_marker',
				'multiple_opening_hours',
				'opening_hours_monday_from',
				'opening_hours_monday_to',
				'opening_hours_monday_second_from',
				'opening_hours_monday_second_to',
				'opening_hours_tuesday_from',
				'opening_hours_tuesday_to',
				'opening_hours_tuesday_second_from',
				'opening_hours_tuesday_second_to',
				'opening_hours_wednesday_from',
				'opening_hours_wednesday_to',
				'opening_hours_wednesday_second_from',
				'opening_hours_wednesday_second_to',
				'opening_hours_thursday_from',
				'opening_hours_thursday_to',
				'opening_hours_thursday_second_from',
				'opening_hours_thursday_second_to',
				'opening_hours_friday_from',
				'opening_hours_friday_to',
				'opening_hours_friday_second_from',
				'opening_hours_friday_second_to',
				'opening_hours_saturday_from',
				'opening_hours_saturday_to',
				'opening_hours_saturday_second_from',
				'opening_hours_saturday_second_to',
				'opening_hours_sunday_from',
				'opening_hours_sunday_to',
				'opening_hours_sunday_second_from',
				'opening_hours_sunday_second_to',
			);
		}

		/**
		 * Display admin notices.
		 */
		public function show_notices() {
			foreach ( $this->messages as $message ) {
				$class = 'notice-';
				if ( 'success' == $message['type'] ) {
					$class .= 'success';
				}
				else if ( 'error' == $message['type'] ) {
					$class .= 'error';
				}
				else {
					$class .= 'warning';
				}

				echo '<div class="notice ' . $class . ' is-dismissible">';
				echo wpautop( $message['content'] );
				echo '</div>';
			}
		}
	}
}
