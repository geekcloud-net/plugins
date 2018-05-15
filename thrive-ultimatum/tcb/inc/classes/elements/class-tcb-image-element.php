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
 * Class TCB_Image_Element
 */
class TCB_Image_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Image', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'media';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'image';
	}

	/**
	 * Text element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve_image_caption';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'image'         => array(
				'config' => array(
					'ImagePicker'   => array(
						'config' => array(
							'label' => __( 'Change Image', 'thrive-cb' ),
						),
						//						'to'     => 'img',
					),
					'ImageSize'     => array(
						'config'  => array(
							'default' => 'auto',
							'min'     => '20',
							'max'     => '1024',
							'label'   => __( 'Image Size', 'thrive-cb' ),
							'um'      => array( 'px', '%' ),
							'css'     => 'width',
						),
						'extends' => 'ImageSize',
					),
					'StylePicker'   => array(
						'config' => array(
							'label'   => __( 'Choose image style', 'thrive-cb' ),
							'items'   => array(
								'no_style'                  => __( 'No Style', 'thrive-cb' ),
								'img_style_dark_frame'      => __( 'Dark Frame', 'thrive-cb' ),
								'img_style_framed'          => __( 'Framed', 'thrive-cb' ),
								'img_style_lifted_style1'   => __( 'Lifted Style 1', 'thrive-cb' ),
								'img_style_lifted_style2'   => __( 'Lifted Style 2', 'thrive-cb' ),
								'img_style_polaroid'        => __( 'Polaroid', 'thrive-cb' ),
								'img_style_rounded_corners' => __( 'Rounded Corners', 'thrive-cb' ),
								'img_style_circle'          => __( 'Circle', 'thrive-cb' ),
								'img_style_caption_overlay' => __( 'Caption Overlay', 'thrive-cb' ),
							),
							'default' => 'no_style',
						),
					),
					'ImageTitle'    => array(
						'config'  => array(
							'label'       => __( 'Title', 'thrive-cb' ),
							'extra_attrs' => '',
							'label_col_x' => 3,
						),
						'extends' => 'LabelInput',
					),
					'ImageAltText'  => array(
						'config'  => array(
							'label'       => __( 'Alt Text', 'thrive-cb' ),
							'extra_attrs' => '',
							'label_col_x' => 3,
						),
						'extends' => 'LabelInput',
					),
					'ImageCaption'  => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Add caption text', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Checkbox',
					),
					'ImageFullSize' => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Open full size image on click', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Checkbox',
					),
					'ImageLink'     => array(
						'config'  => array(
							'label'       => __( 'Image Link', 'thrive-cb' ),
							'extra_attrs' => '',
							'label_col_x' => 12,
							'placeholder' => __( 'Insert linking URL', 'thrive-cb' ),
						),
						'extends' => 'LabelInput',
					),
					'LinkNewTab'    => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Open in new tab', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Checkbox',
					),
					'LinkNoFollow'  => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'No Follow', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Checkbox',
					),
				),
			),
			'background'    => array( 'hidden' => true ),
			'image-effects' => array(
				'config' => array(
					'ImageGreyscale'     => array(
						'config'  => array(
							'default' => '0',
							'min'     => '0',
							'max'     => '100',
							'label'   => __( 'Grayscale', 'thrive-cb' ),
							'um'      => array( '%' ),
							'css'     => 'filter',
						),
						'extends' => 'Slider',
					),
					'ImageOpacity'       => array(
						'config'  => array(
							'default' => '100',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'Opacity', 'thrive-cb' ),
							'um'      => array( '%' ),
							'css'     => 'opacity',
						),
						'extends' => 'Slider',
					),
					'ImageBlur'          => array(
						'config'  => array(
							'default' => '0',
							'min'     => '0',
							'max'     => '100',
							'label'   => __( 'Blur', 'thrive-cb' ),
							'um'      => array( 'px' ),
							'css'     => 'filter',
						),
						'extends' => 'Slider',
					),
					'SectionFullWidth'   => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Stretch to fit screen width', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'ImageOverlaySwitch' => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Image Overlay', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'ImageOverlay'       => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Overlay Color', 'thrive-cb' ),
						),
						'extends' => 'ColorPicker',
					),
				),
			),
			'typography'    => array(
				'hidden' => true,
			),
			'animation'     => array(
				'config' => array(
					'to' => 'img',
				),
			),
			'layout'        => array(
				'disabled_controls' => array(
					'MaxWidth'
				),
			),
			'shadow'        => array(
				'config' => array(
					'disabled_controls' => array( 'inner', 'text' ),
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
	 * @return string
	 */
	public function category() {
		return $this->get_thrive_basic_label();
	}
}
