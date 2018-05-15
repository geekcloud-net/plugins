<?php
/* * class
 * Description of WPEAE_WooCommerce_ProductList
 *
 * @author Geometrix
 * 
 * @position: -1
 */
if (!class_exists('WPEAE_WooCommerce_ProductList')) {

	class WPEAE_WooCommerce_ProductList {

		private $bulk_actions = array();
		private $bulk_actions_text = array();

		public function __construct() {
			if (is_admin()) {
				add_action('admin_footer-edit.php', array($this, 'scripts'));
				add_action('load-edit.php', array($this, 'bulk_actions'));
				add_action('admin_notices', array($this, 'admin_notices'));
				add_filter('post_row_actions', array($this, 'row_actions'), 2, 150);
				add_action('admin_enqueue_scripts', array($this, 'assets'));
				add_action('admin_init', array($this, 'init'));
			}
		}

		function init() {
			if (get_option('wpeae_price_auto_update', false)) {
				$this->bulk_actions[] = 'wpeae_product_update_manual';

				$update_price = get_option('wpeae_regular_price_auto_update', false);
				$text = "Update stock";
				if ($update_price)
					$text = "Update price & stock";

				$this->bulk_actions_text['wpeae_product_update_manual'] = $text;
			}

			list($this->bulk_actions, $this->bulk_actions_text) = apply_filters('wpeae_wcpl_bulk_actions_init', array($this->bulk_actions, $this->bulk_actions_text));
		}

		function row_actions($actions, $post) {
			if ('product' === $post->post_type) {
				$external_id = get_post_meta($post->ID, "external_id", true);
				if ($external_id) {
					$actions = array_merge($actions, array('wpeae_product_info' => sprintf('<a class="wpeae-product-info" id="wpeae-%1$d" href="/">%2$s</a>', $post->ID, 'WooImporter Info')));
				}
			}
			
			return $actions;
		}

		function assets() {

			$plugin_data = get_plugin_data(WPEAE_FILE_FULLNAME);
			wp_enqueue_style('wpeae-wc-pl-style', plugins_url('assets/css/wc_pl_style.css', WPEAE_FILE_FULLNAME), array(), $plugin_data['Version']);
			wp_enqueue_script('jquery-ui-dialog');
			wp_enqueue_script('wpeae-wc-pl-script', plugins_url('assets/js/wc_pl_script.js', WPEAE_FILE_FULLNAME), array(), $plugin_data['Version']);
			
			$lang_data = array(
				'please_wait_data_loads'=>_x('Please wait, data loads..','Status','wpeae'),
				'process_update_d_of_d_erros_d' => _x('Process update %d of %d. Errors: %d.','Status','wpeae'),
				'complete_result_updated_d_erros_d' => _x('Complete! Result updated: %d; errors: %d.','Status','wpeae'),
			);
			
			wp_localize_script('wpeae-wc-pl-script', 'wpeae_wc_pl_script', array('lang' => $lang_data));
		}

		function scripts() {
			global $post_type;

			if ($post_type == 'product') {

				foreach ($this->bulk_actions as $action) {
					$text = $this->bulk_actions_text[$action];
					?>
					<script type="text/javascript">
						jQuery(document).ready(function () {
							jQuery('<option>').val('<?php echo $action; ?>').text('<?php _e($text) ?>').appendTo("select[name='action']");
							jQuery('<option>').val('<?php echo $action; ?>').text('<?php _e($text) ?>').appendTo("select[name='action2']");
						});
					</script>
					<?php
				}
			}
		}

		function bulk_actions() {
			global $typenow;
			$post_type = $typenow;

			if ($post_type == 'product') {

				$wp_list_table = _get_list_table('WP_Posts_List_Table');
				$action = $wp_list_table->current_action();

				$allowed_actions = $this->bulk_actions;
				if (!in_array($action, $allowed_actions))
					return;

				check_admin_referer('bulk-posts');

				// make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'
				if (isset($_REQUEST['post'])) {
					$post_ids = array_map('intval', $_REQUEST['post']);
				}

				if (empty($post_ids))
					return;

				$sendback = remove_query_arg(array_merge($allowed_actions, array('untrashed', 'deleted', 'ids')), wp_get_referer());
				if (!$sendback)
					$sendback = admin_url("edit.php?post_type=$post_type");

				$pagenum = $wp_list_table->get_pagenum();
				$sendback = add_query_arg('paged', $pagenum, $sendback);

				if ($action === 'wpeae_product_update_manual') {

					$updated = 0;
					$skiped = 0;

					foreach ($post_ids as $post_id) {
						$result = $this->perform_update($post_id);
						if ($result === -1)
							$skiped++;
						else if (!$result)
							wp_die(__('Error updating product.'));
						else
							$updated++;
					}

					$sendback = add_query_arg(array('wpeae_updated' => $updated, 'wpeae_skiped' => $skiped, 'ids' => join(',', $post_ids)), $sendback);
				}

				$sendback = apply_filters('wpeae_wcpl_bulk_actions_perform', $sendback, $action, $post_ids);

				$sendback = remove_query_arg(array('action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view'), $sendback);

				wp_redirect($sendback);
				exit();
			}
		}

		/**
		 * Step 3: display an admin notice on the Posts page after exporting
		 */
		function admin_notices() {
			global $post_type, $pagenow;

			if ($pagenow == 'edit.php' && $post_type == 'product' && isset($_REQUEST['wpeae_updated']) && (int) $_REQUEST['wpeae_updated']) {


				$message = sprintf(_n('Product updated.', '%s products updated.', $_REQUEST['wpeae_updated']), number_format_i18n($_REQUEST['wpeae_updated']));

				if (isset($_REQUEST['wpeae_skiped']) && (int) $_REQUEST['wpeae_skiped']) {
					$message .= ' And ' . sprintf(_n('one product skiped.', '%s products skiped.', $_REQUEST['wpeae_skiped']), number_format_i18n($_REQUEST['wpeae_skiped']));
				}

				echo "<div class=\"updated\"><p>{$message}</p></div>";
			}
		}

		function perform_update($post_id) {
			$external_id = get_post_meta($post_id, "external_id", true);

			if ($external_id) {
				wpeae_update_price_proc($post_id, false);
				return true;
			} else
				return -1;
		}

	}

}

new WPEAE_WooCommerce_ProductList();
