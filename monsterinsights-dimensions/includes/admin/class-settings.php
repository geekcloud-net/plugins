<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MonsterInsights_Admin_Custom_Dimensions_Settings {
	/**
	 * @var MonsterInsights_Admin_Custom_Dimensions Holds a MonsterInsights_Admin_Custom_Dimensions instance.
	 */
	protected $dimensions;
	
	public function __construct() {
		$this->dimensions = new MonsterInsights_Admin_Custom_Dimensions();
		add_action( 'admin_enqueue_scripts', array( $this, 'init_assets' ) );
		add_filter( 'monsterinsights_settings_dimensions', array( $this, 'add_settings' ) );

		add_action( 'monsterinsights_settings_save_dimensions_page', array( $this, 'save_dimensions_page' ), 10 , 4 );
		add_action( 'current_screen', array( $this, 'custom_notices') );


		// Deactivate WPSEO errors
		register_deactivation_hook( 'wordpress-seo/wp-seo.php', array( $this, 'wpseo_deactivate' ) );
		register_deactivation_hook( 'wordpress-seo-premium/wp-seo-premium.php', array( $this, 'wpseo_deactivate' ) );
		add_action( 'admin_notices', array( $this,  'monsterinsights_display_wpseo_deactivated_notices' ) );
	}

	public function add_settings( $settings ) {
		$settings['custom_dimensions'] = array( 
			'id'    => 'custom_dimensions',
			'type'  => 'dimensions_page',
		);
		return $settings;
	}

	/**
	 * enqueues the assets
	 */
	public function init_assets() {
		if ( 'monsterinsights_settings' === filter_input( INPUT_GET, 'page' ) || 'monsterinsights_tracking' === filter_input( INPUT_GET, 'page' ) ) {
			wp_enqueue_script( 'monsterinsights_custom_dimensions_script', MONSTERINSIGHTS_DIMENSIONS_ADDON_PLUGIN_URL . 'assets/js/custom_dimensions.js', array(), monsterinsights_get_asset_version() );
			wp_enqueue_style( 'monsterinsights_custom_dimensions_styles',  MONSTERINSIGHTS_DIMENSIONS_ADDON_PLUGIN_URL . 'assets/css/custom_dimensions.css', array(), monsterinsights_get_asset_version() );
		}
	}

	public function save_dimensions_page( $value, $id, $args, $previous_value ) {
		if ( ! is_array( $value ) ) {
			$value = array();
		}
		
		if ( ! $this->dimensions->dimension_ids_are_unique( $value ) ) {
			// don't touch saved options
			add_action( 'monsterinsights_tracking_dimensions_tab_notice', array( $this, 'duplicated_ids_error' ) );
		} else if ( ! $this->dimensions->dimension_types_are_unique( $value ) ) {
			// don't touch saved options
			add_action( 'monsterinsights_tracking_dimensions_tab_notice', array( $this, 'duplicated_types_error' ) );
		} else {
			monsterinsights_update_option( 'custom_dimensions', $value );
			$this->dimensions->active_custom_dimensions = $value;
			$this->dimensions->custom_dimensions_usage  = count( $value );
			add_action( 'monsterinsights_tracking_dimensions_tab_notice', 'monsterinsights_updated_settings' );
		}
	}

	public function custom_notices() {
		remove_action( 'monsterinsights_tracking_dimensions_tab_notice', 'monsterinsights_updated_settings' );
	}

	public function duplicated_ids_error(){
		$description = esc_html__( 'The custom dimension IDs must be unique for each dimension.', 'monsterinsights-dimensions' );
		echo monsterinsights_get_message( 'error', $description );
	}

	public function duplicated_types_error(){
		$description = esc_html__( 'The custom dimension types must be unique for each dimension.', 'monsterinsights-dimensions' );
		echo monsterinsights_get_message( 'error', $description );
	}

	/**
	 * Hook used for preparing a notice when WPSEO is deactivated and SEO dimensions have been set.
	 */
	public function wpseo_deactivate() {
		if ( monsterinsights_is_wp_seo_active() ) {
			$error_message = sprintf(
				__( '%1$sWarning!%2$s Deactivating Wordpress SEO will stop your SEO custom dimensions from working in Google Analytics. Please visit your %3$sGoogle Analytics settings%4$s to see which custom dimensions have been disabled.', 'monsterinsights-dimensions' ),
				'<strong>',
				'</strong>',
				'<a href="' . admin_url( 'admin.php' ) . '?page=monsterinsights_tracking#monsterinsights-main-tab-tracking?monsterinsights-sub-tab-dimensions">',
				'</a>'
			);

			set_transient( 'monsterinsights_wpseo_deactivated_error', $error_message, MINUTE_IN_SECONDS );
		}
	}

	/**
	 * Hook used for outputting an admin notice when transient has been set on deactivation of WPSEO.
	 */
	public function monsterinsights_display_wpseo_deactivated_notices() {
		$wpseo_deactivated_error = get_transient( 'monsterinsights_wpseo_deactivated_error' );

		if ( ! empty( $wpseo_deactivated_error ) ) {
			echo '<div class="error"><p>' . esc_html( $wpseo_deactivated_error ) . '</p></div>';
			delete_transient( 'monsterinsights_wpseo_deactivated_error' );
		}
	}
}

