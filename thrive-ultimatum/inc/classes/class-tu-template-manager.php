<?php

class TU_Template_Manager extends TU_Request_Handler {

	const OPTION_TPL_META = 'tve_ult_saved_tpl_meta';
	const OPTION_TPL_CONTENT = 'tve_ult_saved_tpl';

	/**
	 * map of design types over templates
	 *
	 * @var array
	 */
	protected static $map = array(
		TVE_Ult_Const::DESIGN_TYPE_HEADER_BAR => 'ribbon',
		TVE_Ult_Const::DESIGN_TYPE_FOOTER_BAR => 'ribbon',
	);

	/**
	 * @var $instance TU_Template_Manager
	 */
	protected static $instance = null;

	/**
	 * Row from DB
	 *
	 * @var $design array
	 */
	protected $design;

	/**
	 * Templates config for design templates
	 *
	 * @var array
	 */
	protected $config = array();

	/**
	 * TU_Template_Manager constructor.
	 *
	 * @param $design
	 */
	protected function __construct( $design ) {
		$this->design = $design;
		$this->config();
	}

	protected function config() {
		$this->config = include TVE_Ult_Const::plugin_path() . 'tcb-bridge/editor-templates/_config.php';
	}

	/**
	 * header bars and footer bars should have the same templates
	 *
	 * @param string $tpl
	 *
	 * @return string
	 */
	public static function type( $tpl ) {
		if ( empty( $tpl ) ) {
			return '';
		}
		$parts = explode( '|', $tpl );
		$type  = $parts[0];

		return isset( self::$map[ $type ] ) ? self::$map[ $type ] : $type;
	}

	/**
	 * get the design key (actual design name) from a template formatted like 'ribbon|one_set'
	 *
	 * @param string $tpl
	 */
	public static function key( $tpl ) {
		if ( empty( $tpl ) ) {
			return '';
		}

		list( $type, $key ) = explode( '|', $tpl );

		return $key;
	}

	/**
	 * get the type and tpl name from a design template name
	 *
	 * @param string $tpl
	 *
	 * @return array
	 */
	public static function tpl_type_key( $tpl ) {
		if ( empty( $tpl ) ) {
			return array( '', '' );
		}

		list( $type, $key ) = explode( '|', $tpl );

		return array(
			isset( self::$map[ $type ] ) ? self::$map[ $type ] : $type,
			$key,
		);
	}

	/**
	 * Returns the instance of the design
	 *
	 * @param null $design
	 *
	 * @return TU_Template_Manager
	 */
	public static function getInstance( $design = null ) {

		if ( ! is_array( $design ) || empty( $design ) ) {
			$design = array();
		}

		if ( ! self::$instance ) {
			self::$instance = new self( $design );
		}

		if ( isset( $design['id'] ) && self::$instance->design['id'] != $design['id'] ) {
			self::$instance = new self( $design );
		}

		return self::$instance;
	}

	/**
	 * Returns the design templates set in _config.php for a specific design type
	 *
	 * @param $design_type
	 *
	 * @return array
	 */
	public static function get_templates( $design_type = '' ) {
		$design_type = self::type( $design_type );

		$templates = self::getInstance()->config[ $design_type ];

		foreach ( $templates as $tpl => $tpl_data ) {
			$templates[ $tpl ]['key']       = $design_type . '|' . $tpl;
			$templates[ $tpl ]['thumbnail'] = TVE_Ult_Const::plugin_url( 'tcb-bridge/editor-templates/' . $design_type . '/thumbnails/' . $tpl . '.png' );
		}

		return $templates;
	}

	/**
	 * API entry point for templates
	 *
	 * @param $action
	 *
	 * @return false|string json
	 */
	public function api( $action ) {
		$method = 'api_' . strtolower( $action );

		if ( ! method_exists( $this, $method ) ) {
			return false;
		}

		$result = call_user_func( array( $this, $method ) );

		exit( json_encode( $result ) );
	}

