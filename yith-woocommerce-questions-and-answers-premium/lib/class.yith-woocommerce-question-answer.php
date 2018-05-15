<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'YITH_WooCommerce_Question_Answer' ) ) {
	
	/**
	 *
	 * @class   YITH_WooCommerce_Question_Answer
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_WooCommerce_Question_Answer {
		
		/**
		 * How much questions to show on first time entering a product page
		 *
		 * @var int
		 */
		public $questions_to_show = 0;
		
		/**
		 * How much answers to show on first time entering a question page
		 *
		 * @var int
		 */
		public $answers_to_show = 0;
		
		/**
		 * Questions and answers can be created only on backend
		 *
		 * @var bool
		 */
		public $faq_mode = false;
		
		/**
		 * @var bool set if the plugin should created a tab on the product tabs
		 */
		public $show_tab = true;
		
		/**
		 * question has to be approved before it may be shown.
		 *
		 * @var bool
		 */
		public $question_manual_approval = false;
		
		/**
		 * @var bool answers has to be approved before it may be shown
		 */
		public $answer_manual_approval = false;
		
		/**
		 * @var bool allow guest users to enter questions or answers
		 */
		public $allow_guest_users = true;
		
		/**
		 * @var bool guest users must fill his own name and email in order to submit some content
		 */
		public $mandatory_guest_data = false;
		
		
		/**
		 * Single instance of the class
		 *
		 * @since 1.0.0
		 */
		protected static $instance;
		
		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			
			return self::$instance;
		}
		
		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0
		 * @author Lorenzo Giuffrida
		 */
		protected function __construct() {
			
			$this->init_plugin_settings();
			
			$this->includes();
			
			$this->init_hooks();
		}
		
		public function init_hooks() {
			/**
			 * Add a tab to WooCommerce products tabs, if the plugin option enable it
			 */
			if ( $this->show_tab ) {
				add_filter( 'woocommerce_product_tabs', array( $this, 'show_question_answer_tab' ), 20 );
			}
			
			/**
			 * Do some stuff on plugin init
			 */
			add_action( 'init', array( $this, 'on_plugin_init' ) );
			
			/** Add styles and scripts */
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ), 5, 2 );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles_scripts' ) );
			// Add to admin_init function
			add_filter( 'manage_edit-question_answer_columns', array( $this, 'add_custom_columns_title' ) );
			
			// Add to admin_init function
			add_action( 'manage_question_answer_posts_custom_column', array(
				$this,
				'add_custom_columns_content',
			), 10, 2 );
			
			/**
			 * Add metabox to question and answer post type
			 */
			add_action( 'add_meta_boxes', array( $this, 'add_plugin_metabox' ), 10, 2 );
			
			/**
			 * Save data from question and answer post type metabox
			 */
			add_action( 'save_post', array( $this, 'save_plugin_metabox' ), 1, 2 );
			
			add_filter( 'wp_insert_post_data', array( $this, 'before_insert_discussion' ), 99, 2 );
			
			/**
			 * Insert an answer from an ajax request
			 */
			add_action( 'wp_ajax_submit_answer', array( $this, 'submit_answer_callback' ) );
			
			/**
			 *
			 */
			add_action( 'admin_head-post-new.php', array( $this, 'limit_products_creation' ) );
			add_action( 'admin_head-edit.php', array( $this, 'limit_products_creation' ) );
			add_action( 'admin_menu', array( $this, 'remove_add_product_link' ) );
			
			/*
			 * Avoid "View Post" link when a Q&A custom post type is saved
			 */
			add_filter( 'post_updated_messages', array( $this, 'avoid_view_post_link' ) );
		}
		
		public function includes() {
			
			if ( is_admin() ) {

//				require_once( YITH_YWBC_INCLUDES_DIR . 'class-yith-ywbc-backend.php' );
			}
			
			if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				require_once( YITH_YWQA_LIB_DIR . 'class.yith-ywqa-frontend.php' );
			}
		}
		
		/*
		 * Avoid "View Post" link when a Q&A custom post type is saved
		 */
		public function avoid_view_post_link( $messages ) {
			$messages['post'][1] = __( 'Content updated.', 'yith-woocommerce-questions-and-answers' );
			$messages['post'][4] = __( 'Content updated.', 'yith-woocommerce-questions-and-answers' );
			$messages['post'][6] = __( 'Content published.', 'yith-woocommerce-questions-and-answers' );
			$messages['post'][7] = __( 'Content saved.', 'yith-woocommerce-questions-and-answers' );
			$messages['post'][8] = __( 'Content submitted.', 'yith-woocommerce-questions-and-answers' );
			
			return $messages;
		}
		
		/**
		 * Init plugin settings
		 */
		public function init_plugin_settings() {
			$this->questions_to_show        = get_option( 'ywqa_questions_to_show', false );
			$this->answers_to_show          = get_option( 'ywqa_answers_to_show', false );
			$this->faq_mode                 = ( "yes" === get_option( "ywqa_faq_mode", "no" ) ) ? true : false;
			$this->show_tab                 = ( "yes" === get_option( "ywqa_attach_to_tabs", "no" ) ) ? true : false;
			$this->question_manual_approval = ( "yes" === get_option( "ywqa_question_manual_approval", "no" ) ) ? 1 : 0;
			$this->answer_manual_approval   = ( "yes" === get_option( "ywqa_answer_manual_approval", "no" ) ) ? 1 : 0;
			$this->allow_guest_users        = ( "yes" === get_option( "ywqa_allow_guest", "no" ) ) ? 1 : 0;
			$this->mandatory_guest_data     = ( "yes" === get_option( "ywqa_mandatory_guest_data", "no" ) ) ? true : false;
			
		}
		
		public function limit_products_creation() {
			global $post_type;
			
			if ( YWQA_CUSTOM_POST_TYPE_NAME != $post_type ) {
				return;
			}
		}
		
		public function remove_add_product_link() {
			global $post_type;
			
			if ( YWQA_CUSTOM_POST_TYPE_NAME != $post_type ) {
				return;
			}
			
			echo '<style>.add-new-h2{ display: none; }</style>';
		}
		
		public function submit_answer_callback() {
			$args = array(
				"content"              => $_POST["answer_content"],
				"discussion_author_id" => get_current_user_id(),
				"product_id"           => $_POST["product_id"],
				"parent_id"            => $_POST["question_id"],
			);
			
			if ( ! get_current_user_id() ) {
				$args['discussion_author_name']  = $this->allow_guest_users && isset( $_POST['ywqa-guest-name'] ) ? $_POST['ywqa-guest-name'] : __( "Anonymous user", 'yith-woocommerce-questions-and-answers' );
				$args['discussion_author_email'] = $this->allow_guest_users && isset( $_POST['ywqa-guest-email'] ) ? $_POST['ywqa-guest-email'] : '';
			}
			
			$answer         = new YWQA_Answer( $args );
			$answer->status = "publish";
			$result         = $answer->save();
			if ( ! $result ) {
				wp_send_json( array(
					"code" => - 1,
				) );
			}
			
			wp_send_json( array(
				"code" => 1,
			) );
		}
		
		function before_insert_discussion( $data, $postarr ) {
			if ( $data['post_type'] == YWQA_CUSTOM_POST_TYPE_NAME ) {
				
				if ( isset( $postarr["select_product"] ) ) {
					$data["post_parent"] = $postarr["select_product"];
				}
				
				/*
				 * Update the title for the custom post type, trimming the discussion content
				 */
				$data["post_title"] = ywqa_strip_trim_text( $data["post_content"] );
			}
			
			return $data;
		}
		
		/**
		 * Add metabox for the CPT
		 *
		 * @param string  $post_type the post type
		 * @param WP_Post $post      the Post being shown
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		function add_plugin_metabox( $post_type, $post ) {
			
			if ( ! $post || ! isset( $post->ID ) ) {  // Prevent a warning shown on Members plugin page
				return;
			}
			
			$discussion = $this->get_discussion( $post->ID );
			if ( $discussion instanceof YWQA_Question ) {
				$metabox_title = __( "Questions & Answers - Manage answers for this question", 'yith-woocommerce-questions-and-answers' );
			} else {
				$metabox_title = __( "Questions & Answers - Question information", 'yith-woocommerce-questions-and-answers' );
			}
			
			add_meta_box( 'ywqa_metabox', $metabox_title, array(
				$this,
				'display_plugin_metabox',
			), 'question_answer', 'normal', 'default' );
		}
		
		/**
		 * Show an answer content to be shown on question backend page
		 *
		 * @param $answer
		 */
		public function show_single_answer_backend( $answer ) {
			/** @var  YWQA_Answer $answer */
			
			?>
			<li id="li-answer-<?php echo $answer->ID; ?>"
			    class=" <?php echo $answer->get_item_class( "discussion-container" ); ?>">
				<?php if ( $answer->is_unapproved() ) : ?>
					<div
						class="badge unapproved"><?php _e( "UNAPPROVED", 'yith-woocommerce-questions-and-answers' ); ?></div>
				<?php elseif ( $answer->is_inappropriate() ) : ?>
					<div
						class="badge inappropriate"><?php _e( "INAPPROPRIATE", 'yith-woocommerce-questions-and-answers' ); ?></div>
				<?php endif; ?>
				
				<div class="answer-content">
					<span class="answer-text"><?php echo $answer->content; ?></span>
					
					<div class="answer-owner">
						<?php echo sprintf( __( "%s answered on %s", 'yith-woocommerce-questions-and-answers' ),
							'<span class="answer-author-name">' . $answer->get_author_name() . '</span>',
							'<span class="answer-date">' . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $answer->date ) ) . '</span>' );
						?>
					</div>
				</div>
				<?php $settings = array(
					'textarea_name' => 'edit-answer-' . $answer->ID,
					'editor_height' => 150, // In pixels, takes precedence and has no default value
					'wpautop'       => false,
				); ?>
				<?php wp_editor( $answer->content, "edit-answer-" . $answer->ID/*yith_number_to_letter($answer->ID)*/, $settings ); ?>
				
				<textarea class="answer-text-editor"><?php echo $answer->content; ?></textarea>
				<span
					class="confirm-delete"><?php _e( "Are you sure you want to delete it?", 'yith-woocommerce-questions-and-answers' ); ?></span>
				
				<div class="ywqa-actions">
					<a href="#" class="action-modify"
					   data-discussion-id="<?php echo $answer->ID; ?>"><?php _e( "Edit", 'yith-woocommerce-questions-and-answers' ); ?></a>
					<a href="#" class="action-delete"
					   data-discussion-id="<?php echo $answer->ID; ?>"><?php _e( "Delete", 'yith-woocommerce-questions-and-answers' ); ?></a>
					
					<?php if ( $answer->is_unapproved() || $answer->is_inappropriate() ) : ?>
						<a href="#" class="change-status action-approve"
						   data-action="set_approved"
						   data-discussion-id="<?php echo $answer->ID; ?>"><?php _e( "Approve", 'yith-woocommerce-questions-and-answers' ); ?></a>
					<?php else: ?>
						<a href="#" class="change-status action-unapprove"
						   data-action="set_unapproved"
						   data-discussion-id="<?php echo $answer->ID; ?>"><?php _e( "Unapprove", 'yith-woocommerce-questions-and-answers' ); ?></a>
					<?php endif; ?>
					
					<?php /*if ($answer->is_inappropriate()) : ?>
                        <a href="#"
                           class="change-status action-appropriate inappropriate"
                           data-action="set_appropriate"
                           data-discussion-id="<?php echo $answer->ID; ?>"><?php _e("Set appropriate", 'yith-woocommerce-questions-and-answers'); ?></a>
                    <?php else: ?>
                        <a href="#"
                           class="change-status action-inappropriate"
                           data-action="set_inappropriate"
                           data-discussion-id="<?php echo $answer->ID; ?>"><?php _e("Set inappropriate", 'yith-woocommerce-questions-and-answers'); ?></a>
                    <?php endif; */
					?>
				
				</div>
				<div class="ywqa-modify-content">
					<a href="#" class="action-confirm"
					   data-discussion-id="<?php echo $answer->ID; ?>"
					   data-op-type=""><?php _e( "Confirm", 'yith-woocommerce-questions-and-answers' ); ?></a>
					<a href="#"
					   class="action-cancel"><?php _e( "Cancel", 'yith-woocommerce-questions-and-answers' ); ?></a>
				</div>
			</li>
			<?php
		}
		
		public function show_answers_backend( $question ) {
			/** @var YWQA_Answer $answers */
			$answers = $question->get_answers( - 1, 1, "recent", false );
			
			?>
			<div id="answers">
				<div class="ywqa-section-title">
                    <span
	                    class="answers-block-title"><?php _e( "Answers for this question", 'yith-woocommerce-questions-and-answers' ); ?></span>
				</div>
				<?php if ( count( $answers ) ): ?>
					<ol class="ywqa-items-list answers">
						
						<?php foreach ( $answers as $answer ): ?>
							<?php $this->show_single_answer_backend( $answer ); ?>
						<?php endforeach; ?>
					</ol>
				<?php else: ?>
					<?php _e( "There are no answers for this question yet", 'yith-woocommerce-questions-and-answers' ); ?>
				<?php endif; ?>
			</div>
			<?php
		}
		
		public function show_product_chosen() {
			global $wpdb;
			
			$products = $wpdb->get_results( "select ID, post_title
				from {$wpdb->prefix}posts
				where post_type = 'product'
				order by post_title" );
			
			?>
			<table class="form-table">
				<tbody>
				<tr valign="top" class="titledesc">
					<th scope="row">
						<label
							for="product"><?php _e( 'Select product', 'yith-woocommerce-questions-and-answers' ); ?></label>
					</th>
					<td class="forminp yith-choosen">
                        
                        <?php
                        yit_add_select2_fields( array(
                            'class'         => 'wc-product-search',
                            'id'            => '',
                            'name'          => 'select_product',
                            'style'         => 'width:50%;',
                            'data-multiple' => false,
                            'data-selected' => '',
                            'value'         => '',
                        ) );?>
					</td>
				</tr>
				</tbody>
			</table>
			
			<?php
		}
		
		/**
		 * Retrieve formatted information about a question or answer author
		 *
		 * @param YWQA_Discussion $discussion the discussion
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 *
		 * @return string
		 */
		public function get_author_information( $discussion ) {
			$user        = get_userdata( $discussion->discussion_author_id );
			$author_info = '';
			
			if ( $user ) {
				$author_info = '<a href="' . get_edit_user_link( $user->ID ) . '" class="review-author">' . sprintf( "%s (%s)", $user->display_name, $user->user_email ) . '</span>';
				
			} elseif ( ! empty( $discussion->discussion_author_name ) ) {
				$author_info = '<span class="review-author">' . $discussion->discussion_author_name . '</span>';
				if ( ! empty( $discussion->discussion_author_email ) ) {
					$author_info .= ' (' . $discussion->discussion_author_email . ')';
				}
				$author_info .= '</span>';
				
			} else {
				$author_info = '<span class="review-author">' . __( 'Anonymous', 'yith-woocommerce-questions-and-answers' ) . '</span>';
			}
			
			return $author_info;
		}
		
		/**
		 * Display the Q&A metabox on CPT pages
		 *
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function display_plugin_metabox() {
			//  Display different metabox content when it's a new question or answer

			//if ( isset( $_GET["post"] ) ) {
            $discussion = false;

			if ( isset( $_GET["post"] ) ) {
				$discussion = $this->get_discussion( $_GET["post"] );
				if( $discussion ) {
					$product_id = $discussion->product_id;
					$product    = wc_get_product( $product_id );
				}
			}

			if ( $discussion instanceof YWQA_Question ) { ?>
                <div id="question-content-div">
                    <div class="ywqa-section-title">
                        <table class="ywqa_info_table">
                            <tbody>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label><?php _e( "Product", 'yith-woocommerce-questions-and-answers' ); ?></label>
                                </th>
								<?php if ( $product ) : ?>
                                    <a target="_blank"
                                       href="<?php echo get_permalink( $product_id ); ?>"><?php echo $product->get_title(); ?></a>
								<?php endif; ?>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label><?php _e( "Author", 'yith-woocommerce-questions-and-answers' ); ?></label>
                                </th>
                                <td>
									<?php echo $this->get_author_information( $discussion ); ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="ywqa-add-answer">
                             <span
                                     class="answers-block-title"><?php _e( "Your answer", 'yith-woocommerce-questions-and-answers' ); ?></span>

                        <div class="ywqa-add-answer-content">
                            <input type="hidden" id="product_id" name="product_id"
                                   value="<?php echo $product_id ?>">
                            <input type="hidden" id="discussion_type" name="discussion_type" value="edit-question">
							<?php $settings = array(
								'textarea_name' => 'respond-to-question-text',
								'editor_height' => 150, // In pixels, takes precedence and has no default value
								'wpautop'       => false,
							); ?>
							<?php wp_editor( '', 'respond-to-question', $settings ); ?>

                            <!-- <textarea id="respond-to-question" name="respond-to-question" placeholder="Write an answer"
									  rows="5"></textarea> -->
                            <input id="submit-answer" class="button button-primary button-large" type="submit"
                                   value="<?php _e( "Answer", 'yith-woocommerce-questions-and-answers' ); ?>">
                        </div>
                    </div>
                </div>

				<?php
				$this->show_answers_backend( $discussion );

				//} else {
				//	if ( $discussion instanceof YWQA_Answer ) {
			} elseif ( $discussion instanceof YWQA_Answer ) {
				/** @var YWQA_Question $question */
				$question   = $discussion->get_question();
				$product_id = $discussion->product_id;
				$product    = wc_get_product( $product_id );
				?>
                <div id="question-content-div">
                    <table class="ywqa_info_table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label><?php _e( "Product", 'yith-woocommerce-questions-and-answers' ); ?></label>
                            </th>
                            <td>
                                <a target="_blank"
                                   href="<?php echo get_permalink( $product_id ); ?>"><?php echo $product->get_title(); ?></a>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label><?php _e( "Question", 'yith-woocommerce-questions-and-answers' ); ?></label>
                            </th>
                            <td>
								<?php echo $question->content; ?>
								<?php edit_post_link( __( "Go to question", 'yith-woocommerce-questions-and-answers' ), '', '', $question->ID ); ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </div>
                <input type="hidden" id="discussion_type" name="discussion_type" value="edit-answer">
				<?php
			}
			//}
			//} else {
			else {
				//  it's a new question, let it choose the product to be related to
				?>
                <input type="hidden" id="discussion_type" name="discussion_type" value="new-question">
				<?php
				$this->show_product_chosen();
				//}
			}

		}
		
		/**
		 * Save the Metabox Data
		 *
		 * @param $post_id
		 * @param $post
		 *
		 * @return mixed
		 */
		function save_plugin_metabox( $post_id, $post ) {
			
			if ( YWQA_CUSTOM_POST_TYPE_NAME != $post->post_type ) {
				return;
			}
			
			// verify this is not an auto save routine.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}
			
			/**
			 * Update the discussion inserted
			 */
			if ( isset( $_POST["select_product"] ) ) {
				
				update_post_meta( $post_id, YWQA_METAKEY_PRODUCT_ID, $_POST["select_product"] );
				update_post_meta( $post_id, YWQA_METAKEY_DISCUSSION_TYPE, "question" );
			}
		}
		
		/**
		 * Add custom columns to custom post type table
		 *
		 * @param array $defaults current columns being shown
		 *
		 * @return array new columns
		 */
		function add_custom_columns_title( $defaults ) {
			
			$columns = array_slice( $defaults, 0, 1 );
			
			$columns["image_type"] = '';
			
			return apply_filters( 'yith_questions_answers_custom_column_title', array_merge( $columns, array_slice( $defaults, 1 ) ) );
		}
		
		/**
		 * show content for custom columns
		 *
		 * @param string $column_name column shown
		 * @param int    $post_ID     post to use
		 */
		function add_custom_columns_content( $column_name, $post_ID ) {
			switch ( $column_name ) {
				case 'image_type' :
					
					$discussion = $this->get_discussion( $post_ID );
					if ( $discussion instanceof YWQA_Question ) {
						echo '<span class="dashicons dashicons-admin-comments"></span>';
					} else {
						if ( $discussion instanceof YWQA_Answer ) {
							echo '<span class="dashicons dashicons-admin-page"></span>';
						}
					}
					break;
				
				default:
					do_action( "yith_questions_answers_custom_column_content", $column_name, $post_ID );
			}
		}
		
		/**
		 * Retrieve the instance of the correct object based on the content type of
		 * the post.
		 *
		 * @param $post_id
		 *
		 * @return null|YWQA_Answer|YWQA_Question
		 */
		public function get_discussion( $post_id ) {
			
			$discussion_type = get_post_meta( $post_id, YWQA_METAKEY_DISCUSSION_TYPE, true );

			if ( "question" === $discussion_type ) {
				return new YWQA_Question( $post_id );
			} else if ( "answer" === $discussion_type ) {
				return new YWQA_Answer( $post_id );
			}
			
			return null;
		}
		
		/**
		 *  Execute all the operation need when the plugin init
		 */
		public function on_plugin_init() {
			
			$this->init_post_type();
			
			if ( $this->is_new_question() ) {
				return;
			}
			
			if ( $this->is_new_answer() ) {
				return;
			}
		}
		
		
		/**
		 * Execute update on data used by the plugin that has been changed passing
		 * from a DB version to another
		 */
		public static function update() {
			
			/**
			 * Init DB version if not exists
			 */
			$db_version = get_option( 'yith_qa_db_version' );
			if ( ! $db_version ) {
				$db_version = '0';
			}
			
			if ( version_compare( $db_version, YITH_YWQA_DB_VERSION, '>=' ) ) {
				return; //last DB version in use
			}
			
			//  Updates from DB version earlier than 1.0.0
			if ( version_compare( $db_version, '1.0.0', '<' ) ) {
				
				global $wpdb;
				
				$query = $wpdb->prepare( "update {$wpdb->prefix}posts
                set post_content = post_title
                where post_type = %s and post_content = ''",
					YWQA_CUSTOM_POST_TYPE_NAME
				);
				
				$wpdb->query( $query );
				$db_version = '1.0.0';
			}
			
			//  Updates from DB version from 1.0.0. to 1.0.1
			if ( version_compare( $db_version, '1.0.1', '<' ) ) {
				//  Create the YWQA_METAKEY_DISCUSSION_AUTHOR_NAME and YWQA_METAKEY_DISCUSSION_AUTHOR_ID postmeta for previous questions and answers
				global $wpdb;
				
				$query = $wpdb->prepare( "
                    select ID, post_author
                    from {$wpdb->prefix}posts
                    where post_type = %s",
					YWQA_CUSTOM_POST_TYPE_NAME
				);
				
				$items = $wpdb->get_results( $query, ARRAY_A );
				
				foreach ( $items as $items ) {
					update_post_meta( $items["ID"], YWQA_METAKEY_DISCUSSION_AUTHOR_ID, $items["post_author"] );
				}
				
				$db_version = '1.0.1';
			}
			
			//  Updates from DB version from x.x.x. to y.y.y
			//todo follow this convention
			/*if ( version_compare ( $db_version, '1.0.1', '<' ) ) {
				//  update something
				$db_version = 'y.y.y';
			}*/
			
			//  Finally, update DB version to current value of YITH_YWQA_DB_VERSION
			update_option( 'yith_qa_db_version', YITH_YWQA_DB_VERSION );
		}
		
		/**
		 * Register the custom post type
		 */
		public function init_post_type() {
			
			// Set UI labels for Custom Post Type
			$labels = array(
				'name'               => _x( 'Questions & Answers', 'Post Type General Name', 'yith-woocommerce-questions-and-answers' ),
				'singular_name'      => _x( 'Question', 'Post Type Singular Name', 'yith-woocommerce-questions-and-answers' ),
				'menu_name'          => __( 'Questions & Answers', 'yith-woocommerce-questions-and-answers' ),
				'parent_item_colon'  => __( 'Parent discussion', 'yith-woocommerce-questions-and-answers' ),
				'all_items'          => __( 'All discussion', 'yith-woocommerce-questions-and-answers' ),
				'view_item'          => __( 'View discussions', 'yith-woocommerce-questions-and-answers' ),
				'add_new_item'       => __( 'Add new question', 'yith-woocommerce-questions-and-answers' ),
				'add_new'            => __( 'Add new', 'yith-woocommerce-questions-and-answers' ),
				'edit_item'          => __( 'Edit discussion', 'yith-woocommerce-questions-and-answers' ),
				'update_item'        => __( 'Update discussion', 'yith-woocommerce-questions-and-answers' ),
				'search_items'       => __( 'Search discussion', 'yith-woocommerce-questions-and-answers' ),
				'not_found'          => __( 'Not found', 'yith-woocommerce-questions-and-answers' ),
				'not_found_in_trash' => __( 'Not found in the bin', 'yith-woocommerce-questions-and-answers' ),
			);
			
			// Set other options for Custom Post Type
			$args = array(
				'label'               => __( 'Questions & Answers', 'yith-woocommerce-questions-and-answers' ),
				'description'         => __( 'YITH Questions and Answers', 'yith-woocommerce-questions-and-answers' ),
				'labels'              => $labels,
				// Features this CPT supports in Post Editor
				'supports'            => array(
					//'title',
					'editor',
					//'author',
				),
				'hierarchical'        => false,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'menu_position'       => 9,
				'can_export'          => false,
				'has_archive'         => false,
				'exclude_from_search' => true,
				'menu_icon'           => 'dashicons-clipboard',
				'query_var'           => false,
			);
			
			// Registering your Custom Post Type
			register_post_type( YWQA_CUSTOM_POST_TYPE_NAME, $args );
		}
		
		/**
		 * Check if there is a new question or answer from the user
		 *
		 * @return bool it's a new question
		 */
		public function is_new_question() {
			if ( ! isset( $_POST["add_new_question"] ) ) {
				return false;
			}
			
			if ( ! isset( $_POST["ywqa_product_id"] ) ) {
				return false;
			}
			
			if ( ! isset( $_POST["ywqa_user_content"] ) || empty( $_POST["ywqa_user_content"] ) ) {
				return false;
			}
			
			if (
				! isset( $_POST['ask_question'] )
				|| ! wp_verify_nonce( $_POST['ask_question'], 'ask_question_' . $_POST["ywqa_product_id"] )
			) {
				
				_e( "Please retry submitting your question or answer.", 'yith-woocommerce-questions-and-answers' );
				exit;
			}
			
			
			$product_id = intval( $_POST['ywqa_product_id'] );
			if ( ! $product_id ) {
				_e( "No product ID selected, the question will not be created.", 'yith-woocommerce-questions-and-answers' );
				exit;
			}
			
			$args = array(
				'content'              => sanitize_text_field( $_POST["ywqa_user_content"] ),
				'discussion_author_id' => get_current_user_id(),
				'product_id'           => $product_id,
				'parent_id'            => $product_id,
			);
			
			if ( ! get_current_user_id() ) {
				$args['discussion_author_name']  = $this->allow_guest_users && isset( $_POST['ywqa-guest-name'] ) ? $_POST['ywqa-guest-name'] : __( "Anonymous user", 'yith-woocommerce-questions-and-answers' );
				$args['discussion_author_email'] = $this->allow_guest_users && isset( $_POST['ywqa-guest-email'] ) ? $_POST['ywqa-guest-email'] : '';
			}
			
			$this->create_question( $args );
		}
		
		/**
		 * Create a new question
		 *
		 * @param array $args crate a question with arguments
		 *
		 * @return YWQA_Question
		 */
		public function create_question( $args ) {
			
			$question = new YWQA_Question( $args );
			$question = apply_filters( "yith_questions_answers_before_new_question", $question );
			
			$question->save();
			
			do_action( "yith_questions_answers_after_new_question", $question );
			
			return $question;
		}
		
		/**
		 * Check if there is a new answer
		 *
		 * @return bool it's a new answer
		 */
		public function is_new_answer() {
			
			if ( ! isset( $_POST["add_new_answer"] ) ) {
				return false;
			}
			
			if ( ! isset( $_POST["ywqa_product_id"] ) ) {
				return false;
			}
			
			if ( ! isset( $_POST["ywqa_question_id"] ) ) {
				return false;
			}
			
			if ( ! isset( $_POST["ywqa_user_content"] ) || empty( $_POST["ywqa_user_content"] ) ) {
				return false;
			}
			
			if (
				! isset( $_POST['send_answer'] )
				|| ! wp_verify_nonce( $_POST['send_answer'], 'submit_answer_' . $_POST["ywqa_question_id"] )
			) {
				
				_e( "Please retry submitting your question or answer.", 'yith-woocommerce-questions-and-answers' );
				exit;
			}
			
			$args = array(
				'content'              => sanitize_text_field( $_POST["ywqa_user_content"] ),
				'discussion_author_id' => get_current_user_id(),
				'product_id'           => $_POST["ywqa_product_id"],
				'parent_id'            => $_POST["ywqa_question_id"],
			);
			
			if ( ! get_current_user_id() ) {
				$args['discussion_author_name']  = $this->allow_guest_users && isset( $_POST['ywqa-guest-name'] ) ? $_POST['ywqa-guest-name'] : __( "Anonymous user", 'yith-woocommerce-questions-and-answers' );
				$args['discussion_author_email'] = $this->allow_guest_users && isset( $_POST['ywqa-guest-email'] ) ? $_POST['ywqa-guest-email'] : '';
			}
			
			$this->create_answer( $args );
		}
		
		/**
		 * Create new answer
		 *
		 * @param array $args
		 */
		public function create_answer( $args ) {
			
			$answer = new YWQA_Answer( $args );
			$answer = apply_filters( "yith_questions_answers_before_new_answer", $answer );
			$answer->save();
			do_action( "yith_questions_answers_after_new_answer", $answer );
			
			return $answer;
		}
		
		/**
		 * Add frontend style
		 *
		 * @since  1.0
		 * @author Lorenzo Giuffrida
		 */
		public function enqueue_styles_scripts() {
			$maintanance = isset( $_GET["script_debug_on"] );
			$suffix      = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || $maintanance ? '' : '.min';
			
			//  register and enqueue ajax calls related script file
			wp_register_script( "ywqa-frontend",
				YITH_YWQA_URL . 'assets/js/' . yit_load_js_file( 'ywqa-frontend.js' ),
				array(
					'jquery',
					'woocommerce',
				), YITH_YWQA_VERSION,
				true );
			
			wp_enqueue_style( 'ywqa-frontend', YITH_YWQA_ASSETS_URL . '/css/ywqa-frontend.css' );
		}
		
		/**
		 * Enqueue scripts on administration comment page
		 *
		 * @param $hook
		 */
		function admin_enqueue_styles_scripts( $hook ) {
			global $post_type;
			
			$maintanance = isset( $_GET["script_debug_on"] );
			$suffix      = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) || $maintanance ? '' : '.min';
			
			if ( YWQA_CUSTOM_POST_TYPE_NAME != $post_type ) {
				return;
			}
			
			//  Avoid auto save
			wp_dequeue_script( 'autosave' );
			
			/**
			 * Add styles
			 */
			wp_enqueue_style( 'ywqa-backend', YITH_YWQA_ASSETS_URL . '/css/ywqa-backend.css' );
			
			/**
			 * Add scripts
			 */
			wp_register_script( "ywqa-backend",
				YITH_YWQA_URL . 'assets/js/' . yit_load_js_file( 'ywqa-backend.js' ),
				array(
					'jquery',
					'jquery-blockui',
					'select2',
				),
				YITH_YWQA_VERSION,
				true );
			
			wp_localize_script( 'ywqa-backend', 'ywqa', array(
				'empty_answer'   => __( "You need to write something!", 'yith-woocommerce-questions-and-answers' ),
				'answer_success' => __( "Answer correctly sent.", 'yith-woocommerce-questions-and-answers' ),
				'answer_error'   => __( "An error occurred, your answer has not been added.", 'yith-woocommerce-questions-and-answers' ),
				'loader'         => apply_filters( 'yith_questions_and_answers_loader', YITH_YWQA_ASSETS_URL . '/images/loading.gif' ),
				'ajax_url'       => admin_url( 'admin-ajax.php' ),
			) );
			
			wp_enqueue_script( "ywqa-backend" );
		}
		
		/**
		 * Add a tab for question & answer
		 *
		 * @param $tabs tabs with description for product reviews
		 *
		 * @return mixed
		 */
		public function show_question_answer_tab( $tabs ) {
			global $product;
			
			$tab_title = get_option('ywqa_tab_label','Questions & Answers');
			
			$product_id = yit_get_prop( $product, 'id' );
			if ( isset( $product_id ) ) {
				$count = $this->get_questions_count( $product_id );
				
				if ( $count ) {
					$tab_title .= sprintf( " (%d)", $count );
					$tab_title = apply_filters('yith_ywqa_tab_title',$tab_title,$product_id,$count);
				}
			}
			
			if ( ! isset( $tabs["questions"] ) ) {
				$tabs["questions"] = array(
					'title'    => $tab_title,
					'priority' => 99,
					'callback' => array( $this, 'show_main_template' ),
				);
			}
			
			return $tabs;
		}
		
		/**
		 * Show the question or answer template file
		 */
		public function show_main_template() {
			global $product;

			wc_get_template( 'single-product/yith-questions-and-answers.php',
				array(
					'max_items'     => isset( $_GET["show-all-questions"] ) ? - 1 : $this->questions_to_show,
					'only_answered' => isset( $_GET["show-all-questions"] ) ? 0 : 1,
					'product_id'    => yit_get_prop( $product, 'id' ),
				),
				'', YITH_YWQA_TEMPLATES_DIR );
		}
		
		public function get_questions_count( $product_id, $only_answered = false ) {
			global $wpdb;
			
			$answered_query = '';
			if ( $only_answered ) {
				$answered_query = " and que.ID in (select distinct(post_parent) from {$wpdb->prefix}posts where post_status = 'publish') ";
			}
			
			$query = $wpdb->prepare( "select count(que.ID)
				from {$wpdb->prefix}posts as que left join {$wpdb->prefix}posts as pro
				on que.post_parent = pro.ID
				where que.post_status = 'publish'
				and que.post_type = %s
				and pro.post_type = 'product'
				and pro.ID = %d" . $answered_query,
				YWQA_CUSTOM_POST_TYPE_NAME,
				$product_id
			);
			
			$items = $wpdb->get_row( $query, ARRAY_N );
			
			return $items[0];
		}
		
		/**
		 * Retrieve the questions
		 *
		 * @param int    $product_id
		 * @param string $items
		 * @param int    $page
		 * @param bool   $only_answered
		 *
		 * @return array
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function get_questions( $product_id, $items = 'auto', $page = 1, $only_answered = false ) {
			global $wpdb;
			
			if ( 'auto' === $items ) {
				$items = $this->questions_to_show;
			}
			
			$query_limit = '';
			if ( $items > 0 ) {
				$query_limit = sprintf( " limit %d,%d ", ( $page - 1 ) * $items, $items );
			}
			
			$order_by_query = " order by post_date DESC ";
			
			$answered_query = '';
			if ( $only_answered ) {
				$answered_query = " and ID in (select distinct(post_parent) from {$wpdb->posts} where post_status = 'publish') ";
			}
			
			$query = $wpdb->prepare( "select ID
				from {$wpdb->posts}
				where post_status = 'publish' and
				      post_type = %s and
				      post_parent = %d " . $answered_query . $order_by_query . $query_limit,
				YWQA_CUSTOM_POST_TYPE_NAME,
				$product_id
			);
			
			$post_ids = $wpdb->get_results( $query, ARRAY_A );
			
			$questions = array();
			
			foreach ( $post_ids as $item ) {
				$questions[] = new YWQA_Question( $item["ID"] );
			}
			
			return $questions;
		}
		
		
		/**
		 * Retrieve the item from the id
		 *
		 * @param $item_id id of item to be retrieved
		 *
		 * @return array|null|WP_Post
		 */
		public function get_item( $item_id ) {
			
			$question = new YWQA_Question( $item_id );
			
			return $question;
		}
		
		/**
		 * Show questions by product
		 *
		 * @param int    $product_id
		 * @param string $items
		 * @param int    $page
		 * @param bool   $only_answered
		 *
		 * @return int
		 * @author Lorenzo Giuffrida
		 * @since  1.0.0
		 */
		public function show_questions( $product_id, $items = 'auto', $page = 1, $only_answered = false ) {
			
			$questions = $this->get_questions( $product_id, $items, $page, $only_answered );
			
			foreach ( $questions as $question ) {
				$this->show_question( $question );
			}
			
			return count( $questions );
		}
		
		/**
		 * Call the question template file and show the content
		 *
		 * @param array  $question question to be shown
		 * @param string $classes  CSS classes to use
		 *
		 */
		public function show_question( $question, $classes = '' ) {
			
			wc_get_template( 'single-product/ywqa-single-question.php', array(
				'question' => $question,
				'classes'  => $classes,
			), '', YITH_YWQA_TEMPLATES_DIR );
		}
		
		/**
		 * Show answers for a specific question, with custom order type.
		 *
		 * @param YWQA_Question $question the question
		 * @param int            $count    how may items to show
		 * @param int            $page     page index when using the pagination
		 * @param string         $order    change order of visualization ("recent", "oldest", "useful")
		 */
		public function show_answers( $question, $count = - 1, $page = 1, $order = "recent" ) {
			
			foreach ( $question->get_answers( $count, $page, $order ) as $answer ) {
				
				$this->show_answer( $answer );
			}
		}
		
		/**
		 * Call the question template file and show the content
		 *
		 * @param $question question to be shown
		 */
		public function show_answer( $answer, $classes = '' ) {
			
			wc_get_template( 'single-product/ywqa-single-answer.php', array(
				'answer'  => $answer,
				'classes' => $classes,
			), '', YITH_YWQA_TEMPLATES_DIR );
		}
	}
}