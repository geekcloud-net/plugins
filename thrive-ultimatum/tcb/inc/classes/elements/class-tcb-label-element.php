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
 * Class TCB_Label_Element
 */
class TCB_Label_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Inline text', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return '';
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-inline-text, .tve_faqB h4';
	}

	/**
	 * Hidden element
	 *
	 * @return string
	 */
	public function hide() {
		return true;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'text'             => array(
				'config' => array(
					'FontSize'       => array(
						'config'  => array(
							'default' => '16',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'Font Size', 'thrive-cb' ),
							'um'      => array( 'px', 'em' ),
							'css'     => 'fontSize',
						),
						'extends' => 'Slider',
					),
					'LineHeight'     => array(
						'config'  => array(
							'default' => '1',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'Line Height', 'thrive-cb' ),
							'um'      => array( 'em', 'px' ),
							'css'     => 'lineHeight',
						),
						'extends' => 'Slider',
					),
					'LetterSpacing'  => array(
						'config'  => array(
							'default' => 'auto',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'Letter Spacing', 'thrive-cb' ),
							'um'      => array( 'px' ),
							'css'     => 'letterSpacing',
						),
						'extends' => 'Slider',
					),
					'FontColor'      => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Font Color', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'FontBackground' => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Font Highlight', 'thrive-cb' ),
							'options' => array(
								'output' => 'object',
							),
						),
						'extends' => 'ColorPicker',
					),
					'FontFace'       => array(
						'config'  => array(
							'template' => 'controls/font-manager',
							'tinymce'  => true,
						),
						'extends' => 'FontManager',
					),
				),
			),
			'typography'       => array( 'hidden' => true ),
			'layout'           => array( 'hidden' => true ),
			'borders'          => array( 'hidden' => true ),
			'animation'        => array( 'hidden' => true ),
			'background'       => array( 'hidden' => true ),
			'responsive'       => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
			'shadow'           => array(
				'config' => array(
					'disabled_controls' => array( 'inner', 'drop' ),
					'with_froala'       => true,
				),
			),
		);
	}
}
