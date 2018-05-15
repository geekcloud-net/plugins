<?php

/**
 * Handles opening of video popups
 *
 * Class TCB_Thrive_Video_Popup
 */
class TCB_Thrive_Video_Popup extends TCB_Event_Action_Abstract {

	/**
	 * Action key.
	 *
	 * @var string
	 */
	protected $key = 'thrive_video';

	/**
	 * Render settings.
	 *
	 * @deprecated
	 *
	 * @param mixed $data template data.
	 */
	public function renderSettings( $data ) {

	}

	/**
	 * Should return the user-friendly name for this Action
	 *
	 * @return string
	 */
	public function getName() {
		return __( 'Open Video', 'thrive-cb' );
	}

	/**
	 * Should return an actual string containing the JS function that's handling this action.
	 * The function will be called with 3 parameters:
	 *      -> event_trigger (e.g. click, dblclick etc)
	 *      -> action_code (the action that's being executed)
	 *      -> config (specific configuration for each specific action - the same configuration that has been setup in the settings section)
	 *
	 * Example (php): return 'function (trigger, action, config) { console.log(trigger, action, config); }';
	 *
	 * The function will be called in the context of the element
	 *
	 * The output MUST be a valid JS function definition.
	 *
	 * @return string the JS function definition (declaration + body)
	 */
	public function getJsActionCallback() {
		return tcb_template( 'actions/video-popup.js', null, true );
	}

	/**
	 * Checks for cases of Wistia popups and ensures backward compatibility
	 *
	 * @param array $data
	 */
	protected function _check_wistia_bc( & $data ) {
		if ( $data['a'] === 'thrive_wistia' && ! isset( $data['config']['s'] ) ) {
			/**
			 * Backwards compatibility support for Wistia popups
			 */
			$data['config'] = $this->provider_factory( 'wistia', $data['config'] )->migrate_config();
		}
	}

	/**
	 *
	 * @param mixed $data action configuration data.
	 */
	public function applyContentFilter( $data ) {

		$this->_check_wistia_bc( $data );

		if ( empty( $data['config'] ) || empty( $data['config']['s'] ) || ! isset( $data['config']['p'] ) ) {
			return;
		}

		$config   = $data['config'];
		$provider = $this->provider_factory( $config['s'], $config['p'] );
		if ( empty( $provider ) ) {
			return;
		}

		return $provider->get_html();
	}

	public function mainPostCallback( $data ) {
		$this->_check_wistia_bc( $data );

		$provider = $this->provider_factory( $data['config']['s'], $data['config']['p'] );
		if ( ! $provider ) {
			return;
		}

		return $provider->main_post_callback();
	}

	/**
	 * @return string
	 */
	public function get_editor_js_view() {
		return 'VideoPopup';
	}

	public function get_options() {
		return array( 'labels' => __( 'Open Video', 'thrive-cb' ) );
	}

	public function render_editor_settings() {
		echo '<div class="video-settings"></div>';
	}

	/**
	 * @param $provider
	 * @param $config
	 *
	 * @return null|TCB_Video_Base
	 */
	public function provider_factory( $provider, $config ) {
		switch ( $provider ) {
			case 'youtube':
				return new TCB_Video_Youtube( $config );
			case 'vimeo':
				return new TCB_Video_Vimeo( $config );
			case 'wistia':
				return new TCB_Video_Wistia( $config );
			case 'custom':
				return new TCB_Video_Custom( $config );
			default:
				return null;
		}
	}
}

class TCB_Video_Base {
	protected $params;

	public function __construct( $params = array() ) {
		$this->params = $params;
	}

	public function get_embed() {
		return sprintf(
			'<iframe data-src="%s" frameborder="0" allowtransparency="true" style="display: block"></iframe>',
			$this->get_url()
		);
	}

	public function main_post_callback() {
		return '';
	}

	public function get_id() {
		return isset( $this->params['id'] ) ? $this->params['id'] : '';
	}

	public function get_url() {
		return '';
	}

	protected function _get( $key, $default = null ) {
		return array_key_exists( $key, $this->params ) ? $this->params[ $key ] : $default;
	}

	public function get_html() {

		return sprintf(
			'<div class="tcb-video-popup" style="visibility: hidden; position:fixed; left: -5000px;max-width: 100%%;width: 70%%" id="tcb-video-popup-%s">%s</div>',
			$this->get_id(),
			'<div class="tve_responsive_video_container">' . $this->get_embed() . '</div>'
		);
	}
}

class TCB_Video_Youtube extends TCB_Video_Base {

	public function get_url() {
		$url = 'https://www.youtube.com/embed/' . $this->get_id();
		$url = add_query_arg( array(
			'rel'            => (int) ( ! $this->_get( 'hrv', false ) ),
			'modestbranding' => (int) $this->_get( 'hyl' ),
			'controls'       => (int) ( ! $this->_get( 'ahi', false ) ),
			'showinfo'       => (int) ( ! $this->_get( 'htb', false ) ),
			'autoplay'       => (int) $this->_get( 'a' ),
			'fs'             => (int) ( ! $this->_get( 'hfs', false ) ),
			'wmode'          => 'transparent',
		), $url );

		return $url;
	}
}

class TCB_Video_Vimeo extends TCB_Video_Base {
	public function get_url() {
		$url = 'https://player.vimeo.com/video/' . $this->get_id();
		$url = add_query_arg( array(
			'color'    => $this->_get( 'c', 'ffffff' ),
			'portrait' => (int) $this->_get( 'p', 0 ),
			'title'    => (int) $this->_get( 't', 0 ),
			'byline'   => (int) $this->_get( 'b', 0 ),
			'badge'    => 0,
			'autoplay' => (int) $this->_get( 'a', 0 ),
			'loop'     => (int) $this->_get( 'l', 0 ),
		), $url );

		return $url;
	}
}

