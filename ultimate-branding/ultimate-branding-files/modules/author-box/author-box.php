<?php
/*
Plugin Name: Author Box
Description: Adds a responsive author box at the end of your posts, showing the author name, author gravatar and author description and social profiles.
Customize the author_box Mode page and create Coming Soon Page.
License: GNU General Public License (Version 2 - GPLv2)
Copyright 2018 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! class_exists( 'ub_author_box' ) ) {

	class ub_author_box extends ub_helper {
		protected $option_name = 'ub_author_box';
		private $current_sites = array();

		/**
		 * Constructor
		 *
		 * @since 1.9.7
		 */
		public function __construct() {
			parent::__construct();
			$this->module = 'author-box';
			$this->set_options();
			/**
			 * Admin area
			 */
			add_action( 'ultimatebranding_settings_author_box', array( $this, 'admin_options_page' ) );
			add_filter( 'ultimatebranding_settings_author_box_process', array( $this, 'update' ), 10, 1 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			/**
			 * Front end
			 */
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'wp_head', array( $this, 'print_css' ) );
			add_filter( 'the_content', array( $this, 'author_box' ) );
			/**
			 * user profile
			 */
			add_action( 'edit_user_profile_update', array( $this, 'save_user_profile' ) );
			add_action( 'edit_user_profile', array( $this, 'add_social_media' ) );
			add_action( 'personal_options_update', array( $this, 'save_user_profile' ) );
			add_action( 'show_user_profile', array( $this, 'add_social_media' ) );
		}

		/**
		 * Set options for module
		 *
		 * @since 1.9.7
		 */
		protected function set_options() {
			$post_types = array();
			$p = get_post_types( array( 'public' => true ), 'objects' );
			foreach ( $p as $key => $data ) {
				$post_types[ $key ] = $data->label;
			}

			$options = array(
				'show' => array(
					'title' => __( 'General configuration', 'ub' ),
					'fields' => array(
						'post_type' => array(
							'type' => 'select2',
							'label' => __( 'Post types', 'ub' ),
							'options' => $post_types,
							'multiple' => true,
							'classes' => array( 'ub-select2' ),
							'description' => __( 'Please select post types in which the author box will be displayed.', 'ub' ),
						),
						'display_name' => array(
							'type' => 'checkbox',
							'label' => __( 'Show name', 'ub' ),
							'options' => array(
								'on' => __( 'Show', 'ub' ),
								'off' => __( 'Hide', 'ub' ),
							),
							'default' => 'on',
							'classes' => array( 'switch-button' ),
							'slave-class' => 'display-name',
						),
						'display_name_link' => array(
							'type' => 'checkbox',
							'label' => __( 'Link name', 'ub' ),
							'description' => __( 'Link author name to author archive.', 'ub' ),
							'options' => array(
								'on' => __( 'Link', 'ub' ),
								'off' => __( 'no', 'ub' ),
							),
							'default' => 'on',
							'classes' => array( 'switch-button' ),
							'master' => 'display-name',
						),
						'description' => array(
							'type' => 'checkbox',
							'label' => __( 'Show description', 'ub' ),
							'options' => array(
								'on' => __( 'Show', 'ub' ),
								'off' => __( 'Hide', 'ub' ),
							),
							'default' => 'on',
							'classes' => array( 'switch-button' ),
						),
						'avatar' => array(
							'type' => 'checkbox',
							'label' => __( 'Show avatar', 'ub' ),
							'options' => array(
								'on' => __( 'Show', 'ub' ),
								'off' => __( 'Hide', 'ub' ),
							),
							'default' => 'on',
							'classes' => array( 'switch-button' ),
							'slave-class' => 'avatar-related',
						),
						'social_media' => array(
							'type' => 'checkbox',
							'label' => __( 'Show social media profiles', 'ub' ),
							'description' => __( 'Autor can add it on user profile page', 'ub' ),
							'options' => array(
								'on' => __( 'Show', 'ub' ),
								'off' => __( 'Hide', 'ub' ),
							),
							'default' => 'on',
							'classes' => array( 'switch-button' ),
							'slave-class' => 'social-media',
						),
						'social_media_link_in_new_tab' => array(
							'type' => 'checkbox',
							'label' => __( 'Open Social media links', 'ub' ),
							'options' => array(
								'on' => __( 'open new', 'ub' ),
								'off' => __( 'in the same', 'ub' ),
							),
							'default' => 'off',
							'classes' => array( 'switch-button' ),
							'master' => 'social-media',
						),
					),
				),
				'box' => array(
					'title' => __( 'Box options', 'ub' ),
					'fields' => array(
						'border' => array(
							'type' => 'checkbox',
							'label' => __( 'Show border', 'ub' ),
							'options' => array(
								'on' => __( 'Show', 'ub' ),
								'off' => __( 'Hide', 'ub' ),
							),
							'default' => 'off',
							'classes' => array( 'switch-button' ),
							'slave-class' => 'border',
						),
						'border_width' => array(
							'type' => 'number',
							'label' => __( 'Border width', 'ub' ),
							'attributes' => array( 'placeholder' => '20' ),
							'default' => 1,
							'min' => 0,
							'classes' => array( 'ui-slider' ),
							'after' => __( 'px', 'ub' ),
							'master' => 'border',
						),
						'border_color' => array(
							'type' => 'color',
							'label' => __( 'Border color', 'ub' ),
							'default' => '#ddd',
							'master' => 'border',
						),
						'border_style' => array(
							'type' => 'select',
							'label' => __( 'Border style', 'ub' ),
							'default' => 'solid',
							'master' => 'border',
							'options' => $this->css_border_options(),
						),
						'border_radius' => array(
							'type' => 'number',
							'label' => __( 'Radius corners', 'ub' ),
							'description' => __( 'How much would you like to round the border?', 'ub' ),
							'attributes' => array( 'placeholder' => '20' ),
							'default' => 0,
							'min' => 0,
							'classes' => array( 'ui-slider' ),
							'after' => __( 'px', 'ub' ),
							'master' => 'border',
						),
					),
				),
				'avatar' => array(
					'title' => __( 'Avatar options', 'ub' ),
					'fields' => array(
						'size' => array(
							'type' => 'number',
							'label' => __( 'Size', 'ub' ),
							'description' => __( 'How much would you like to round the border?', 'ub' ),
							'attributes' => array( 'placeholder' => '20' ),
							'default' => 96,
							'min' => 0,
							'max' => 200,
							'classes' => array( 'ui-slider' ),
							'after' => __( 'px', 'ub' ),
						),
						'rounded' => array(
							'type' => 'number',
							'label' => __( 'Radius corners', 'ub' ),
							'description' => __( 'How much would you like to round the border?', 'ub' ),
							'attributes' => array( 'placeholder' => '20' ),
							'default' => 0,
							'min' => 0,
							'classes' => array( 'ui-slider' ),
							'after' => __( 'px', 'ub' ),
						),
						'border' => array(
							'type' => 'number',
							'label' => __( 'Border width', 'ub' ),
							'attributes' => array( 'placeholder' => '20' ),
							'default' => 0,
							'min' => 0,
							'classes' => array( 'ui-slider' ),
							'after' => __( 'px', 'ub' ),
						),
						'border_color' => array(
							'type' => 'color',
							'label' => __( 'Border color', 'ub' ),
							'attributes' => array( 'placeholder' => '20' ),
							'default' => false,
							'after' => __( 'px', 'ub' ),
						),
					),
					'master' => array(
						'section' => 'show',
						'field' => 'avatar',
						'value' => 'on',
					),
				),
				'social_media' => array(
					'title' => __( 'Social Media Profiles', 'ub' ),
					'fields' => array(),
					'sortable' => true,
					'master' => array(
						'section' => 'show',
						'field' => 'social_media',
						'value' => 'on',
					),
				),
			);

			$social = $this->get_social_media_array();
			$order = $this->get_value( '_social_media_sortable' );
			if ( is_array( $order ) ) {
				foreach ( $order as $key ) {
					if ( isset( $social[ $key ] ) ) {
						$options['social_media']['fields'][ $key ] = $social[ $key ];
						unset( $social[ $key ] );
					}
				}
			}
			$options['social_media']['fields'] += $social;
			/**
			 * set type
			 */
			$set = array(
				'type' => 'checkbox',
				'options' => array(
					'on' => __( 'Allow', 'ub' ),
					'off' => __( 'Disallow', 'ub' ),
				),
				'default' => 'off',
				'default_hide' => true,
				'classes' => array( 'switch-button' ),
				'master' => 'social-media',
			);
			foreach ( $options['social_media']['fields'] as $key => $data ) {
				$options['social_media']['fields'][ $key ] += $set;
			}
			/**
			 * turn on few by default
			 */
			$options['social_media']['fields']['facebook']['default'] = 'on';
			$options['social_media']['fields']['twitter']['default'] = 'on';
			$options['social_media']['fields']['google']['default'] = 'on';
			/**
			 * return options/
			 */
			$this->options = $options;
		}

		/**
		 * Enqueue needed scripts.
		 *
		 * @since 1.9.7
		 */
		public function enqueue_scripts() {
			/**
			 * load on admin
			 */
			if ( is_admin() ) {
				$screen = get_current_screen();
				$this->load_social_logos_css();
				return;
			}
			/**
			 * Load on frontend
			 */
			$is_allowed_post_type = $this->check_post_type();
			if ( $is_allowed_post_type ) {
				$this->load_social_logos_css();
				$url = ub_files_url( 'modules/author-box/author-box.css' );
				wp_enqueue_style( __CLASS__, $url, array(), $this->build, 'screen' );
			}
		}

		/**
		 * Add social media links to author box.
		 *
		 * @since 1.9.7
		 */
		public function add_social_media( $profileuser ) {
			$data = $this->get_value( 'social_media' );
			$show = isset( $data ) && is_array( $data ) && in_array( 'on', $data );
			if ( ! $show ) {
				return;
			}
			$options = $this->options['social_media']['fields'];
			$order = $this->get_value( '_social_media_sortable' );
			$value = get_user_meta( $profileuser->ID, 'ub_author_box', true );
			printf( '<h2>%s</h2>', esc_html__( 'Social Media profiles', 'ub' ) );
			echo '<table class="form-table"><tbody>';
			foreach ( $order as $key ) {
				if ( ! isset( $data[ $key ] ) ) {
					continue;
				}
				if ( 'on' != $data[ $key ] ) {
					continue;
				}
				printf( '<tr class="user-author-box user-author-box-%s">', esc_attr( $key ) );
				printf( '<th><label for="user-author-box-%s">%s</label></th>', esc_attr( $key ), esc_html( $options[ $key ]['label'] ) );
				printf(
					'<td><input type="text" id="user-author-box-%s" class="regular-text" value="%s" name="ub_author_box[%s]" /></td>',
					esc_attr( $key ),
					esc_attr( isset( $value[ $key ] )? $value[ $key ]:'' ),
					esc_attr( $key )
				);
				echo '</tr>';
				unset( $data[ $key ] );
			}
			foreach ( $data as $key => $value ) {
				if ( ! isset( $data[ $key ] ) ) {
					continue;
				}
				if ( 'on' != $data[ $key ] ) {
					continue;
				}
				printf( '<tr class="user-author-box user-author-box-%s">', esc_attr( $key ) );
				printf( '<th><label for="user-author-box-%s">%s</label></th>', esc_attr( $key ), esc_html( $options[ $key ]['label'] ) );
				printf(
					'<td><input type="text" id="user-author-box-%s" class="regular-text" value="%s" name="ub_author_box[%s]" /></td>',
					esc_attr( $key ),
					esc_attr( isset( $value[ $key ] )? $value[ $key ]:'' ),
					esc_attr( $key )
				);
				echo '</tr>';
				unset( $data[ $key ] );
			}

			echo '</tbody></table>';
		}

		/**
		 * Save user profile
		 *
		 * @since 1.9.7
		 */
		public function save_user_profile( $user_id ) {
			if ( current_user_can( 'edit_user', $user_id ) && isset( $_POST['ub_author_box'] ) ) {
				$value = array_filter( $_POST['ub_author_box'] );
				$result = add_user_meta( $user_id, 'ub_author_box', $value, true );
				if ( false === $result ) {
					update_user_meta( $user_id, 'ub_author_box', $value );
				}
			}
		}

		/**
		 * add author box
		 *
		 * @since 1.9.7
		 */
		public function author_box( $content ) {
			/**
			 * Check allowed post types.
			 */
			$is_allowed_post_type = $this->check_post_type();
			if ( ! $is_allowed_post_type ) {
				return $content;
			}
			$user_id = get_the_author_meta( 'ID' );
			$content .= '<div class="ub-author-box">';
			$content .= '<div class="ub-author-box-content">';
			/**
			 * Gravatar
			 */
			$show = $this->get_value( 'show', 'avatar', false );
			if ( 'on' == $show ) {
				$size = $this->get_value( 'avatar', 'size', 96 );
				$content .= get_avatar( $user_id, $size );
			}
			/**
			 * name
			 */
			$content .= '<div class="ub-author-box-desc">';
			$show = $this->get_value( 'show', 'display_name', false );
			if ( 'on' == $show ) {
				$value = get_the_author_meta( 'display_name' );
				$link = $this->get_value( 'show', 'display_name_link', false );
				if ( 'on' == $link ) {
					$value = sprintf(
						'<a href="%s">%s</a>',
						get_author_posts_url( get_the_author_meta( 'ID' ) ),
						$value
					);
				}
				$content .= sprintf( '<h4>%s</h4>', $value );
			}
			/**
			 * description
			 */
			$show = $this->get_value( 'show', 'description', false );
			if ( 'on' == $show ) {
				$description = get_the_author_meta( 'user_description' );
				if ( $description ) {
					$content .= sprintf( '<div class="description">%s</div>', wpautop( $description ) );
				}
			}
			$content .= '</div>';
			$content .= '</div>';
			/**
			 * social_media
			 */
			$show = $this->get_value( 'show', 'social_media', false );
			if ( 'on' == $show ) {
				/**
				 * open link target
				 */
				$target = $this->get_value( 'show', 'social_media_link_in_new_tab', false );
				$target = ( 'on' === $target )? ' target="_blank"':'';
				/**
				 * process
				 */
				$sm = '';
				$data = $this->get_value( 'social_media' );
				$value = get_the_author_meta( 'ub_author_box' );
				$order = $this->get_value( '_social_media_sortable' );
				if ( ! empty( $order ) && is_array( $order ) ) {
					foreach ( $order as $key ) {
						if ( ! isset( $data[ $key ] ) ) {
							continue;
						}
						if ( 'on' != $data[ $key ] ) {
							continue;
						}
						if ( isset( $value[ $key ] ) ) {
							$v = trim( $value[ $key ] );
							if ( $v ) {
								$sm .= sprintf(
									'<li><a href="%s"%s><span class="social-logo social-logo-%s"></span></a></li>',
									esc_url( $v ),
									$target,
									esc_attr( $key )
								);
							}
						}
						unset( $data[ $key ] );
					}
				}
				if ( ! empty( $data ) && is_array( $data ) ) {
					foreach ( $data as $key => $value ) {
						if ( ! isset( $data[ $key ] ) ) {
							continue;
						}
						if ( 'on' != $data[ $key ] ) {
							continue;
						}
						if ( isset( $value[ $key ] ) ) {
							$v = trim( $value[ $key ] );
							if ( $v ) {
								$sm .= sprintf(
									'<li><a href="%s">span class="social-logo social-%s"></span></a></li>',
									esc_url( $v ),
									esc_attr( $key )
								);
							}
						}
					}
				}
				if ( $sm ) {
					$content .= sprintf( '<ul class="social-media">%s</ul>', $sm );
				}
			}
			$content .= '</div>';
			return $content;
		}

		/**
		 * Print custom CSS
		 *
		 * @since 1.9.7
		 */
		public function print_css() {
			/**
			 * Check allowed post types.
			 */
			$is_allowed_post_type = $this->check_post_type();
			if ( ! $is_allowed_post_type ) {
				return;
			}
			$value = ub_get_option( $this->option_name );
			if ( $value == 'empty' ) {
				$value = '';
			}
			if ( empty( $value ) ) {
				return;
			}
			printf( '<style type="text/css" id="%s">', esc_attr( __CLASS__ ) );
			/**
			 * box border
			 */
			if ( isset( $value['box'] ) ) {
				$v = $value['box'];
				echo '.ub-author-box {';
				if ( isset( $v['border'] ) && 'on' === $v['border'] ) {
					$width = isset( $v['border_width'] )? $v['border_width']:1;
					$color = isset( $v['border_color'] )? $v['border_color']:'solid';
					$style = isset( $v['border_style'] )? $v['border_style']:'#ddd';
					printf( 'border: %dpx %s %s;', esc_attr( $width ), esc_attr( $style ), esc_attr( $color ) );
					if ( isset( $v['border_radius'] ) && '0' != $v['border_radius'] ) {
						$this->border_radius( $v['border_radius'] );
					}
				} else {
					echo 'border:none;';
				}
				echo '}';
			}
			/**
			 * avatar
			 */
			if ( isset( $value['avatar'] ) ) {
				$v = $value['avatar'];
				echo '.ub-author-box .ub-author-box-content img {';
				/**
				 * rounded_form
				 */
				if ( isset( $v['rounded'] ) && '0' != $v['rounded'] ) {
					$this->border_radius( $v['rounded'] );
				}
				if ( isset( $v['border'] ) ) {
					$color = isset( $v['border_color'] )? $v['border_color']:'transparent';
					printf( 'border: %dpx solid %s;', $v['border'], esc_attr( $color ) );
				}
				echo '}';
			}
			echo '</style>';
		}

		/**
		 * modify option name
		 *
		 * @since 1.9.7
		 */
		public function get_module_option_name( $option_name, $module ) {
			if ( is_string( $module ) && $this->module == $module ) {
				return $this->option_name;
			}
			return $option_name;
		}

		/**
		 * Check allowed post types.
		 *
		 * @since 1.9.7
		 */
		private function check_post_type() {
			if ( is_admin() ) {
				return false;
			}
			if ( is_singular() ) {
				$allowed_post_types = $this->get_value( 'show', 'post_type', false );
				if ( empty( $allowed_post_types ) ) {
					return false;
				}
				$post_type = get_post_type();
				return in_array( $post_type, $allowed_post_types );
			}
			return false;
		}

		private function border_radius( $radius ) {
			$radius = intval( $radius );
			if ( 1 > $radius ) {
				return;
			}
?>
-webkit-border-radius: <?php echo esc_attr( $radius ); ?>px;
-moz-border-radius: <?php echo esc_attr( $radius ); ?>px;
border-radius: <?php echo esc_attr( $radius ); ?>px;
<?php
		}
	}
}
new ub_author_box();
