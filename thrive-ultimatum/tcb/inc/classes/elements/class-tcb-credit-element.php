<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 3/28/2017
 * Time: 10:09 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Credit_Card_Element
 */
class TCB_Credit_Element extends TCB_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Credit Card', 'thrive-cb' );
	}
	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'icon ';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'credit';
	}

	/**
	 * Text element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-credit';
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
			'credit'     => array(
				'config' => array(
					'size'                  => array(
						'config' => array(
							'default' => '25',
							'min'     => '10',
							'max'     => '150',
							'label'   => __( 'Size', 'thrive-cb' ),
							'um'      => array( 'px' ),
						),
					),
					'monochrome_background' => array(
						'css_suffix' => array( ' .visa-style-4-st0', ' .masterCard-style-4-st0', ' .americanExpress-style-4-st0', ' .payPal-style-4-st0', ' .discover-style-4-st0' ),
						'config'     => array(
							'default' => '#595E60',
							'label'   => __( 'Color', 'thrive-cb' ),
						),
					),
					'style'                 => array(
						'config' => array(
							'label' => __( 'Style', 'thrive-cb' ),
						),
					),
					'stylePicker'           => array(
						'config' => array(
							'label' => __( 'Change style', 'thrive-cb' ),
						),
					),
					'cards_list'            => array(
						'config' => array(
							'label' => __( 'Cards', 'thrive-cb' ),
						),
					),
					'cards'                 => array(
						'config' => array(
							'label' => __( 'Change cards', 'thrive-cb' ),
						),
					),
					'preview'               => array(
						'config' => array(
							'sortable' => true,
						),
					),
				),
				'order'  => 1,
			),
			'typography' => array( 'hidden' => true ),
			'shadow'     => array( 'hidden' => true ),
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
