<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 10/19/2017
 * Time: 4:21 PM
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Landing_Page_Text_Element
 */
class TCB_Landing_Page_Text_Element extends TCB_Element_Abstract {

	/**
	 * This is only available on landing pages
	 *
	 * @return false|string
	 */
	public function is_available() {
		return tcb_post()->is_landing_page();
	}

	public function name() {
		return __( 'Landing Page Text Element', 'thrive-cb' );
	}

	/**
	 * Element identifier
	 *
	 * These settings apply directly on <body>, on landing pages
	 *
	 * @return string
	 */
	public function identifier() {
		return 'body.tve_lp .thrv-lp-text';
	}

	/**
	 * Either to display or not the element in the sidebar menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
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
	 * Hide all general components.
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'lp-text'            => array(),
			'lp-text-typography' => array(
				'config' => array(
					'LinkStates'     => array(
						'config' => array(
							'name'    => __( 'States', 'thrive-cb' ),
							'buttons' => array(
								array( 'value' => 'default', 'text' => 'Default', 'default' => true ),
								array( 'value' => 'hover', 'text' => 'Hover' ),
							),
						),
					),
					'ToggleControls' => array(
						'config'  => array(
							'buttons' => array(
								array( 'value' => 'tcb-lp-text-font-size', 'text' => __( 'Font Size', 'thrive-cb' ), 'default' => true ),
								array( 'value' => 'tcb-lp-text-line-height', 'text' => __( 'Line Height', 'thrive-cb' ) ),
								array( 'value' => 'tcb-lp-text-letter-spacing', 'text' => __( 'Letter Spacing', 'thrive-cb' ) ),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'FontSize'       => array(
						'config'  => array(
							'default' => '16',
							'min'     => '1',
							'max'     => '100',
							'label'   => '',
							'um'      => array( 'px', 'em' ),
							'css'     => 'fontSize',
						),
						'extends' => 'Slider',
					),
					'LetterSpacing'  => array(
						'config'  => array(
							'default' => 'auto',
							'min'     => '0',
							'max'     => '100',
							'label'   => '',
							'um'      => array( 'px' ),
							'css'     => 'letterSpacing',
						),
						'extends' => 'Slider',
					),
					'FontColor'      => array(
						'config'  => array(
							'default' => '000',
							'icon'    => true,
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'TextAlign'      => array(
						'config'  => array(
							'buttons' => array(
								array(
									'icon'    => 'format-align-left',
									'text'    => '',
									'value'   => 'left',
									'default' => true,
								),
								array(
									'icon'  => 'format-align-center',
									'text'  => '',
									'value' => 'center',
								),
								array(
									'icon'  => 'format-align-right',
									'text'  => '',
									'value' => 'right',
								),
								array(
									'icon'  => 'format-align-justify',
									'text'  => '',
									'value' => 'justify',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'TextStyle'      => array(
						'config'  => array(
							'checkbox' => true,
							'buttons'  => array(
								array(
									'icon'  => 'format-bold',
									'text'  => '',
									'value' => 'bold',
									'data'  => array( 'style' => 'font-weight', 'off' => 'normal' ),
								),
								array(
									'icon'  => 'format-italic',
									'text'  => '',
									'value' => 'italic',
									'data'  => array( 'style' => 'font-style', 'off' => 'normal' ),
								),
								array(
									'icon'  => 'format-underline',
									'text'  => '',
									'value' => 'underline',
									'data'  => array( 'style' => 'text-decoration' ),
								),
								array(
									'icon'  => 'format-strikethrough-variant',
									'text'  => '',
									'value' => 'line-through',
									'data'  => array( 'style' => 'text-decoration' ),
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'TextTransform'  => array(
						'config'  => array(
							'buttons' => array(
								array(
									'icon'    => 'none',
									'text'    => '',
									'value'   => 'none',
									'default' => true,
								),
								array(
									'icon'  => 'format-all-caps',
									'text'  => '',
									'value' => 'uppercase',
								),
								array(
									'icon'  => 'format-capital',
									'text'  => '',
									'value' => 'capitalize',
								),
								array(
									'icon'  => 'format-lowercase',
									'text'  => '',
									'value' => 'lowercase',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'FontFace'       => array(
						'config'  => array(
							'template' => 'controls/font-manager',
							'tinymce'  => false,
						),
						'extends' => 'FontManager',
					),
					'LineHeight'     => array(
						'config'  => array(
							'default' => '16',
							'min'     => '1',
							'max'     => '100',
							'label'   => '',
							'um'      => array( 'px', 'em' ),
							'css'     => 'lineHeight',
						),
						'extends' => 'Slider',
					),
				),
			),
			'lp-text-layout'     => array(
				'disabled_controls' => array(
					'MaxWidth',
					'hr',
					'Alignment',
					'.tve-advanced-controls',
				),
				'config'            => array(),
			),
			'lp-text-background' => array(
				'config'            => array(
					'ColorPicker'       => array(
						'config' => array(
							'icon' => true,
						),
					),
					'PreviewFilterList' => array(
						'config' => array(
							'sortable'    => false,
							'extra_class' => 'tcb-preview-list-white',
						),
					),
					'PreviewList'       => array(
						'config' => array(
							'sortable' => true,
						),
					),
				),
				'disabled_controls' => array(
					'video',
				),
			),
			'lp-text-borders'    => array(
				'config' => array(),
			),
			'lp-text-shadow'     => array(
				'config' => array(),
			),
			'typography'         => array( 'hidden' => true ),
			'layout'             => array( 'hidden' => true ),
			'borders'            => array( 'hidden' => true ),
			'animation'          => array( 'hidden' => true ),
			'responsive'         => array( 'hidden' => true ),
			'styles-templates'   => array( 'hidden' => true ),
			'shadow'             => array( 'hidden' => true ),
			'background'         => array( 'hidden' => true ),
		);
	}
}
