<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 11/3/2017
 * Time: 9:20 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Nunberlist_Element
 */
class TCB_Numberedlist_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Numbered List', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'list';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'numbered-list';
	}

	/**
	 * Number List element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return ' .thrv-numbered_list';
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
		$numberedlist = array(
			'numberedlist' => array(
				'config' => array(
					'FontFace'         => array(
						'css_suffix' => ' .tcb-numbered-list-number',
						'config'     => array(
							'template' => 'controls/font-manager',
							'tinymce'  => false,
						),
						'extends'    => 'FontManager',
					),
					'decorations'      => array(
						'css_suffix' => ' .tcb-numbered-list-number',
						'config'     => array(
							'name'     => __( 'Numbers Style', 'thrive-cb' ),
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
						'extends'    => 'ButtonGroup',
					),
					'numbers_color'    => array(
						'css_suffix' => ' .tcb-numbered-list-number',
						'config'     => array(
							'label' => __( 'Numbers color', 'thrive-cb' ),
						),
						'extends'    => 'ColorPicker',
					),
					'item_spacing'     => array(
						'css_suffix' => ' > ul > li',
						'css_prefix' => '#tve_editor ',
						'config'     => array(
							'default' => '20',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'List Item Spacing', 'thrive-cb' ),
							'um'      => array( 'px', 'em' ),
							'css'     => 'margin-bottom',
						),
						'extends'    => 'Slider',
					),
					'starting_number'  => array(
						'config'  => array(
							'name'      => __( 'Starting Number', 'thrive-cb' ),
							'default'   => 10,
							'min'       => 0,
							'max'       => 366,
							'maxlength' => 3,
						),
						'extends' => 'Input',
					),
					'increment_number' => array(
						'config'  => array(
							'name'      => __( 'Increment', 'thrive-cb' ),
							'default'   => 10,
							'min'       => 1,
							'max'       => 366,
							'maxlength' => 3,
						),
						'extends' => 'Input',
					),
					'numbers_size'     => array(
						'css_suffix' => ' .tcb-numbered-list-number',
						'config'     => array(
							'default' => '18',
							'min'     => '8',
							'max'     => '200',
							'label'   => __( 'Numbers size', 'thrive-cb' ),
							'um'      => array( 'px' ),
							'css'     => 'fontSize',
						),
						'extends'    => 'Slider',
					),
					'preview'          => array(
						'config' => array(
							'sortable' => true,
						),
					),
				),
			),
			'typography'   => array(
				'disabled_controls' => array(
					'.tve-advanced-controls',
					'LineHeight',
					'p_spacing',
					'h1_spacing',
					'h2_spacing',
					'h3_spacing',
				),
				'config'            => array(
					'TextAlign' => array(
						'css_suffix'   => ' .thrv-styled-list-item',
						'property'     => 'justify-content',
						'property_val' => array(
							'left'    => 'flex-start',
							'center'  => 'center',
							'right'   => 'flex-end',
							'justify' => 'space-evenly',
						),
					),
				),
			),
		);

		return array_merge( $numberedlist, $this->group_component() );
	}

	/**
	 * Element category that will be displayed in the sidebar
	 * @return string
	 */
	public function category() {
		return $this->get_thrive_advanced_label();
	}

	/**
	 * Group Edit Properties
	 *
	 * @return array|bool
	 */
	public function has_group_editing() {
		return array(
			'exit_label'    => __( 'Exit Group Styling', 'thrive-cb' ),
			'select_values' => array(
				array(
					'value'    => 'all_list_items',
					'selector' => '.thrv-styled-list-item',
					'name'     => __( 'Grouped List Items', 'thrive-cb' ),
					'singular' => __( '-- List Item %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_numbers',
					'selector' => '.thrv-disabled-label',
					'name'     => __( 'Grouped Figures', 'thrive-cb' ),
					'singular' => __( '-- Figure %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'all_texts',
					'selector' => '.thrv-advanced-inline-text',
					'name'     => __( 'Grouped Texts', 'thrive-cb' ),
					'singular' => __( '-- Text %s', 'thrive-cb' ),
				),
			),
		);
	}
}
