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
 * Class TCB_Columns_Element
 */
class TCB_Table_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Table', 'thrive-cb' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'table';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_table,.thrv-table-placeholder';
	}

	/**
	 * Table extra sidebar state - used in MANAGE CELLS mode.
	 *
	 * @return null|string
	 */
	public function get_sidebar_extra_state() {
		return tcb_template( 'sidebars/table-edit-state', null, true );
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'table'        => array(
				'config' => array(
					'cellpadding'         => array(
						'css_suffix' => array( ' .tve_table td', ' .tve_table th' ),
						'config'     => array(
							'min'     => 0,
							'max'     => 60,
							'default' => '',
							'label'   => __( 'Cell padding', 'thrive-cb' ),
							'um'      => array( 'px' ),
						),
					),
					'sortable'            => array(
						'to'     => '.tve_table',
						'config' => array(
							'label' => __( 'Make table sortable', 'thrive-cb' ),
						),
					),
					'header_bg'           => array(
						'css_suffix' => ' > .tve_table > thead > tr > th',
						'config'     => array(
							'label' => __( 'Header color', 'thrive-cb' ),
						),
					),
					'cell_bg'             => array(
						'css_suffix' => ' > .tve_table > tbody > tr > td',
						'config'     => array(
							'label' => __( 'Cell color', 'thrive-cb' ),
						),
					),
					'even_rows'           => array(
						'css_suffix' => ' > .tve_table > tbody > tr:nth-child(2n) > td',
						'config'     => array(
							'label' => __( 'Even rows color', 'thrive-cb' ),
						),
					),
					'odd_rows'            => array(
						'css_suffix' => ' > .tve_table > tbody > tr:nth-child(2n+1) > td',
						'config'     => array(
							'label' => __( 'Odd rows color', 'thrive-cb' ),
						),
					),
					'valign'              => array(
						'css_suffix' => array( ' .tve_table td', ' .tve_table th' ),
						'config'     => array(
							'name'    => __( 'Vertical Align', 'thrive-cb' ),
							'buttons' => array(
								array(
									'icon'    => 'none',
									'default' => true,
									'value'   => '',
								),
								array(
									'icon'  => 'top',
									'value' => 'top',
								),
								array(
									'icon'  => 'vertical',
									'value' => 'middle',
								),
								array(
									'icon'  => 'bot',
									'value' => 'bottom',
								),
							),
						),
						'extends'    => 'ButtonGroup',
					),
					'mobile_table'        => array(
						'config' => array(
							'name'  => '',
							'label' => __( 'Create mobile-responsive table', 'thrive-cb' ),
						),
					),
					'mobile_header_width' => array(
						'config' => array(
							'default' => '50',
							'min'     => '10',
							'max'     => '90',
							'label'   => __( 'Mobile header width', 'thrive-cb' ),
							'um'      => array( '%' ),
						),
					),
				),
			),
			'tableborders' => array(
				'config' => array(
					'to'           => '> .tve_table',
					'InnerBorders' => array(
						'config' => array(
							'label' => __( 'Apply inner border', 'thrive-cb' ),
						),
					),
					'border_th'    => array(
						'css_suffix' => ' > thead > tr > th',
						'config'     => array(
							'label' => __( 'Heading border', 'thrive-cb' ),
						),
					),
					'border_td'    => array(
						'css_suffix' => ' > tbody > tr > td',
						'config'     => array(
							'label' => __( 'Cell border', 'thrive-cb' ),
						),
					),
				),
				'order'  => 10,
			),
			'borders'      => array(
				'hidden' => true,
			),
			'animation'    => array(
				'hidden' => true,
			),
			'typography'   => array(
				'disabled_controls' => array(
					'[data-value="tcb-typography-line-height"]'
				),
				'config'            => array(
					'TextAlign' => array(
						'css_suffix' => array( ' .tve_table td', ' .tve_table th' ),
					),
				),
			),
			'shadow'       => array(
				'config' => array(
					'disabled_controls' => array( 'inner' ),
				),
			),
			'background'   => array( 'hidden' => true ),
		);
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return $this->get_thrive_advanced_label();
	}
}
