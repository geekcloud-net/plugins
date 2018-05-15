<?php

/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 5/29/2017
 * Time: 9:39 AM
 */
class TCB_Post_Grid {
	private $_template = 'sc-post-grid.php';
	private $_cfg_code = '__CONFIG_post_grid__';

	/**
	 * When set to false, the shortcode config DIV wont be rendered
	 *
	 * @var bool
	 */
	public $output_shortcode_config = true;

	/**
	 * PostGrid constructor.
	 *
	 * @param $config
	 */
	public function __construct( $config = array() ) {

		$config = stripslashes_deep( $config );

		$defaults = array(
			'display'          => 'grid',
			'grid_layout'      => 'horizontal',
			'columns'          => '3',
			'text_type'        => 'summary',
			'read-more-text'   => __( 'Read More', 'thrive-cb' ),
			'image-height'     => '',
			'font-size'        => '', //Backwards Compatibility: Title Font Size
			'text-line-height' => '', //Backwards Compatibility: Title Line Height
			'teaser_layout'    => array(
				'featured_image' => 'true',
				'title'          => 'true',
				'text'           => 'true',
				'read_more'      => 'true',
			),
			'layout'           => array(
				'featured_image',
				'title',
				'text',
				'read_more',
			),
			'orderby'          => 'date',
			'order'            => 'DESC',
			'recent_days'      => '0',
			'posts_start'      => '0',
			'posts_per_page'   => '6',
			'content_types'    => array( 'post' ),
			'filters'          => array(
				'category' => array(),
				'tag'      => array(),
				'tax'      => array(),
				'author'   => array(),
				'posts'    => array(),
			),
		);

		/**
		 * Backwards compatible $config['post_types'].
		 * This can be removed after users update a while when users update their post grids and the post_types variable is removed from the config array
		 */
		if ( ! empty( $config['post_types'] ) && is_array( $config['post_types'] ) && empty( $config['content_types'] ) ) {
			$config['content_types'] = array();
			foreach ( $config['post_types'] as $type => $checked ) {
				if ( $checked === 'true' ) {
					$config['content_types'][] = $type;
				}
			}
		}

		$this->_config = array_merge( $defaults, $config );
	}

	/**
	 * Render Functions
	 * Goes through all posts and builds the HTML code
	 *
	 * @return string
	 */
	public function render() {
		$posts = $this->_get_post_grid_posts();

		$count         = count( $posts );
		$index         = 1;
		$extra_classes = 'tve_post_grid_' . $this->_config['display'];

		if ( $this->_config['grid_layout'] === 'vertical' ) {
			$extra_classes .= ' tve_post_grid_vertical';
		}

		$html = '';
		if ( $this->output_shortcode_config ) {
			$html .= $this->_get_shortcode_config();
		}
		$html .= '<div class="tve_post_grid_wrapper tve_clearfix ' . $extra_classes . '">';

		if ( $count === 0 ) {
			$html .= __( 'No results have been returned for your Query. Please edit the query for content to display.', 'thrive-cb' );
		}

		foreach ( $posts as $key => $post ) {
			$html .= tcb_template( $this->_template, array( 'cls' => $this, 'index' => $index, 'post' => $post, 'count' => $count ), true );
			$index ++;
		}


		$html .= '</div>';

		return $html;
	}

	/**
	 * Outputs the config shortcode.
	 *
	 * @return string
	 */
	private function _get_shortcode_config() {
		$encoded_config = tve_json_utf8_unslashit( json_encode( $this->_config ) );

		return '<div class="thrive-shortcode-config" style="display: none !important">' . $this->_cfg_code . $encoded_config . $this->_cfg_code . '</div>';
	}

