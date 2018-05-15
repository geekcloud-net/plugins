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
 * Class TCB_Element_Abstract
 */
abstract class TCB_Element_Abstract {

	/**
	 * Element alternate.
	 *
	 * @var string
	 */
	protected $_alternate = '';


	/**
	 * TCB_Element_Abstract constructor.
	 *
	 * @param string $alternate element alternate.
	 */
	public function __construct_alternate( $alternate = '' ) {
		if ( empty( $this->_alternate ) ) {
			$this->_alternate = $alternate;
		}
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return $this->_alternate;
	}


	/**
	 * Element tag.
	 *
	 * @var string
	 */
	protected $_tag = '';

	/**
	 * TCB_Element_Abstract constructor.
	 *
	 * @param string $tag element tag.
	 */
	public function __construct( $tag = '' ) {
		if ( empty( $this->_tag ) ) {
			$this->_tag = $tag;
		}
	}

	/**
	 * Get element tag
	 *
	 * @return string
	 */
	public function tag() {
		return $this->_tag;
	}

	/**
	 * Element identifier that will help us understand on what we click and open the right menu
	 *
	 * @return string
	 */
	public function identifier() {
		return '';
	}

	/**
	 * Configuration of the element with components and elements
	 *
	 * @return array
	 */
	public function components() {
		$own_components = $this->own_components();
		$components     = tve_array_replace_recursive( $this->general_components(), $own_components );
		foreach ( $own_components as $key => $component ) {

			if ( isset( $component['disabled_controls'] ) ) {
				$components[ $key ]['disabled_controls'] = $component['disabled_controls'];
			}
		}

		$components = $this->normalize_components( $components );

		return $components;
	}

	/**
	 * Components that apply only to the element
	 *
	 * @return array
	 */
	public function own_components() {
		return array();
	}

