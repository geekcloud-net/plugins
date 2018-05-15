<?php  
    $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'repricing'; 
?>  

<?php if ( @$_REQUEST['page'] == 'wpla-settings-repricing' ) : ?>

    <h2><?php echo __('Repricing Tool','wpla') ?></h2>  

<?php else : ?>

	<h2 class="nav-tab-wrapper">  

        <a href="<?php echo $wpl_tools_url; ?>&tab=repricing" class="nav-tab <?php echo $active_tab == 'repricing' ? 'nav-tab-active' : ''; ?>"><?php echo __('Repricing','wpla') ?></a>  
        <a href="<?php echo $wpl_tools_url; ?>&tab=inventory" class="nav-tab <?php echo $active_tab == 'inventory' ? 'nav-tab-active' : ''; ?>"><?php echo __('Inventory','wpla') ?></a>  
        <a href="<?php echo $wpl_tools_url; ?>&tab=skugen"    class="nav-tab <?php echo $active_tab == 'skugen'    ? 'nav-tab-active' : ''; ?>"><?php echo __('SKU Generator','wpla') ?></a>  
        <a href="<?php echo $wpl_tools_url; ?>&tab=stock_log" class="nav-tab <?php echo $active_tab == 'stock_log' ? 'nav-tab-active' : ''; ?>"><?php echo __('Stock Log','wpla') ?></a>  
        <a href="<?php echo $wpl_tools_url; ?>&tab=developer" class="nav-tab <?php echo $active_tab == 'developer' ? 'nav-tab-active' : ''; ?>"><?php echo __('Developer','wpla') ?></a>  

    </h2>  

<?php endif; ?>
