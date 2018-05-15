<?php
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class WC_Pos_ACF_Fields{

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
        add_filter('woocommerce_checkout_fields', array($this, 'acf_checkout_fields') );
        add_action('pos_admin_enqueue_scripts',   array($this, 'acf_admin_enqueue_scripts'));
        add_action('pos_admin_print_scripts',   array($this, 'acf_admin_print_scripts'));
	}

	public function acf_checkout_fields($checkout_fields)
	{
		
		if( is_pos() && is_plugin_active( 'advanced-custom-fields/acf.php' ) ) {
			add_filter('acf/location/rule_match/ef_crm_customers', '__return_true');

			$acfs = apply_filters('acf/get_field_groups', false);
			if( $acfs )
			{
				$checkout_fields['pos_acf'] = array();
				$filter = array(
					'ef_user' => true,
					'ef_crm_customers' => true,
					'post_type' => 'shop_order'
				);
				$field_groups = apply_filters( 'acf/location/match_field_groups', array(), $filter );
				
				foreach( $acfs as $acf )
				{
					if( in_array($acf['id'], $field_groups)){
						$fields    = apply_filters('acf/field_group/get_fields', array(), $acf['id']);
						$wc_fields = array();
						$i = 0;
						foreach ($fields as $field) {
							$i++;
							$defaults = array(
								'type'              => isset($field['type']) ? $field['type'] : 'text',
								'description'       => $field['instructions'],
								'class'             => isset($field['class']) ? array($field['class']) : array(),
								'label_class'       => array(),
								'input_class'       => array(),
								'return'            => false,
								'options'           => isset($field['choices']) ? $field['choices'] : array(),
								'custom_attributes' => array(),
								'default'           => isset($field['default_value']) ? $field['default_value'] : '',
							);

							switch ($defaults['type']) {
								case 'wysiwyg' :
	                                $defaults['type'] = 'textarea';
	                                break;
                                case 'true_false' :
	                                $defaults['type']    = 'checkbox';
	                                $defaults['options'] = array(1 => $field['message']);
	                                break;
                                case 'color_picker' :
                                    $defaults['class'][] = 'acf-color_picker';
	                                $defaults['type'] = 'text';
	                                break;
	                            case 'page_link':
	                            case 'post_object':
	                            case 'user':
	                            	$defaults['class'][] = 'wc-enhanced-select';
	                            	break;
                            	case 'taxonomy':
                            		$defaults['type'] = $field['field_type'];
                            		if( $defaults['type'] == 'multi_select'){
                            			$defaults['type']  = 'select';
                            			$field['multiple'] = 1;
                            		}
	                                $defaults['options'] = array();
	                                $terms = get_terms( array(
	                                    'taxonomy' => $field['taxonomy'],
	                                    'hide_empty' => false,
	                                ) );
	                                if( $terms ){
	                                    foreach ($terms as $term) {
	                                        $defaults['options'][$term->term_id] = $term->name;
	                                    }
	                                }
	                            	break;
							}
							$custom_attributes = array('rows', 'multiple');
							$intersect = array_intersect($custom_attributes, array_keys($field) );
							if( !empty($intersect) ){
								foreach ($intersect as $attr_key) {
									switch ($attr_key) {
										case 'multiple':
											if( $field[$attr_key] > 0){
												$defaults['custom_attributes']['multiple'] = 'multiple';
											}
											break;
										default:
											$defaults['custom_attributes'][$attr_key] = $field[$attr_key];
											break;
									}
								}
							}
							if( $defaults['type'] == 'select' ){
								$data_attributes = array('allow_null', 'multiple');
								$intersect = array_intersect($data_attributes, array_keys($field) );
								if( !empty($intersect) ){
									foreach ($intersect as $data_key) {
										switch ($data_key) {
											case 'multiple':
												if( $field[$data_key] > 0){
													$defaults['custom_attributes']['data-multiple'] = true;
												}
												break;
											case 'allow_null':
												$defaults['custom_attributes']['data-allow_clear'] = $field[$data_key] > 0 ? true : false;
												break;
											default:
												$defaults['custom_attributes']['data-' . $data_key] = $field[$data_key];
												break;
										}
									}
								}
								$defaults['input_class'] = array('wc-enhanced-select');
							}
							$wc_fields['acf-field-'.$field['name']] = array_merge($field, $defaults);
						}

						$checkout_fields['pos_acf'][] = array(
							'title'  => $acf['title'],
							'fields' => $wc_fields,
						);
					}

				}
			}
			remove_filter('acf/location/rule_match/ef_crm_customers', '__return_true');
		}
		return $checkout_fields;
	}

	public function acf_admin_print_scripts()
	{
		global $post;
		if( !$post ){
			$post = (object)array();
		}
		$post->ID = 'user_';			

		do_action('acf/input/admin_head');

	}
	public function acf_admin_enqueue_scripts()
	{
		global $typenow, $post;
		if( !$post ){
			$post = (object)array();
		}
		$post->ID = 'user_';
		wp_enqueue_style( 'wp-color-picker' );
	    wp_enqueue_script(
	        'iris',
	        admin_url( 'js/iris.min.js' ),
	        array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ),
	        false,
	        1
	    );
	    wp_enqueue_script(
	        'wp-color-picker',
	        admin_url( 'js/color-picker.min.js' ),
	        array( 'iris' ),
	        false,
	        1
	    );
	    $colorpicker_l10n = array(
	        'clear' => __( 'Clear' ),
	        'defaultString' => __( 'Default' ),
	        'pick' => __( 'Select Color' ),
	        'current' => __( 'Current Color' ),
	    );
	    wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );

		do_action('acf/input/admin_enqueue_scripts');

	}



    /**
	 * Main WC_Pos_Registers Instance
	 *
	 * Ensures only one instance of WC_Pos_Registers is loaded or can be loaded.
	 *
	 * @since 1.9
	 * @static
	 * @return WC_Pos_Registers Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.9
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '1.9' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.9
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '1.9' );
	}

}

return new WC_Pos_ACF_Fields();