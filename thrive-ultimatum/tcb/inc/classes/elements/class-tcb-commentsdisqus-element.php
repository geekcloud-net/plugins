<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/3/2017
 * Time: 1:42 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Commentsdisqus_Element
 */
class TCB_Commentsdisqus_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Disqus Comments', 'thrive-cb' );
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
		return 'disqus_comments';
	}

	/**
	 * Disqus Comments element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_disqus_comments'; // Compatibility with TCB 1.5
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'commentsdisqus' => array(
				'config' => array(
					'ForumName' => array(
						'config'  => array(
							'label'       => __( 'Forum Name', 'thrive-cb' ),
							'extra_attrs' => '',
							'label_col_x' => 12,
						),
						'extends' => 'LabelInput',
					),
					'URL'       => array(
						'config'  => array(
							'label'       => __( 'URL', 'thrive-cb' ),
							'extra_attrs' => '',
							'label_col_x' => 12,
						),
						'extends' => 'LabelInput',
					),
				),
			),
			'typography'     => array( 'hidden' => true ),
			'animation'      => array( 'hidden' => true ),
			'background'     => array( 'hidden' => true ),
			'shadow'         => array( 'hidden' => true ),
			'layout'         => array(
				'disabled_controls' => array(
					'MaxWidth',
					'.tve-advanced-controls',
					'Alignment',
					'hr',
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
