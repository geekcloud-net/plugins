<?php

/**
 * FUE_Admin_Actions class
 *
 * Handles the processing of POSTed data through the admin dashboard
 */
class FUE_Admin_Actions {

	/**
	 * Process request for sending a manual email
	 *
	 * @see FUE_Scheduler::queue_manual_emails
	 */
	public static function send_manual() {
		set_time_limit( 0 );
		$post = array_map( 'stripslashes_deep', $_POST );

		$send_type  = $post['send_type'];
		$recipients = array(); // Format: array(user_id, email_address, name).

		if ( 'email' === $send_type ) {
			// Support multiple email addresses.
			if ( strpos( $post['recipient_email'], ',' ) !== false ) {
				$emails = array_filter( array_map( 'trim', explode( ',', $post['recipient_email'] ) ) );

				foreach ( $emails as $email ) {
					$key = '0|' . $email . '|';
					$recipients[ $key ] = array( 0, $email, '' );
				}
			} else {
				$key = '0|' . $post['recipient_email'] . '|';
				$recipients[ $key ] = array( 0, $post['recipient_email'], '' );
			}
		} elseif ( 'subscribers' === $send_type ) {
			$list        = ! empty( $post['email_list'] ) ? $post['email_list'] : false;
			$subscribers = fue_get_subscribers( array(
				'list' => $list,
			) );

			foreach ( $subscribers as $subscriber ) {
				$key = '0|' . $subscriber['email'] . '|';
				$recipients[ $key ] = array( 0, $subscriber['email'], '' );
			}
		} elseif ( 'roles' === $send_type ) {
			if ( ! empty( $post['roles'] ) ) {
				foreach ( $post['roles'] as $role ) {
					$users = get_users( array( 'role' => $role ) );

					foreach ( $users as $user ) {
						$key = $user->ID . '|' . $user->user_email . '|' . $user->display_name;
						$recipients[ $key ] = array( $user->ID, $user->user_email, $user->display_name );
					}
				}
			}
		}

		$recipients = apply_filters( 'fue_manual_email_recipients', $recipients, $post );

		if ( ! empty( $recipients ) ) {
			$args = apply_filters( 'fue_manual_email_args', array(
				'email_id'          => $post['id'],
				'recipients'        => $recipients,
				'subject'           => $post['email_subject'],
				'message'           => $post['email_message'],
				'tracking'          => $post['tracking'],
				'schedule_email'    => ( isset( $post['schedule_email'] ) && 1 == $post['schedule_email'] ) ? true : false,
				'schedule_date'     => $post['sending_schedule_date'],
				'schedule_hour'     => $post['sending_schedule_hour'],
				'schedule_minute'   => $post['sending_schedule_minute'],
				'schedule_ampm'     => $post['sending_schedule_ampm'],
				'send_again'        => ( isset( $post['send_again'] ) && 1 == $post['send_again'] ) ? true : false,
				'interval'          => $post['interval'],
				'interval_duration' => $post['interval_duration'],
				'meta'              => array(
					'send_type' => $post['send_type'],
				),
			), $post );

			$email_batch_enabled = get_option( 'fue_email_batches', 0 );
			$emails_per_batch    = get_option( 'fue_emails_per_batch', 100 );

			if ( $email_batch_enabled && count( $recipients ) > $emails_per_batch ) {
				$key = $args['email_id'] . '-' . time();

				FUE_Transients::set_transient( 'fue_manual_email_recipients_' . $key, $recipients, 86400, 250 );
				unset( $args['recipients'] );
				FUE_Transients::set_transient( 'fue_manual_email_' . $key, $args, 86400 );

				wp_redirect( 'admin.php?page=followup-emails&tab=send_manual_email_batches&params[key]=' . $key );
				exit;
			} else {
				// if the number of recipients exceed 50 and the email is set
				// to send immediately, use an AJAX worker to avoid timeouts
				if ( count( $recipients ) > 50 ) {
					$key = $args['email_id'] . '-' . time();

					FUE_Transients::set_transient( 'fue_manual_email_recipients_' . $key, $recipients, 86400, 250 );

					unset( $args['recipients'] );
					FUE_Transients::set_transient( 'fue_manual_email_' . $key, $args, 86400 );

					wp_redirect( 'admin.php?page=followup-emails&tab=send_manual_emails&key=' . $key );
					exit;
				}

				FUE_Sending_Scheduler::queue_manual_emails( $args );
				do_action( 'sfn_followup_emails' );
			}
		}

		wp_redirect( 'admin.php?page=followup-emails&manual_sent=1#manual_mails' );
		exit;
	}

	/**
	 * Process form submission for creating a new FUE_Email
	 */
	public static function process_email_form() {
		$post = array_map( 'stripslashes_deep', $_POST );

		$step   = absint( $post['step'] );
		$id     = ( isset( $post['id'] ) ) ? $post['id'] : '';
		$data   = array();
		$new    = ( empty( $id ) || (isset( $_POST['new'] ) && 1 == $_POST['new'] ) ) ? '&new=1' : '';

		if ( 1 == $step ) {
			$data['name']              = $post['name'];
			$data['type']              = $post['email_type'];
		} elseif ( 2 == $step ) {
			$data['always_send']       = isset( $post['always_send'] ) ? $post['always_send'] : 0;
			$data['meta']              = $post['meta'];
			$data['subject']           = $post['email_subject'];
			$data['interval_num']      = $post['interval'];
			$data['interval_duration'] = ( isset( $post['interval_duration'] ) ) ? $post['interval_duration'] : '';
			$data['interval_type']     = ( isset( $post['interval_type'] ) ) ? $post['interval_type'] : '';
			$data['send_date']         = $post['send_date'];
			$data['send_date_hour']    = $post['send_date_hour'];
			$data['send_date_minute']  = $post['send_date_minute'];
			$data['tracking_on']       = isset( $post['tracking_on'] ) ? $post['tracking_on'] : 0;
			$data['tracking_code']     = $post['tracking'];
			$data['product_id']        = isset( $post['product_id'] ) ? $post['product_id'] : 0;
			$data['category_id']       = isset( $post['category_id'] ) ? $post['category_id'] : 0;
		} elseif ( 3 == $step ) {
			$data['message']           = $post['email_message'];
			$data['meta']              = $post['meta'];
		}

		// Do not enable new emails until after step 3.
		if ( $new  ) {
			if ( 3 != $step )
				$data['status'] = FUE_Email::STATUS_INACTIVE;
			else
				$data['status'] = FUE_Email::STATUS_ACTIVE;
		}

		$data = apply_filters( 'fue_pre_save_data', $data, $post );

		if ( ! empty( $id ) )
			$data['ID'] = $id;

		$id = fue_save_email( $data );

		// If quick-saving, redirect back to the list.
		if ( isset( $post['mode'] ) && 'quicksave' === $post['mode'] ) {
			wp_redirect( 'admin.php?page=followup-emails&updated=1' );
			exit;
		}

		$step++;

		$total_steps = apply_filters( 'fue_form_total_steps', 3 );

		if ( $step > $total_steps ) {
			// Process is complete.
			$save_type = ( empty( $new ) ) ? 'updated' : 'created';
			wp_redirect( 'admin.php?page=followup-emails&' . $save_type . '=1' );
		} else {
			// Load next step.
			wp_redirect( 'admin.php?page=followup-emails-form&step=' . $step . '&id=' . $id . $new );
		}

		exit;
	}