	/**
	 * Applies the settings and returns an array with all posts with the corresponding settings
	 *
	 * @return array
	 */
	private function _get_post_grid_posts() {

		if ( empty( $this->_config['exclude'] ) ) {
			$this->_config['exclude'] = 0;
		}

		$types = empty( $this->_config['content_types'] ) ? 'any' : $this->_config['content_types'];
		$args  = array(
			'post_type'      => $types,
			'offset'         => $this->_config['posts_start'],
			'posts_per_page' => intval( $this->_config['posts_per_page'] ) == 0 ? - 1 : $this->_config['posts_per_page'],
			'order'          => $this->_config['order'],
			'orderby'        => $this->_config['orderby'],
			'post_status'    => 'publish',
			'post__not_in'   => array( $this->_config['exclude'] ),
		);

		if ( ! empty( $this->_config['filters']['category'] ) ) {
			//Backwards compatibility:
			if ( is_string( $this->_config['filters']['category'] ) ) {
				$this->_config['filters']['category'] = explode( ',', $this->_config['filters']['category'] );
			}
			$args['tax_query'] = array(
				'relation' => 'AND',
				array(
					'relation' => 'OR',
					array(
						'taxonomy' => 'category',
						'field'    => 'name',
						'terms'    => $this->_config['filters']['category'],
						'operator' => 'IN',
					),
					array(
						'taxonomy' => 'apprentice',
						'field'    => 'name',
						'terms'    => $this->_config['filters']['category'],
						'operator' => 'IN',
					)
				)
			);
		}

		if ( ! empty( $this->_config['filters']['tag'] ) ) {
			//Backwards compatibility:
			if ( is_string( $this->_config['filters']['tag'] ) ) {
				$tags                            = explode( ',', trim( $this->_config['filters']['tag'], ',' ) );
				$tags                            = empty( $tags ) ? array() : $tags;
				$this->_config['filters']['tag'] = array_unique( $tags );
			}
			$query_tags = array(
				'relation' => 'OR',
				array(
					'taxonomy' => 'post_tag',
					'field'    => 'name',
					'terms'    => $this->_config['filters']['tag'],
					'operator' => 'IN',
				),
				array(
					'taxonomy' => 'apprentice-tag',
					'field'    => 'name',
					'terms'    => $this->_config['filters']['tag'],
					'operator' => 'IN',
				)
			);
			if ( ! empty( $this->_config['filters']['category'] ) ) {
				$args['tax_query'][] = $query_tags;
			} else {
				$args['tax_query'] = array(
					'relation' => 'AND',
					$query_tags,
				);
			}
		}

		if ( ! empty( $this->_config['filters']['tax'] ) ) {
			//Backwards compatibility:
			if ( is_string( $this->_config['filters']['tax'] ) ) {
				$tax_parts                       = explode( ',', trim( $this->_config['filters']['tax'], ',' ) );
				$tax_parts                       = empty( $tax_parts ) ? array() : $tax_parts;
				$this->_config['filters']['tax'] = array_unique( $tax_parts );
			}
			$tax_names = $this->_config['filters']['tax']; //array_unique( $tax_names );
			$tax_query = array();
			//foreach taxonomy name get all its terms and build tax_query for it
			foreach ( $tax_names as $tax_name ) {
				$terms_obj = get_terms( $tax_name );
				if ( empty( $terms_obj ) || $terms_obj instanceof WP_Error ) {
					continue;
				}
				$tax_terms = array();
				foreach ( $terms_obj as $term ) {
					$tax_terms[] = $term->slug;
				}
				$tax_query[] = array(
					'taxonomy' => $tax_name,
					'field'    => 'slug',
					'terms'    => $tax_terms,
				);
			}
			if ( ! empty( $tax_query ) ) {
				$tax_query['relation'] = 'OR';
				$args['tax_query']     = $tax_query;
			}
		}

		if ( ! empty( $this->_config['filters']['author'] ) ) {
			//Backwards compatibility:
			if ( is_string( $this->_config['filters']['author'] ) ) {
				$this->_config['filters']['author'] = array_unique( explode( ',', trim( $this->_config['filters']['author'], ',' ) ) );
			}
			$author_names = $this->_config['filters']['author']; //array_unique( $author_names );
			$author_ids   = array();
			foreach ( $author_names as $name ) {
				$author = get_user_by( 'slug', $name );
				if ( $author ) {
					$author_ids[] = $author->ID;
				}
			}
			if ( ! empty( $author_ids ) ) {
				$args['author'] = implode( ',', $author_ids );
			}
		}

		if ( ! empty( $this->_config['filters']['posts'] ) ) {
			if ( is_string( $this->_config['filters']['posts'] ) ) { //Backwards Compatibility
				$post_ids = array_unique( explode( ',', $this->_config['filters']['posts'] ) );
			} else {
				$post_ids = wp_list_pluck( array_filter( $this->_config['filters']['posts'] ), 'id' ); //array_unique( $post_ids );
			}

			$args['post__in'] = $post_ids;
		}

		if ( ! empty( $this->_config['recent_days'] ) ) {
			$args['date_query'] = array(
				'after' => date( 'Y-m-d', strtotime( '-' . intval( $this->_config['recent_days'] ) . ' days', strtotime( date( 'Y-m-d' ) ) ) ),
			);
		}

		$args['ignore_sticky_posts'] = 1;
		remove_filter( 'pre_get_posts', 'thrive_exclude_category' );
		$results = new WP_Query( $args );

		return $results->posts;
	}

