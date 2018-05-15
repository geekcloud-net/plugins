<?php
/**
 * Custom Controls
 *
 * @package Page Builder Framework Premium Addon
 * @subpackage Customizer
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// wysiwyg control
add_action( 'customize_register', function( $wp_customize ) {

	class Kirki_WPBF_WYSIWYG_Control extends Kirki_Control_Base {
		public $type = 'wysiwyg';
		public function render_content() { ?>
	        <label>
	          <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
	          <?php
				$settings = array(
					'media_buttons' => false,
					'editor_height' => '150',
					'teeny' => true,
				);
				$this->filter_editor_setting_link();
				wp_editor( $this->value(), $this->id, $settings );

	          ?>
	        </label>
	    <?php
	        do_action( 'admin_footer' );
	        do_action( 'admin_print_footer_scripts' );
	    }

	    private function filter_editor_setting_link() {
	        add_filter( 'the_editor', function( $output ) { return preg_replace( '/<textarea/', '<textarea ' . $this->get_link(), $output, 1 ); } );
	    }

	}

	// Register our custom control with Kirki
	add_filter( 'kirki/control_types', function( $controls ) {
		$controls['wysiwyg'] = 'Kirki_WPBF_WYSIWYG_Control';
		return $controls;
	} );

} );