	/**
	 * Delete an existing Follow-Up Email.
	 */
	static function delete_email() {
		$wpdb = Follow_Up_Emails::instance()->wpdb;

		check_admin_referer( 'delete-email' );

		$id = absint( $_GET['id'] );

		// delete
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}followup_email_orders WHERE email_id = %d", $id ) );

		wp_delete_post( $id, true );

		do_action( 'fue_email_deleted', $id );

		wp_redirect( 'admin.php?page=followup-emails&deleted=true' );
		exit;
	}

	/**
	 * Process form submission from the email list page.
	 */
	public static function save_list() {
		if ( ! empty( $_POST['update_priorities'] ) ) {
			self::update_priorities();
			$message = __( 'Follow-up emails updated', 'follow_up_emails' );
		} else {
			// Look for bulk actions.
			$types   = Follow_Up_Emails::instance()->get_email_types();
			$count   = empty( $_POST['chk_emails'] ) ? 0 : count( $_POST['chk_emails'] );
			$action  = '';
			$message = '';

			if ( empty( $count ) ) {
				$message = __( 'No emails selected', 'follow_up_emails' );
				wp_redirect( "admin.php?page=followup-emails&tab=list&updated=1&message=" . urlencode( $message ) );
				exit;
			}

			foreach ( $types as $type ) {
				if ( ! empty( $_POST[ 'bulk_action_' . $type->id . '_active_button' ] ) ) {
					$action = $_POST[ 'bulk_action_' . $type->id . '_active' ];
					self::execute_bulk_action( $action, $_POST['chk_emails'] );
					break;
				} elseif ( ! empty( $_POST[ 'bulk_action_' . $type->id . '_archived_button' ] ) ) {
					$action = $_POST[ 'bulk_action_' . $type->id . '_archived' ];
					self::execute_bulk_action( $action, $_POST['chk_emails'] );
					break;
				}
			}

			switch ( $action ) {
				case 'activate':
				case 'unarchive':
					$message = sprintf(
						_n(
							'%d email activated',
							'%d emails activated',
							$count,
							'follow_up_emails'
						),
						$count
					);
					break;

				case 'deactivate':
					$message = sprintf(
						_n(
							'%d email deactivated',
							'%d emails deactivated',
							$count,
							'follow_up_emails'
						),
						$count
					);
					break;

				case 'archive':
					$message = sprintf(
						_n(
							'%d email archived',
							'%d emails archived',
							$count,
							'follow_up_emails'
						),
						$count
					);
					break;

				case 'delete':
					$message = sprintf(
						_n(
							'%d email deleted',
							'%d emails deleted',
							$count,
							'follow_up_emails'
						),
						$count
					);
					break;
			}
		}

		wp_redirect( 'admin.php?page=followup-emails&tab=list&updated=1&message=' . urlencode( $message ) );
		exit;
	}

	/**
	 * Generate the CSV for the selected list and output it into the browser.
	 */
	public static function export_list() {
		global $wpdb;

		$id   = $_GET['id'];
		$file = $export_file = sys_get_temp_dir() . '/fue_export_' . $id;

		if ( ! file_exists( $file ) ) {
			wp_die( 'File not found.' );
		}

		header( 'Content-Type: application/csv' );
		header( 'Content-Disposition:attachment;filename=email_list.csv' );
		header( 'Pragma: no-cache' );

		readfile( $file );
		exit;
	}

	/**
	 * Update the priorities the emails are loaded and displayed.
	 */
	public static function update_priorities() {
		$types = Follow_Up_Emails::get_email_types();

		foreach ( $types as $key => $type ) {
			if ( isset( $_POST[ $key . '_order' ] ) && ! empty( $_POST[ $key . '_order' ] ) ) {
				foreach ( $_POST[ $key . '_order' ] as $idx => $email_id ) {
					$priority = $idx + 1;

					fue_save_email( array(
						'id'       => $email_id,
						'priority' => $priority,
					) );
				}
			}
		}

		if ( isset( $_POST['bcc'] ) ) {
			update_option( 'fue_bcc_types', $_POST['bcc'] );
		}

		if ( isset( $_POST['from_email'] ) ) {
			update_option( 'fue_from_email_types', $_POST['from_email'] );
		}

		if ( isset( $_POST['from_name'] ) ) {
			update_option( 'fue_from_name_types', $_POST['from_name'] );
		}

		do_action( 'fue_update_priorities', $_POST );

	}

	/**
	 * Execute the requested bulk action on the selected emails.
	 *
	 * @param string $action
	 * @param array  $emails
	 */
	public static function execute_bulk_action( $action, $emails ) {
		if ( ! is_array( $emails ) || empty( $emails ) ) {
			return;
		}

		foreach ( $emails as $email_id ) {
			$email = new FUE_Email( $email_id );
			switch ( $action ) {
				case 'activate':
					$email->update_status( FUE_Email::STATUS_ACTIVE );
					break;
				case 'deactivate':
					$email->update_status( FUE_Email::STATUS_INACTIVE );
					break;
				case 'archive':
					$email->update_status( FUE_Email::STATUS_ARCHIVED );
					break;
				case 'unarchive':
					$email->update_status( FUE_Email::STATUS_ACTIVE );
					break;
				case 'delete':
					wp_delete_post( $email_id, true );
					break;
			}
		}

		do_action( 'fue_execute_bulk_action', $action, $emails );
	}

	/**
	 * update_settings method.
	 */
	public static function update_settings() {
		$wpdb     = Follow_Up_Emails::instance()->wpdb;
		$data     = stripslashes_deep( $_POST );
		$section  = $_POST['section'];
		$imported = '';

		if ( 'system' === $section ) {
			self::update_settings_system( $data );
		} elseif ( 'subscribers' === $section ) {
			self::update_settings_subscribers( $data );
		} elseif ( 'auth' === $section ) {
			self::update_settings_auth( $data );
		} elseif ( 'tools' === $section ) {
			self::update_settings_tools( $data );
		}

		do_action( 'fue_settings_saved', $_POST );

		wp_redirect( "admin.php?page=followup-emails-settings&tab=$section&settings_updated=1$imported" );
		exit;
	}

	public static function update_settings_system( $data ) {
		// staging.
		update_option( 'fue_staging', empty( $data['staging'] ) ? 'no' : $data['staging'] );

		// bcc.
		if ( isset( $data['bcc'] ) ) {
			update_option( 'fue_bcc', $data['bcc'] );
		}

		// from/reply-to name.
		if ( isset( $data['from_name'] ) ) {
			update_option( 'fue_from_name', $data['from_name'] );
		}

		// from/reply-to.
		if ( isset( $data['from_email'] ) ) {
			update_option( 'fue_from_email', $data['from_email'] );
		}

		// bounce settings.
		if ( isset( $data['bounce'] ) ) {
			update_option( 'fue_bounce_settings', $data['bounce'] );

			$bouncer = new FUE_Bounce_Handler();
			if ( $bouncer->is_bounce_handling_enabled() ) {
				$bouncer->schedule_bounce_handling();
			} else {
				$bouncer->unschedule_bounce_handling();
			}
		}

		// daily summary emails.
		$summary_enabled = ( empty( $data['enable_daily_summary'] ) ) ? 'no' : $data['enable_daily_summary'];
		update_option( 'fue_enable_daily_summary', $summary_enabled );

		if ( isset( $data['daily_emails'] ) ) {
			update_option( 'fue_daily_emails', $data['daily_emails'] );
		}

		if ( isset( $data['daily_emails_time_hour'] ) ) {
			$previous_time = get_option( 'fue_daily_emails_time', '00:00 AM' );
			$time = $data['daily_emails_time_hour']
					. ':' . $data['daily_emails_time_minute']
					. ' ' . $data['daily_emails_time_ampm'];

			if ( 'yes' === $summary_enabled && $previous_time != $time ) {
				update_option( 'fue_daily_emails_time', $time );

				Follow_Up_Emails::instance()->scheduler->reschedule_daily_summary_email();
			}
		}

		do_action( 'fue_settings_crm_save', $data );

		// Capability.
		if ( isset( $data['roles'] ) ) {
			$roles    = get_editable_roles();
			$wp_roles = new WP_Roles();

			foreach ( $roles as $key => $role ) {
				if ( in_array( $key, $data['roles'] ) ) {
					$wp_roles->add_cap( $key, 'manage_follow_up_emails' );
				} else {
					$wp_roles->remove_cap( $key, 'manage_follow_up_emails' );
				}
			}

			// Make sure the admin has this capability.
			$wp_roles->add_cap( 'administrator', 'manage_follow_up_emails' );
		}

		do_action( 'fue_settings_email_save', $data );

		// Email batches.
		if ( isset( $data['email_batch_enabled'] ) && 1 == $data['email_batch_enabled'] ) {
			update_option( 'fue_email_batches', 1 );
			update_option( 'fue_emails_per_batch', intval( $data['emails_per_batch'] ) );
			update_option( 'fue_batch_interval', intval( $data['email_batch_interval'] ) );
		} else {
			update_option( 'fue_email_batches', 0 );
		}

		do_action( 'fue_settings_system_save', $data );
	}

	/**
	 * Handle import of Subscribers in Settings screen.
	 *
	 * @param array $data Posted data
	 */
	public static function update_settings_subscribers( $data ) {
		// Process importing request.
		$section = $data['section'];
		$action  = ( ! empty( $data['upload'] ) ) ? 'upload' : 'save';

		if ( 'upload' === $action ) {
			if ( isset( $_FILES['csv'] ) && is_uploaded_file( $_FILES['csv']['tmp_name'] ) ) {
				ini_set( 'auto_detect_line_endings', true );

				$fh         = @fopen( $_FILES['csv']['tmp_name'], 'r' );
				$i          = 0;
				$added      = 0;
				$newsletter = new FUE_Newsletter();

				while ( $row = fgetcsv( $fh ) ) {
					$i ++;

					if ( empty( $row ) || empty( $row[0] ) ) {
						continue;
					}

					$lists = array();
					if ( ! empty( $row[3] ) ) {
						$lists = array_filter( array_map( 'trim', explode( ',', $row[3] ) ) );
					}

					$email      = $row[0]; // Required
					$first_name = ! empty( $row[1] ) ? $row[1] : '';
					$last_name  = ! empty( $row[2] ) ? $row[2] : '';

					$subs_id = $newsletter->add_subscriber_to_list( $lists, array(
						'email'      => $row[0], // Required
						'first_name' => ! empty( $row[1] ) ? $row[1] : '',
						'last_name'  => ! empty( $row[2] ) ? $row[2] : '',
					) );

					if ( is_wp_error( $subs_id ) ) {
						continue;
					}

					$added ++;
				}
			}

			do_action( 'fue_settings_subscribers_imported', $data );

			wp_redirect( "admin.php?page=followup-emails-settings&tab=$section&subscribers_added=$added" );
			exit;
		} else {
			$account_label  = $data['email_subscriptions_page_title'];
			$account_button = $data['email_subscriptions_button_text'];
			$unsubscribe_enpoint            = ! empty( $data['unsubscribe_endpoint'] )
				? $data['unsubscribe_endpoint']
				: 'unsubscribe';
			$email_subscriptions_endpoint   = ! empty( $data['email_subscriptions_endpoint'] )
				? urlencode( $data['email_subscriptions_endpoint'] )
				: 'email-subscriptions';
			$email_preferences_endpoint     = ! empty( $data['email_preferences_endpoint'] )
				? urlencode( $data['email_preferences_endpoint'] )
				: 'email-preferences';

			update_option( 'fue_email_subscriptions_page_title', $account_label );
			update_option( 'fue_email_subscriptions_button_text', $account_button );
			update_option( 'fue_unsubscribe_endpoint', $unsubscribe_enpoint );
			update_option( 'fue_email_subscriptions_endpoint', $email_subscriptions_endpoint );
			update_option( 'fue_email_preferences_endpoint', $email_preferences_endpoint );

			Follow_Up_Emails::instance()->query->init_query_vars();
			Follow_Up_Emails::instance()->query->add_endpoints();
			flush_rewrite_rules();

			do_action( 'fue_settings_subscribers_save', $data );
		}
	}

	public static function update_settings_auth( $data ) {
		$spf  = ( isset( $data['spf'] ) ) ? $data['spf'] : array();
		$dkim = ( isset( $data['dkim'] ) ) ? $data['dkim'] : array();

		update_option( 'fue_spf', $spf );
		update_option( 'fue_dkim', $dkim );

		do_action( 'fue_settings_authentication_save', $data );
	}

	public static function update_settings_tools( $data ) {
		$wpdb = Follow_Up_Emails::instance()->wpdb;

		// Process importing request.
		if ( isset( $_FILES['emails_json'] ) && is_uploaded_file( $_FILES['emails_json']['tmp_name'] ) ) {
			$json          = file_get_contents( $_FILES['emails_json']['tmp_name'] );
			$json_importer = new FUE_JSON_Importer( $json );
			$status        = $json_importer->import();

			if ( ! is_wp_error( $status ) ) {
				$imported = '&imported=1';
			}
		}

		if ( isset( $_FILES['emails_file'] ) && is_uploaded_file( $_FILES['emails_file']['tmp_name'] ) ) {
			ini_set( 'auto_detect_line_endings', true );

			$fh      = @fopen( $_FILES['emails_file']['tmp_name'], 'r' );
			$columns = array();
			$i       = 0;
			while ( $row = fgetcsv( $fh ) ) {
				$i ++;

				if ( 1 == $i ) {
					foreach ( $row as $idx => $col ) {
						$columns[ $idx ] = $col;
					}

					continue;
				}

				$data = array();
				foreach ( $columns as $idx => $col ) {
					if ( 'email_type' === $col ) {
						$col = 'type';

						// Convert 'product' emails to 'storewide'.
						if ( in_array( $row[ $idx ], array( 'product', 'normal', 'generic' ) ) ) {
							$row[ $idx ] = 'storewide';
						}
					} elseif ( 'status' === $col ) {
						if (  -1 == $row[ $idx ] ) {
							$row[ $idx ] = FUE_Email::STATUS_ARCHIVED;
						} elseif ( 0 == $row[ $idx ] ) {
							$row[ $idx ] = FUE_Email::STATUS_INACTIVE;
						} else {
							$row[ $idx ] = FUE_Email::STATUS_ACTIVE;
						}
					}

					$data[ $col ] = $row[ $idx ];
				}

				fue_create_email( $data );
			}

			$imported = '&imported=1';
		}

		// Restore settings file from backup.
		if ( isset( $_FILES['settings_file'] ) && is_uploaded_file( $_FILES['settings_file']['tmp_name'] ) ) {
			ini_set( "auto_detect_line_endings", true );

			$fh = @fopen( $_FILES['settings_file']['tmp_name'], 'r' );
			$i  = 0;
			while ( $row = fgetcsv( $fh ) ) {
				$i ++;

				if ( 1 == $i ) {
					continue;
				}

				update_option( $row[0], $row[1] );
			}

			$imported = '&imported=1';
		}

		// Usage data.
		if ( isset( $data['disable_usage_data'] ) && 1 == $data['disable_usage_data'] ) {
			update_option( 'fue_disable_usage_data', 1 );
		} else {
			delete_option( 'fue_disable_usage_data' );
		}

		// Disable logging.
		if (
			isset( $data['action_scheduler_disable_logging'] )
			&& 1 == $data['action_scheduler_disable_logging']
		) {
			update_option( 'fue_disable_action_scheduler_logging', 1 );
		} else {
			update_option( 'fue_disable_action_scheduler_logging', 0 );
		}

		// Delete all action scheduler comments.
		if ( isset( $data['action_scheduler_delete_logs'] ) && 1 == $data['action_scheduler_delete_logs'] ) {
			$comment_ids = $wpdb->get_col(
				"SELECT comment_ID
					FROM {$wpdb->comments}
					WHERE comment_type = 'action_log'"
			);

			if ( $comment_ids ) {
				foreach ( $comment_ids as $comment_id ) {
					wp_delete_comment( $comment_id, true );
				}
			}
		}

		if ( isset( $data['log_level'] ) ) {
			update_option( 'fue_log_level', $data['log_level'] );
		}
	}

	/**
	 * Connect to the POP3 server and process any bounced emails.
	 */
	public static function handle_bounced_emails() {
		$handler = new FUE_Bounce_Handler();
		$pop3 = $handler->connect();
		$handler->handle_bounce_messages( $pop3 );
	}

	/**
	 * Add/Remove emails from the Excluded List.
	 */
	public static function manage_optout() {
		$wpdb = Follow_Up_Emails::instance()->wpdb;

		$post = stripslashes_deep( $_POST );
		if ( ! empty( $post['button_add'] ) ) {
			// Add an email address to the excludes list.
			$email = $post['email'];

			// Make sure it is a valid email address.
			if ( ! is_email( $email ) ) {
				$error = urlencode( __( 'The email address is invalid', 'follow_up_emails' ) );
				wp_redirect(
					'admin.php?page=followup-emails-subscribers&view=opt-outs&error=' . $error
				);
				exit;
			}

			$status = fue_exclude_email_address( $email );

			if ( is_wp_error( $status ) ) {
				$message = $status->get_error_message();

				if ( 'fue_email_excluded' === $status->get_error_code() ) {
					$message = __( 'This email has already been added', 'follow_up_emails' );
				}
				wp_redirect( 'admin.php?page=followup-emails-subscribers&view=opt-outs&error=' . urlencode( $message ) );
				exit;
			}

			wp_redirect( 'admin.php?page=followup-emails-subscribers&view=opt-outs&opt-out-added=' . urlencode( $email ) );
			exit;
		} elseif ( ! empty( $post['button_restore'] ) && 'Apply' === $post['button_restore'] ) {
			$emails    = $post['email'];
			$email_ids = '';

			if ( is_array( $emails ) && ! empty( $emails ) ) {
				$email_ids = "'" . implode( "','", $emails ) . "'";
			}

			if ( ! empty( $email_ids ) ) {
				$wpdb->query( "DELETE FROM {$wpdb->prefix}followup_email_excludes WHERE id IN($email_ids)" );
			}

			wp_redirect( 'admin.php?page=followup-emails-subscribers&view=opt-outs&opt-out-restored=' . count( $emails ) );
			exit;
		}

		wp_redirect( 'admin.php?page=followup-emails-optouts' );
		exit;
	}

	/**
	 * Delete an email from the excludes/optouts table admin-post action for
	 * fue_optout_remove.
	 */
	public static function optout_delete_email() {
		check_admin_referer( 'optout_remove' );

		$email = ! empty( $_GET['email'] ) ? sanitize_email( $_GET['email'] ) : '';

		if ( ! is_email( $email ) ) {
			$error = urlencode( __( 'Invalid email address passed.', 'follow_up_emails' ) );
			wp_redirect( 'admin.php?page=followup-emails-subscribers&view=opt-outs&error=' . $error );
			exit;
		}

		Follow_Up_Emails::instance()->newsletter->remove_excluded_email( $email );

		wp_redirect( 'admin.php?page=followup-emails-subscribers&view=opt-outs&deleted=1' );
		exit;
	}

	/**
	 * Subscriber table actions.
	 */
	public static function optout_bulk_actions() {
		$wpdb = Follow_Up_Emails::instance()->wpdb;

		if ( ! empty( $_REQUEST['page'] ) && 'followup-emails-subscribers' === $_REQUEST['page'] ) {
			$action  = ! empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
			$action2 = ! empty( $_REQUEST['action2'] ) ? $_REQUEST['action2'] : '';

			if ( 'restore' === $action || 'restore' === $action2 ) {
				$emails    = $_REQUEST['email'];
				$email_ids = '';

				if ( is_array( $emails ) && ! empty( $emails ) ) {
					$email_ids = "'" . implode( "','", $emails ) . "'";
				}

				if ( ! empty( $email_ids ) ) {
					$wpdb->query( "DELETE FROM {$wpdb->prefix}followup_email_excludes WHERE id IN($email_ids)" );
				}

				wp_redirect( 'admin.php?page=followup-emails-subscribers&view=opt-outs&opt-out-restored=' . count( $emails ) );
				exit;
			}
		}
	}

	/**
	 * Subscriber table actions.
	 */
	public static function manage_subscribers() {
		$post = stripslashes_deep( $_POST );

		if ( ! empty( $post['button_add'] ) ) {
			// Add a new subscriber.
			$email      = sanitize_email( $post['email'] );
			$first_name = $post['first_name'];
			$last_name  = $post['last_name'];
			$status     = fue_add_subscriber_to_list( array(), array(
				'email'      => $email,
				'first_name' => $first_name,
				'last_name'  => $last_name,
			) );

			if ( is_wp_error( $status ) ) {
				wp_redirect( 'admin.php?page=followup-emails-subscribers&error=' . urlencode( $status->get_error_message() ) );
				exit;
			}

			wp_redirect( 'admin.php?page=followup-emails-subscribers&added_subscriber=' . urlencode( $email ) );
			exit;
		} elseif ( ! empty( $post['button_create_list'] ) ) {
			$list     = stripslashes( $_POST['list_name'] );
			$redirect = admin_url( 'admin.php?page=followup-emails-subscribers&added_list=' . urlencode( $list ) );

			Follow_Up_Emails::instance()->newsletter->add_list( $list );

			if ( ! empty( $_POST['from_lists_table'] ) ) {
				$redirect = add_query_arg( 'view', 'lists', $redirect );
			}

			wp_redirect( $redirect );
			exit;
		} elseif ( ! empty( $post['upload'] ) && is_uploaded_file( $_FILES['csv']['tmp_name'] ) ) {
			set_time_limit( 0 );
			ini_set( 'auto_detect_line_endings', true );

			$newsletter = Follow_Up_Emails::instance()->newsletter;
			$fh         = @fopen( $_FILES['csv']['tmp_name'], 'r' );
			$i          = 0;
			$added      = 0;

			$add_to_list = ( ! empty( $_POST['import_to_list'] ) ) ? absint( $_POST['import_to_list'] ) : '';

			while ( $row = fgetcsv( $fh ) ) {
				$i ++;

				if ( empty( $row ) || empty( $row[0] ) ) {
					continue;
				}

				$subs_id = fue_add_subscriber_to_list( $add_to_list, array(
					'email'      => $row[0], // Required
					'first_name' => ! empty( $row[1] ) ? $row[1] : '',
					'last_name'  => ! empty( $row[2] ) ? $row[2] : '',
				) );

				if ( is_wp_error( $subs_id ) ) {
					continue;
				}

				$added ++;
			}

			wp_redirect( 'admin.php?page=followup-emails-subscribers&added=' . $added );
			exit;
		}

		wp_redirect( 'admin.php?page=followup-emails-subscribers' );
		exit;
	}

	/**
	 * Subscriber table actions.
	 */
	public static function subscribers_bulk_actions() {
		$wpdb = Follow_Up_Emails::instance()->wpdb;

		if ( ! empty( $_REQUEST['page'] ) && 'followup-emails-subscribers' === $_REQUEST['page'] ) {
			$post = stripslashes_deep( $_REQUEST );

			if ( isset( $post['action2'] ) && -1 != $post['action2'] ) {
				$action         = $post['action2'];
				$subscriber_ids = $post['email'];

				if ( 'delete' === $action  ) {
					$ids = '';

					if ( is_array( $subscriber_ids ) && ! empty( $subscriber_ids ) ) {
						$ids = "'" . implode( "','", array_map( 'absint', $subscriber_ids ) ) . "'";
					}

					if ( ! empty( $ids ) ) {
						$wpdb->query( "DELETE FROM {$wpdb->prefix}followup_subscribers WHERE id IN($ids)" );
					}

					wp_redirect( 'admin.php?page=followup-emails-subscribers&deleted=' . count( $subscriber_ids ) );
					exit;
				} elseif ( 'move' === $action ) {
					$newsletter = Follow_Up_Emails::instance()->newsletter;
					$list       = $post['list'];

					foreach ( $subscriber_ids as $subscriber_id ) {
						if ( is_array( $list ) ) {
							foreach ( $list as $list_id ) {
								$newsletter->add_to_list( $subscriber_id, $list_id );
							}
						} else {
							$newsletter->add_to_list( $subscriber_id, $list );
						}
					}

					wp_redirect( 'admin.php?page=followup-emails-subscribers&moved=' . count( $subscriber_ids ) );
					exit;
				} elseif ( 'new' === $action ) {
					$newsletter = Follow_Up_Emails::instance()->newsletter;
					$list       = $post['new_list_name'];
					$newsletter->add_list( $list );

					foreach ( $subscriber_ids as $subscriber_id ) {
						$newsletter->remove_from_list( $subscriber_id );
						Follow_Up_Emails::instance()->newsletter->add_to_list( $subscriber_id, $list );
					}

					wp_redirect( 'admin.php?page=followup-emails-subscribers&moved=' . count( $subscriber_ids ) );
					exit;
				}
			}
		}
	}

	/**
	 * Process queue updates and removal of bulk items from Subscribers Lists
	 * List Table.
	 */
	public static function process_subscribers_lists_bulk_action() {
		$current_action = false;

		if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] ) {
			$current_action = $_REQUEST['action2'];
		}

		if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] ) {
			$current_action = $_REQUEST['action'];
		}

		if ( false === $current_action || ! isset( $_GET['_wpnonce'] ) ) {
			return;
		}

		if ( empty( $_GET['list'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-lists' ) ) {
			wp_die( __( 'Bulk edit failed. Invalid Nonce.', 'follow_up_emails' ) );
		}

		$lists          = $_GET['list'];
		$messages       = array();
		$error_messages = array();
		$query_args     = array();

		if ( in_array( $current_action, array( 'delete_list' ) ) ) {

			$item_count     = count( $lists );
			$error_count    = 0;

			foreach ( $lists as $idx => $list_id ) {
				switch ( $current_action ) {
					case 'delete_list':
						Follow_Up_Emails::instance()->newsletter->remove_list( $list_id );
						break;

					default :
						$error_messages[] = __( 'Error: Unknown action.', 'follow_up_emails' );
						break;
				}
			}

			if ( $item_count > 0 ) {
				switch ( $current_action ) {
					case 'delete_list' :
						$messages[] = sprintf(
							_n(
								'%d list has been deleted',
								'%s lists have been deleted',
								$item_count,
								'follow_up_emails'
							),
							$item_count
						);
						break;
				}
			}

			if ( ! empty( $messages ) || ! empty( $error_messages ) ) {
				$message_nonce = wp_create_nonce( __FILE__ );
				set_transient(
					'_fue_messages_' . $message_nonce,
					array( 'messages' => $messages, 'error_messages' => $error_messages ),
					60 * 60
				);
			}

			if ( ! empty( $messages ) || ! empty( $error_messages ) ) {
				$query_args['message'] = $message_nonce;
			}

			if ( isset( $_GET['paged'] ) ) {
				$query_args['paged'] = $_GET['paged'];
			}

			$search_query = _admin_search_query();

			if ( ! empty( $search_query ) ) {
				$query_args['s'] = $search_query;
			}

			$redirect_to = add_query_arg(
				$query_args,
				admin_url( 'admin.php?page=followup-emails-subscribers&view=lists' )
			);

			// Redirect to avoid performning actions on a page refresh
			wp_safe_redirect( $redirect_to );
			exit;
		}
	}

	/**
	 * An easy way to delete all unsent items in the queue
	 */
	public static function process_subscribers_lists_delete_all() {
		if ( empty( $_REQUEST['fue_delete_all_lists'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-lists' ) ) {
			wp_die( __( 'Are you sure you want to do this?', 'follow_up_emails' ) );
		}

		set_time_limit( 0 );

		$newsletter = Follow_Up_Emails::instance()->newsletter;
		$lists      = $newsletter->get_lists();
		$count      = count( $lists );
		foreach ( $lists as $list ) {
			$newsletter->remove_list( $list['id'] );
		}

		$messages = array(
			sprintf(
				_n(
					'%d list has been deleted',
					'%s lists have been deleted',
					$count,
					'follow_up_emails'
				),
				$count
			),
		);

		$message_nonce = wp_create_nonce( __FILE__ );
		set_transient(
			'_fue_messages_' . $message_nonce,
			array( 'messages' => $messages, 'error_messages' => array() ),
			60 * 60
		);
		$query_args = array( 'message' => $message_nonce );

		$redirect_to = add_query_arg(
			$query_args,
			admin_url( 'admin.php?page=followup-emails-subscribers&view=lists' )
		);

		// Redirect to avoid performning actions on a page refresh.
		wp_safe_redirect( $redirect_to );
		exit;
	}

	/**
	 * reset_reports() method.
	 */
	static function reset_reports() {

		$data = $_POST;

		FUE_Reports::reset( $data );

		wp_redirect( 'admin.php?page=followup-emails-reports&cleared=1' );
		exit;

	}

	/**
	 * Process queue updates and removal of bulk items from Queue List Table.
	 */
	public static function process_queue_bulk_action() {
		$current_action = false;

		if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] ) {
			$current_action = $_REQUEST['action2'];
		}

		if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] ) {
			$current_action = $_REQUEST['action'];
		}

		if ( false === $current_action || ! isset( $_GET['_wpnonce'] ) ) {
			return;
		}

		if ( empty( $_GET['queue'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-items' ) ) {
			wp_die( __( 'Bulk edit failed. Invalid Nonce.', 'follow_up_emails' ) );
		}

		$scheduler      = Follow_Up_Emails::instance()->scheduler;
		$items          = $_GET['queue'];
		$messages       = array();
		$error_messages = array();
		$query_args     = array();

		if ( in_array( $current_action, array( 'send', 'activate', 'suspend', 'delete' ) ) ) {
			$item_count  = count( $items );
			$error_count = 0;
			$scheduler   = Follow_Up_Emails::instance()->scheduler;

			foreach ( $items as $idx => $queue_id ) {
				$item = new FUE_Sending_Queue_Item( $queue_id );

				switch ( $current_action ) {
					case 'send':
						$sent = Follow_Up_Emails::instance()->mailer->send_queue_item( $item, true );
						if ( is_wp_error( $sent ) ) {
							$error_messages[] = sprintf(
								__( 'Queue #%1$d: %2$s', 'follow_up_emails' ),
								$item->id,
								$sent->get_error_message()
							);
						} else {
							$messages[] = sprintf(
								__( 'Queue #%d: Scheduled email sent manually', 'follow_up_emails' ),
								$item->id
							);
						}
						break;

					case 'activate':
						$item->status = 1;
						$item->save();
						$scheduler->schedule_email( $queue_id, $item->send_on );
						break;

					case 'suspend':
						$item->status = 0;
						$item->save();
						$scheduler->unschedule_email( $queue_id );
						break;

					case 'delete':
						$scheduler->delete_item( $queue_id );
						break;

					default :
						$error_messages[] = __( 'Error: Unknown action.', 'follow_up_emails' );
						break;
				}
			}

			if ( $item_count > 0 ) {
				switch ( $current_action ) {
					case 'activate' :
						$messages[] = sprintf(
							_n(
								'%d email has been activated',
								'%s emails have been activated',
								$item_count,
								'follow_up_emails'
							),
							$item_count
						);
						break;
					case 'suspend' :
						$messages[] = sprintf(
							_n(
								'%d email has been suspended',
								'%s emails have been suspended',
								$item_count,
								'follow_up_emails'
							),
							$item_count
						);
						break;
					case 'deleted' :
						$messages[] = sprintf(
							_n(
								'%d email has been deleted',
								'%s emails have been deleted',
								$item_count,
								'follow_up_emails'
							),
							$item_count
						);
						break;
				}
			}

			if ( ! empty( $messages ) || ! empty( $error_messages ) ) {
				$message_nonce = wp_create_nonce( __FILE__ );
				set_transient(
					'_fue_messages_' . $message_nonce,
					array( 'messages' => $messages, 'error_messages' => $error_messages ),
					60 * 60
				);
			}

			// Filter by a given customer or product?
			if ( isset( $_GET['_customer_user'] ) || isset( $_GET['_product_id'] ) ) {

				if ( ! empty( $_GET['_customer_user'] ) ) {
					$user_id = intval( $_GET['_customer_user'] );
					$user    = get_user_by( 'id', absint( $_GET['_customer_user'] ) );

					if ( false === $user ) {
						wp_die( __( 'Action failed. Invalid user ID.', 'follow_up_emails' ) );
					}

					$query_args['_customer_user'] = $user_id;
				}

				if ( ! empty( $_GET['_product_id'] ) ) {
					$product_id = intval( $_GET['_product_id'] );
					$product    = wc_get_product( $product_id );

					if ( false === $product ) {
						wp_die( __( 'Action failed. Invalid product ID.', 'follow_up_emails' ) );
					}

					$query_args['_product_id'] = $product_id;
				}
			}

			$query_args['status'] = ( isset( $_GET['status'] ) ) ? $_GET['status'] : 'all';

			if ( ! empty( $messages ) || ! empty( $error_messages ) ) {
				$query_args['message'] = $message_nonce;
			}

			if ( isset( $_GET['paged'] ) ) {
				$query_args['paged'] = $_GET['paged'];
			}

			$search_query = _admin_search_query();

			if ( ! empty( $search_query ) ) {
				$query_args['s'] = $search_query;
			}

			$redirect_to = add_query_arg( $query_args, admin_url( 'admin.php?page=followup-emails-queue' ) );

			// Redirect to avoid performning actions on a page refresh.
			wp_safe_redirect( $redirect_to );
			exit;
		}
	}

	/**
	 * An easy way to delete all unsent items in the queue.
	 */
	public static function process_queue_delete_all() {
		if ( empty( $_REQUEST['fue_delete_all'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-items' ) ) {
			wp_die( __( 'Are you sure you want to do this?', 'follow_up_emails' ) );
		}

		set_time_limit( 0 );

		$scheduler  = Follow_Up_Emails::instance()->scheduler;
		$wpdb       = Follow_Up_Emails::instance()->wpdb;
		$sql        = "SELECT SQL_CALC_FOUND_ROWS *
							FROM {$wpdb->prefix}followup_email_orders eo, {$wpdb->posts} p
							WHERE 1=1
							AND eo.is_sent = 0
							AND p.ID = eo.email_id";

		$items = $wpdb->get_results( $sql );

		$deleted = 0;
		foreach ( $items as $item ) {
			$scheduler->delete_item( $item->id );
			$deleted++;
		}

		$messages = array(
			sprintf(
				_n(
					'%d email has been deleted',
					'%s emails have been deleted',
					$deleted,
					'follow_up_emails'
				),
				$deleted
			),
		);

		$message_nonce = wp_create_nonce( __FILE__ );
		set_transient(
			'_fue_messages_' . $message_nonce,
			array( 'messages' => $messages, 'error_messages' => array() ),
			60 * 60
		);
		$query_args = array( 'message' => $message_nonce );

		$redirect_to = add_query_arg( $query_args, admin_url( 'admin.php?page=followup-emails-queue' ) );

		// Redirect to avoid performning actions on a page refresh
		wp_safe_redirect( $redirect_to );
		exit;
	}

	/**
	 * Change a queue item's status and redirect the browser back to the scheduled
	 * emails page after.
	 */
	public static function update_queue_item_status() {
		check_admin_referer( 'update_queue_status' );

		$scheduler = Follow_Up_Emails::instance()->scheduler;
		$item      = new FUE_Sending_Queue_Item( absint( $_GET['id'] ) );

		$item->status = absint( $_GET['status'] );
		$item->save();

		if ( 1 == $item->status ) {
			$scheduler->schedule_email( $item->id, $item->send_on );
		} elseif ( 0 == $item->status ) {
			$scheduler->unschedule_email( $item->id );
		}

		$messages       = array( __( 'Scheduled email updated successfully', 'follow_up_emails' ) );
		$message_nonce  = wp_create_nonce( __FILE__ );
		set_transient( '_fue_messages_' . $message_nonce, array( 'messages' => $messages ), 60 * 60 );

		// redirect back to scheduled emails
		wp_redirect( add_query_arg( 'message', $message_nonce , 'admin.php?page=followup-emails-queue' ) );
		exit;
	}

	/**
	 * Delete an item from the queue and redirect back to the scheduled emails page
	 */
	public static function delete_queue_item() {
		check_admin_referer( 'delete_queue_item' );

		Follow_Up_Emails::instance()->scheduler->delete_item( absint( $_GET['id'] ) );

		$messages       = array( __( 'Scheduled email deleted', 'follow_up_emails' ) );
		$message_nonce  = wp_create_nonce( __FILE__ );
		set_transient( '_fue_messages_' . $message_nonce, array( 'messages' => $messages ), 60 * 60 );

		// redirect back to scheduled emails
		wp_redirect( add_query_arg( 'message', $message_nonce , 'admin.php?page=followup-emails-queue' ) );
		exit;

	}

	/**
	 * Manually send a specific queue item.
	 */
	public static function send_queue_item() {
		check_admin_referer( 'send_queue_item' );

		$queue  = new FUE_Sending_Queue_Item( $_GET['id'] );
		$sent   = Follow_Up_Emails::instance()->mailer->send_queue_item( $queue, true );

		$message_nonce  = wp_create_nonce( __FILE__ );
		$messages       = array( 'messages' => array(), 'error_messages' => array() );

		if ( is_wp_error( $sent ) ) {
			$message = $sent->get_error_message();
			$queue->add_note( $message );
			$messages['error_messages'][] = $message;
		} else {
			$message = __( 'Scheduled email sent manually', 'follow_up_emails' );
			$queue->add_note( $message );
			$messages['messages'][] = $message;
		}

		set_transient( '_fue_messages_' . $message_nonce, $messages, 60 * 60 );

		// redirect back to scheduled emails
		wp_redirect( add_query_arg( 'message', $message_nonce , 'admin.php?page=followup-emails-queue' ) );
		exit;
	}

	/**
	 * Generate and serve the settings in a CSV format.
	 */
	static function backup_settings() {
		check_admin_referer( 'fue_backup' );

		$contents = '';

		$headers    = array( 'meta_key', 'meta_value' );
		$contents  .= self::array_to_csv( $headers );

		$wpdb = Follow_Up_Emails::instance()->wpdb;

		$options = $wpdb->get_results(
			"SELECT option_name, option_value
			FROM {$wpdb->options}
			WHERE option_name LIKE 'fue%'"
		);

		foreach ( $options as $option ) {
			$row = array( $option->option_name, $option->option_value );
			$contents .= self::array_to_csv( $row );
		}

		header( 'Content-Type: application/csv' );
		header( 'Content-Disposition:attachment;filename=follow_up_settings.csv' );
		header( 'Pragma: no-cache' );

		echo $contents;
		exit;
	}

	/**
	 * Formats an array into a CSV line.
	 *
	 * @param array $fields
	 * @param string $delimiter
	 * @param string $enclosure
	 * @param bool $encloseAll
	 * @param bool $nullToMysqlNull
	 *
	 * @return string
	 */
	private static function array_to_csv(
		$fields = array(),
		$delimiter = ',',
		$enclosure = '"',
		$encloseAll = false,
		$nullToMysqlNull = false
	) {
		$delimiter_esc = preg_quote( $delimiter, '/' );
		$enclosure_esc = preg_quote( $enclosure, '/' );

		$output = array();
		foreach ( $fields as $field ) {
			if ( null === $field && $nullToMysqlNull ) {
				$output[] = 'NULL';
				continue;
			}

			// Enclose fields containing $delimiter, $enclosure or whitespace.
			if ( $encloseAll || preg_match( "/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field ) ) {
				$output[] = $enclosure . str_replace( $enclosure, $enclosure . $enclosure, $field ) . $enclosure;
			} else {
				$output[] = $field;
			}
		}

		return implode( $delimiter, $output ) . "\n";
	}
}