class TCB_Video_Wistia extends TCB_Video_Base {
	public function get_params() {

		return sprintf(
			'videoFoam=true autoPlay=%s playbar=%s fullscreenButton=%s playerColor=%s',
			$this->_get( 'a' ) ? 'true' : 'false',
			$this->_get( 'p' ) ? 'true' : 'false',
			$this->_get( 'hfs' ) ? 'false' : 'true',
			$this->_get( 'c', '00aeef' )
		);
	}

	/**
	 * migrate old-style wistia popups to TCB2.0
	 */
	public function migrate_config() {
		$old          = $this->params;
		$this->params = array(
			's' => 'wistia',
			'p' => array(
				'a'   => true,
				'url' => ! empty( $old['event_video_url'] ) ? $old['event_video_url'] : '',
				'c'   => ! empty( $old['event_video_color'] ) ? str_replace( '#', '', $old['event_video_color'] ) : '',
				'p'   => ! empty( $old['event_option_play_bar'] ),
				'hfs' => ! empty( $old['event_option_fs'] ),
			),
		);
		$id_pattern   = '#https?:\/\/(.+)?(wistia\.com|wi\.st)\/(medias|embed)\/(.+)#';
		if ( preg_match( $id_pattern, $this->params['p']['url'], $m ) ) {
			$this->params['p']['id'] = $m[4];
		}

		return $this->params;
	}

	public function main_post_callback() {
		wp_script_is( 'tl-wistia-popover' ) || wp_enqueue_script( 'tl-wistia-popover', '//fast.wistia.com/assets/external/E-v1.js', array(), '', true );
		ob_start(); ?>
		<script type="text/javascript">window._wq = window._wq || [];
			window.tcb_w_videos = window.tcb_w_videos || {};
			_wq.push( {
				id: <?php echo json_encode( $this->_get( 'id' ) ) ?>, onReady: function ( video ) {
					tcb_w_videos[<?php echo json_encode( $this->_get( 'id' ) ) ?>] = video;
				}
			} );
		</script><?php

		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public function get_html() {

		return sprintf(
			'<div class="wistia_embed wistia_async_%s popover=true %s">Wistia popup</div>',
			$this->get_id(),
			$this->get_params()
		);
	}
}

class TCB_Video_Custom extends TCB_Video_Base {

	/**
	 * Attributes for the current video
	 *
	 * @var array
	 */
	protected $video_attr = array();

	public function wp_video_shortcode( $output, $atts ) {
		if ( empty( $this->video_attr['width'] ) || empty( $this->video_attr['height'] ) || empty( $atts['width'] ) || empty( $atts['height'] ) ) {
			return $output;
		}

		$replace = array();
		if ( $atts['width'] != $this->video_attr['width'] ) {
			$replace[ 'width:' . $atts['width'] ]    = 'width:' . $this->video_attr['width'];
			$replace[ 'width: ' . $atts['width'] ]   = 'width: ' . $this->video_attr['width'];
			$replace[ 'width="' . $atts['width'] ]   = 'width="' . $this->video_attr['width'];
			$replace[ 'height:' . $atts['height'] ]  = 'height:' . $this->video_attr['height'];
			$replace[ 'height: ' . $atts['height'] ] = 'height: ' . $this->video_attr['height'];
			$replace[ 'height="' . $atts['height'] ] = 'height="' . $this->video_attr['height'];

			$output = str_replace( array_keys( $replace ), array_values( $replace ), $output );
		}

		remove_filter( 'wp_video_shortcode', array( $this, 'wp_video_shortcode' ) );

		return $output;
	}

	public function get_html() {
		$html = '';

		if ( ! $this->get_id() || ! function_exists( 'wp_video_shortcode' ) ) {
			return $html;
		}

		$attr = array();
		if ( empty( $this->params['url'] ) ) { //This is a WordPress saved video

			$metadata = wp_get_attachment_metadata( $this->get_id() );
			if ( ! empty( $metadata['width'] ) ) {
				$attr['width'] = $metadata['width'];
			}
			if ( ! empty( $metadata['height'] ) ) {
				$attr['height'] = $metadata['height'];
			}
			$attr['src'] = wp_get_attachment_url( $this->get_id() );

		} elseif ( ! empty( $this->params['url'] ) && filter_var( $this->params['url'], FILTER_VALIDATE_URL ) !== false ) { //This is a custom URL video. Not saved in WordPress
			//TODO: Mabey allow control width and height from Architect UI
			$attr['src']    = $this->params['url'];
		}

		$attr['class']    = 'tcb-video-shortcode';
		$attr['autoplay'] = false;
		$this->video_attr = $attr;
		add_filter( 'wp_video_shortcode', array( $this, 'wp_video_shortcode' ), 10, 2 );

		return sprintf(
			'<div class="tcb-video-popup tcb-custom-video" style="position:fixed;visibility:hidden;left: -5000px;max-width: 100%%" id="tcb-video-popup-%s">%s</div>',
			$this->get_id(),
			wp_video_shortcode( $attr )
		);
	}

	public function main_post_callback() {
		tve_dash_ajax_enqueue_style( 'mediaelement' );
		tve_dash_ajax_enqueue_style( 'mediaelement' );
		wp_style_is( 'wp-mediaelement' ) || wp_enqueue_style( 'mediaelement' );
		wp_script_is( 'wp-mediaelement' ) || wp_enqueue_script( 'mediaelement' );
	}
}
