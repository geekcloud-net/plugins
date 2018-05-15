<?php /* @var $dashboard WPEAE_DashboardPage */ ?>
<div class="adm-wrap">
  <h3><?php echo $dashboard->api->get_config_value("promo_title"); ?></h3>
  <div class="adm-text">
  <?php echo $dashboard->api->get_config_value("promo_text"); ?>
  </div>
  <div class="adm-banner"></div> 
  <a href="<?php echo $dashboard->api->get_config_value("promo_link"); ?>" class="adm-btn"><?php _e('Get it right now','wpeae'); ?></a>
  <?php $plugin_data = get_plugin_data(WPEAE_FILE_FULLNAME);?>
  <p class="adm-sub-text">*<?php printf( __('This is the extension of the %s plugins', 'wpeae'), $plugin_data['Name'] ); ?></p>
  <div class="adm-geometrix">
	<a alt="developer" href="http://gmetrixteam.com/"><img src="<?php echo WPEAE_ROOT_URL; ?>assets/img/geo.png"></a><br>
	<span><?php _e('WordPress Plugins Development Team','wpeae'); ?></span>
  </div>
</div>
