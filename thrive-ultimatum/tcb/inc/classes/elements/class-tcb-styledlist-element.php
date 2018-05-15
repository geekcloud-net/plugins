<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/9/2017
 * Time: 8:58 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Styledlist_Element
 */
class TCB_Styledlist_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Styled List', 'thrive-cb' );
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
		return 'styled_list';
	}

	/**
	 * Styled List element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return ' .thrv-styled_list';
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
		$styledlist = array(
			'styledlist' => array(
				'config' => array(
					'item_spacing' => array(
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
					'ModalPicker'  => array(
						'config' => array(
							'label' => __( 'Change all icons', 'thrive-cb' ),
						),
					),
					'preview'      => array(
						'config' => array(
							'sortable' => true,
						),
					),
				),
			),
			'typography' => array(
				'disabled_controls' => array(
					'[data-value="tcb-typography-line-height"] ',
					'.tve-advanced-controls',
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

		return array_merge( $styledlist, $this->group_component() );
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
					'value'    => 'all_icons',
					'selector' => '.thrv_icon:not(.tve_lg_input_container .thrv_icon)',
					'name'     => __( 'Grouped Icons', 'thrive-cb' ),
					'singular' => __( '-- Icon %s', 'thrive-cb' ),
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
