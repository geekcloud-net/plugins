<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

require_once plugin_dir_path( __FILE__ ) . 'class-tcb-element-abstract.php';
require_once plugin_dir_path( __FILE__ ) . 'class-tcb-cloud-template-element-abstract.php';

/**
 * Class TCB_Elements
 */
class TCB_Elements {

	/**
	 * Elements instances
	 *
	 * @var TCB_Element_Abstract[]
	 */
	private $_instances = array();

	public $pinned_category = 'pinned';

	/**
	 * @return array
	 */
	private function categories_order() {
		$order = array(
			0  => $this->pinned_category,
			10 => TCB_Element_Abstract::get_thrive_basic_label(),
			20 => TCB_Element_Abstract::get_thrive_advanced_label(),
			30 => TCB_Element_Abstract::get_thrive_integrations_label(),
		);

		$order = apply_filters( 'tcb_categories_order', $order );

		ksort( $order );

		return $order;
	}

	/**
	 * Include element class and create and instance for that element
	 */
	private function set() {

		$path  = plugin_dir_path( __FILE__ ) . 'elements/';
		$files = array_diff( scandir( $path ), array( '.', '..' ) );
		foreach ( $files as $file ) {
			$element = str_replace( array( 'class-tcb-', '-element.php' ), '', $file );
			$element = self::capitalize_class_name( $element );

			$class = 'TCB_' . $element . '_Element';

			require_once $path . $file;

			if ( class_exists( $class ) ) {
				$tag = strtolower( $element );
				/** @var TCB_Element_Abstract $instance */
				$instance = new $class( $tag );
				if ( $instance->is_available() ) {
					$this->_instances[ $tag ] = $instance;
				}
			}
		}

		$this->_instances = apply_filters( 'tcb_element_instances', $this->_instances );

		/*Order Specified By Shane*/
		$order_template   = array(
			'text',
			'heading',
			'image',
			'button',
			'columns',
			'section',
			'contentbox',
			'ct',
			'moretag',
			'tweet',
			'reveal',
			'countdown',
			'countdownevergreen',
			'credit',
			'html',
			'menu',
			'commentsdisqus',
			'divider',
			'commentsfacebook',
			'fillcounter',
			'gmap',
			'icon',
			'lead_generation',
			'postgrid',
			'progressbar',
			'social',
			'rating',
			'styledlist',
			'table',
			'toc',
			'tabs',
			'testimonial',
			'toggle',
			'responsivevideo',
			'wordpress',
		);
		$this->_instances = array_merge( array_flip( $order_template ), $this->_instances );

		/* Allow plugins to remove TCB Elements */
		$this->_instances = apply_filters( 'tcb_remove_instances', $this->_instances );
	}

	/**
	 * Get elements to be displayed on sidebar, grouped in categories
	 *
	 * @return array
	 */
	public function get_for_front() {
		$elements = $this->get();

		$all = array();

		$pinned_elements = get_user_option( 'tcb_pinned_elements' );
		if ( empty( $pinned_elements ) ) {
			$pinned_elements = array();
		}

		$order = $this->categories_order();

		foreach ( $order as $category ) {
			$all[ $category ] = array();
		}

		foreach ( $elements as $element ) {
			if ( ! $element->hide() ) {
				$element->pinned = in_array( $element->tag(), $pinned_elements );

				$category = $element->pinned ? $this->pinned_category : $element->category();

				$all[ $category ][] = $element;
			}
		}

		return $all;
	}

	/**
	 * Get all elements available in TCB. Uses tcb_elements filter to allow extending
	 *
	 * @param $element string|null $element element to get.
	 *
	 * @return TCB_Element_Abstract[]|null
	 */
	public function get( $element = null ) {

		if ( empty( $this->_instances ) ) {
			$this->set();
		}

		/**
		 * Action filter
		 *
		 * Allows extending existing elements, or adding new functionality
		 *
		 * @since 2.0
		 *
		 * @param array $elements
		 */
		$elements = apply_filters( 'tcb_elements', $this->_instances );
		if ( null === $element ) {
			return $elements;
		}

		if ( ! isset( $elements[ $element ] ) ) {
			return null;
		}

		/**
		 * Action filter
		 *
		 * Allows extending the configuration for a single element
		 *
		 * @since 2.0
		 *
		 * @param array $config element configuration
		 */
		return apply_filters( 'tcb_element_' . $element, $elements[ $element ] );
	}

	/**
	 * Outputs the html containing the menu components for all TCB elements
	 *
	 * @return void
	 */
	public function output_components() {
		$menu_folder = TVE_TCB_ROOT_PATH . 'inc/menu/';
		foreach ( $this->menu_components() as $component ) {
			/**
			 * Action filter
			 *
			 * Allows insertion of custom Menu components in TCB.
			 * TODO: figure out how to handle this in JS
			 *
			 * @since 2.0
			 *
			 * @param string $file default file path
			 */
			$file = apply_filters( 'tcb_menu_path_' . $component, $menu_folder . $component . '.php' );

			if ( ! is_file( $file ) ) {
				continue;
			}
			include $file;
		}

		do_action( 'tcb_output_components' );
	}

