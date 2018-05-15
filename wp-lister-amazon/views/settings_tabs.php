<?php  
    $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'settings'; 
?>  

<?php if ( @$_REQUEST['page'] == 'wpla-settings-categories' ) : ?>

    <h2><?php echo __('Categories','wpla') ?></h2>  

<?php elseif ( @$_REQUEST['page'] == 'wpla-settings-accounts' ) : ?>

    <h2><?php echo __('My Account','wpla') ?></h2>  

<?php elseif ( @$_REQUEST['page'] == 'wpla-settings-repricing' ) : ?>

    <h2><?php echo __('Repricing Tool','wpla') ?></h2>  

<?php else : ?>

	<h2 class="nav-tab-wrapper">  

        <a href="<?php echo $wpl_settings_url; ?>&tab=settings"   class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"><?php echo __('General Settings','wpla') ?></a>  
        <a href="<?php echo $wpl_settings_url; ?>&tab=accounts"   class="nav-tab <?php echo $active_tab == 'accounts' ? 'nav-tab-active' : ''; ?>"><?php echo __('Accounts','wpla') ?></a>  
        <a href="<?php echo $wpl_settings_url; ?>&tab=categories" class="nav-tab <?php echo $active_tab == 'categories' ? 'nav-tab-active' : ''; ?>"><?php echo __('Categories','wpla') ?></a>  
        <a href="<?php echo $wpl_settings_url; ?>&tab=advanced"   class="nav-tab <?php echo $active_tab == 'advanced' ? 'nav-tab-active' : ''; ?>"><?php echo __('Advanced','wpla') ?></a>

        <?php if ( ! defined('WPLISTER_RESELLER_VERSION') || ( $active_tab == 'developer' ) ) : ?>
        <a href="<?php echo $wpl_settings_url; ?>&tab=developer"  class="nav-tab <?php echo $active_tab == 'developer' ? 'nav-tab-active' : ''; ?>"><?php echo __('Developer','wpla') ?></a>  
        <?php endif; ?>

        <!-- ## BEGIN PRO ## -->
        <?php if ( ! defined('WPLISTER_RESELLER_VERSION') || ( $active_tab == 'license' ) ) : ?>
        <a href="<?php echo $wpl_settings_url; ?>&tab=license"    class="nav-tab <?php echo $active_tab == 'license' ? 'nav-tab-active' : ''; ?>"><?php echo __('Updates','wpla') ?></a>  
        <?php endif; ?>
        <!-- ## END PRO ## -->

    </h2>  

<?php endif; ?>
