<?php

abstract class QC_SourceControl {
	public $id;
	public $admin_page;

	protected $const_name;
	protected $request;

	protected $url_form = 'https://{{auth}}@{{account}}.beanstalkapp.com/api/changesets/repository.xml?repository_id={{repo}}';

	abstract function get_title();

	abstract function store_changesets( $data, $settings );


	function __construct() {
		global $blog_id;

		if ( is_multisite() && 1 != $blog_id ) {
			$this->const_name .= '_' . $blog_id;
		}
	}


	function init_tabs( $admin_page ) {
		$this->admin_page = $admin_page;

		$admin_page->tabs->add( $this->id, $this->get_title() );

		$admin_page->tab_sections[ $this->id ]['url'] = array(
			'renderer' => array( $this, 'render_repo_url' ),
			'fields' => array(
				array(
					'name' => array( 'repository', 'details', $this->id, 'account' ),
					'type' => 'text',
					'sanitize' => 'appthemes_clean',
					'extra' => array( 'style' => 'width: 10em', 'placeholder' => __( 'account', APP_TD ) )
				),
				array(
					'name' => array( 'repository', 'details', $this->id, 'repo' ),
					'type' => 'text',
					'sanitize' => 'appthemes_clean',
					'extra' => array( 'style' => 'width: 10em', 'placeholder' => __( 'repository', APP_TD ) )
				),
			),
		);

		$admin_page->tab_sections[ $this->id ]['user'] = array(
			'fields' => array(
				array(
					'title' => sprintf( __( '%s User', APP_TD ), $this->get_title() ),
					'type' => 'text',
					'sanitize' => 'appthemes_clean',
					'name' => array( 'repository', 'details', $this->id, 'user' ),
					'extra' => array( 'style' => 'width: 10em' ),
				),
			),
		);

		if ( defined( $this->const_name ) && '' != constant( $this->const_name ) ) {
			$admin_page->tab_sections[ $this->id ]['pass'] = array(
				'fields' => array(
					array(
						'title' => sprintf( __( '%s Password', APP_TD ), $this->get_title() ),
						'type' => 'text',
						'name' => array( 'repository', 'details', $this->id, 'pass' ),
						'extra' => array( 'style' => 'display: none' ),
						'desc' => sprintf( __( 'Password is set in <code>wp-config.php</code>: %s', APP_TD ), html( 'code', $this->const_name ) ),

					),
				),
			);
		} else {
			$admin_page->tab_sections[ $this->id ]['pass'] = array(
				'fields' => array(
					array(
						'title' => sprintf( __( '%s Password', APP_TD ), $this->get_title() ),
						'type' => 'text',
						'name' => array( 'repository', 'details', $this->id, 'pass' ),
						'extra' => array( 'style' => 'width: 35em', 'readonly' => 'readonly', 'class' => 'code' ),
						'value' => "define( '" . $this->const_name . "', 'your password' );",
						'desc' => '<br />' . __( 'Please set the password in your <code>wp-config.php</code> file.', APP_TD ),
					),
				),
			);
		}

	}


	function render_repo_url( $section, $section_id ) {
		global $qc_options;

		$fields = array();
		foreach ( $section['fields'] as $field ) {
			$key = end( $field['name'] );
			$fields[ $key ] = scbForms::input( $field, $qc_options->get() );
		}

		$fields_html = self::substitute( $this->url_form, $fields );
		// Remove unnecessary label wrapper
		$fields_html = str_replace( array( '<label>', '</label>' ), '', $fields_html );

		$example_url = self::substitute( $this->url_form, array(
			'account' => 'scribu',
			'repo' => 'wp-posts-to-posts',
		) );
		$example_url = '<br />' . html( 'span class="description"', sprintf( __( 'Example: %s', APP_TD ), $example_url ) );

		$output = html( "tr",
			html( "th scope='row'", __( 'Repository URL', APP_TD ) ),
			html( "td class='tip'", '' ),
			html( "td", $fields_html . $example_url )
		);

		echo $this->admin_page->table_wrap( $output );
	}


	// Poor-man's Mustache
	private static function substitute( $template, $data ) {
		foreach ( $data as $key => $value ) {
			$template = str_replace( '{{' . $key . '}}', $value, $template );
		}

		return $template;
	}


	function import_changesets( $settings ) {
		if ( ! defined( $this->const_name ) || constant( $this->const_name ) == '' )
			return;

		$data = array(
			'{{auth}}' => $settings['user'] . ':' . constant( $this->const_name ),
			'{{account}}' => $settings['account'],
			'{{repo}}' => $settings['repo']
		);

		$changesets_url = str_replace( array_keys( $data ), array_values( $data ), $this->request );

		$r = wp_remote_get( $changesets_url, array( 'sslverify' => false ) );
		if ( is_wp_error( $r ) )
			return $r;

		if ( 200 != $r['response']['code'] )
			return new WP_Error( 'remote_error', $r['response']['message'] );

		return $this->store_changesets( $r['body'], $settings );
	}


}


class QC_Beanstalk extends QC_SourceControl {
	public $id = 'beanstalk';

	protected $const_name = 'QC_BEANSTALK_PASS';

