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
 * Class TCB_Social_Element
 */
class TCB_Social_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Social Share', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'social';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'social_share';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_social.thrv_social_custom';
	}

	/**
	 * The HTML is generated from js
	 *
	 * @return string
	 */
	protected function html() {
		return '';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'social'     => array(
				'config' => array(
					'style'          => array(
						'config' => array(
							'label' => __( 'Style', 'thrive-cb' ),
						),
					),
					'stylePicker'    => array(
						'config' => array(
							'label' => __( 'Change style', 'thrive-cb' ),
							'items' => array(
								'tve_style_1' => 'Style 1',
								'tve_style_2' => 'Style 2',
								'tve_style_3' => 'Style 3',
								'tve_style_4' => 'Style 4',
								'tve_style_5' => 'Style 5',
							),
						),
					),
					'type'           => array(
						'config' => array(
							'name'    => __( 'Type', 'thrive-cb' ),
							'buttons' => array(
								array( 'value' => 'tve_social_ib', 'text' => __( 'Icon only', 'thrive-cb' ) ),
								array( 'value' => 'tve_social_itb', 'text' => __( 'Icon + text', 'thrive-cb' ), 'default' => true ),
								array( 'value' => 'tve_social_cb', 'text' => __( 'Counter', 'thrive-cb' ) ),
							),
						),
					),
					'orientation'    => array(
						'config' => array(
							'name'    => __( 'Orientation', 'thrive-cb' ),
							'buttons' => array(
								array( 'value' => 'h', 'text' => __( 'Horizontal', 'thrive-cb' ), 'default' => true ),
								array( 'value' => 'v', 'text' => __( 'Vertical', 'thrive-cb' ) ),
							),
						),
					),
					'size'           => array(
						'config' => array(
							'default' => '25',
							'min'     => '10',
							'max'     => '60',
							'label'   => __( 'Size', 'thrive-cb' ),
							'um'      => array( 'px' ),
						),
					),
					'preview'        => array(
						'config' => array(
							'sortable'     => true,
							'settingsIcon' => true,
						),
					),
					'has_custom_url' => array(
						'config' => array(
							'label' => __( 'Custom Share URL' ),
						),
					),
					'custom_url'     => array(
						'config' => array(
							'label_col_x' => 0,
							'placeholder' => __( 'http://', 'thrive-cb' ),
						),
					),
					'counts'         => array(
						'config' => array(
							'min'     => 0,
							'max'     => 2000,
							'default' => 0,
						),
					),
					'total_share'    => array(
						'config' => array(
							'label' => __( 'Show share count greater than', 'thrive-cb' ),
						),
					),
				),
				'order'  => 1,
			),
			'shadow'     => array( 'hidden' => true ),
			'typography' => array(
				'hidden' => true,
			),
			'animation'  => array(
				'hidden' => true,
			),
			'layout'     => array(
				'disabled_controls' => array(
					'MaxWidth',
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
