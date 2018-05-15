<?php
// @codingStandardsIgnoreStart
/*
UpdraftPlus Addon: webdav:WebDAV Support
Description: Allows UpdraftPlus to back up to WebDAV servers
Version: 2.2
Shop: /shop/webdav/
Include: includes/PEAR
IncludePHP: methods/stream-base.php
Latest Change: 1.12.35
*/
// @codingStandardsIgnoreEnd

/*
To look at:
http://sabre.io/dav/http-patch/
http://sabre.io/dav/davclient/
https://blog.sphere.chronosempire.org.uk/2012/11/21/webdav-and-the-http-patch-nightmare
*/

if (!defined('UPDRAFTPLUS_DIR')) die('No direct access allowed');

// In PHP 5.2, the instantiation of the class has to be after it is defined, if the class is extending a class from another file. Hence, that has been moved to the end of this file.

if (!class_exists('UpdraftPlus_AddonStorage_viastream')) require_once(UPDRAFTPLUS_DIR.'/methods/stream-base.php');

class UpdraftPlus_Addons_RemoteStorage_webdav extends UpdraftPlus_AddonStorage_viastream {
	
	public $upload_stream_chunk_size = 2097152;

	public $download_stream_chunk_size = 5242880;
	
	public function __construct() {
		$this->is_supress_initial_remote_404_log = true;
		parent::__construct('webdav', 'WebDAV');
	}

	/**
	 * This method overrides the parent method and lists the supported features of this remote storage option.
	 *
	 * @return Array - an array of supported features (any features not
	 * mentioned are assumed to not be supported)
	 */
	public function get_supported_features() {
		// This options format is handled via only accessing options via $this->get_options()
		return array('multi_options', 'config_templates', 'multi_storage');
	}

	/**
	 * Retrieve default options for this remote storage module.
	 *
	 * @return Array - an array of options
	 */
	public function get_default_options() {
		return array(
			'url' => ''
		);
	}

	public function bootstrap($opts = false, $connect = true) {
		if (!class_exists('HTTP_WebDAV_Client_Stream')) {
			// Needed in the include path because PEAR modules (including the file immediately required) will themselves require based on the relative path only
			set_include_path(UPDRAFTPLUS_DIR.'/includes/PEAR'.PATH_SEPARATOR.get_include_path());
			include_once(UPDRAFTPLUS_DIR.'/includes/PEAR/HTTP/WebDAV/Client.php');
		}
		return true;
	}
	
	/**
	 * Acts as a WordPress options filter
	 *
	 * @param  Array $webdav - An array of WebDAV options
	 * @return Array - the returned array can either be the set of updated WebDAV settings or a WordPress error array
	 */
	public function options_filter($webdav) {
	
		global $updraftplus;
	
		// Get the current options (and possibly update them to the new format)
		$opts = $updraftplus->update_remote_storage_options_format('webdav');

		if (is_wp_error($opts)) {
			if ('recursion' !== $opts->get_error_code()) {
				$msg = "WebDAV (".$opts->get_error_code()."): ".$opts->get_error_message();
				$updraftplus->log($msg);
				error_log("UpdraftPlus: $msg");
			}
			// The saved options had a problem; so, return the new ones
			return $webdav;
		}

		// If the input is not as expected, then return the current options
		if (!is_array($webdav)) return $opts;

		// Remove instances that no longer exist
		if (is_array($opts['settings'])) {
			foreach ($opts['settings'] as $instance_id => $storage_options) {
				if (!isset($webdav['settings'][$instance_id])) unset($opts['settings'][$instance_id]);
			}
		}

		// WebDAV has a special case where the settings could be empty so we should check for this before proceeding
		if (!empty($webdav['settings'])) {
			
			foreach ($webdav['settings'] as $instance_id => $storage_options) {
				if (isset($storage_options['webdav'])) {
			
					$url = null;
					$slash = "/";
					$host = "";
					$colon = "";
					$port_colon = "";
					
					if ((80 == $storage_options['port'] && 'webdav' == $storage_options['webdav']) || (443 == $storage_options['port'] && 'webdavs' == $storage_options['webdav'])) {
						$storage_options['port'] = '';
					}
					
					if ('/' == substr($storage_options['path'], 0, 1)) {
						$slash = "";
					}
					
					if (false === strpos($storage_options['host'], "@")) {
						$host = "@";
					}
					
					if ('' != $storage_options['user'] && '' != $storage_options['pass']) {
						$colon = ":";
					}
					
					if ('' != $storage_options['host'] && '' != $storage_options['port']) {
						$port_colon = ":";
					}

					if (!empty($storage_options['url']) && 'http' == strtolower(substr($storage_options['url'], 0, 4))) {
						$storage_options['url'] = 'webdav'.substr($storage_options['url'], 4);
					} elseif ('' != $storage_options['user'] && '' != $storage_options['pass']) {
						$storage_options['url'] = $storage_options['webdav'].urlencode($storage_options['user']).$colon.urlencode($storage_options['pass']).$host.urlencode($storage_options['host']).$port_colon.$storage_options['port'].$slash.$storage_options['path'];
					} else {
						$storage_options['url'] = $storage_options['webdav'].urlencode($storage_options['host']).$port_colon.$storage_options['port'].$slash.$storage_options['path'];
					}

					$opts['settings'][$instance_id]['url'] = $storage_options['url'];

					// Now we have constructed the URL we should loop over the options and save any extras, but we should ignore the options used to create the URL as they are no longer needed.
					$skip_keys = array("url", "webdav", "user", "pass", "host", "port", "path");

					foreach ($storage_options as $key => $value) {
						if (!in_array($key, $skip_keys)) {
							$opts['settings'][$instance_id][$key] = $storage_options[$key];
						}
					}
				}
			}
		}
		
		return $opts;
	}
	