	/**
	 * exchange data from $template to this->variation or vice-versa
	 *
	 * @param array  $template
	 * @param string $dir can either be left-right or right-left
	 *
	 * @return array
	 */
	protected function interchange_data( $template, $dir = 'left -> right' ) {
		$fields = array(
			TVE_Ult_Const::FIELD_CONTENT,
			TVE_Ult_Const::FIELD_INLINE_CSS,
			TVE_Ult_Const::FIELD_USER_CSS,
			TVE_Ult_Const::FIELD_GLOBALS,
			TVE_Ult_Const::FIELD_CUSTOM_FONTS,
			TVE_Ult_Const::FIELD_ICON_PACK,
			TVE_Ult_Const::FIELD_MASONRY,
			TVE_Ult_Const::FIELD_TYPEFOCUS,
		);

		foreach ( $fields as $field ) {
			if ( strpos( $dir, 'left' ) === 0 ) {
				$this->design[ $field ] = $template[ $field ];
			} else {
				$template[ $field ] = $this->design[ $field ];
			}
		}

		return $template;
	}


	/**
	 * --------------------------------------------------------------------
	 * -------------------- API-calls after this point -------------------- :)
	 * --------------------------------------------------------------------
	 */

	/**
	 * Choose a new template
	 */
	public function api_choose() {
		if ( ! ( $template = $this->param( 'tpl' ) ) ) {
			return false;
		}

		if ( strpos( $template, 'user-saved-template-' ) === 0 ) {
			/* at this point, the template is one of the previously saved templates (saved by the user) -
				it holds the index from the option array which needs to be loaded */
			$contents = get_option( self::OPTION_TPL_CONTENT );
			$meta     = get_option( self::OPTION_TPL_META );

			$template_index = intval( str_replace( 'user-saved-template-', '', $template ) );

			/* make sure we don't mess anything up */
			if ( empty( $contents ) || empty( $meta ) || ! isset( $contents[ $template_index ] ) ) {
				return '';
			}
			$tpl_data = $contents[ $template_index ];
			$template = $meta[ $template_index ]['tpl'];

			$this->interchange_data( $tpl_data, 'left -> right' );

			$this->design[ TVE_Ult_Const::FIELD_TEMPLATE ] = $template;
		} else {
			$this->design[ TVE_Ult_Const::FIELD_TEMPLATE ] = $template;
			$this->design[ TVE_Ult_Const::FIELD_CONTENT ]  = tve_ult_editor_get_template_content( $this->design, $template );

			tve_ult_save_design( $this->design );

		}

		$parent_design = empty( $this->design['parent_id'] ) ? $this->design : tve_ult_get_design( $this->design['parent_id'] );

		return TU_State_Manager::getInstance( $parent_design )->state_data( $this->design );
	}

	/**
	 * reset contents for the current template
	 */
	public function api_reset() {

		$this->design[ TVE_Ult_Const::FIELD_CONTENT ] = tve_ult_editor_get_template_content( $this->design );

		tve_ult_save_design( $this->design );

		return TU_State_Manager::getInstance( $this->design )->state_data( $this->design );
	}

