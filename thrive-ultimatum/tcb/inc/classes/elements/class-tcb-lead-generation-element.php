<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TCB_Lead_Generation_Element
 */
class TCB_Lead_Generation_Element extends TCB_Element_Abstract {

	/**
	 * @return string
	 */
	public function name() {
		return __( 'Lead Generation', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'form';
	}

	/**
	 * @return string
	 */
	public function icon() {
		return 'lead_gen';
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.thrv_lead_generation';
	}

	/**
	 * @return string
	 */
	public function get_captcha_site_key() {

		$credentials = Thrive_Dash_List_Manager::credentials( 'recaptcha' );

		return ! empty( $credentials['site_key'] ) ? $credentials['site_key'] : '';
	}

	/**
	 * Lead Generation extra sidebar state - used in EDIT COMPONENTS mode.
	 *
	 * @return null|string
	 */
	public function get_sidebar_extra_state() {
		return tcb_template( 'sidebars/lead-generation', null, true );
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return array(
			'lead_generation' => array(
				'config' => array(
					'ApiConnections' => array(
						'config' => array(),
					),
					'Captcha'        => array(
						'config'  => array(
							'name'     => '',
							'label'    => __( 'Add Captcha to prevent spam signups', 'thrive-cb' ),
							'default'  => false,
							'site_key' => $this->get_captcha_site_key(),
						),
						'extends' => 'Checkbox',
					),
					'CaptchaTheme'   => array(
						'config'  => array(
							'name'        => __( 'Theme', 'thrive-cb' ),
							'label_col_x' => 5,
							'options'     => array(
								array(
									'value' => 'light',
									'name'  => __( 'Light', 'thrive-cb' ),
								),
								array(
									'value' => 'dark',
									'name'  => __( 'Dark', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'Select',
					),
					'CaptchaType'    => array(
						'config'  => array(
							'name'        => __( 'Type', 'thrive-cb' ),
							'label_col_x' => 5,
							'options'     => array(
								array(
									'value' => 'image',
									'name'  => __( 'Image', 'thrive-cb' ),
								),
								array(
									'value' => 'audio',
									'name'  => __( 'Audio', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'Select',
					),
					'CaptchaSize'    => array(
						'config'  => array(
							'name'        => __( 'Size', 'thrive-cb' ),
							'label_col_x' => 5,
							'options'     => array(
								array(
									'value' => 'normal',
									'name'  => __( 'Normal', 'thrive-cb' ),
								),
								array(
									'value' => 'compact',
									'name'  => __( 'Compact', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'Select',
					),
				),
			),
			'typography'      => array(
				'hidden' => true,
			),
			'layout'          => array(
				'disabled_controls' => array(
					'.tve-advanced-controls'
				),
				'config'            => array(),
			),
			'borders'         => array(
				'disabled_controls' => array(),
			),
			'animation'       => array(
				'hidden' => true,
			),
			'shadow'          => array(
				'config' => array(
					'disabled_controls' => array( 'text' ),
				),
			),
		);
	}

	/**
	 * Element category that will be displayed in the sidebar
	 * @return string
	 */
	public function category() {
		return $this->get_thrive_advanced_label();
	}
}