function monsterinsights_dimensions_page_callback() {
	$dimensions = new MonsterInsights_Admin_Custom_Dimensions();
	$tracking_mode = monsterinsights_get_option( 'tracking_mode', 'analytics' );
	if ( 'ga' !== $tracking_mode ) {
		echo '<p>';
		printf(
			esc_html__( 'Visit our knowledge base to learn more about %1$show to setup%3$s and %2$show to use%3$s custom dimensions in Google Analytics.', 'monsterinsights-dimensions' ),
			"<a href='https://www.monsterinsights.com/docs/how-do-i-set-up-custom-dimensions/#utm_medium=helptext&utm_source=gawp-config&utm_campaign=wpgaplugin' class='monsterinsights-settings-click-excluded' target='_blank' rel='noopener noreferrer' referrer='no-referrer'>",
			"<a href='https://www.monsterinsights.com/docs/can-find-custom-dimension-reports/#utm_medium=helptext&utm_source=gawp-config&utm_campaign=wpgaplugin' class='monsterinsights-settings-click-excluded' target='_blank' rel='noopener noreferrer' referrer='no-referrer'>",
			'</a>'
		);
		echo '</p>';
		if ( monsterinsights_is_wp_seo_active() === false ) {
			echo '<p>';
			printf(
				esc_html__( 'You need to install %1$sWordPress SEO by Yoast%2$s to be able to use the %3$sSEO Score%4$s and %3$sFocus keyword%4$s custom dimensions. If you\'re already running another SEO plugin, WordPress SEO can import its meta data.', 'monsterinsights-dimensions' ),
				'<a href="http://www.wpbeginner.com/refer/yoast-premium/"  class="monsterinsights-settings-click-excluded" target="_blank" rel="noopener noreferrer" referrer="no-referrer">',
				'</a>',
				'<strong>',
				'</strong>'
			); 
			echo '</p>';
		}

	} else {
		$url = esc_url( wp_nonce_url( add_query_arg( array( 'monsterinsights-action' => 'switch_to_analyticsjs', 'return' => 'dimensions' ) ), 'monsterinsights-switch-to-analyticsjs-nonce' ) );
		echo monsterinsights_get_message( 'info', sprintf( esc_html__( 'Custom dimension tracking is only available on Universal Tracking (analytics.js). You\'re currently using deprecated ga.js tracking. We recommend switching to analytics.js, as it is significantly more accurate than ga.js, and allows for additional functionality (like the more accurate Javascript based events tracking we offer). Further Google Analytics has deprecated support for ga.js, and it may stop working at any time when Google decides to disable it from their server. To switch to using the newer Universal Analytics (analytics.js) %1$sclick here%2$s.', 'monsterinsights-dimensions' ), '<a href="' . $url .'">', '</a>' ) );
	}
	if ( 'ga' !== $tracking_mode ) {
		?>
		<!-- Settings Form -->
		<table id="monsterinsights-custom_dimensions" class="form-table">
			<thead>
				<th width="45%"><?php esc_html_e( 'Type', 'monsterinsights-dimensions' ); ?></th>
				<th width="45%"><?php esc_html_e( 'Custom Dimension ID', 'monsterinsights-dimensions' ); ?></th>
				<th width="10%">&nbsp;</th>
			 </thead>
			<tbody>
				<?php $total = 1;
				$active_custom_dimension_ids  = array( 0 );
				foreach ( $dimensions->active_custom_dimensions as $active_custom_dimension ) :
					$active_custom_dimension_ids[] = $active_custom_dimension['id'];
					?>
					<tr id="monsterinsights-<?php echo $total; ?>">
						<?php $select_disabled = '';
						if ( ! $dimensions->custom_dimensions[ $active_custom_dimension['type'] ]['enabled'] ) {
							$select_disabled = 'disabled';
						}

						?>
						<td>
							<?php
							if ( $select_disabled ) {
								echo '<input type="hidden" name="monsterinsights_settings[custom_dimensions][' . $total . '][type]" value="' . esc_attr( $active_custom_dimension['type'] ) . '">';
							}
							?>
							<select name="monsterinsights_settings[custom_dimensions][<?php echo $total; ?>][type]" <?php echo $select_disabled ?>>
								<?php foreach ( $dimensions->custom_dimensions as $key => $dimension ) :
									$option_disabled = ( ( $dimension['enabled'] ) ? '' : 'disabled' );

									if ( $active_custom_dimension['type'] == $key ) {
										echo '<option value="' . esc_attr( $key ) . '" SELECTED >' . esc_html( $dimension['title'] ) . '</option>';
									}
									else {
										echo '<option value="' . esc_attr( $key ) . '" ' . $option_disabled . '>' . esc_html( $dimension['title'] ) . '</option>';
									}
								endforeach; ?>
							</select>
							<?php
							if ( $select_disabled ) {
								$enable_inactive_plugins_help = esc_html__( 'Inactive: To use this custom dimension, please activate WordPress SEO or WordPress SEO Premium', 'monsterinsights-dimensions' );
								echo '<span class="monsterinsights-inactive-custom-dimension">' . $enable_inactive_plugins_help . '</span>';
							}
							?>
						</td>
						<td align="left">
							<input type="text" name="monsterinsights_settings[custom_dimensions][<?php echo $total; ?>][id]" value="<?php echo esc_attr( $active_custom_dimension['id'] ); ?>" style="width: 50px;" />
						</td>
						<td>
							<a href="#monsterinsights-main-tab-tracking?monsterinsights-sub-tab-dimensions" id="monsterinsights_remove_<?php echo $total; ?>" class="monsterinsights-settings-click-excluded"><?php esc_html_e( 'Delete', 'monsterinsights-dimensions' ); ?></a>
						</td>
					</tr>
				 <?php $total++; endforeach; ?>
			</tbody>
			<tfoot>
				<th colspan="1" id="monsterinsights_add_cd_holder">
					<strong><a href="#monsterinsights-main-tab-tracking?monsterinsights-sub-tab-dimensions" id="monsterinsights_add_row" class="monsterinsights-settings-click-excluded">+ <?php esc_html_e( 'Add new custom dimension', 'monsterinsights-dimensions' ); ?></a>&nbsp;
					</strong></th>
				<th align="left" colspan="2">
					<?php
					/* translators %1$s shows the total number of used custom dimensions. %2$s shows the total number of custom dimensions available */
					echo '<em>' . sprintf( esc_html__( 'You are using %1$s out of %2$s custom dimensions.', 'monsterinsights-dimensions' ), '<span id="monsterinsights_limit">' . $dimensions->custom_dimensions_usage . '</span>', $dimensions->custom_dimensions_limit ) . '</em>' ;
					?>
				</th>
			</tfoot>
		</table>
		<input type="hidden" name="string_error_custom_dimensions" id="string_error_custom_dimensions" value="<?php esc_attr_e( 'The custom dimension ID already exists!', 'monsterinsights-dimensions' ); ?>" />
		<script type="text/javascript">
			var total 			 = <?php echo intval( max( $active_custom_dimension_ids ) ); ?>;
			var limit 			 = <?php echo $dimensions->custom_dimensions_limit; ?>;
			var tmp_total 		 = <?php echo $dimensions->custom_dimensions_usage; ?>;
			var translate_delete = '<?php _e( 'Delete', 'monsterinsights-dimensions' ); ?>';
			var options_to_add 	 = '';

			<?php
			$options_to_add = '';
			foreach ( $dimensions->custom_dimensions as $key => $dimension ) :
				$option_disabled = ( ( $dimension['enabled'] ) ? '' : 'disabled' );

				$options_to_add .= '<option value="' . esc_js( esc_attr( $key ) )  . '" ' . $option_disabled . '>' . esc_js( esc_html( $dimension['title'] ) ) . '</option>';
			endforeach;
			echo "options_to_add = '{$options_to_add}';";
			?>

			jQuery(document).ready(function () {
				custom_dimensions.init();
			});

		</script>
	<?php }
}