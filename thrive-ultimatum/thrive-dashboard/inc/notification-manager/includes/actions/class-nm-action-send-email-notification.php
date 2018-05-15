<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TD_NM_Action_Send_Email_Notification extends TD_NM_Action_Abstract {

	public function execute( $prepared_data ) {

		$connection = $this->get_service();

		if ( ! $connection ) {
			return;
		}

		$email_data = array(
			'emails'       => $this->get_emails(),
			'subject'      => $this->get_subject( $prepared_data ),
			'text_content' => $this->get_text_content( $prepared_data ),
			'html_content' => $this->get_html_content( $prepared_data ),
		);

		try {
			$connection->sendMultipleEmails( $email_data );
		} catch ( Exception $e ) {
			global $wpdb;

			/**
			 * at this point, we need to log the error in a DB table, so that the user can see all these error later on and (maybe) re-subscribe the user
			 */
			$log_data = array(
				'date'          => date( 'Y-m-d H:i:s' ),
				'error_message' => $e->getMessage(),
				'api_data'      => serialize( $prepared_data ),
				'connection'    => $connection->getKey(),
				'list_id'       => 'asset'
			);

			$wpdb->insert( $wpdb->prefix . 'tcb_api_error_log', $log_data );
		}
	}

	public function prepare_email_sign_up_data( $sign_up_data ) {
		$data = array();

		$tl_item = $sign_up_data[0];
		$tl_form = $sign_up_data[2];
		$tl_data = $sign_up_data[4];

		$lead_details['source']           = $tl_item->post_type;
		$lead_details['source_name']      = $tl_item->post_title;
		$lead_details['source_id']        = $tl_item->ID;
		$lead_details['source_form_name'] = $tl_form['post_title'];
		$lead_details['source_form_id']   = $tl_form['key'];
		/*Send also the custom fields to the lead_detail shortcode*/
		foreach ( $tl_data['custom_fields'] as $key => $value ) {
			$lead_details[ 'custom_' . $key ] = $value;
		}


		$data['lead_details'] = $lead_details;
		$data['lead_email']   = $tl_data['email'];

		return $data;
	}

	public function prepare_split_test_ends_data( $split_test_ends_data ) {
		$data = array();

		$test_item = $split_test_ends_data[0];
		$test      = $split_test_ends_data[1];

		$test_details['thrv_event']             = 'split_test';
		$test_details['test_id']                = $test->id;
		$test_details['test_url']               = $test->url;
		$test_details['winning_variation_name'] = $test_item->variation['post_title'];
		$test_details['winning_variation_id']   = $test_item->variation['key'];

		$data['test_details'] = $test_details;
		$data['test_link']    = $test->url;

		return $data;
	}

	public function prepare_testimonial_submitted_data( $testimonial_data ) {
		$data = array();

		$testimonial = $testimonial_data[0];
		unset( $testimonial['id'] );
		unset( $testimonial['content'] );
		unset( $testimonial['status'] );
		unset( $testimonial['source'] );

		foreach ( $testimonial as $key => $value ) {
			if ( ! empty( $value ) ) {
				if ( is_string( $value ) ) {
					$testimonial_details[ $key ] = $value;
				} elseif ( is_array( $value ) ) {
					$aux = array();
					foreach ( $value as $val ) {
						$aux[] = ( ! empty( $val['text'] ) ) ? $val['text'] : '';
					}
					$testimonial_details[ $key ] = implode( ',', $aux );
				}
			}
		}

		$data['lead_details'] = $testimonial_details;

		return $data;
	}

	/**
	 * @return bool|Thrive_Dash_List_Connection_Abstract
	 */
	public function get_service() {
		$connection = get_option( 'tvd-nm-email-service', false );

		if ( ! $connection ) {
			return false;
		}

		return Thrive_Dash_List_Manager::connectionInstance( $connection );
	}

	/**
	 * returns an array of emails from settings->recipients
	 *
	 * @return array
	 */
	public function get_emails() {

		$emails = array();

		foreach ( $this->settings['recipients'] as $item ) {
			$emails[] = $item['value'];
		}

		return $emails;
	}

	public function get_html_content( $prepared_data ) {
		$content = $this->settings['message']['content'];

		$item_details = "<br>\n";
		if ( isset( $prepared_data['lead_details'] ) ) {
			foreach ( $prepared_data['lead_details'] as $key => $value ) {
				$item_details .= "<strong>{$key}</strong>: {$value}<br>\n";
			}
		}

		if ( isset( $prepared_data['quiz'] ) ) {
			$item_details .= "<br>\n<strong style='font-size: 14px'>" . __( 'Quiz Details', TVE_DASH_TRANSLATE_DOMAIN ) . "</strong><br>\n";
			foreach ( $prepared_data['quiz'] as $key => $value ) {
				$item_details .= "<p style='font-size: 12px'><strong>{$key}</strong>: <span style='color: #555555'>{$value}</span></p>\n";
			}
		}

		if ( isset( $prepared_data['quiz_user'] ) ) {
			$item_details .= "<br>\n<strong style='font-size: 14px'>" . __( 'User Details', TVE_DASH_TRANSLATE_DOMAIN ) . "</strong><br>\n";
			foreach ( $prepared_data['quiz_user'] as $key => $value ) {
				$item_details .= "<p style='font-size: 12px'><strong>{$key}</strong>: <span style='color: #555555'>{$value}</span></p>\n";
			}
		}

		$lead_email = '';
		if ( isset( $prepared_data['lead_email'] ) ) {
			$lead_email .= "email: " . $prepared_data['lead_email'];
		}

		if ( stripos( $content, '[lead_email]' ) !== false ) {
			$content = str_replace( '[lead_email]', $lead_email, $content );
		}

		$keywords = array( '[lead_details]', '[testimonial_details]', '[quiz_details]' );
		foreach ( $keywords as $keyword ) {
			if ( stripos( $content, $keyword ) !== false ) {
				$content = str_replace( $keyword, $item_details, $content );
			}
		}

		preg_match( '@\[test_link\](.+)\[/test_link\]@', $content, $matches );
		if ( ! empty( $matches ) ) {
			$content = str_replace( $matches[0], '<a href="' . $prepared_data["test_link"] . '">' . $matches[1] . '</a>', $content );
		}

		return $content;
	}

	public function get_text_content( $prepared_data ) {
		$content = $this->settings['message']['content'];

		$item_details = "\n";
		if ( isset( $prepared_data['lead_details'] ) ) {
			foreach ( $prepared_data['lead_details'] as $key => $value ) {
				$item_details .= "{$key}: {$value}\n";
			}
		}

		if ( isset( $prepared_data['quiz'] ) ) {
			$item_details .= "\nQuiz Details:\n";
			foreach ( $prepared_data['quiz'] as $key => $value ) {
				$item_details .= "{$key}: {$value}\n";
			}
		}

		if ( isset( $prepared_data['quiz_user'] ) ) {
			$item_details .= "\nUser Details:\n";
			foreach ( $prepared_data['quiz_user'] as $key => $value ) {
				$item_details .= "{$key}: {$value}\n";
			}
		}

		$lead_email = '';
		if ( isset( $prepared_data['lead_email'] ) ) {
			$lead_email .= "email: " . $prepared_data['lead_email'];
		}

		if ( stripos( $content, '[lead_details]' ) !== false ) {
			$content = str_replace( '[lead_details]', $item_details, $content );
		}

		if ( stripos( $content, '[lead_email]' ) !== false ) {
			$content = str_replace( '[lead_email]', $lead_email, $content );
		}

		preg_match( '@\[test_link\](.+)\[/test_link\]@', $content, $matches );
		if ( ! empty( $matches ) ) {
			$content = str_replace( $matches[0], $prepared_data["test_link"], $content );
		}

		return $content;
	}

	public function get_subject( $prepared_data ) {
		$subject = $this->settings['message']['subject'];

		if ( stristr( $subject, '[lead_email]' ) !== false ) {
			$subject = str_replace( '[lead_email]', $prepared_data['lead_email'], $subject );
		}

		return $subject;
	}

	public function prepare_quiz_completion_data( $data ) {

		$quiz = $data[0];
		$user = $data[1];

		$data = array(
			'quiz'          => array(
				'Name' => $quiz->post_title
			),
			'quiz_user'     => array(
				'Result'       => $user['points'],
				'Email'        => ! empty( $user['email'] ) ? $user['email'] : __( 'unknown', TVE_DASH_TRANSLATE_DOMAIN ),
				'Date started' => $user['date_started'],
			),
			'original_data' => $data,
		);

		$filtered = apply_filters( 'td_nm_email_notification_quiz_completion', $data );

		if ( ! is_array( $filtered ) ) {
			$filtered = array();
		}

		$return = array(
			'quiz'      => ! empty( $filtered['quiz'] ) ? $filtered['quiz'] : null,
			'quiz_user' => ! empty( $filtered['quiz_user'] ) ? $filtered['quiz_user'] : null,
		);

		return $return;
	}
}
