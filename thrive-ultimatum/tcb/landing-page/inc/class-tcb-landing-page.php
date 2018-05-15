<?php

/**
 * Created by PhpStorm.
 * User: radu
 * Date: 19.09.2014
 * Time: 10:15
 */

if ( ! class_exists( 'TCB_Landing_Page' ) ) {

	class TCB_Landing_Page extends TCB_Post {
		const HOOK_HEAD = 'tcb_landing_head';
		const HOOK_BODY_OPEN = 'tcb_landing_body_open';
		const HOOK_FOOTER = 'tcb_landing_footer';
		const HOOK_BODY_CLOSE = 'tcb_landing_body_close';

		/**
		 * landing page id
		 *
		 * @var int
		 */
		protected $id;

		/**
		 * holds the configuration array for the landing page
		 *
		 * @var array
		 */
		protected $config = array();

		/**
		 * holds the tve_globals meta configuration values
		 *
		 * @var array
		 */
		protected $globals = array();

		/**
		 * currently used landing page template
		 *
		 * @var string
		 */
		protected $template = '';

		/**
		 * javascripts for the head and footer section, if any
		 *
		 * @var array
		 */
		protected $global_scripts = array();

		/**
		 * stores the configuration for a template downloaded from the cloud, if this landing page is using one
		 *
		 * @var array
		 */
		protected $cloud_template_data = array();

		/**
		 * flag that holds whether or not this is a template downloaded from the cloud
		 *
		 * @var bool
		 */
		public $is_cloud_template = false;

		/**
		 * holds the events setup from page event manager
		 *
		 * @var array
		 */
		protected $page_events = array();

		/**
		 * sent all necessary parameters to avoid extra calls to get_post_meta
		 *
		 * @param int    $landing_page_id
		 * @param string $landing_page_template
		 */
		public function __construct( $landing_page_id, $landing_page_template ) {
			parent::__construct( $landing_page_id );
			if ( is_null( $landing_page_template ) ) {
				$landing_page_template = $this->is_landing_page();
			}

			$this->id             = $this->post->ID;
			$this->globals        = $this->meta( 'tve_globals', null, true, array() );
			$this->template       = $landing_page_template;
			$this->global_scripts = $this->meta( 'tve_global_scripts' );
			$this->page_events    = $this->meta( 'tve_page_events', null, true, array() );

			$this->config = tve_get_landing_page_config( $landing_page_template );

			if ( $landing_page_template && tve_is_cloud_template( $landing_page_template ) ) {
				$this->is_cloud_template   = true;
				$this->cloud_template_data = tve_get_cloud_template_config( $landing_page_template );
			}

			$this->globals     = empty( $this->globals ) ? array() : $this->globals;
			$this->page_events = empty( $this->page_events ) ? array() : $this->page_events;
		}

		/**
		 * outputs the HEAD section specific to the landing page
		 * finally, it calls the tcb_landing_head hook to allow injecting other stuff in the head
		 */
		public function head() {
			/* I think the favicon should be added using the wp_head hook and not like this */
			if ( function_exists( 'thrive_get_options_for_post' ) ) {
				$options = thrive_get_options_for_post();
				if ( ! empty( $options['favicon'] ) ) : ?>
					<link rel="shortcut icon" href="<?php echo $options['favicon']; ?>"/>
				<?php endif;
			}

			$this->fonts();

			if ( ! empty( $this->global_scripts['head'] ) && ! is_editor_page() ) {
				$this->global_scripts['head'] = $this->remove_jquery( $this->global_scripts['head'] );
				echo $this->global_scripts['head'];
			}

			empty( $this->globals['do_not_strip_css'] ) ?
				$this->strip_head_css() : wp_head();

			/* finally, call the tcb_landing_head hook */
			do_action( self::HOOK_HEAD, $this->id );

			if ( $this->is_v2() ) {
				/** On thrive themes, there is a nasty overflow on html */
				/** echo '<style>html,body{overflow-x:initial}</style>'; */
			}
		}

		/**
		 * outputs <link>s for each font used by the page
		 * fonts come from the configuration array
		 *
		 * @return TCB_Landing_Page allows chained calls
		 */
		protected function fonts() {
			if ( empty( $this->config['fonts'] ) ) {
				return $this;
			}
			foreach ( $this->config['fonts'] as $font ) {
				echo sprintf( '<link href="%s" rel="stylesheet" type="text/css" />', $font );
			}

			return $this;
		}

		/**
		 * this calls the WP wp_head() function, it will remove every <style>..</style> from the head
		 */
		protected function strip_head_css() {
			/* capture the output and strip out some of the <style></style> nodes */
			ob_start();
			wp_head();
			$contents = ob_get_clean();
			/* keywords to search for within the CSS rules */
			$tcb_rules_keywords = array(
				'.ttfm',
				'data-tve-custom-colour',
				'.tve_more_tag',
				'.thrive-adminbar-icon',
				'#wpadminbar',
				'html { margin-top: 32px !important; }',
			);
			/* keywords to search for within CSS style node - classes and ids for the <style> element */
			$tcb_style_classes = array( 'tve_user_custom_style', 'tve_custom_style' );

			if ( preg_match_all( '#<style(.*?)>(.+?)</style>#ms', $contents, $m ) ) {
				foreach ( $m[2] as $index => $css_rules ) {
					$css_node  = $m[1][ $index ];
					$remove_it = true;
					foreach ( $tcb_rules_keywords as $tcb_keyword ) {
						if ( strpos( $css_rules, $tcb_keyword ) !== false ) {
							$remove_it = false;
							break;
						}
					}
					if ( $remove_it ) {
						foreach ( $tcb_style_classes as $style_class ) {
							if ( strpos( $css_node, $style_class ) !== false ) {
								$remove_it = false;
								break;
							}
						}
					}
					if ( $remove_it ) {
						$contents = str_replace( $m[0][ $index ], '', $contents );
					}
				}
			}
			echo $contents;
		}

		/**
		 * get all the css data needed for this landing page that's been previously saved from the editor
		 * example: body background, content background (if content is outside tve_editor) etc
		 *
		 * @return array
		 */
		public function get_css_data_tcb2() {
			$config = $this->globals;

			return array(
				'custom_color' => ! empty( $config['body_css'] ) ? ' data-css="' . $config['body_css'] . '"' : '',
				'class'        => '',
				'css'          => '',
				'main_area'    => array(
					'css' => '',
				),
			);
		}

		/**
		 * get all the css data needed for this landing page that's been previously saved from the editor
		 * example: body background, content background (if content is outside tve_editor) etc
		 *
		 * @return array
		 */
		public function get_css_data_tcb1() {
			$config  = $this->globals;
			$lp_data = array(
				'custom_color' => ! empty( $config['lp_bg'] ) ? ' data-tve-custom-colour="' . $config['lp_bg'] . '"' : '',
				'class'        => ! empty( $config['lp_bgcls'] ) ? ' ' . $config['lp_bgcls'] : '',
				'css'          => '',
				'main_area'    => array(
					'css' => '',
				),
			);
			if ( ! empty( $config['lp_bg'] ) && $config['lp_bg'] == '#ffffff' ) {
				$lp_data['custom_color'] = '';
				$lp_data['css']          .= 'background-color:#ffffff;';
			}
			if ( ! empty( $config['lp_bgp'] ) ) {
				if ( $config['lp_bgp'] === 'none' ) {
					$background_string = 'background-image:none;';
				} else {
					$background_string = "background-image:url('{$config['lp_bgp']}');";
				}
				$lp_data['css'] .= $background_string . 'background-repeat:repeat;background-size:auto;';
			} elseif ( ! empty( $config['lp_bgi'] ) ) {
				if ( $config['lp_bgi'] === 'none' ) {
					$background_string = 'background-image:none;';
				} else {
					$background_string = "background-image:url('{$config['lp_bgi']}');";
				}
				$lp_data['css'] .= $background_string . 'background-repeat:no-repeat;background-size:cover;background-position:center center;';
			}
			if ( ! empty( $config['lp_bga'] ) ) {
				$lp_data['css'] .= "background-attachment:{$config['lp_bga']};";
				if ( $config['lp_bga'] == 'fixed' ) {
					$lp_data['class'] .= ( $lp_data['class'] ? ' ' : '' ) . 'tve-lp-fixed';
				}
			}
			if ( ! empty( $config['lp_cmw'] ) && ! empty( $config['lp_cmw_apply_to'] ) ) { // landing page - content max width
				if ( $config['lp_cmw_apply_to'] == 'tve_post_lp' ) {
					$lp_data['main_area']['css'] .= "max-width: {$config['lp_cmw']}px;";
				}
			}

			return $lp_data;
		}

		/**
		 * get all the css data needed for this landing page that's been previously saved from the editor
		 * example: body background, content background (if content is outside tve_editor) etc
		 *
		 * @return array
		 */
		public function get_css_data() {
			if ( isset( $this->globals['body_css'] ) ) {
				/* TCB2 - just a single body attribute which controls all styles */
				$lp_data = $this->get_css_data_tcb2();
			} else {
				$lp_data = $this->get_css_data_tcb1();
			}

			$lp_data['class'] .= ! empty( $lp_data['class'] ) ? ' tve_lp' : 'tve_lp';
			$lp_data['class'] .= is_editor_page() ? ' tve_editor_page tve_editable' : '';

			return $lp_data;
		}

		/**
		 * called right after <body> open tag
		 */
		public function after_body_open() {
			if ( ! empty( $this->global_scripts['body'] ) && ! is_editor_page() ) {
				$this->global_scripts['body'] = $this->remove_jquery( $this->global_scripts['body'] );
				echo $this->global_scripts['body'];
			}

			do_action( self::HOOK_BODY_OPEN, $this->id );
		}

		/**
		 * called before the WP get_footer hook
		 */
		public function footer() {
			do_action( self::HOOK_FOOTER, $this->id );
		}

		/**
		 * called right before the <body> end tag
		 */
		public function before_body_end() {
			do_action( self::HOOK_BODY_CLOSE, $this->id );

			if ( ! empty( $this->global_scripts['footer'] ) && ! is_editor_page() ) {
				$this->global_scripts['footer'] = $this->remove_jquery( $this->global_scripts['footer'] );
				echo $this->global_scripts['footer'];
			}
		}

		/**
		 * whether or not this landing page should have lightbox associated
		 */
		public function needs_lightbox() {
			return ! empty( $this->config['has_lightbox'] );
		}

		/**
		 * check if the associated lightbox exists and, if not, create it
		 *
		 * @param bool $replace_default_texts
		 */
		public function check_lightbox( $replace_default_texts = true ) {
			if ( $replace_default_texts ) {
				$this->replace_default_texts();
			}

			if ( ! $this->needs_lightbox() ) {
				return;
			}

			if ( isset( $this->globals['lightbox_id'] ) ) {
				$lightbox = get_post( $this->globals['lightbox_id'] );
				if ( ! $lightbox || $lightbox->post_type !== 'tcb_lightbox' ) {
					unset( $lightbox );
				}
			}

			if ( empty( $lightbox ) ) {

				$this->globals['lightbox_id'] = $this->new_lightbox();

				tve_update_post_meta( $this->id, 'tve_globals', $this->globals );
			}
			if ( ! empty( $this->config['lightbox'] ) && ! empty( $this->config['lightbox']['exit_intent'] ) && ! $this->has_page_exit_intent() ) {
				/* setup the lightbox to be triggered on exit intent */
				$this->page_events    = empty( $this->page_events ) ? array() : $this->page_events;
				$this->page_events [] = array(
					't'      => 'exit',
					'a'      => 'thrive_lightbox',
					'config' => array(
						'e_mobile' => '1',
						'e_delay'  => '30',
						'l_id'     => $this->globals['lightbox_id'],
						'l_anim'   => 'slide_top',
					),
				);
				tve_update_post_meta( $this->id, 'tve_page_events', $this->page_events );
			}

			/* check if the id of the lightbox from the content is different than the id of the generated lightbox */
			$post_content = tve_get_post_meta( $this->id, 'tve_updated_post' );

			/* 12.10.2015 - lightbox events can also be setup with a simple string: tcb_open_lightbox */
			$open_lightbox_event = '{tcb_open_lightbox}';
			$events_config       = array(
				array(
					't'      => 'click',
					'a'      => 'thrive_lightbox',
					'config' => array(
						'l_id'   => empty( $this->globals['lightbox_id'] ) ? '' : $this->globals['lightbox_id'],
						'l_anim' => 'slide_top',
					),
				),
			);
			$post_content        = str_replace( $open_lightbox_event, '__TCB_EVENT_' . htmlentities( json_encode( $events_config ) ) . '_TNEVE_BCT__', $post_content, $number_of_replacements );
			$save_it             = $number_of_replacements;

			if ( strpos( $post_content, "&quot;l_id&quot;:&quot;{$this->globals['lightbox_id']}&quot;" ) === false ) {
				$post_content = preg_replace( '#&quot;l_id&quot;:(|&quot;)(\d+)(\1)#', '&quot;l_id&quot;:&quot;' . $this->globals['lightbox_id'] . '&quot;', $post_content );
				$save_it      = true;
			}

			if ( $save_it ) {
				tve_update_post_meta( $this->id, 'tve_updated_post', $post_content );
			}
		}

		/**
		 * generate new lightbox specific for this landing page
		 *
		 * @param string $title
		 *
		 * @return int
		 */
		public function new_lightbox( $title = null, $lb_meta = null, $template_suffix = '' ) {
			$landing_page = get_post( $this->id );
			$meta         = array(
				'tve_lp_lightbox' => $this->template,
			);

			$tcb_content = $this->lightbox_default_content( $template_suffix );

			if ( $this->is_cloud_template && is_null( $lb_meta ) && ! empty( $this->cloud_template_data['lightbox']['meta'] ) ) {
				$lb_meta = $this->cloud_template_data['lightbox']['meta'];
			}

			if ( $this->is_cloud_template && ! is_null( $lb_meta ) ) {
				$meta                   = array_merge( $meta, $lb_meta );
				$meta['tve_custom_css'] = $this->get_cloud_css_v2( true, $template_suffix );
				$lightbox_globals       = $meta['tve_globals'];
			} else {
				$lightbox_globals = array(
					'l_cmw' => isset( $this->config['lightbox']['max_width'] ) ? $this->config['lightbox']['max_width'] : '600px',
					'l_cmh' => isset( $this->config['lightbox']['max_height'] ) ? $this->config['lightbox']['max_height'] : '600px',
				);
			}

			return TCB_Lightbox::create(
				$title ? $title : ( 'Lightbox - ' . $landing_page->post_title . ' (' . $this->config['name'] . ')' ),
				$tcb_content,
				$lightbox_globals,
				$meta
			);
		}

		public function update_lightbox( $lightbox_id, $title = null, $lb_meta = null, $template_suffix = '' ) {
			$landing_page = get_post( $this->id );
			$meta         = array(
				'tve_lp_lightbox' => $this->template,
			);

			$tcb_content = $this->lightbox_default_content( $template_suffix );

			if ( $this->is_cloud_template && is_null( $lb_meta ) && ! empty( $this->cloud_template_data['lightbox']['meta'] ) ) {
				$lb_meta = $this->cloud_template_data['lightbox']['meta'];
			}

			if ( $this->is_cloud_template && ! is_null( $lb_meta ) ) {
				$meta                   = array_merge( $meta, $lb_meta );
				$meta['tve_custom_css'] = $this->get_cloud_css_v2( true, $template_suffix );
				$lightbox_globals       = $meta['tve_globals'];
			} else {
				$lightbox_globals = array(
					'l_cmw' => isset( $this->config['lightbox']['max_width'] ) ? $this->config['lightbox']['max_width'] : '600px',
					'l_cmh' => isset( $this->config['lightbox']['max_height'] ) ? $this->config['lightbox']['max_height'] : '600px',
				);
			}

			return TCB_Lightbox::update(
				$lightbox_id,
				$title ? $title : ( 'Lightbox - ' . $landing_page->post_title . ' (' . $this->config['name'] . ')' ),
				$tcb_content,
				$lightbox_globals,
				$meta
			);
		}

		/**
		 * fetch default lightbox content from one of the files inside landing-page/lightbox/ folder
		 *
		 * @param string $template_suffix used for multi-lightboxes landing pages
		 */
		public function lightbox_default_content( $template_suffix = '' ) {
			if ( $this->is_cloud_template ) {
				/* if it's a cloud template, the lightbox content needs to be fetched from wp-uploads/tcb_lp_templates/lightboxes/{template_name}.tpl */
				$lb_file  = tcb_get_cloud_base_path() . 'lightboxes/' . $this->template . $template_suffix . '.tpl';
				$contents = '';

				if ( file_exists( $lb_file ) ) {
					$contents = file_get_contents( $lb_file );
				}

				return $this->replace_default_texts( $contents );
			}

			/**
			 * from this point forward => this is a regular template - the lightbox content is available in a local php file from the plugin
			 */

			ob_start();
			if ( file_exists( dirname( dirname( __FILE__ ) ) . '/lightboxes/' . $this->template . '.php' ) ) {
				include dirname( dirname( __FILE__ ) ) . '/lightboxes/' . $this->template . '.php';
			}
			$contents = ob_get_contents();
			ob_end_clean();

			return $this->replace_default_texts( $contents );
		}

		/**
		 * removes references to jquery loaded directly from CDN - this will break the editor scripts on this page
		 *
		 * @param string $custom_script
		 *
		 * @return string
		 */
		public function remove_jquery( $custom_script ) {
			if ( ! is_editor_page() ) {
				return $custom_script;
			}

			$js_search = '/src=(["\'])(.+?)((code.jquery.com\/jquery-|ajax.googleapis.com\/ajax\/libs\/jquery\/))(\d)(.+?)\1/si';

			return preg_replace( $js_search, 'src=$1$1', $custom_script );
		}

		/**
		 * replace all occurences of custom texts we currently use for generating server-specifing data
		 *
		 * {tcb_timezone}
		 *
		 * @param string $post_content if null it will take by default this contents of this landing page
		 *
		 * @return string
		 */
		public function replace_default_texts( $post_content = null ) {
			if ( null === $post_content ) {
				$update_post_meta = true;
				$post_content     = tve_get_post_meta( $this->id, 'tve_updated_post' );
			}

			if ( empty( $post_content ) ) {
				return '';
			}

			$save_it = false;

			/**
			 * {tcb_timezone}
			 */
			if ( strpos( $post_content, 'data-timezone="{tcb_timezone}"' ) !== false ) {
				$timezone_offset = get_option( 'gmt_offset' );
				$sign            = ( $timezone_offset < 0 ? '-' : '+' );
				$min             = abs( $timezone_offset ) * 60;
				$hour            = floor( $min / 60 );
				$tzd             = $sign . str_pad( $hour, 2, '0', STR_PAD_LEFT ) . ':' . str_pad( $min % 60, 2, '0', STR_PAD_LEFT );
				$post_content    = str_replace( 'data-timezone="{tcb_timezone}"', 'data-timezone="' . $tzd . '"', $post_content );
				$save_it         = true;
			}

			if ( strpos( $post_content, '{tcb_lp_base_url}' ) !== false ) {
				$replacement  = $this->is_cloud_template ? tcb_get_cloud_base_url() . 'templates' : TVE_LANDING_PAGE_TEMPLATE;
				$post_content = str_replace( '{tcb_lp_base_url}', untrailingslashit( $replacement ), $post_content );
				$save_it      = true;
			}

			if ( isset( $update_post_meta ) && $save_it ) {
				tve_update_post_meta( $this->id, 'tve_updated_post', $post_content );
			}

			return $post_content;
		}

		/**
		 * enqueue the CSS file needed for this template
		 */
		public function enqueue_css() {
			$handle = 'tve_landing_page_' . $this->template;

			if ( $this->is_cloud_template ) {
				if ( (int) $this->cloud_template_data['LP_VERSION'] !== 2 ) {
					tve_enqueue_style( $handle, trailingslashit( tcb_get_cloud_base_url() ) . 'templates/css/' . $this->template . '.css', 100 );
				}
			} elseif ( file_exists( plugin_dir_path( dirname( __FILE__ ) ) . 'templates/css/' . $this->template . '.css' ) ) {
				tve_enqueue_style( $handle, TVE_LANDING_PAGE_TEMPLATE . '/css/' . $this->template . '.css', 100 );
			}
		}

		public function ensure_external_assets() {

			$lightbox_ids = array();

			/**
			 * look for page events
			 */
			foreach ( $this->page_events as $event ) {
				if ( isset( $event['a'] ) && $event['a'] === 'thrive_lightbox' && ! empty( $event['config'] ) && ! empty( $event['config']['l_id'] ) ) {
					$lightbox_ids[] = $event['config']['l_id'];
				}
			}

			/**
			 * look for page invents in content
			 */
			$post_content = tve_get_post_meta( $this->id, 'tve_updated_post' );
			if ( preg_match_all( '#&quot;l_id&quot;:(null|&quot;(.*?)&quot;)#', $post_content, $matches ) ) {
				$lightbox_ids = array_merge( $lightbox_ids, $matches[2] );
			}

			$lightbox_ids = array_unique( $lightbox_ids );

			global $post;
			$old_post = $post;

			/**
			 * This code is executed really early in the request - and sometimes it generates output ( before the <html> tag )
			 * we need to catch and ignore this output
			 */
			ob_start();

			/**
			 * let the others do their content and add their scripts
			 */
			foreach ( $lightbox_ids as $id ) {
				$post = get_post( $id );
				apply_filters( 'the_content', '' );
			}

			/**
			 * get rid of any undesired output.
			 */
			ob_end_clean();

			$post = $old_post;
		}

		/**
		 * check if this landing page has a "Exit Intent" event setup to display a lightbox
		 */
		public function has_page_exit_intent() {
			if ( empty( $this->page_events ) ) {
				return false;
			}
			foreach ( $this->page_events as $page_event ) {
				if ( ! empty( $page_event['t'] ) && ! empty( $page_event['a'] ) && $page_event['t'] == 'exit' && ( $page_event['a'] == 'thrive_lightbox' || $page_event['a'] == 'thrive_leads_2_step' ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * reset landing page to its default content
		 * this assumes that the tve_landing_page post meta field is set to the value of the correct landing page template
		 *
		 * @param bool $reset_global_scripts whether or not to also reset the 'tve_global_scripts' data
		 */
		public function reset( $reset_global_scripts = true ) {

			$post_content    = $this->default_content();
			$meta_key_suffix = '_' . $this->template;
			$globals         = $this->meta( 'tve_globals' . $meta_key_suffix );

			$meta = $this->is_cloud_template && $this->cloud_template_data && ! empty( $this->cloud_template_data['meta'] ) ? $this->cloud_template_data['meta'] : array();

			$meta = wp_parse_args( $meta, array(
				'tve_custom_css'         => '',
				'tve_user_custom_css'    => '',
				'tve_has_masonry'        => '',
				'tve_has_typefocus'      => '',
				'tve_has_wistia_popover' => '',
				'tve_page_events'        => array(),
				'thrive_icon_pack'       => '',
			) );

			if ( ! empty( $meta['tve_globals'] ) ) {
				$meta['tve_globals']['lightbox_id'] = isset( $globals['lightbox_id'] ) ? $globals['lightbox_id'] : 0;
			} else {
				$meta['tve_globals'] = array( 'lightbox_id' => isset( $globals['lightbox_id'] ) ? $globals['lightbox_id'] : 0 );
			}
			if ( ! $this->is_cloud_template && ! empty( $this->config['globals']['body_css'] ) ) {
				$meta['tve_globals']['body_css'] = $this->config['globals']['body_css'];
			}
			if ( $this->is_cloud_template && ! empty( $this->cloud_template_data ) && (int) $this->cloud_template_data['LP_VERSION'] === 2 ) {
				/* Load the default css for the page */
				$meta['tve_custom_css'] = $this->get_cloud_css_v2();
				if ( ! empty( $this->cloud_template_data['lightboxes'] ) ) {
					$meta['tve_globals']['lb_map'] = isset( $globals['lb_map'] ) ? $globals['lb_map'] : array();
				}
			} elseif ( ! $this->is_cloud_template && ! empty( $this->config['head_css'] ) ) {
				$meta['tve_custom_css'] = $this->config['head_css'];
			}

			$post_content = $this->replace_default_texts( $post_content );
			$this->meta( 'tve_updated_post' . $meta_key_suffix, $post_content );

			foreach ( $meta as $k => $v ) {
				$this->meta( $k . $meta_key_suffix, $v );
			}

			if ( $reset_global_scripts ) {
				/* this does not use LP-specific meta key */
				$this->meta( 'tve_global_scripts', array() );
			}

			$this->globals = $meta['tve_globals'];

			/* check to see if a default lightbox exists for this and if necessary, create it */
			/* make sure the associated lightbox exists and is setup in the event manager */
			if ( isset( $this->cloud_template_data['lightboxes'] ) ) {
				/**
				 * new version: multiple lightboxes for a landing page
				 */
				$this->ensure_multi_lightbox( isset( $meta['tve_globals']['lb_map'] ) ? $meta['tve_globals']['lb_map'] : array() );
			} else {
				$this->check_lightbox( false );
			}

			tve_update_post_custom_fonts( $this->post->ID, array() );
		}

		/**
		 * Make sure all the needed lightboxes exist
		 */
		public function ensure_multi_lightbox( $lb_id_map = array() ) {
			$lightboxes = $this->cloud_template_data['lightboxes'];

			/* check if the id of the lightbox from the content is different than the id of the generated lightbox */
			$post_content = $this->meta( 'tve_updated_post', null, true );

			foreach ( $lightboxes as $lb_id => $lb_data ) {
				if ( isset( $lb_id_map[ $lb_id ] ) ) {
					$lb = get_post( $lb_id_map[ $lb_id ] );
					if ( ! $lb ) {
						unset( $lb_id_map[ $lb_id ] );
					}
				}
				if ( ! isset( $lb_id_map[ $lb_id ] ) ) {
					$lb_id_map[ $lb_id ] = $this->new_lightbox( null, $lb_data['meta'], '-' . $lb_id );
				} else {
					$this->update_lightbox( $lb_id_map[ $lb_id ], null, $lb_data['meta'], '-' . $lb_id );
				}
				$post_content = preg_replace( '#&quot;l_id&quot;:(&quot;)?' . $lb_id . '(&quot;)?#', '&quot;l_id&quot;:&quot;' . $lb_id_map[ $lb_id ] . '&quot;', $post_content );
			}

			/**
			 * Page events
			 */
			if ( ! empty( $this->cloud_template_data['meta']['tve_page_events'] ) ) {
				$this->page_events = $this->cloud_template_data['meta']['tve_page_events'];

				foreach ( $this->page_events as $index => $evt ) {
					if ( $evt['a'] == 'thrive_lightbox' && isset( $lb_id_map[ $evt['config']['l_id'] ] ) ) {
						$this->page_events[ $index ]['config']['l_id'] = $lb_id_map[ $evt['config']['l_id'] ];
					}
				}
				$this->meta( 'tve_page_events', $this->page_events, true );
			}

			$this->globals['lb_map'] = $lb_id_map;

			$this->meta( 'tve_globals', $this->globals, true );
			$this->meta( 'tve_updated_post', $post_content, true );
		}

		/**
		 * Get the CSS text for the landing page or for a lightbox
		 *
		 * @param bool
		 * @param string $lb_suffix
		 *
		 * @return string
		 */
		public function get_cloud_css_v2( $for_lightbox = false, $lb_suffix = '' ) {
			$suffix = $for_lightbox ? ( $lb_suffix . '_lightbox.css' ) : '.css';
			$file   = tcb_get_cloud_base_path() . 'templates/css/' . $this->template . $suffix;
			$css    = '';

			if ( file_exists( $file ) ) {
				$css = str_replace( '{tcb_lp_base_url}', tcb_get_cloud_base_url() . 'templates/css/images/', file_get_contents( $file ) );
			}

			return $css;
		}

		/**
		 * Set the cloud template for this landing page
		 *
		 * @param string $cloud_template
		 *
		 * @throws Exception
		 *
		 * @return TCB_Landing_Page
		 */
		public function set_cloud_template( $cloud_template ) {
			$config = tve_get_cloud_template_config( $cloud_template );
			if ( $config === false ) {
				throw new Exception( 'Could not validate Landing Page configuration' );
			}
			$this->template            = $cloud_template;
			$this->is_cloud_template   = true;
			$this->cloud_template_data = $this->config = $config;

			$this->meta( 'tve_landing_page', $this->template );
			$this->reset( true );

			return $this;
		}

		/**
		 * remove or change the current landing page template for the post with a default landing page, or a previously saved landing page
		 * this also updates the post meta fields related to the selected template
		 *
		 * if it's a default template, then it will not change anything related to post content, as it will try to load it from the saved template
		 *
		 * each template will have it's own fields saved for the post, this helps users to not lose any content when switching back and forth various templates
		 *
		 * @param     $landing_page_template
		 *
		 * @return TCB_Landing_Page
		 */
		public function change_template( $landing_page_template ) {

			if ( ! $landing_page_template ) {
				$this->template = '';
				$this->meta_delete( 'tve_landing_page' );
				//Delete Also The Setting To Disable Theme CSS
				$this->meta_delete( 'tve_disable_theme_dependency' );

				return $this;
			}

			/* Landing Page default template */
			if ( strpos( $landing_page_template, 'user-saved-template-' ) !== 0 ) {
				/* default landing page template: load in the default template content - this can also be a template downloaded from the cloud */
				$this->template          = $landing_page_template;
				$this->config            = tve_get_landing_page_config( $this->template );
				$this->is_cloud_template = tve_is_cloud_template( $landing_page_template );
				if ( $this->is_cloud_template ) {
					$this->cloud_template_data = tve_get_cloud_template_config( $landing_page_template );
				}

				/* 2014-09-19: reset the landing page contents, the whole page will reload using the clear new template */
				$this->reset( false );

			} else {
				/* at this point, the template is one of the previously saved templates (saved by the user) - it holds the index from the tve_saved_landing_pages_content which needs to be loaded */
				$contents       = get_option( 'tve_saved_landing_pages_content' );
				$meta           = get_option( 'tve_saved_landing_pages_meta' );
				$template_index = intval( str_replace( 'user-saved-template-', '', $landing_page_template ) );

				/* make sure we don't mess anything up */
				if ( empty( $contents ) || empty( $meta ) || ! isset( $contents[ $template_index ] ) ) {
					return $this;
				}
				$content        = $contents[ $template_index ];
				$this->template = $landing_page_template = $meta[ $template_index ]['template'];

				if ( empty( $content['more_found'] ) ) {
					$content['more_found']  = false;
					$content['before_more'] = $content['content'];
				}

				$key = '_' . $landing_page_template;

				$this->meta( "tve_content_before_more{$key}", $content['before_more'] );
				$this->meta( "tve_content_more_found{$key}", $content['more_found'] );
				$this->meta( "tve_custom_css{$key}", $content['inline_css'] );
				$this->meta( "tve_user_custom_css{$key}", $content['custom_css'] );
				$this->meta( "tve_updated_post{$key}", $content['content'] );
				$this->meta( "tve_globals{$key}", ! empty( $content['tve_globals'] ) ? $content['tve_globals'] : array() );
				$this->meta( 'tve_global_scripts', ! empty( $content['tve_global_scripts'] ) ? $content['tve_global_scripts'] : array() );
			}

			$this->meta( 'tve_landing_page', $this->template );

			return $this;
		}

		/**
		 * Get the full path to the landing-page folder
		 *
		 * @param string|null $file
		 *
		 * @return string
		 */
		public static function path( $file = null ) {
			$file = $file ? ltrim( $file, '/\\' ) : '';

			return plugin_dir_path( dirname( __FILE__ ) ) . $file;
		}

		/**
		 * get all the available landing page templates
		 * this function reads in the landing page config file and returns an array with names, thumbnail images, and template codes
		 *
		 * @return array
		 */
		public static function templates() {
			$templates = array();
			$config    = include self::path( 'templates/_config.php' );
			foreach ( $config as $code => $template ) {
				$templates[ $code ] = array(
					'name'       => $template['name'],
					'set'        => $template['set'],
					'tags'       => isset( $template['tags'] ) ? $template['tags'] : array(),
					'downloaded' => isset( $template['downloaded'] ) ? $template['downloaded'] : false,
				);
			}
			if ( ! empty( $templates['blank'] ) ) {
				$blank = array( 'blank' => $templates['blank'] );
				unset( $templates['blank'] );
				$templates = $blank + $templates;
			}

			return $templates;
		}

		/**
		 * Should only return the blank template
		 *
		 * @return array
		 */
		public static function templates_v2() {
			return array(
				'blank_v2' => array(
					'name'         => 'Blank Page v2',
					'tags'         => array( 'blank' ),
					'set'          => 'Blank',
					'style_family' => 'Flat',
					'LP_VERSION'   => 2,
				),
			);
		}

		/**
		 * returns the default template content for a landing page post
		 *
		 * if the landing page template is a local one - the contents are stored in a php file template inside the landing-page folder in the plugin
		 * if the landing page template is a "Cloud" template (previously downloaded from the API) - the contents are stored in a corresponding file in the wp-uploads folder
		 *
		 * @param string $default possibility to use a template as default
		 *
		 * @return string
		 */
		public function default_content( $default = 'blank_v2' ) {
			$template_name = $this->template;
			if ( $this->is_cloud_template ) {

				/* if $data === false => this is not a valid template - this means either some files got deleted, either the wp_options entry is corrupted */
				if ( $this->cloud_template_data ) {
					$content = file_get_contents( tcb_get_cloud_base_path() . 'templates/' . $template_name . '.tpl' );
				}
			}

			if ( empty( $content ) ) {
				$landing_page_dir = plugin_dir_path( dirname( __FILE__ ) );

				if ( empty( $template_name ) || ! is_file( $landing_page_dir . 'templates/' . $template_name . '.php' ) ) {
					$template_name = $default;
				}

				ob_start();
				include $landing_page_dir . 'templates/' . $template_name . '.php';
				$content = ob_get_contents();
				ob_end_clean();
			}

			return $content;
		}

		/**
		 * Check if a Landing Page is actually a TCB2 template-based landing page
		 *
		 * @return bool
		 */
		public function is_v2() {
			if ( empty( $this->config ) ) {
				return false;
			}

			return isset( $this->config['LP_VERSION'] ) && (int) $this->config['LP_VERSION'] === 2;
		}
	}

	function tcb_landing_page( $post_id, $landing_page_template = null ) {
		return new TCB_Landing_Page( $post_id, $landing_page_template );
	}

	/**
	 *
	 * Get the full path to the folder storing cloud templates on the user's wp install
	 *
	 * @return array|string
	 */
	function tcb_get_cloud_base_path() {
		$upload = wp_upload_dir();
		if ( ! empty( $upload['error'] ) ) {
			return '';
		}

		return trailingslashit( $upload['basedir'] ) . TVE_CLOUD_LP_FOLDER . '/';
	}

	/**
	 *
	 * Get the full path to the folder storing cloud templates on the user's wp install
	 *
	 * @return array|string
	 */
	function tcb_get_cloud_base_url() {
		$upload = wp_upload_dir();
		if ( ! empty( $upload['error'] ) ) {
			return trailingslashit( site_url() ) . 'wp-content/uploads/' . TVE_CLOUD_LP_FOLDER;
		}

		$base_url = str_replace( array( 'http://', 'https://' ), '//', $upload['baseurl'] );

		return trailingslashit( $base_url ) . TVE_CLOUD_LP_FOLDER . '/';
	}
}
