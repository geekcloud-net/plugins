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
 * Class TCB_Ct_Element
 *
 * Content templates - allows inserting saved content templates into the page
 */
class TCB_Ct_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Content Template', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'template,content';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'content_temp';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-ct';
	}

	/**
	 * This is only a placeholder element
	 *
	 * @return bool
	 */
	public function is_placeholder() {
		return true;
	}

	/**
	 * Get all information about all saved templates
	 *
	 * @return mixed|array
	 */
	public function get() {
		return get_option( 'tve_user_templates', array() );
	}

	/**
	 * Gets the list of saved templates ( just names and indexes, no content )
	 * Used in searching for content templates - autocomplete-ready list
	 *
	 * @return array
	 */
	public function get_list( $templates = null ) {
		if ( $templates === null ) {
			$templates = $this->get();
		}
		$list = array();
		if ( empty( $templates ) ) {
			$templates = array();
		}
		foreach ( $templates as $key => $tpl ) {
			$temp_array = array(
				'id'    => $key,
				'label' => rawurldecode( $tpl['name'] ),
				'type'  => ! empty( $tpl['type'] ) ? $tpl['type'] : '',
			);

			if ( in_array( $temp_array['type'], array( 'button' ) ) ) {
				$temp_array = array_merge( $temp_array, array(
					'media'   => $tpl['media_css'],
					'content' => stripslashes( $tpl['content'] ),
				) );
			}

			$list[] = $temp_array;
		}

		return $list;
	}

	/**
	 * Loads data for a template
	 *
	 * @param int $key
	 *
	 * @return array
	 */
	public function load( $key ) {
		$templates = $this->get();

		$media_css = isset( $templates[ $key ]['media_css'] ) ? array_map( 'stripslashes', $templates[ $key ]['media_css'] ) : null;
		if ( $media_css ) {
			/* make sure the server did not mess up the inline rules - e.g. instances where it replaces double quotes with single quotes */
			foreach ( $media_css as $k => $value ) {
				$media_css[ $k ] = preg_replace( "#data-css='(.+?)'#s", 'data-css="$1"', $value );
			}
		}

		$response = array(
			'html_code' => stripslashes( $templates[ $key ]['content'] ),
			'css_code'  => stripslashes( $templates[ $key ]['css'] ),
			'media_css' => $media_css,
		);
		if ( ob_get_contents() ) {
			ob_clean();
		}

		return $response;
	}

	/**
	 * Deletes a saved content template
	 *
	 * @param int $key
	 *
	 * @return array with template information
	 */
	public function delete( $key ) {
		$templates = $this->get();
		array_splice( $templates, $key, 1 );

		update_option( 'tve_user_templates', $templates );

		return $this->get_list( $templates );
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return $this->get_thrive_basic_label();
	}
}
