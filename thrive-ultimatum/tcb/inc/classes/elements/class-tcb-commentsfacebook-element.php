<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/4/2017
 * Time: 11:56 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Commentsfacebook_Element
 */
class TCB_Commentsfacebook_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Facebook Comments', 'thrive-cb' );
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
		return 'facebook_comments';
	}

	/**
	 * Facebook Comments element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_facebook_comments'; // Compatibility with TCB 1.5
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'commentsfacebook' => array(
				'config' => array(
					'moderators'     => array(
						'config'  => array(
							'top_text'        => __( 'Add Facebook user ID for the people that you will like to moderate the comments.', 'thrive-cb' ),
							'add_button_text' => __( 'Add Moderator', 'thrive-cb' ),
							'list_label'      => 'ID',
							'remove_title'    => __( 'Remove Moderator', 'thrive-cb' ),
							'list_items'      => array(),
						),
						'extends' => 'InputMultiple',
					),
					'URL'            => array(
						'config'  => array(
							'label'       => __( 'URL', 'thrive-cb' ),
							'extra_attrs' => '',
							'label_col_x' => 12,
						),
						'extends' => 'LabelInput',
					),
					'nr_of_comments' => array(
						'config'  => array(
							'default' => '20',
							'min'     => '1',
							'max'     => '200',
							'label'   => __( 'Number of comments', 'thrive-cb' ),
							'um'      => array( '' ),
						),
						'extends' => 'Slider',
					),
					'color_scheme'   => array(
						'config'  => array(
							'name'        => __( 'Color Scheme', 'thrive-cb' ),
							'label_col_x' => 5,
							'options'     => array(
								array(
									'value' => 'light',
									'name'  => 'Light',
								),
								array(
									'value' => 'dark',
									'name'  => 'Dark',
								),
							),
						),
						'extends' => 'Select',
					),
					'order_by'       => array(
						'config'  => array(
							'name'        => __( 'Order By', 'thrive-cb' ),
							'label_col_x' => 5,
							'options'     => array(
								array(
									'value' => 'social',
									'name'  => 'Social Popularity',
								),
								array(
									'value' => 'time',
									'name'  => 'Oldest First',
								),
								array(
									'value' => 'reverse_time',
									'name'  => 'Newest first',
								),
							),
						),
						'extends' => 'Select',
					),
				),
			),
			'typography'       => array( 'hidden' => true ),
			'animation'        => array( 'hidden' => true ),
			'background'       => array( 'hidden' => true ),
			'shadow'           => array( 'hidden' => true ),
			'layout'           => array( 'disabled_controls' => array( 'MaxWidth', 'Alignment' ) ),
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