	/**
	 * Save the current variation config and content as a template so that it can later be applied to other variation
	 */
	public function api_save() {
		/**
		 * we keep the template content separately from the template meta data (name and date)
		 */
		if ( empty( $this->design[ TVE_Ult_Const::FIELD_GLOBALS ] ) ) {
			$this->design[ TVE_Ult_Const::FIELD_GLOBALS ] = array( 'e' => 1 );
		}
		$template_content = $this->interchange_data( array(), 'right -> left' );

		list( $type, $key ) = self::tpl_type_key( $this->design[ TVE_Ult_Const::FIELD_TEMPLATE ] );
		$template_meta     = array(
			'name' => $this->param( 'name', '' ),
			'tpl'  => $this->design[ TVE_Ult_Const::FIELD_TEMPLATE ],
			'type' => $type,
			'key'  => $key,
			'date' => date( 'Y-m-d' ),
		);
		$templates_content = get_option( self::OPTION_TPL_CONTENT, array() );
		$templates_meta    = get_option( self::OPTION_TPL_META, array() );

		$templates_content [] = $template_content;
		$templates_meta []    = $template_meta;

		// make sure these are not autoloaded, as it is a potentially huge array
		add_option( self::OPTION_TPL_CONTENT, null, '', 'no' );

		update_option( self::OPTION_TPL_CONTENT, $templates_content );
		update_option( self::OPTION_TPL_META, $templates_meta );

		return array(
			'message' => __( 'Template saved.', TVE_Ult_Const::T ),
			'list'    => $this->api_get_saved( true ),
		);
	}

	/**
	 * get user-saved templates
	 *
	 * @param bool $return whether or not to return the $html or output it and exit
	 */
	public function api_get_saved( $return = false ) {
		$only_current_template = (int) $this->param( 'current_template' );
		$html                  = '';

		/**
		 * prepare for multiple templates applying to the same design type
		 */
		$types = array( self::type( $this->design[ TVE_Ult_Const::FIELD_TEMPLATE ] ) );

		$templates = get_option( self::OPTION_TPL_META );
		$templates = empty( $templates ) ? array() : array_reverse( $templates, true ); // order by date DESC

		$img = TVE_Ult_Const::plugin_url() . 'tcb-bridge/editor-templates/%s/thumbnails/%s';

		foreach ( $templates as $index => $template ) {
			/* make sure we only load the same type, e.g. ribbon */
			if ( ! in_array( $template['type'], $types ) ) {
				continue;
			}

			if ( ! empty( $only_current_template ) && $this->design[ TVE_Ult_Const::FIELD_TEMPLATE ] != $template[ TVE_Ult_Const::FIELD_TEMPLATE ] ) {
				continue;
			}

			$item = '';
			$item .= '<div class="tve-template-item">';
			$item .= '<div class="template-wrapper click" data-fn="select_template" data-key="user-saved-template-' . $index . '">';
			$item .= '<div class="template-thumbnail" style="background-image: url(' . $img . ')">';
			$item .= '<div class="template-thumbnail-overlay">';
			$item .= '<div class="tcb-right margin-right-5 tcb-delete-saved-template click" data-fn="delete_confirmation">';
			$item .= tcb_icon( 'trash', true );
			$item .= '</div>';
			$item .= '</div>';
			$item .= '</div>';
			$item .= '<div class="template-name">' . $template['name'] . '<br>(' . strftime( '%d.%m.%y', strtotime( $template['date'] ) ) . ')</div>';
			$item .= '<div class="selected"></div>';
			$item .= '</div>';
			$item .= '</div>';

			$item = sprintf( $item, $template['type'], $template['key'] . '.png' );
			$html .= $item;
		}
		$html = $html ? $html : __( 'No saved templates found', TVE_Ult_Const::T );
		if ( $return ) {
			return $html;
		}
		echo $html;
		exit();
	}

	public function api_delete() {
		$tpl_index = (int) str_replace( 'user-saved-template-', '', $this->param( 'tpl' ) );

		$contents = get_option( self::OPTION_TPL_CONTENT );
		$meta     = get_option( self::OPTION_TPL_META );

		if ( ! isset( $contents[ $tpl_index ] ) || ! isset( $meta[ $tpl_index ] ) ) {
			return $this->api_get_saved();
		}

		array_splice( $contents, $tpl_index, 1 );
		array_splice( $meta, $tpl_index, 1 );

		update_option( self::OPTION_TPL_CONTENT, array_values( $contents ) );
		update_option( self::OPTION_TPL_META, array_values( $meta ) );

		return $this->api_get_saved();
	}
}