	protected $request = 'https://{{auth}}@{{account}}.beanstalkapp.com/api/changesets/repository.xml?repository_id={{repo}}';

	protected $url_form = 'https://{{account}}.beanstalkapp.com/{{repo}}';

	function get_title() {
		return __( 'Beanstalk', APP_TD );
	}

	function store_changesets( $xmlstr, $settings ) {
		global $wpdb;

		libxml_use_internal_errors( true );
		try {
			$xml = new SimpleXMLElement( $xmlstr );
		} catch (Exception $e) {
			return new WP_Error( 'xml_error', $e->getMessage() );
		}

		$count = 0;
		foreach ( $xml->{'revision-cache'} as $changeset ) {
			$changeset_url = 'https://' . $settings['account'] . '.beanstalkapp.com/' . $settings['repo'] . '/changesets/' . $changeset->revision;

			// Check for duplicates
			if ( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid = %s", $changeset_url ) ) )
				continue;

			$post_data = array(
				'post_type' => QC_CHANGESET_PTYPE,
				'guid' => $changeset_url,
				'post_title' => (string) $changeset->revision,
				'post_excerpt' => (string) $changeset->message,
				'post_date' => date( 'Y-m-d H:i:s', strtotime( $changeset->time ) ),
				'post_status' => 'publish'
			);

			$author = get_user_by( 'email', (string) $changeset->email );
			if ( $author )
				$post_data['post_author'] = $author->ID;

			$post_id = wp_insert_post( $post_data, true );
			if ( is_wp_error( $post_id ) )
				continue;

			foreach ( array( 'author', 'email', 'changed-files', 'changed-dirs' ) as $key ) {
				$value = (string) $changeset->$key;
				if ( !empty( $value ) )
					add_post_meta( $post_id, $key, $value );
			}

			$count++;
		}

		return $count;
	}


}


class QC_Github extends QC_SourceControl {
	public $id = 'github';

	protected $const_name = 'QC_GITHUB_PASS';

	protected $request = 'https://{{auth}}@api.github.com/repos/{{account}}/{{repo}}/commits';

	protected $url_form = 'http://github.com/{{account}}/{{repo}}';

	function get_title() {
		return __( 'Github', APP_TD );
	}

	function store_changesets( $jsonstr, $settings ) {
		global $wpdb;

		$data = json_decode( $jsonstr );	// TODO: handle parse errors

		$count = 0;
		foreach ( $data as $push ) {
			$changeset_url = "https://github.com/{$settings['account']}/{$settings['repo']}/commit/{$push->sha}";

			// Check for duplicates
			if ( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid = %s", $changeset_url ) ) )
				continue;

			$post_data = array(
				'post_type' => QC_CHANGESET_PTYPE,
				'guid' => $changeset_url,
				'post_title' => $push->sha,
				'post_excerpt' => $push->commit->message,
				'post_date' => date( 'Y-m-d H:i:s', strtotime( $push->commit->author->date ) ),
				'post_status' => 'publish'
			);

			$author = get_user_by( 'name', $push->commit->author->name );
			if ( $author )
				$post_data['post_author'] = $author->ID;

			$post_id = wp_insert_post( $post_data, true );
			if ( is_wp_error( $post_id ) )
				continue;

			add_post_meta( $post_id, 'author', $push->commit->author->name );

			$count++;
		}

		return $count;
	}


}


class QC_Bitbucket extends QC_SourceControl {
	public $id = 'bitbucket';

	protected $const_name = 'QC_BITBUCKET_PASS';

	protected $request = 'https://{{auth}}@api.bitbucket.org/1.0/repositories/{{account}}/{{repo}}/changesets';

	protected $url_form = 'http://bitbucket.org/{{account}}/{{repo}}';

	function get_title() {
		return __( 'Bitbucket', APP_TD );
	}

	function store_changesets( $jsonstr, $settings ) {
		global $wpdb;

		$data = json_decode( $jsonstr );

		$count = 0;
		foreach ( $data as $key => $value ) {
			if ( $key != 'changesets' ) {
				continue;
			}

			foreach ( $value as $push ) {
				$changeset_url = "https://bitbucket.org/{$settings['account']}/{$settings['repo']}/changeset/{$push->node}";

				// Check for duplicates
				if ( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid = %s", $changeset_url ) ) ) {
					continue;
				}

				$post_data = array(
					'post_type' => QC_CHANGESET_PTYPE,
					'guid' => $changeset_url,
					'post_title' => $push->node,
					'post_excerpt' => $push->message,
					'post_date' => date( 'Y-m-d H:i:s', strtotime( $push->utctimestamp ) + ( get_option( 'gmt_offset' ) * 3600 ) ),
					'post_date_gmt' => date( 'Y-m-d H:i:s', strtotime( $push->utctimestamp ) ),
					'post_status' => 'publish',
				);

				$author = get_user_by( 'name', $push->author );
				if ( $author ) {
					$post_data['post_author'] = $author->ID;
				}

				$post_id = wp_insert_post( $post_data, true );
				if ( is_wp_error( $post_id ) ) {
					continue;
				}

				add_post_meta( $post_id, 'author', $push->author );

				$count++;
			}
		}

		return $count;
	}


}

