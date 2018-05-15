<?php

if ( ! class_exists( 'TCB_Landing_Page_Cloud_Templates_Api' ) ) {
	require_once TVE_TCB_ROOT_PATH . 'landing-page/inc/TCB_Landing_Page_Transfer.php';
}

class TCB_Content_Templates_Api extends TCB_Landing_Page_Cloud_Templates_Api {

	/**
	 * Needed to support extending the parent singleton
	 *
	 * @return TCB_Content_Templates_Api
	 */
	public static function getInstance() {
		return new TCB_Content_Templates_Api();
	}

	/**
	 *
	 * Fetches all Content Templates of a type from landingpages.thrivethemes.com
	 *
	 * @param string $type
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	public function get_all( $type = null ) {
		$params = array(
			'route' => 'getAll',
			'type'  => $type,
			'ct'    => md5( time() ),
		);

		$response = $this->_request( $params );
		$data     = json_decode( $response, true );

		if ( empty( $data ) ) {
			throw new Exception( 'Got response: ' . $response );
		}

		if ( empty( $data['success'] ) ) {
			throw new Exception( $data['error_message'] );
		}

		if ( ! isset( $data['data'] ) ) {
			throw new Exception( 'Could not fetch templates.' );
		}

		$this->_validateReceivedHeader( $data );

		$templates = $data['data'];

		return $templates;
	}

	/**
	 * Just forward the call to get_all()
	 *
	 * Should not be used
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public function getTemplateList() {
		$args = func_get_args();
		if ( ! count( $args ) ) {
			$args = array( 'testimonial' );
		}

		return $this->get_all( array_shift( $args ) );
	}

	/**
	 * Get a post associated with a cloud content template
	 *
	 * @param string $id
	 *
	 * @return WP_Post|null
	 */
	public function get_post_for_content_template( $id ) {

		$maybe = get_posts( array(
			'post_type'      => TCB_CT_POST_TYPE,
			'meta_key'       => 'tcb_ct_id',
			'meta_value'     => $id,
			'posts_per_page' => 1,
		) );

		return $maybe ? $maybe[0] : null;
	}

	/**
	 * get content template data
	 *
	 * @param $id
	 *
	 * @return null|array
	 */
	public function get_content_template( $id ) {
		$post = $this->get_post_for_content_template( $id );

		if ( ! $post ) {
			return null;
		}

		$meta = get_post_meta( $post->ID, 'tcb_ct_meta', true );
		$data = array(
			'id'         => $id,
			'type'       => $meta['type'],
			'content'    => $post->post_content,
			'head_css'   => $meta['head_css'],
			'custom_css' => $meta['custom_css'],
			'v'          => (int) ( isset( $meta['v'] ) ? $meta['v'] : 0 ),
		);

		return $data;
	}

	/**
	 * Get data for a content template, or download it if it's not available locally
	 *
	 * @param string|int $id
	 *
	 * @throws Exception
	 *
	 * @return array|WP_Error|null
	 */
	public function download( $id ) {
		/**
		 * first make sure we can save the downloaded template
		 */
		$upload = wp_upload_dir();
		if ( ! empty( $upload['error'] ) ) {
			throw new Exception( $upload['error'] );
		}

		$base = trailingslashit( $upload['basedir'] ) . TVE_CLOUD_TEMPLATES_FOLDER . '/';
		if ( false === wp_mkdir_p( $base . 'images' ) ) {
			throw new Exception( 'Could not create the templates folder' );
		}

		$params = array(
			'route' => 'download',
			'ct'    => md5( time() ),
			'id'    => $id,
		);

		$body = $this->_request( $params );

		$control = array(
			'auth' => $this->request['headers']['X-Thrive-Authenticate'],
			'id'   => $id,
		);

		/**
		 * this means an error -> error message is json_encoded
		 */
		if ( empty( $this->received_auth_header ) || strpos( $body, '{"success' ) === 0 ) {
			$data = json_decode( $body, true );
			throw new Exception( isset( $data['error_message'] ) ? $data['error_message'] : ( 'Invalid response: ' . $body ) );
		}

		$this->_validateReceivedHeader( $control );

		/**
		 * at this point, $body holds the contents of the zip file
		 */
		$zip_path = trailingslashit( $upload['basedir'] ) . TVE_CLOUD_TEMPLATES_FOLDER . '/ct-' . $id . '.zip';

		tve_wp_upload_bits( $zip_path, $body );

		$template_data = $this->process_zip( $zip_path );

		$post = $this->get_post_for_content_template( $id );
		$data = array(
			'post_title'   => $template_data['name'],
			'post_content' => $template_data['content'],
			'post_type'    => TCB_CT_POST_TYPE,
			'post_status'  => 'publish',
		);
		if ( ! $post ) {
			$post_id = wp_insert_post( $data );
		} else {
			$data['ID'] = $post->ID;
			wp_update_post( $data );
			$post_id = $post->ID;
		}

		update_post_meta( $post_id, 'tcb_ct_id', $id );
		update_post_meta( $post_id, 'tcb_ct_meta', array(
			'v'          => isset( $template_data['v'] ) ? $template_data['v'] : '0',
			'type'       => $template_data['type'],
			'head_css'   => $template_data['head_css'],
			'custom_css' => $template_data['custom_css'],
		) );
	}

	/**
	 * Extract the content template data from the archive located at $path
	 *
	 * @throws Exception
	 *
	 * @param string $path
	 */
	public function process_zip( $zip_file_path ) {
		$old_umask = umask( 0 );

		defined( 'FS_METHOD' ) || define( 'FS_METHOD', 'direct' );
		WP_Filesystem();

		$upload = wp_upload_dir();

		$folder = trailingslashit( $upload['basedir'] ) . TVE_CLOUD_TEMPLATES_FOLDER . '/';

		/* this means the template archive is coming directly from the Thrive Template Cloud, we can trust it */
		$result = unzip_file( $zip_file_path, $folder );

		if ( $result instanceof WP_Error ) {
			umask( $old_umask );
			throw new Exception( __( 'Could not extract the archive file', 'thrive-cb' ) );
		}

		if ( ! file_exists( $folder . 'data.json' ) ) {
			throw new Exception( __( 'Invalid archive contents', 'thrive-cb' ) );
		}

		@unlink( $zip_file_path );

		$config = json_decode( file_get_contents( $folder . 'data.json' ), true );

		$uri = trailingslashit( str_replace( array( 'http://', 'https://' ), '//', $upload['baseurl'] ) ) . TVE_CLOUD_TEMPLATES_FOLDER . '/' . $config['type'] . '/images/';
		$this->replace_images( $config['head_css'], $config['image_map'], $uri );
		$this->replace_images( $config['content'], $config['image_map'], $uri );

		@unlink( $folder . 'data.json' );

		return $config;
	}

	/**
	 * Modified string, replaces md5 image codes with image URLs
	 *
	 * @param string $string
	 * @param array  $image_map
	 * @param string $uri
	 */
	protected function replace_images( & $string, $image_map, $uri ) {
		foreach ( $image_map as $key => $name ) {
			$string = str_replace( "{{img={$key}}}", $uri . $name, $string );
		}
	}
}

/**
 * @var TCB_Content_Templates_Api
 */
global $tcb_content_templates_api;

function tcb_content_templates_api() {
	global $tcb_content_templates_api;

	if ( ! isset( $tcb_content_templates_api ) ) {
		$tcb_content_templates_api = TCB_Content_Templates_Api::getInstance();
	}

	return $tcb_content_templates_api;
}
