<?php

/**
 * Description of WPEAE_AbstractAccount
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_AbstractAccount')):

	abstract class WPEAE_AbstractAccount {

		public $api = false;
		public $id = false;
		public $name = "";
		public $default = true;

		public function __construct($api) {
			$this->api = $api;
			$this->id = false;
			$this->default = $this->is_default_account();
			$this->load();
		}

		abstract protected function load_default();

		abstract public function get_form();

		public function print_form() {
			echo '<div class="account-content">';
			$form = $this->get_form();
			foreach ($form["fields"] as $field) {
				if ($field['type'] == "hidden") {
					echo '<input type="hidden" id="' . $field["id"] . '" name="<?php echo $field["name"]; ?>" value="' . $field["value"] . '"/>';
				}
			}
			echo '<h3>' . $form["title"] . '</h3>';

			if ($form["use_default_account"]) {
				printf('%s<a href="#" class="use_custom_account_param">%s</a>', _x('Using Default ', 'Setting desc', 'wpeae'), _x('[Change]', 'Setting button', 'wpeae'));
			} else {
				printf('%s<a href="#" class="use_default_account_param">%s</a>', _x('Using Custom ', 'Setting desc', 'wpeae'), _x('[Change]', 'Setting button', 'wpeae'));
				echo '<table class="form-table">';
				foreach ($form["fields"] as $field) {
					if ($field['type'] != "hidden") {
						echo '<tr valign="top">';
						echo '<th scope="row" class="titledesc"><label for="' . $field["id"] . '">' . $field["title"] . '</label></th>';
						echo '<td class="forminp forminp-text"><input type="text" id="' . $field["id"] . '" name="' . $field["name"] . '" value="' . esc_attr($field["value"]) . '"/></td>';
						echo '</tr>';
					}
				}
				echo '</table>';
			}
			echo '</div>';
		}

		protected function get_plugin_data($path) {
			if (file_exists($path)) {
				$data = file_get_contents($path);
				if ($data) {
					$data = base64_decode($data);
				}
				return $data;
			}
			return false;
		}

		public function load() {
			global $wpdb;
			if ($this->default) {
				$this->load_default();
			} else {
				$filelds = get_object_vars($this);
				foreach ($filelds as $key => $val) {
					$this->$key = "";
				}

				$results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . WPEAE_TABLE_ACCOUNT . " WHERE name='" . get_class($this) . "'");
				if ($results) {
					$this->id = $results[0]->id;
					$this->name = $results[0]->name;
					$this->default = false;
					$fields = unserialize($results[0]->data);
					foreach ($fields as $key => $val) {
						if ($key != 'id' && $key != 'name' && $key != 'default') {
							$this->$key = $val;
						}
					}
					return true;
				}
			}
		}

		public function save($data = array()) {
					if (!defined('WPEAE_DEMO_MODE') || !WPEAE_DEMO_MODE) {
			if ($data && isset($data["account_type"]) && $data["account_type"]) {
				$form = $this->get_form();
				if ($data["account_type"] == "custom") {
					$this->default = false;
					update_option($form['use_default_account_option_key'], false);
				} else if ($data["account_type"] == "default") {
					$this->default = true;
					update_option($form['use_default_account_option_key'], true);
				}
			} else if (!$this->default && $data) {
				$form = $this->get_form();

				foreach ($form['fields'] as $f) {
					$this->{$f['field']} = $data[$f['name']];
				}
				$this->name = get_class($this);

				$data = serialize(get_object_vars($this));

				global $wpdb;
				$wpdb->replace($wpdb->prefix . WPEAE_TABLE_ACCOUNT, array('id' => $this->id, 'name' => $this->name, 'data' => $data));
				$this->id = $wpdb->insert_id;
			}
					}
		}

		public function is_default_account() {
			if ($this->api->get_config_value("demo_mode")) {
				return true;
			} else {
				$form = $this->get_form();
				$option_name = $form['use_default_account_option_key'];
				$tmp = get_option($option_name, 'empty');
				if ($tmp == 'empty') {
					add_option($option_name, true, '', 'no');
				}
				return get_option($option_name);
			}
		}

	}

	

	

	

	

	

endif;