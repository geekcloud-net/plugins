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
abstract class TCB_Cloud_Template_Element_Abstract extends TCB_Element_Abstract {

	/**
	 * General components that apply to all elements
	 * Elements having cloud templates take the components from their base element ( e.g. Columns, Content Box etc )
	 *
	 * @return array
	 */
	protected function general_components() {
		return array();
	}

	/**
	 * Whether or not this element has cloud templates
	 *
	 * @return bool
	 */
	public function has_cloud_templates() {
		return true;
	}

	/**
	 * All these elements act as placeholders
	 *
	 * @return true
	 */
	public function is_placeholder() {
		return true;
	}

	/**
	 * These elements do not have their own identifiers - they are built from base elements and inherit options from base elements
	 *
	 * @return string
	 */
	public function identifier() {
		return '';
	}

	/**
	 * HTML layout of the element for when it's dragged in the canvas
	 *
	 * @return string
	 */
	protected function html() {
		return $this->html_placeholder( sprintf( __( 'Insert %s', 'thrive-cb' ), $this->name() ) );
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
			'icon'       => $this->icon(),
			'class'      => 'tcb-ct-placeholder',
			'title'      => $title,
			'extra_attr' => 'data-ct="' . $this->tag() . '-0" data-element-name="' . esc_attr( $this->name() ) . '"',
		), true );
	}

	/**
	 * Fetches a list of cloud templates for an element
	 *
	 * @param array $args allows controlling aspects of the method:
	 *                    $nocache - do not use caching (transients)
	 *
	 * @return array|WP_Error
	 */
	public function get_cloud_templates( $args = array() ) {

		if ( ! $this->has_cloud_templates() ) {
			return new WP_Error( 'invalid_element', __( 'Element does not have cloud templates', 'thrive-cb' ) );
		}

		$args = wp_parse_args( $args, array(
			'nocache' => false,
		) );

		$do_not_use_cache = ( defined( 'TCB_TEMPLATE_DEBUG' ) && TCB_TEMPLATE_DEBUG ) || $args['nocache'];

		$transient = 'tcb_cloud_templates_' . $this->tag();

		if ( $do_not_use_cache || ! ( $templates = get_transient( $transient ) ) ) {

			require_once plugin_dir_path( __FILE__ ) . 'content-templates/class-tcb-content-templates-api.php';

			try {
				$templates = tcb_content_templates_api()->get_all( $this->tag() );
				set_transient( $transient, $templates, 8 * HOUR_IN_SECONDS );
			} catch ( Exception $e ) {
				return new WP_Error( 'tcb_api_error', $e->getMessage() );
			}
		}

		return $templates;
	}

	/**
	 * Get information about a cloud template:
	 * html content
	 * css
	 * custom css
	 * etc
	 *
	 * If the template does not exist, download it from the cloud
	 *
	 * @param string $id   Template id
	 * @param array  $args allow modifying the behavior
	 *
	 * @return array|WP_Error
	 */
	public function get_cloud_template_data( $id, $args = array() ) {
		if ( ! $this->has_cloud_templates() ) {
			return new WP_Error( 'invalid_element', __( 'Element does not have cloud templates', 'thrive-cb' ) );
		}

		$args = wp_parse_args( $args, array(
			'nocache' => false,
		) );

		$force_fetch = ( defined( 'TCB_TEMPLATE_DEBUG' ) && TCB_TEMPLATE_DEBUG ) || $args['nocache'];

		require_once plugin_dir_path( __FILE__ ) . 'content-templates/class-tcb-content-templates-api.php';
		$api = tcb_content_templates_api();

		/**
		 * check for newer versions - only download the template if there is a new version available
		 */
		$current_version = false;
		if ( ! $force_fetch ) {
			$all = $this->get_cloud_templates();
			if ( is_wp_error( $all ) ) {
				return $all;
			}

			foreach ( $all as $tpl ) {
				if ( $tpl['id'] == $id ) {
					$current_version = (int) ( isset( $tpl['v'] ) ? $tpl['v'] : 0 );
				}
			}
		}

		try {

			/**
			 * Download template if:
			 * $force_fetch OR
			 * template not downloaded OR
			 * template is downloaded but the version on the cloud has changed
			 */
			if ( $force_fetch || ! ( $data = $api->get_content_template( $id ) ) || ( $current_version !== false && $current_version > $data['v'] ) ) {
				$api->download( $id );
				$data = $api->get_content_template( $id );
			}
		} catch ( Exception $e ) {
			$data = new WP_Error( 'tcb_download_err', $e->getMessage() );
		}

		return $data;
	}
}