	/**
	 * General components that apply to all elements
	 *
	 * @return array
	 */
	protected function general_components() {
		/**
		 * Avoid creating extra javascript configuration data
		 */
		if ( $this->inherit_components_from() || $this->is_placeholder() ) {
			return array();
		}
		$texts = array(
			' p',
			' li',
			' blockquote',
			' address',
		);

		$headings = array(
			' h1',
			' h2',
			' h3',
			' h4',
			' h5',
			' h6',
		);

		$h1_spacing = $h2_spacing = $h3_spacing = $p_spacing = array(
			'css_suffix' => ' p',
			'important'  => true,
			'config'     => array(
				'default' => '',
				'min'     => '0',
				'max'     => '100',
				'label'   => __( 'Paragraph Spacing', 'thrive-cb' ),
				'um'      => array( 'px', 'em' ),
				'css'     => 'fontSize',
			),
			'extends'    => 'Slider',
		);

		$h1_spacing['css_suffix']      = ' h1';
		$h1_spacing['config']['label'] = __( 'H1 Spacing', 'thrive-cb' );
		$h2_spacing['css_suffix']      = ' h2';
		$h2_spacing['config']['label'] = __( 'H2 Spacing', 'thrive-cb' );
		$h3_spacing['css_suffix']      = ' h3';
		$h3_spacing['config']['label'] = __( 'H3 Spacing', 'thrive-cb' );

		return array(
			'typography'       => array(
				'disabled_controls' => array(
					'.tve-advanced-controls',
					'p_spacing',
					'h1_spacing',
					'h2_spacing',
					'h3_spacing',
				),
				'order'             => 90,
				'config'            => array(
					'ToggleControls' => array(
						'config'  => array(
							'buttons' => array(
								array( 'value' => 'tcb-typography-font-size', 'text' => __( 'Font Size', 'thrive-cb' ), 'default' => true ),
								array( 'value' => 'tcb-typography-line-height', 'text' => __( 'Line Height', 'thrive-cb' ) ),
								array( 'value' => 'tcb-typography-letter-spacing', 'text' => __( 'Letter Spacing', 'thrive-cb' ) ),
							),
						),
						'extends' => 'ButtonGroup',
					),

					'FontSize'      => array(
						'css_suffix' => $texts,
						'css_prefix' => '#tve_editor ',
						'config'     => array(
							'default' => '16',
							'min'     => '1',
							'max'     => '100',
							'label'   => '',
							'um'      => array( 'px', 'em' ),
							'css'     => 'fontSize',
						),
						'extends'    => 'Slider',
					),
					'LetterSpacing' => array(
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
					'FontColor'     => array(
						'css_suffix' => array_merge( $texts, $headings ),
						'old_suffix' => $texts, //TODO: Remove this after some time
						'css_prefix' => '#tve_editor ',
						'config'     => array(
							'default' => '000',
							'icon'    => true,
							'options' => array(
								'output' => 'object',
							),
						),
						'extends'    => 'ColorPicker',
					),
					'TextAlign'     => array(
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
					'TextStyle'     => array(
						'css_suffix' => array_merge( $texts, $headings ),
						'old_suffix' => $texts, //TODO: Remove this after some time
						'config'     => array(
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
					'TextTransform' => array(
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
					'FontFace'      => array(
						'css_suffix' => $texts,
						'css_prefix' => '#tve_editor ',
						'config'     => array(
							'template' => 'controls/font-manager',
							'tinymce'  => false,
						),
						'extends'    => 'FontManager',
					),
					'LineHeight'    => array(
						'css_suffix' => $texts,
						'css_prefix' => '#tve_editor ',
						'config'     => array(
							'default' => '16',
							'min'     => '1',
							'max'     => '100',
							'label'   => '',
							'um'      => array( 'px', 'em' ),
							'css'     => 'lineHeight',
						),
						'extends'    => 'Slider',
					),
					'p_spacing'     => $p_spacing,
					'h1_spacing'    => $h1_spacing,
					'h2_spacing'    => $h2_spacing,
					'h3_spacing'    => $h3_spacing,
				),
			),
			'layout'           => array(
				'order' => 100,
			),
			'background'       => array(
				'order'             => 110,
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
			'borders'          => array(
				'order' => 120,
			),
			'animation'        => array(
				'order' => 130,
			),
			'responsive'       => array(
				'order' => 140,
			),
			'styles-templates' => array(
				'order' => 150,
			),
			'shadow'           => array(
				'order'          => 140,
				'inline_text_to' => '',
			),

		);
	}

	/**
	 * Return element config containing: components, identifier, name, icon, hide
	 *
	 * @return array
	 */
	public function config() {

		$config = array(
			'components'     => $this->components(),
			'identifier'     => $this->identifier(),
			'name'           => $this->name(),
			'icon'           => $this->icon(),
			'hide'           => $this->hide(),
			'tag'            => $this->tag(),
			'is_placeholder' => $this->is_placeholder(),
			'hover'          => $this->has_hover_state(),
			'has_group'      => $this->has_group_editing(),
		);
		if ( ( $inherit_from = $this->inherit_components_from() ) ) {
			$config['inherit_from'] = $inherit_from;
		}

		$config = apply_filters( 'tcb_element_' . $this->tag() . '_config', $config );

		return $config;
	}

	/**
	 * Normalize components by making sure all the variables are present
	 *
	 * @param array $components element config.
	 *
	 * @return array
	 */
	private function normalize_components( $components = array() ) {

		$i = 1;

		foreach ( $components as $key => $c ) {

			/* update the order of the component in case it's not set */
			if ( ! isset( $c['order'] ) ) {
				$components[ $key ]['order'] = $i ++;
			}

			/* set the 'to' as empty if nothing is added => in this case the component will apply to the wrapper */
			if ( ! isset( $c['to'] ) ) {
				$components[ $key ]['to'] = '';
			}

			/* by default, if nothing is set, the nothing is hidden in the component */
			if ( ! isset( $c['hide'] ) ) {
				$components[ $key ]['hide'] = array();
			}

			/* if nothing is added, by default the config is empty */
			if ( ! isset( $c['config'] ) ) {
				$components[ $key ]['config'] = array();
			}
		}

		return $components;
	}

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return '';
	}

	/**
	 * The toString override
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->name();
	}

	/**
	 * The icon of the element
	 *
	 * @return string
	 */
	public function icon() {
		return '';
	}

	/**
	 * Either to display or not the element in the sidebar menu
	 *
	 * @return bool
	 */
	public function hide() {
		return false;
	}

	/**
	 * HTML layout of the element for when it's dragged in the canvas
	 *
	 * @return string
	 */
	protected function html() {
		return tcb_template( 'elements/' . $this->tag() . '.php', $this, true );
	}

	/**
	 * Return the element html layout after applying the filter
	 *
	 * @param string $html Element layout.
	 *
	 * @return mixed
	 */
	public function layout( $html = '' ) {

		if ( empty( $html ) ) {
			$html = $this->html();
		}

		$html = apply_filters( 'tcb_' . $this->name() . '_element_layout', $html );

		return $html;
	}

	/**
	 * Get all custom sidebar states
	 * This can be overridden if an element needs multiple sidebar states
	 *
	 * @return array
	 */
	public function get_custom_sidebars() {
		$sidebar = $this->get_sidebar_extra_state();
		if ( $sidebar ) {
			return array( $this->_tag => $sidebar );
		}

		return array();
	}

	/**
	 * Get an extra state for the sidebar. Can be used to switch the sidebar to this state
	 *
	 * @return string|null
	 */
	public function get_sidebar_extra_state() {

	}

	/**
	 * Whether or not this element is only a placeholder ( it has no menu, it's not selectable etc )
	 * e.g. Content Templates
	 *
	 * @return bool
	 */
	public function is_placeholder() {
		return false;
	}

	/**
	 * Whether or not this element is available and should be included in the current page
	 *
	 * @return bool
	 */
	public function is_available() {
		return true;
	}

	/**
	 * Whether or not the this element can be edited while under :hover state
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return false;
	}

	/**
	 * Whether or not this element has cloud templates
	 *
	 * @return bool
	 */
	public function has_cloud_templates() {
		return false;
	}

	/**
	 * Allows different element names to use the same exact components as a base building block
	 * Example: a Testimonial element uses the same exact components as the Columns element ( because it is a column container element ) and
	 * has extra testimonial options
	 *
	 * @return null|string
	 */
	public function inherit_components_from() {
		return null;
	}

	/**
	 * Unified place for the "Thrive Integrations" category. Implemented here so that we can have a single translation for this
	 *
	 * @return string
	 */
	public static function get_thrive_integrations_label() {
		return __( 'Thrive Integrations', 'thrive-cb' );
	}

	/**
	 * Unified place for the "Thrive Foundation (Basic)" category. Implemented here so that we can have a single translation for this
	 *
	 * @return string
	 */
	public static function get_thrive_basic_label() {
		return __( 'Foundation', 'thrive-cb' );
	}

	/**
	 * Unified place for the "Thrive Building Blocks (Advanced)" category. Implemented here so that we can have a single translation for this
	 *
	 * @return string
	 */
	public static function get_thrive_advanced_label() {
		return __( 'Building Blocks', 'thrive-cb' );
	}

	/**
	 * Returns the HTML placeholder for an element (contains a wrapper, and a button with icon + element name)
	 *
	 * @param string $title Optional. Defaults to the name of the current element
	 *
	 * @return string
	 */
	public function html_placeholder( $title = null ) {
		if ( empty( $title ) ) {
			$title = $this->name();
		}

		return tcb_template( 'elements/element-placeholder', array(
			'icon'  => $this->icon(),
			'class' => str_replace( array( ',.', ',', '.' ), array( ' ', '', '' ), $this->identifier() ),
			'title' => $title,
		), true );
	}

	/**
	 * Element category that will be displayed in the sidebar.
	 * If the element is hidden it's ok not to have a category defined.
	 *
	 * @throws Exception
	 */
	public function category() {
		if ( ! $this->hide() ) {
			throw new Exception( 'Please define category for element:' . $this->name() );
		}
	}

	public function has_group_editing() {
		return false;
	}

	/**
	 *
	 *
	 * @return array
	 */
	public function group_component() {
		return array(
			'group' => array(
				'hidden' => true,
				'config' => array(
					'ButtonToggle' => array(
						'config' => array(
							'label'   => '',
							'class'   => 'tcb-group-toggle-btn',
							'tooltip' => array(
								'active'   => __( 'Group styling disabled. The styling will be applied only for the selected element.', 'thrive-cb' ),
								'inactive' => __( 'Group styling active. The same styling will be applied to similar elements.', 'thrive-cb' ),
							),
						),
					),
					'preview'      => array(
						'config'  => array(
							'name'        => '',
							'label_col_x' => 0,
							'options'     => array(),
						),
						'extends' => 'Select',
					),

				),
			),
		);
	}
}
