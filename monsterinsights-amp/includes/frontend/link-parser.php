<?php
if ( ! defined( 'AMP__DIR__' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}
require_once ( AMP__DIR__ . '/includes/sanitizers/class-amp-base-sanitizer.php' );

class MonsterInsights_AMP_Parser extends AMP_Base_Sanitizer {
	/**
	 * The actual sanitization function
	 */
	public function sanitize() {
		// What should we track downloads as?
		$track_download_as = monsterinsights_get_option( 'track_download_as', '' );
		$track_download_as = $track_download_as === 'pageview' ? 'pageview' : 'event';

		// What label should be used for internal links?
		$internal_label = monsterinsights_get_option( 'track_internal_as_label', 'int' );
		if ( ! empty( $internal_label ) && is_string( $internal_label ) ) {
			$internal_label = trim( $internal_label, ',' );
			$internal_label = trim( $internal_label );
		}

		// If the label is empty, set a default value
		if ( empty( $internal_label ) ) {
			$internal_label = 'int';
		}

		$internal_label = esc_js( $internal_label );

		// Get inbound as outbound to track
		$inbound_paths = monsterinsights_get_option( 'track_internal_as_outbound','' );
		$inbound_paths = explode( ',', $inbound_paths );
		if ( ! is_array( $inbound_paths ) ) {
			$inbound_paths = array( $inbound_paths );
		}
		$i = 0;
		foreach ( $inbound_paths as $path ){
			$inbound_paths[ $i ] = esc_js( trim( $path ) );
			$i++;
		}

		// Get download extensions to track
		$download_extensions = monsterinsights_get_option( 'extensions_of_files', '' );
		$download_extensions = explode( ',', str_replace( '.', '', $download_extensions ) );
		if ( ! is_array( $download_extensions ) ) {
			$download_extensions = array( $download_extensions );
		}
		$i = 0;
		foreach( $download_extensions as $extension ){
			$download_extensions[ $i ] = esc_js( trim( $extension ) );
			$i++;
		}

		$this->options    = array(
			'extensions'                 => $download_extensions,
			'internal_as_outbound'       => $inbound_paths,
			'track_download_as'          => $track_download_as,
			'internal_as_outbound_label' => $internal_label,
		);

		$body = $this->get_body_node();
		$this->parse_nodes_recursive( $body );
	}
	/**
	 * Passes through the DOM and removes stuff that shouldn't be there.
	 *
	 * @param DOMNode $node
	 */
	private function parse_nodes_recursive( $node ) {
		if ( $node->nodeType !== XML_ELEMENT_NODE ) {
			return;
		}
		if (  $node->nodeName === 'a' ) {
			$node_name = $node->nodeName;
			$this->parse_href( $node );
		}
		foreach ( $node->childNodes as $child_node ) {
			$this->parse_nodes_recursive( $child_node );
		}
	}

	/**
	 * Sanitizes anchor attributes
	 *
	 * @param DOMNode $node
	 * @param object $attribute
	 */
	private function parse_href( $node ) {
		$href  = $node->getAttribute('href');
		$hrefe = esc_attr( $href );

		// if has Javascript in link
		if ( substr( $href, 0, strlen( 'javascript:' ) ) === 'javascript:' ) {
			return;
		}

		$title = esc_attr( $node->getAttribute('title') );
		$class = esc_attr( $node->getAttribute('class') );
		if ( !empty( $class ) ) {
			$class = $class . ' ';
		}

		if ( empty( $title ) ) {
			$title = esc_attr( $node->nodeValue );
		}

		// if tel
		if ( substr( $href, 0, strlen( 'tel:' ) ) === 'tel:' ) {
			$node->setAttribute( 'class', $class . 'monsterinsights-tel' );
			$node->setAttribute( 'data-vars-category', 'tel' ); // type of link
			$node->setAttribute( 'data-vars-action', $hrefe );  // href
			$node->setAttribute( 'data-vars-label', $title ); // Link text
			return;
		}

		// if mailto
		if ( substr( $href, 0, strlen( 'mailto:' ) ) === 'mailto:' ) {
			$node->setAttribute( 'class', $class . 'monsterinsights-mailto' );
			$node->setAttribute( 'data-vars-category', 'mailto' ); // type of link
			$node->setAttribute( 'data-vars-action', $hrefe );  // href
			$node->setAttribute( 'data-vars-label', $title ); // Link text
			return;
		}

		$url  = wp_parse_url( $href );

		// if download
		if ( ! empty( $url['path'] ) && ! empty( $this->options['extensions'] ) && ! empty( $this->options['track_download_as'] ) ) {
			foreach ( $this->options['extensions'] as $extension ) {
				if ( monsterinsights_string_ends_with( $url['path'], $extension ) ) {
					if ( $this->options['track_download_as'] === 'pageview' ) {
						$node->setAttribute( 'class', $class . 'monsterinsights-download' );
						$node->setAttribute( 'data-vars-page', $hrefe );  // href
					} else {
						$node->setAttribute( 'class', $class . 'monsterinsights-download' );
						$node->setAttribute( 'data-vars-category', 'download' ); // type of link
						$node->setAttribute( 'data-vars-action', $hrefe );  // href
						$node->setAttribute( 'data-vars-label', $title ); // Link text
					}
					return;
				}
			}
		}

		// if external
		$current_url = home_url();
		$current_url = str_replace( 'www.', '', $current_url );
		if ( ! monsterinsights_string_ends_with( $url['host'], $current_url ) ) {
			$node->setAttribute( 'class', $class . 'monsterinsights-outbound-link' );
			$node->setAttribute( 'data-vars-category', 'outbound-link' ); // type of link
			$node->setAttribute( 'data-vars-action', $hrefe );  // href
			$node->setAttribute( 'data-vars-label', $title ); // Link text
			return;
		}

		// if internal as outbound
		if ( ! empty( $this->options['internal_as_outbound'] ) && ! empty( $this->options['internal_as_outbound_label'] ) ) {
			foreach( $this->options['internal_as_outbound'] as $path ) {
				if ( monsterinsights_string_starts_with( $url['path'], $path ) ) {
					$node->setAttribute( 'class', $class . 'monsterinsights-internal-as-outbound' );
					$node->setAttribute( 'data-vars-category', $this->options['internal_as_outbound_label'] ); // type of link
					$node->setAttribute( 'data-vars-action', $hrefe );  // href
					$node->setAttribute( 'data-vars-label', $title ); // Link text
					return;
				}
			} 
		}
	}
}