	/**
	 * Returns the post content by checking if post option to be displayed exists: featured image, text, read more, title
	 *
	 * @param $post
	 *
	 * @return string
	 */
	public function get_post_content( $post ) {
		$html = '';
		if ( ! in_array( 'read_more', $this->_config['layout'] ) ) {
			$this->_config['layout'][] = 'read_more';
		}

		foreach ( $this->_config['layout'] as $layout ) {
			if ( ! empty( $this->_config['teaser_layout'][ $layout ] ) && $this->_config['teaser_layout'][ $layout ] === 'true' ) {
				$function_name = '_display_post_' . $layout;
				$html .= call_user_func( array( $this, $function_name ), $post );
			}
		}

		return $html;
	}

	/**
	 * Displays the post featured image
	 * Used in get_post_content method
	 *
	 * @param $post
	 *
	 * @return string
	 */
	private function _display_post_featured_image( $post ) {
		if ( ! has_post_thumbnail( $post->ID ) ) {
			return '';
		}

		$src    = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );
		$height = ! empty( $this->_config['image-height'] ) ? "height: {$this->_config['image-height']}px" : '';

		return '<a href="' . get_permalink( $post ) . '"><div class="tve_post_grid_image_wrapper" style="background-image: url(' . $src . ' ); ' . $height . '"><div class="tve_pg_img_overlay"><span class="thrv-icon thrv-icon-forward"></span></div></div></a>';

	}

	/**
	 * Display the post text
	 * Used in get_post_content method
	 *
	 * @param $post
	 *
	 * @return string
	 */
	private function _display_post_text( $post ) {
		//get whole the content
		$content = $post->post_content;

		//strip all the shortcodes from the content
		$content = strip_shortcodes( $content );


		if ( $this->_config['text_type'] === 'summary' ) {

			$content = $this->_get_summary_text( $content );

		} elseif ( $this->_config['text_type'] === 'excerpt' ) {

			$content = empty( $post->post_excerpt ) ? $this->_get_summary_text( $content ) : $post->post_excerpt;

		} elseif ( $this->_config['text_type'] === 'fulltext' ) {
			$content = preg_replace( '#<a href="javascript:(.+?)</a>#', '', $content );
			$content = preg_replace( '#<span class="tve_s_cnt">(.+?)</span> shares#', '', $content );
			$content = strip_tags( $content, '<p><h1><h2><h3><h4><h5><h6><a><strong><b>' );
			// Remove breaks (new line characters)
			$content = trim( preg_replace( '/[\r\n\t ]+/', ' ', $content ) );

		}

		if ( empty( $content ) ) {
			return '';
		}

		return '<div class="tve-post-grid-text">' . $content . '</div>';
	}

	/**
	 * Displays post title
	 * Used in get_post_content method
	 *
	 * @param $post
	 *
	 * @return string
	 */
	private function _display_post_title( $post ) {
		$title_font_size   = ! empty( $this->_config['font-size'] ) ? 'font-size: ' . $this->_config['font-size'] . 'px;' : '';
		$title_line_height = ! empty( $this->_config['text-line-height'] ) ? 'line-height: ' . $this->_config['text-line-height'] . ';' : '';
		$title_style       = '';
		if ( ! empty( $title_font_size ) || ! empty( $title_line_height ) ) {
			$title_style = 'style="' . $title_font_size . $title_line_height . '"';
		}

		return '<span class="tve-post-grid-title" ' . $title_style . '><a href="' . get_permalink( $post ) . '">' . get_the_title( $post->ID ) . '</a></span>';
	}

	/**
	 * Displays post read more text
	 * Used in get_post_content method
	 *
	 * @param $post
	 *
	 * @return string
	 */
	private function _display_post_read_more( $post ) {
		return '<div class="tve_pg_more"><a href="' . get_permalink( $post ) . '">' . $this->_config['read-more-text'] . '</a>&nbsp;<span class="thrv-icon thrv-icon-uniE602"></span></div>';
	}

	private function _get_summary_text( $text ) {
		$text = preg_replace( '#<a href="javascript:(.+?)</a>#', '', $text );
		$text = preg_replace( '#<span class="tve_s_cnt">(.+?)</span> shares#', '', $text );
		$text = wp_strip_all_tags( $text, true );
		$text = wp_trim_words( $text, 20, '&#91;...&#93;' );

		return $text;
	}
}
