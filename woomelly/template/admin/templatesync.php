<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap">
    <h2 class="uk-heading-divider wp-heading-inline"><?php echo __("Connection Template", "woomelly"); if ( $l_is_ok['result'] ) { ?><a href="<?php echo admin_url( 'admin.php?page=woomelly-templatesync&action=add' ); ?>" class="page-title-action"><?php echo __("Add New", "woomelly"); ?></a> <?php } ?></h2>
    <br>    
    <?php if ( $l_is_ok['result'] ) { ?>
        <form id="movies-filter" method="get">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <?php $testListTable->search_box( __("Search", "woomelly"), 'search_id' ); ?>
            <?php $testListTable->display() ?>
        </form>
    <?php } else {
        echo $l_is_ok['form'];
    } ?>
</div>    