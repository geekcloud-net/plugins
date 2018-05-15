<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TD_NM_Action_Custom_Script extends TD_NM_Action_Abstract {

	public function execute( $prepared_data ) {

		$url = $this->settings['url'];

		wp_remote_post( $url, array(
			'body' => $prepared_data
		) );
	}

	public function prepare_email_sign_up_data( $sign_up_data ) {
		$data = array();

		$tl_item = $sign_up_data[0];
		$tl_form = $sign_up_data[2];
		$tl_data = $sign_up_data[4];

		$data['thrv_event']       = 'thrv_signup';
		$data['source']           = $tl_item->post_type;
		$data['source_name']      = $tl_item->post_title;
		$data['source_id']        = $tl_item->ID;
		$data['source_form_name'] = $tl_form['post_title'];
		$data['source_form_id']   = $tl_form['key'];
		$data['user_email']       = $tl_data['email'];
		$data['user_custom_data'] = $tl_data['custom_fields'];

		return $data;
	}

	public function prepare_split_test_ends_data( $split_test_ends_data ) {
		$data = array();

		$test_item = $split_test_ends_data[0];
		$test      = $split_test_ends_data[1];

		$data['thrv_event']             = 'split_test';
		$data['test_id']                = $test->id;
		$data['test_url']               = $test->url;
		$data['winning_variation_name'] = $test_item->variation['post_title'];
		$data['winning_variation_id']   = $test_item->variation['key'];

		return $data;
	}

	public function prepare_testimonial_submitted_data( $testimonial_data ) {
		$data = array();

		$testimonial = $testimonial_data[0];
		$extra_data  = $testimonial_data[1];

		$data['id']          = $testimonial['id'];
		$data['title']       = $testimonial['title'];
		$data['date']        = $testimonial['date'];
		$data['content']     = $testimonial['summary'];
		$data['role']        = $testimonial['role'];
		$data['name']        = $testimonial['name'];
		$data['email']       = $testimonial['email'];
		$data['website_url'] = $testimonial['website_url'];
		$data['picture_url'] = $testimonial['picture_url'];
		if ( ! empty( $testimonial['tags'] ) && is_array( $testimonial['tags'] ) ) {
			$tags_text_arr = array();
			foreach ( $testimonial['tags'] as $tag ) {
				$tags_text_arr[] = $tag['text'];
			}
			$data['tags'] = implode( ',', $tags_text_arr );
		}

		return $data;
	}

	public function prepare_quiz_completion_data( $data ) {

		$quiz = $data[0];
		$user = $data[1];

		$data = array(
			'quiz'          => array(
				'Name' => $quiz->post_title
			),
			'quiz_user'     => array(
				'result'       => $user['points'],
				'email'        => ! empty( $user['email'] ) ? $user['email'] : __( 'unknown', TVE_DASH_TRANSLATE_DOMAIN ),
				'date_started' => $user['date_started'],
			),
			'original_data' => $data,
		);

		$data = apply_filters( 'td_nm_custom_script_quiz_completion', $data );

		if ( ! is_array( $data ) ) {
			$data = array();
		}

		$return = array(
			'quiz' => ! empty( $data['quiz'] ) ? $data['quiz'] : null,
			'user' => ! empty( $data['quiz_user'] ) ? $data['quiz_user'] : null,
		);

		return $return;
	}
}