	/**
	 * Component options that apply to all elements
	 *
	 * @return array
	 */
	public function component_options() {
		return array(
			'animation' => tcb_event_manager_config(),
		);
	}

	/**
	 * Resolves all menu components included in the elements config
	 *
	 * @return array
	 */
	public function menu_components() {
		$components = array( 'general' );

		foreach ( $this->get() as $key => $element ) {
			$c = $element->components();

			if ( empty( $c ) || ! is_array( $c ) ) {
				continue;
			}

			$components = array_merge( $components, array_keys( $c ) );
		}

		return array_unique( $components );
	}

	/**
	 * Return element static layout
	 *
	 * @return string
	 */
	public function layout() {
		$layout = '';

		foreach ( $this->get() as $key => $element ) {
			$content = $element->layout();
			if ( ! empty( $content ) ) {
				$layout .= '<div data-elem="' . $key . '">' . $content . '</div>';
			}
		}

		return $layout;
	}

	/**
	 * Displays custom sidebars needed by elements.
	 * Example: Table element - it has a custom sidebar layout for when editing the cells
	 *
	 * @return array
	 */
	public function custom_sidebars() {
		$sidebars = array();
		foreach ( $this->get() as $element ) {
			if ( $element_sidebars = $element->get_custom_sidebars() ) {
				$sidebars = array_merge( $sidebars, $element_sidebars );
			}
		}

		return $sidebars;
	}

	/**
	 * Prepares the elements config for javascript.
	 *
	 * @return array
	 */
	public function js() {
		$js = array();

		foreach ( $this->get() as $key => $element ) {
			$js[ $key ] = $element->config();
		}

		return $js;
	}

	/**
	 * Create an array for localizing with all the elements to decide which should show the advanced options
	 * TODO This is not used anymore - according to the new logic of hiding / showing components in the sidebar
	 *
	 * @return array
	 */
	public function elements_to_display() {
		$elements = $this->get();
		$display  = array();

		foreach ( $elements as $k => $element ) {
			$display[ $k ] = array();
			foreach ( $element->components() as $c => $component ) {
				$options = array(
					'displayed' => isset( $component['hidden'] ) ? ! $component['hidden'] : true,
					'advanced'  => isset( $component['advanced'] ) ? $component['advanced'] : false,
					'docked'    => isset( $component['docked'] ) ? $component['docked'] : true,
				);

				$display[ $k ][ $c ] = $options;
			}
		}

		return $display;
	}

	/**
	 * Gets the templates category for category autocomplete widget
	 *
	 * @return array
	 */
	public function user_templates_category() {
		$templates_category = get_option( 'tve_user_templates_categories', array() );
		$return             = array();

		foreach ( $templates_category as $category ) {
			$obj        = new stdClass();
			$obj->id    = $category['id'];
			$obj->text  = $category['name'];
			$obj->value = $category['name'];
			$return[]   = $obj;
		}

		return $return;
	}

	/**
	 * transforms lead-generation into Lead_Generation
	 *
	 * @param string $element_file_name
	 */
	public static function capitalize_class_name( $element_file_name ) {

		$chunks = explode( '-', $element_file_name );
		$chunks = array_map( 'ucfirst', $chunks );

		return implode( '_', $chunks );
	}

	/**
	 * Instantiate an element class identified by $element_type or return it if it already exists in the instances array
	 *
	 * @param string $element_type
	 *
	 * @return null|TCB_Element_Abstract
	 */
	public function element_factory( $element_type ) {

		if ( isset( $this->_instances[ $element_type ] ) ) {
			return $this->_instances[ $element_type ];
		}

		$instance = null;

		/**
		 * Internal TCB elements
		 */
		$class_name = 'TCB_' . self::capitalize_class_name( $element_type ) . '_Element';
		if ( ! class_exists( $class_name ) ) {
			$file = plugin_dir_path( __FILE__ ) . 'elements/class-tcb-' . $element_type . '-element.php';
			if ( file_exists( $file ) ) {
				include $file;
			}
		}

		if ( class_exists( $class_name ) ) {
			$instance = new $class_name( $element_type );
		}

		if ( ! isset( $instance ) ) {
			/**
			 * Try out also possible external class instances
			 */
			$external_instances = apply_filters( 'tcb_element_instances', array() );
			if ( isset( $external_instances[ $element_type ] ) ) {
				$instance = $external_instances[ $element_type ];
			}
		}

		return $instance;
	}
}

global $tcb_elements;

/**
 * Singleton instantiator for TCB_Elements
 *
 * @return TCB_Elements
 */
function tcb_elements() {
	global $tcb_elements;
	if ( ! isset( $tcb_elements ) ) {
		$tcb_elements = new TCB_Elements();
	}

	return $tcb_elements;
}
