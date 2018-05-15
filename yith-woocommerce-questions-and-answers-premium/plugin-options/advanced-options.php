<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined ( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$advanced_options = array(
	
	'advanced' => array(
		'section_advanced_settings' => array(
			'name' => __ ( 'Advanced settings', 'yith-woocommerce-questions-and-answers' ),
			'type' => 'title',
			'id'   => 'ywqa_section_advanced'
		),
		
		'ywqa_enable_question_vote'                 => array(
			'name'    => __ ( 'Vote question', 'yith-woocommerce-questions-and-answers' ),
			'type'    => 'checkbox',
			'desc'    => __ ( 'Allow users to vote product questions.', 'yith-woocommerce-questions-and-answers' ),
			'id'      => 'ywqa_enable_question_vote',
			'default' => 'yes'
		),
		'ywqa_notify_new_question'                  => array(
			'name'    => __ ( 'New question notification', 'yith-woocommerce-questions-and-answers' ),
			'desc'    => __ ( 'Send a notification email to the administrator when new questions are available.', 'yith-woocommerce-questions-and-answers' ),
			'type'    => 'select',
			'options' => array(
				'disabled' => __ ( 'Do not send notifications', 'yith-woocommerce-questions-and-answers' ),
				//'plain'    => __( 'Notification in text email', 'yith-woocommerce-questions-and-answers' ),
				'html'     => __ ( 'Notification in HTML email', 'yith-woocommerce-questions-and-answers' ),
			),
			'default' => 'disabled',
			'std'     => 'disabled',
			'id'      => 'ywqa_notify_new_question',
		),
		'ywqa_notify_new_answer'                    => array(
			'name'    => __ ( 'New answer notification', 'yith-woocommerce-questions-and-answers' ),
			'desc'    => __ ( 'Send a notification email to the administrator when new answers are available.', 'yith-woocommerce-questions-and-answers' ),
			'type'    => 'select',
			'options' => array(
				'disabled' => __ ( 'Do not send notifications', 'yith-woocommerce-questions-and-answers' ),
				//'plain'    => __( 'Notification in text email', 'yith-woocommerce-questions-and-answers' ),
				'html'     => __ ( 'Notification in HTML email', 'yith-woocommerce-questions-and-answers' ),
			),
			'default' => 'disabled',
			'std'     => 'disabled',
			'id'      => 'ywqa_notify_new_answer',
		),
		'ywqa_notify_answers_to_user'               => array(
			'name'    => __ ( 'User notification', 'yith-woocommerce-questions-and-answers' ),
			'desc'    => __ ( 'Allow users to receive a notification when their questions receive an answer.', 'yith-woocommerce-questions-and-answers' ),
			'type'    => 'checkbox',
			'default' => 'yes',
			'std'     => 'yes',
			'id'      => 'ywqa_notify_answers_to_user',
		),
		'ywqa_enable_answer_vote'                   => array(
			'name'    => __ ( 'Vote answers', 'yith-woocommerce-questions-and-answers' ),
			'type'    => 'checkbox',
			'desc'    => __ ( 'Allow users to vote answers.', 'yith-woocommerce-questions-and-answers' ),
			'id'      => 'ywqa_enable_answer_vote',
			'default' => 'yes'
		),
		'ywqa_enable_answer_abuse_reporting'        => array(
			'name'    => __ ( 'Inappropriate content', 'yith-woocommerce-questions-and-answers' ),
			'type'    => 'select',
			'desc'    => __ ( 'Let users report an answer as inappropriate content.', 'yith-woocommerce-questions-and-answers' ),
			'id'      => 'ywqa_enable_answer_abuse_reporting',
			'options' => array(
				'disabled'   => __ ( 'Not enabled', 'yith-woocommerce-questions-and-answers' ),
				'registered' => __ ( 'Only registered users can report an inappropriate content', 'yith-woocommerce-questions-and-answers' ),
				'everyone'   => __ ( 'Everyone can report an inappropriate content', 'yith-woocommerce-questions-and-answers' ),
			),
			'default' => '2'
		),
		'ywqa_hide_inappropriate_content_threshold' => array(
			'name'              => __ ( 'Hiding threshold', 'yith-woocommerce-questions-and-answers' ),
			'type'              => 'number',
			'desc'              => __ ( 'Hide temporarily an answer when a specific number of users has flagged it as inappropriate. Set this value to 0 to never hide automatically the reviews.', 'yith-woocommerce-questions-and-answers' ),
			'id'                => 'ywqa_hide_inappropriate_content_threshold',
			'custom_attributes' => array(
				'min'      => 0,
				'step'     => 1,
				'required' => 'required'
			),
			'default'           => '0'
		),
		'ywqa_enable_answer_excerpt'                => array(
			'name'              => __ ( 'Answer excerpt', 'yith-woocommerce-questions-and-answers' ),
			'type'              => 'number',
			'desc'              => __ ( 'Set max length for answers and show a "Read more" text for showing all content.', 'yith-woocommerce-questions-and-answers' ),
			'id'                => 'ywqa_enable_answer_excerpt',
			'custom_attributes' => array(
				'min'      => 0,
				'step'     => 1,
				'required' => 'required'
			),
			'default'           => '0'
		),
		'ywqa_anonymise_user'                       => array(
			'name'    => __ ( 'Anonymous mode', 'yith-woocommerce-questions-and-answers' ),
			'type'    => 'checkbox',
			'desc'    => __ ( "Do not show the name of the users that have added a question or an answer.", 'yith-woocommerce-questions-and-answers' ),
			'id'      => 'ywqa_anonymise_user',
			'default' => '1'
		),
		'ywqa_ask_customers'                        => array(
			'name'    => __ ( 'Ask customers for an answer', 'yith-woocommerce-questions-and-answers' ),
			'desc'    => __ ( 'Send an email to whoever purchased a product when a new question is available.', 'yith-woocommerce-questions-and-answers' ),
			'type'    => 'select',
			'options' => array(
				'disabled' => __ ( 'Do not send requests', 'yith-woocommerce-questions-and-answers' ),
				'all'      => __ ( 'Send an email to all customers', 'yith-woocommerce-questions-and-answers' ),
				'custom'   => __ ( 'Send an email to a sample of customers', 'yith-woocommerce-questions-and-answers' ),
			),
			'default' => 'disabled',
			'std'     => 'disabled',
			'id'      => 'ywqa_ask_customers',
		),
		'ywqa_ask_customers_percent'                => array(
			'name'              => __ ( 'Survey sample size', 'yith-woocommerce-questions-and-answers' ),
			'type'              => 'number',
			'desc'              => __ ( '(%) Set the percentage of customers that have bought the product that you want to contact to ask for an answer.', 'yith-woocommerce-questions-and-answers' ),
			'id'                => 'ywqa_ask_customers_percent',
			'custom_attributes' => array(
				'min'      => 1,
				'max'      => 100,
				'step'     => 1,
				'required' => 'required'
			),
			'default'           => '50'
		),
		'ywqa_enable_recaptcha'                     => array(
			'name'    => __ ( 'reCaptcha', 'yith-woocommerce-questions-and-answers' ),
			'type'    => 'checkbox',
			'desc'    => __ ( 'Enable reCaptcha on plugin forms.', 'yith-woocommerce-questions-and-answers' ),
			'id'      => 'ywqa_enable_recaptcha',
			'default' => 'not'
		),
		'ywqa_recaptcha_site_key'                   => array(
			'name' => __ ( 'reCaptcha site key', 'yith-woocommerce-questions-and-answers' ),
			'type' => 'text',
			'desc' => __ ( 'Insert your reCaptcha site key.', 'yith-woocommerce-questions-and-answers' ),
			'id'   => 'ywqa_recaptcha_site_key',
			'css'  => 'min-width:50%;',
		
		),
		'ywqa_recaptcha_secret_key'                 => array(
			'name' => __ ( 'reCaptcha secret key', 'yith-woocommerce-questions-and-answers' ),
			'type' => 'text',
			'desc' => __ ( 'Insert your reCaptcha secret key.', 'yith-woocommerce-questions-and-answers' ),
			'id'   => 'ywqa_recaptcha_secret_key',
			'css'  => 'min-width:50%;',
		),
		'ywqa_enable_search'                        => array(
			'name' => __ ( 'Enable search', 'yith-woocommerce-questions-and-answers' ),
			'type' => 'checkbox',
			'desc' => __ ( 'Show a search bar for filtering questions and answers.', 'yith-woocommerce-questions-and-answers' ),
			'id'   => 'ywqa_enable_search',
			'css'  => 'min-width:50%;',
		),
		'ywqa_search_items'                => array(
			'name'              => __ ( 'Search result items', 'yith-woocommerce-questions-and-answers' ),
			'type'              => 'number',
			'desc'              => __ ( 'Choose the number of results matching the search string to shown.', 'yith-woocommerce-questions-and-answers' ),
			'id'                => 'ywqa_search_items',
			'custom_attributes' => array(
				'min'      => 1,
				'step'     => 1,
			),
			'default'           => '10'
		),
		'section_advanced_settings_end'             => array(
			'type' => 'sectionend',
			'id'   => 'ywqa_section_advanced_end'
		)
	)
);


return apply_filters ( 'ywqa_advanced_options', $advanced_options );

