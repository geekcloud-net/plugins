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
 * Class TCB_ContentBox_Element
 */
class TCB_ContentBox_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Content Box', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'container,box,content';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'content_box';
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_contentbox_shortcode, .thrv-content-box';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$prefix_config = array( 'css_prefix' => '#tve_editor .thrv-content-box ' );

		return array(
			'contentbox' => array(
				'config' => array(
					'BoxHeight'        => array(
						'config'  => array(
							'default' => '80',
							'min'     => '1',
							'max'     => '1000',
							'label'   => __( 'Content Minimum Height', 'thrive-cb' ),
							'um'      => array( 'px', 'vh' ),
							'css'     => 'min-height',
						),
						'to'      => ' > .tve-cb',
						'extends' => 'Slider',
					),
					'BoxWidth'         => array(
						'config'  => array(
							'default' => '1024',
							'min'     => '100',
							'max'     => '2000',
							'label'   => __( 'Content Maximum Width', 'thrive-cb' ),
							'um'      => array( 'px', '%' ),
							'css'     => 'max-width',
						),
						'extends' => 'Slider',
					),
					'VerticalPosition' => array(
						'config'  => array(
							'name'    => __( 'Vertical Position', 'thrive-cb' ),
							'buttons' => array(
								array(
									'icon'    => 'top',
									'default' => true,
									'value'   => '',
								),
								array(
									'icon'  => 'vertical',
									'value' => 'center',
								),
								array(
									'icon'  => 'bot',
									'value' => 'flex-end',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
				),
			),
			'borders'    => array(
				'config' => array(
					'Borders' => array(
						'to'        => '>.tve-content-box-background',
						'important' => true,
					),
					'Corners' => array(
						'to' => '>.tve-content-box-background',
					),
				),
			),
			'layout'     => array(
				'config' => array(
					'Position' => array(
						'important' => true,
					),
				),
			),
			'background' => array(
				'config' => array(
					'to' => '>.tve-content-box-background',
				),
			),
			'shadow'     => array(
				'config' => array(
					'to' => '>.tve-content-box-background',
				),
			),
			'decoration' => array(
				'config' => array(
					'to' => '>.tve-content-box-background',
				),
			),
			'typography' => array(
				'disabled_controls' => array(),
				'config'            => array(
					'to'         => '.tve-cb',
					'FontSize'   => $prefix_config,
					'FontColor'  => $prefix_config,
					'LineHeight' => $prefix_config,
					'FontFace'   => $prefix_config,
				),
			),
		);
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return $this->get_thrive_basic_label();
	}
}
