<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 3/6/2017
 * Time: 1:58 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Admin_Ajax {
	const ACTION = 'tcb_admin_ajax_controller';
	const NONCE = 'tcb_admin_ajax_request';

	const USER_TEMPLATES = 'tve_user_templates';
	const USER_TEMPLATES_CATEGORIES = 'tve_user_templates_categories';
	const UPLOAD_DIR_CUSTOM_FOLDER = 'thrive-visual-editor';

	/**
	 * Init the object, during the AJAX request. Adds ajax handlers and verifies nonces
	 */
	public function init() {
		add_action( 'wp_ajax_' . self::ACTION, array( $this, 'handle' ) );
	}

	/**
	 * Sets the request's header with server protocol and status
	 * Sets the request's body with specified $message
	 *
	 * @param string $message the error message.
	 * @param string $status  the error status.
	 */
	protected function error( $message, $status = '404 Not Found' ) {
		header( $_SERVER['SERVER_PROTOCOL'] . ' ' . $status );
		echo esc_attr( $message );
		wp_die();
	}

	/**
	 * Returns the params from $_POST or $_REQUEST
	 *
	 * @param int  $key     the parameter kew.
	 * @param null $default the default value.
	 *
	 * @return mixed|null|$default
	 */
	protected function param( $key, $default = null ) {
		return isset( $_POST[ $key ] ) ? $_POST[ $key ] : ( isset( $_REQUEST[ $key ] ) ? $_REQUEST[ $key ] : $default );
	}

	/**
	 * Entry-point for each ajax request
	 * This should dispatch the request to the appropriate method based on the "route" parameter
	 *
	 * @return array|object
	 */
	public function handle() {
		if ( ! check_ajax_referer( self::NONCE, '_nonce', false ) ) {
			$this->error( sprintf( __( 'Invalid request.', 'thrive-cb' ) ) );
		}

		$route = $this->param( 'route' );

		$route       = preg_replace( '#([^a-zA-Z0-9-_])#', '', $route );
		$method_name = $route . '_action';

		if ( ! method_exists( $this, $method_name ) ) {
			$this->error( sprintf( __( 'Method %s not implemented', 'thrive-cb' ), $method_name ) );
		}

		wp_send_json( $this->{$method_name}() );
	}

	/**
	 * Template action callback
	 * Templates collection
	 */
	/**
	 * @return array
	 */
	public function templates_action() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];

		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
			case 'DELETE':
				$this->error( __( 'Invalid call', 'thrive-cb' ) );
				break;
			case 'GET':
				$templates = get_option( self::USER_TEMPLATES, array() );
				if ( empty( $templates ) ) {
					$templates = array();
				}
				$templates = array_reverse( $templates );

				$tpl_categs = get_option( self::USER_TEMPLATES_CATEGORIES, array() );
				if ( empty( $tpl_categs ) ) {
					$tpl_categs = array();
				}

				$categ_tpls = tcb_admin_get_category_templates( $templates );
				$return     = array();

				foreach ( $tpl_categs as $categ ) {
					$return[] = array(
						'id'   => $categ['id'],
						'name' => $categ['name'],
						'tpl'  => ! empty( $categ_tpls[ $categ['id'] ] ) ? $categ_tpls[ $categ['id'] ] : array(),
					);
				}
				$return[] = array(
					'id'   => 'uncategorized',
					'name' => __( 'Uncategorized templates', 'thrive-cb' ),
					'tpl'  => ! empty( $categ_tpls['uncategorized'] ) ? $categ_tpls['uncategorized'] : array(),
				);

				$return[] = array(
					'id'   => '[#page#]',
					'name' => __( 'Page Templates', 'thrive-cb' ),
					'tpl'  => ! empty( $categ_tpls['[#page#]'] ) ? $categ_tpls['[#page#]'] : array(),
				);

				return $return;
				break;
		}
	}

	public function templatemodel_action() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
				//Modify the category name

				$tpl_categories = get_option( self::USER_TEMPLATES_CATEGORIES );
				if ( empty( $tpl_categories ) || ! is_array( $tpl_categories ) ) {
					$this->error( __( 'The template category list is empty!', 'thrive-cb' ) );
					break;
				}

				if ( ! is_numeric( $model['id'] ) || empty( $model['name'] ) || empty( $tpl_categories[ $model['id'] ] ) ) {
					$this->error( __( 'Invalid parameters', 'thrive-cb' ) );
					break;
				}

				$tpl_categories[ $model['id'] ]['name'] = $model['name'];
				update_option( 'tve_user_templates_categories', $tpl_categories );

				return array( 'text' => __( 'The category name was modified!', 'thrive-cb' ) );
				break;
			case 'DELETE':
				$id             = $this->param( 'id', '' );
				$tpl_categories = get_option( self::USER_TEMPLATES_CATEGORIES );
				$templates      = get_option( self::USER_TEMPLATES );

				if ( ! is_numeric( $id ) ) {
					$this->error( __( 'Undefined parameter: id', 'thrive-cb' ) );
					break;
				}

				if ( empty( $tpl_categories ) || ! is_array( $tpl_categories ) || empty( $tpl_categories[ $id ] ) ) {
					$this->error( __( 'Invalid category template', 'thrive-cb' ) );
				}

				$upload_dir = wp_upload_dir();
				$base       = $upload_dir['basedir'] . '/' . self::UPLOAD_DIR_CUSTOM_FOLDER . '/user_templates';

				unset( $tpl_categories[ $id ] );
				update_option( 'tve_user_templates_categories', $tpl_categories );

				// Move existing templates belonging to the deleted category to uncategorized
				$categ_tpls = tcb_admin_get_category_templates( $templates );
				if ( ! empty( $categ_tpls[ $id ] ) ) {

					if ( ! empty( $_POST['extra_setting_check'] ) ) {
						foreach ( $templates as $key => $value ) {
							if ( isset( $value['id_category'] ) && is_numeric( $value['id_category'] ) && $value['id_category'] == $id ) {
								unset( $templates[ $key ] );

								// Delete Cover Image
								$file_name = $base . '/' . $value['name'] . '.png';
								@unlink( $file_name );
							}
						}
					} else {
						foreach ( $templates as $key => $value ) {
							if ( isset( $value['id_category'] ) && is_numeric( $value['id_category'] ) && $value['id_category'] == $id ) {
								unset( $templates[ $key ]['id_category'] );
							}
						}
					}

					update_option( 'tve_user_templates', $templates );
				}

				return array( 'text' => __( 'The category was deleted!', 'thrive-cb' ) );
				break;
			case 'GET':
				$this->error( __( 'Invalid call', 'thrive-cb' ) );
				break;
		}
	}

	/**
	 * Template Category action callback
	 *
	 * @return array
	 */
	public function templatecategory_action() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];

		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
				if ( empty( $model['category'] ) ) {
					$this->error( __( 'Category parameter could not be found!', 'thrive-cb' ) );
					break;
				}

				$template_categories = get_option( self::USER_TEMPLATES_CATEGORIES );
				if ( ! is_array( $template_categories ) ) {
					$template_categories = array();
				}

				$last_category = end( $template_categories );
				if ( ! empty( $last_category ) ) {
					$index = $last_category['id'] + 1;
				} else {
					$index = 0;
				}
				foreach ( $model['category'] as $category ) {
					if ( ! empty( $category ) ) {
						$template_categories[] = array(
							'id'   => $index,
							'name' => $category,
						);
						$index ++;
					}
				}

				update_option( 'tve_user_templates_categories', $template_categories );

				return array( 'text' => __( 'The category(s) was saved!', 'thrive-cb' ) );
				break;
			case 'DELETE':
			case 'GET':
				$this->error( __( 'Invalid call', 'thrive-cb' ) );
				break;

		}
	}

	/**
	 * User template action callbacks
	 *
	 * @return array
	 */
	public function usertpl_action() {
		$model  = json_decode( file_get_contents( 'php://input' ), true );
		$method = empty( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? 'GET' : $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];

		switch ( $method ) {
			case 'POST':
			case 'PUT':
			case 'PATCH':
				// Modify the template name or template category
				$templates = array_reverse( get_option( self::USER_TEMPLATES ) );

				if ( empty( $templates ) || ! is_array( $templates ) ) {
					$this->error( __( 'The template list is empty', 'thrive-cb' ) );
					break;
				}

				if ( ! is_numeric( $model['id'] ) || empty( $model['name'] ) || empty( $templates[ $model['id'] ] ) ) {
					$this->error( __( 'Invalid parameters', 'thrive-cb' ) );
					break;
				}

				$templates[ $model['id'] ]['name'] = $model['name'];
				if ( isset( $model['id_category'] ) && is_numeric( $model['id_category'] ) ) {
					$templates[ $model['id'] ]['id_category'] = $model['id_category'];
				} else {
					unset( $templates[ $model['id'] ]['id_category'] );
				}

				update_option( 'tve_user_templates', $templates );

				return array( 'text' => __( 'The template saved!', 'thrive-cb' ) );
				break;
			case 'DELETE':
				$id        = $this->param( 'id', '' );
				$templates = array_reverse( get_option( self::USER_TEMPLATES, array() ) );

				if ( ! is_numeric( $id ) ) {
					$this->error( __( 'Undefined parameter: id', 'thrive-cb' ) );
					break;
				}

				if ( empty( $templates[ $id ] ) ) {
					$this->error( __( 'Invalid template', 'thrive-cb' ) );
				}

				// Delete Cover Image
				$upload_dir = wp_upload_dir();
				$base       = $upload_dir['basedir'] . '/' . self::UPLOAD_DIR_CUSTOM_FOLDER . '/user_templates';
				$file_name  = $base . '/' . $templates[ $id ]['name'] . '.png';
				@unlink( $file_name );

				// Delete Template
				unset( $templates[ $id ] );
				update_option( 'tve_user_templates', array_values( array_reverse( $templates ) ) );


				return array( 'text' => __( 'The template was deleted!', 'thrive-cb' ) );
				break;
			case 'GET':
				$id        = $this->param( 'id', '' );
				$templates = array_reverse( get_option( self::USER_TEMPLATES ) );

				if ( ! is_numeric( $id ) ) {
					$this->error( __( 'Undefined parameter: id', 'thrive-cb' ) );
					break;
				}

				if ( empty( $templates[ $id ] ) ) {
					$this->error( __( 'Invalid template', 'thrive-cb' ) );
				}

				if ( empty( $templates[ $id ]['image_url'] ) ) {
					$templates[ $id ]['image_url'] = tcb_admin()->admin_url( 'assets/images/no-template-preview.jpg' );
				}

				return array_merge( array( 'id' => $id ), $templates[ $id ] );
				break;
		}

	}

	/**
	 * upgrade the post_meta key for a post marking it as "migrated" to TCB2.0
	 * Takes care of 2 things:
	 * appends wordpress content at the end of tcb content, saves that into the TCB content
	 * and
	 * updates the post_content field to a text and images version of all the content
	 */
	public function migrate_post_content_action() {
		$post_id = $this->param( 'post_id' );
		$post    = tcb_post( $post_id );
		$post->migrate();

		return array( 'success' => true );
	}

	/**
	 * Enables the TCB-only editor for a post
	 */
	public function enable_tcb_action() {
		tcb_post( $this->param( 'post_id' ) )->enable_editor();

		return array( 'success' => true );
	}
}

$tcb_admin_ajax = new TCB_Admin_Ajax();
$tcb_admin_ajax->init();