	/**
	 * Get configuration template of middle section
	 *
	 * @return String - the partial template, ready for substitutions to be carried out
	 */
	public function get_configuration_middlesection_template() {
		ob_start();
		$classes = $this->get_css_classes();
		?>
			<tr class="<?php echo $classes; ?>">
				<th><?php _e('WebDAV URL', 'updraftplus');?>:</th>
				<td>
					<input data-updraft_settings_test="url" type="text" style="width: 532px" <?php $this->output_settings_field_name_and_id('url');?> value="{{url}}" readonly />
					<p>
						<em><?php _e('This WebDAV URL is generated by filling in the options below. If you do not know the details, then you will need to ask your WebDAV provider.', 'updraftplus');?></em>
					</p>
				</td>
			</tr>
			<tr class="<?php echo $classes; ?>">
				<th><?php _e('Protocol (SSL or not)', 'updraftplus');?>:</th>
				<td>
					<select <?php $this->output_settings_field_name_and_id('webdav');?> class="updraft_webdav_settings" >
						<option value="webdav://" {{#if is_webdav_protocol}}selected="selected"{{/if}}>webdav://</option>
						<option value="webdavs://" {{#if is_webdavs_protocol}}selected="selected"{{/if}}>webdavs://</option>
					</select>
				</td>
			</tr>
			<tr class="<?php echo $classes; ?>">
				<th><?php _e('Username', 'updraftplus');?>:</th>
				<td>
					<input type="text" style="width: 432px" <?php $this->output_settings_field_name_and_id('user');?> class="updraft_webdav_settings" value="{{user}}"/>
				</td>
			</tr>
			<tr class="<?php echo $classes; ?>">
				<th><?php _e('Password', 'updraftplus');?>:</th>
				<td>
					<input type="<?php echo apply_filters('updraftplus_admin_secret_field_type', 'password'); ?>" style="width: 432px" <?php $this->output_settings_field_name_and_id('pass');?> class="updraft_webdav_settings" value="{{pass}}" />
				</td>
			</tr>
			<tr class="<?php echo $classes; ?>">
				<th><?php _e('Host', 'updraftplus');?>:</th>
				<td>
					<input type="text" style="width: 432px" <?php $this->output_settings_field_name_and_id('host');?> class="updraft_webdav_settings" value="{{host}}"/>
					<br>
					<em id="updraft_webdav_host_error" style="display: none;"><?php echo __('Error:', 'updraftplus').' '.__('A host name cannot contain a slash.', 'updraftplus').' '.__('Enter any path in the field below.', 'updraftplus'); ?></em>
				</td>
			</tr>
			<tr class="<?php echo $classes; ?>">
				<th><?php _e('Port', 'updraftplus');?>:</th>
				<td>
					<input type="number" step="1" min="1" max="65535" style="width: 432px" <?php $this->output_settings_field_name_and_id('port');?> class="updraft_webdav_settings" value="{{port}}" />
					<br>
					<em><?php _e('Leave this blank to use the default (80 for webdav, 443 for webdavs)', 'updraftplus');?></em>
				</td>
			</tr>

			<tr class="<?php echo $classes; ?>">
				<th><?php _e('Path', 'updraftplus');?>:</th>
				<td>
					<input type="text" style="width: 432px" <?php $this->output_settings_field_name_and_id('path');?> class="updraft_webdav_settings" value="{{path}}"/>
				</td>
			</tr>
		<?php
		return ob_get_clean();
	}
	
	/**
	 * Modifies handerbar template options
	 *
	 * @param array $opts
	 * @return array - Modified handerbar template options
	 */
	public function transform_options_for_template($opts) {
		$url = isset($opts['url']) ? $opts['url'] : '';
		$parse_url = @parse_url($url);
		if (false === $parse_url) $url = '';
		$opts['url'] = $url;
		$url_scheme = @parse_url($url, PHP_URL_SCHEME);
		if ('webdav' == $url_scheme) {
			$opts['is_webdav_protocol'] = true;
		} elseif ('webdavs' == $url_scheme) {
			$opts['is_webdavs_protocol'] = true;
		}
		$opts['user'] = urldecode(@parse_url($url, PHP_URL_USER));
		$opts['pass'] = urldecode(@parse_url($url, PHP_URL_PASS));
		$opts['host'] = urldecode(@parse_url($url, PHP_URL_HOST));
		$opts['port'] = @parse_url($url, PHP_URL_PORT);
		$opts['path'] = @parse_url($url, PHP_URL_PATH);
		return $opts;
	}

	public function credentials_test($posted_settings) {
	
		if (empty($posted_settings['url'])) {
			printf(__("Failure: No %s was given.", 'updraftplus'), 'URL');
			return;
		}

		$url = preg_replace('/^http/i', 'webdav', untrailingslashit($posted_settings['url']));
		$this->credentials_test_go($url);
	}
}

// Do *not* instantiate here; it is a storage module, so is instantiated on-demand
// $updraftplus_addons_webdav = new UpdraftPlus_Addons_RemoteStorage_webdav;
