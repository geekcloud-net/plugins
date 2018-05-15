<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

?>
<div class="wrap">
    <h2 class="uk-heading-divider"><?php echo __("Settings", "woomelly"); ?></h2>
    <br>
    <?php if ( $l_is_ok['result'] ) { ?>
        <div style="padding-top: 15px;">
            <ul uk-tab>
                <li class="uk-active"><a href="#"><?php echo __("General", "woomelly"); ?></a></li>
                <li><a href="#"><?php echo __("Publication", "woomelly"); ?></a></li>
                <li><a href="#"><?php echo __("Security", "woomelly"); ?></a></li>
            </ul>
            <ul class="uk-switcher uk-margin">
                <li>
                    <form class="uk-form-horizontal uk-margin-large" form method="post">                                                
                        <?php if ( $is_connect ) { ?>
                            <div id="wm_settings_page_container">
                                <div id="wm_settings_page_container_unlink" class="uk-alert-primary wm_settings_page_container" uk-alert>
                                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><?php echo sprintf( __("Store connected to Mercadolibre. If you want to unlink your store press %s.", "woomelly"), '<a id="wm_settings_page_unlink" class="uk-button uk-button-danger uk-button-small" style="margin-left: 5px;">'.__("Unlink", "woomelly").'</a>' ); ?></div>
                                </div>
                            </div>
                        <?php } else if ( $wm_settings_page_app_id != "" && $wm_settings_page_secret_key != "" && $wm_settings_page_site_id != "" ) { ?>
                            <div id="wm_settings_page_container">
                                <div id="wm_settings_page_container_link" class="uk-alert-primary wm_settings_page_container" uk-alert>
                                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12"><?php echo sprintf( __("Store not connected with Mercadolibre. If you want to connect your store press %s.", "woomelly"), '<a href="'.$url.'" class="uk-button uk-button-primary uk-button-small" style="margin-left: 5px;">'.__("Link", "woomelly").'</a>' ); ?></div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="wm_settings_page_app_id"><?php echo sprintf(__("ID Application %s", "woomelly"), '<span style="color: red;">*</span>'); ?> <span class="uk-margin-small-right" uk-icon="icon: question" title="<?php echo __('ID of the Mercadolibre application. Press the Help button for more information.', 'woomelly'); ?>" uk-tooltip="pos: right"></span></label>
                            <div class="uk-inline" style="width: 80%;">
                                <span class="uk-form-icon" uk-icon="icon: cog"></span>
                                <input name="wm_settings_page_app_id" class="uk-input" id="wm_settings_page_app_id" type="text" placeholder="<?php echo __('Enter your Application ID...', 'woomelly'); ?>" value="<?php echo $wm_settings_page_app_id; ?>" />
                            </div>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="wm_settings_page_secret_key"><?php echo sprintf(__("Secret Key %s", "woomelly"), '<span style="color: red;">*</span>'); ?> <span class="uk-margin-small-right" uk-icon="icon: question" title="<?php echo __('Security key of Mercadolibre. Press the Help button for more information.', 'woomelly'); ?>" uk-tooltip="pos: right"></span></label>
                            <div class="uk-inline" style="width: 80%;">
                                <span class="uk-form-icon" uk-icon="icon: cog"></span>
                                <input name="wm_settings_page_secret_key" class="uk-input" id="wm_settings_page_secret_key" type="text" placeholder="<?php echo __('Enter your Secret Key...', 'woomelly'); ?>" value="<?php echo $wm_settings_page_secret_key; ?>" />
                            </div>
                        </div>
                        <input type="hidden" class="wm_hidden" value="<?php echo $wm_settings_page_access_token; ?>" />
                        <div class="uk-margin">
                            <label class="uk-form-label" for="wm_settings_page_site_id"><?php echo sprintf(__("Country %s", "woomelly"), '<span style="color: red;">*</span>'); ?> <span class="uk-margin-small-right" uk-icon="icon: question" title="<?php echo __('Country where you operate the data of your Mercadolibre account.', 'woomelly'); ?>" uk-tooltip="pos: right"></span></label>
                            <div class="uk-inline" style="width: 80%;">
                                <span class="uk-form-icon" uk-icon="icon: location"></span>
                                <select name="wm_settings_page_site_id" id="wm_settings_page_site_id" class="uk-select" style="padding-left: 35px;">
                                    <option value=""><?php echo __("- Select -", "woomelly"); ?></option>
                                    <?php
                                        if ( !empty($sites_id) ) {
                                            foreach ( $sites_id as $site_id ) {
                                                $wm_settings_page_site_id_selected = '';
                                                if ( $wm_settings_page_site_id == $site_id->id ) {
                                                    $wm_settings_page_site_id_selected = 'selected="selected"';
                                                }
                                                echo '<option value="'.$site_id->id.'" '.$wm_settings_page_site_id_selected.'>'.$site_id->name.'</option>';
                                            }
                                        }
                                    ?>                            
                                </select>
                            </div>
                        </div>
                        <div class="uk-margin">
                            <div class="uk-column-1-2">
                                <input name="wm_settings_page_submit" type="submit" class="uk-button uk-button-primary" value="<?php echo __('Save this changes', 'woomelly'); ?>" />
                                <button class="uk-button uk-button-secondary" type="button" uk-toggle="target: #wm-help-settings"><?php echo __("Help", "woomelly"); ?></button>
                            </div>
                            <div class="uk-offcanvas-content">
                                <div id="wm-help-settings" uk-offcanvas="flip: true; overlay: true;">
                                    <div class="uk-offcanvas-bar">
                                        <button class="uk-offcanvas-close" type="button" style="margin-top: 25px;" uk-close></button>
                                        <h4><?php echo __("Configure Mercadolibre Application", "woomelly"); ?></h4>
                                        <ul class="uk-list uk-list-bullet">
                                            <li><?php echo sprintf( __("Go %s and create an application depending on the country with which your store will work. You fill the fields indicated there.", "woomelly"), '<a href="http://applications.mercadolibre.com/" target="_blank">applications.mercadolibre.com</a>'); ?></li>
                                            <li><?php echo sprintf( __("Important: Copy this information %s", "woomelly"), '<code style="white-space: normal;" id="copy_one">' . admin_url( "admin.php?page=woomelly-settings") . '</code>' ) ; ?><a class="copy_code" uk-icon="icon: copy; ratio: 0.8" title="<?php echo __('Copy','woomelly'); ?>" uk-tooltip="pos: bottom" onclick="wm_copy_to_clipboard('#copy_one')"></a><?php echo sprintf(__(" in the %s of your application.", "woomelly"), '<strong>Redirect URI</strong>'); ?></li>
                                            <li><?php echo sprintf( __("In the %s section select all options (read, offline access, write).", "woomelly"), '<strong>Scopes</strong>'); ?></li>
                                            <li><?php echo sprintf( __("In the %s section select these options (orders_v2/orders)", "woomelly"), '<strong>'.__("Topics", "woomelly").'</strong>'); ?></li>
                                            <li><?php echo sprintf( __("Important: Copy this information %s", "woomelly"), '<code style="white-space: normal;" id="copy_two">' . $webhook_url . '</code>' ); ?><a class="copy_code" uk-icon="icon: copy; ratio: 0.8" title="<?php echo __("Copy", "woomelly"); ?>" uk-tooltip="pos: bottom" onclick="wm_copy_to_clipboard('#copy_two')"></a><?php echo sprintf( __(" in %s of your application.", "woomelly"), '<strong>'.__("URL callbacks notifications", "woomelly").'</strong>'); ?></li>
                                            <li><?php echo sprintf( __("Save the changes and copy your %s and %s generated in your form.", "woomelly"), '<strong>'.__("APPLICATION ID", "woomelly").'</strong>', '<strong>'.__("SECRET KEY", "woomelly").'</strong>'); ?></li>
                                            <li><?php echo sprintf( __("To finish press the button of %s so that the access with Mercadolibre is generated correctly.", "woomelly"), '<strong>'.__("Link", "woomelly").'</strong>'); ?></li>
                                            <li><?php echo sprintf( __("If you have any questions you can check the %s or write an email to makeplugins@gmail.com", "woomelly"), '<a href="http://developers.mercadolibre.com/es/registra-tu-aplicacion/" target="_blank">'.__("Mercadolibre Documentation", "woomelly").'</a>'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </li>
                <li>
                    <form class="uk-form-horizontal uk-margin-large" form action="" method="post">
                        <div class="uk-margin">
                            <label class="uk-form-label" for="wm_settings_page_currency_id"><?php echo sprintf(__("Currency %s", "woomelly"), '<span style="color: red;">*</span>'); ?> <span class="uk-margin-small-right" uk-icon="icon: question" title="<?php echo __('Currency where your Mercadolibre account operates.', 'woomelly'); ?>" uk-tooltip="pos: right"></span></label>
                            <div class="uk-inline" style="width: 80%;">
                                <span class="uk-form-icon" uk-icon="icon: cog"></span>
                                <select name="wm_settings_page_currency_id" id="wm_settings_page_currency_id" class="uk-select" style="padding-left: 35px;">
                                    <?php if ( !empty($currencies) ) {
                                        foreach ( $currencies as $currency ) {
                                            echo '<option value="'.$currency->id.'" '.( ($wm_settings_page_currency_id==$currency->id)? "selected=\"selected\"" : "" ).'>'.$currency->description.' ('.$currency->symbol.')</option>';
                                        }
                                    } else {
                                        echo '<option value=""> ' . __("- Select -", "woomelly") . '</option>';
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="wm_settings_page_template"><?php echo __("Create Template", "woomelly"); ?> <span class="uk-margin-small-right" uk-icon="icon: question" title="<?php echo __('Although it is optional, it is advisable to place general data on how you wish to display the data of your publication in Mercadolibre.', 'woomelly'); ?>" uk-tooltip="pos: right"></span></label>
                            <div class="uk-inline" style="width: 80%;">
                                <div><?php echo sprintf( __( 'Available tags: %s', 'woomelly'), $tags_available ); ?></div>
                                <textarea name="wm_settings_page_template" id="wm_settings_page_template" class="uk-textarea" rows="18"><?php echo $wm_settings_page_template; ?></textarea>
                            </div>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="wm_settings_format_template"><?php echo sprintf(__("Publication format %s", "woomelly"), '<span style="color: red;">*</span>'); ?> <span class="uk-margin-small-right" uk-icon="icon: question" title="<?php echo __('HTML publications are already obsolete in the majority of Mercadolibre accounts. However, we still have the option in case your country is still used.', 'woomelly'); ?>" uk-tooltip="pos: right"></span></label>
                            <div class="uk-inline" style="width: 80%;">
                                <span class="uk-form-icon" uk-icon="icon: cog"></span>
                                <select name="wm_settings_format_template" id="wm_settings_format_template" class="uk-select" style="padding-left: 35px;">
                                    <option value="plain_text" <?php echo ( ($wm_settings_format_template=="plain_text")? 'selected="selected"' : '' ); ?>><?php echo __("Plane Text", "woomelly"); ?></option>
                                    <option value="text" <?php echo ( ($wm_settings_format_template=="text")? 'selected="selected"' : '' ); ?>><?php echo __("HTML", "woomelly"); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label"><?php echo __("Omit fields to Sync", "woomelly"); ?> <span class="uk-margin-small-right" uk-icon="icon: question" title="<?php echo __('Check the fields you want to omit in the synchronizations.', 'woomelly'); ?>" uk-tooltip="pos: right"></span></label>
                            <div class="uk-inline">
                                <input type="checkbox" name="wm_settings_omit_fields_title" value="1" <?php echo ( ($wm_settings_omit_fields[0]==true)? 'checked' : '' ); ?>><?php echo __(" Omit Title", "woomelly"); ?><br>
                                <input type="checkbox" name="wm_settings_omit_fields_description" value="1" <?php echo ( ($wm_settings_omit_fields[1]==true)? 'checked' : '' ); ?>><?php echo __(" Omit Description", "woomelly"); ?><br>
                                <input type="checkbox" name="wm_settings_omit_fields_pictures" value="1" <?php echo ( ($wm_settings_omit_fields[2]==true)? 'checked' : '' ); ?>><?php echo __(" Omit Pictures", "woomelly"); ?><br>
                                <input type="checkbox" name="wm_settings_omit_fields_stock" value="1" <?php echo ( ($wm_settings_omit_fields[3]==true)? 'checked' : '' ); ?>><?php echo __(" Omit Stock", "woomelly"); ?><br>
                                <input type="checkbox" name="wm_settings_omit_fields_sku" value="1" <?php echo ( ($wm_settings_omit_fields[4]==true)? 'checked' : '' ); ?>><?php echo __(" Omit SKU", "woomelly"); ?><br>
                            </div>
                        </div>
                        <div class="uk-margin">
                            <input name="wm_settings_page_publish_submit" type="submit" class="uk-button uk-button-primary" value="<?php echo __('Save this changes', 'woomelly'); ?>" />
                        </div>    
                    </form>
                </li>
                <li>
                    <form class="uk-form-horizontal uk-margin-large" form action="" method="post">
                        <div class="uk-margin">
                            <label class="uk-form-label" for="wm_settings_refresh_token_email"><?php echo __("Notification Renewal of Token", "woomelly"); ?></label>
                            <input name="wm_settings_refresh_token" id="wm_settings_refresh_token" type="checkbox" value="1" <?php echo ( ($wm_settings_refresh_token==true)? 'checked' : '' ); ?>/>
                            <div class="uk-inline" style="width: 80%;">
                                <span class="uk-form-icon" uk-icon="icon: mail"></span>
                                <input name="wm_settings_refresh_token_email" id="wm_settings_refresh_token_email" class="uk-input" type="text" value="<?php echo $wm_settings_refresh_token_email; ?>" />
                            </div>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="wm_settings_notification_sync_email"><?php echo __("Automatic Synchronization Summary Notification", "woomelly"); ?></label>
                            <input name="wm_settings_notification_sync" id="wm_settings_notification_sync" type="checkbox" value="1" <?php echo ( ($wm_settings_notification_sync==true)? 'checked' : '' ); ?>/>
                            <div class="uk-inline" style="width: 80%;">
                                <span class="uk-form-icon" uk-icon="icon: mail"></span>
                                <input name="wm_settings_notification_sync_email" id="wm_settings_notification_sync_email" class="uk-input" type="text" value="<?php echo $wm_settings_notification_sync_email; ?>" />
                            </div>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="wm_settings_sync_automatic_time"><?php echo __("Automatic Synchronization Interval", "woomelly"); ?> <span class="uk-margin-small-right" uk-icon="icon: question" title="<?php echo __('The time is calculated in hours.', 'woomelly'); ?>" uk-tooltip="pos: right"></span></label>
                            <input name="wm_settings_sync_automatic" id="wm_settings_sync_automatic" type="checkbox" value="1" <?php echo ( ($wm_settings_sync_automatic==true)? 'checked' : '' ); ?>/>
                            <div class="uk-inline" style="width: 80%;">
                                <span class="uk-form-icon" uk-icon="icon: cog"></span>
                                <input name="wm_settings_sync_automatic_time" id="wm_settings_sync_automatic_time" class="uk-input" type="text" value="<?php echo $wm_settings_sync_automatic_time; ?>" />
                            </div>
                        </div>
                        <div class="uk-margin">
                            <input name="wm_settings_page_notification_submit" type="submit" class="uk-button uk-button-primary" value="<?php echo __('Save this changes', 'woomelly'); ?>" />
                        </div>
                    </form>
                </li>
            </ul>
        </div>
    <?php } else {
        echo $l_is_ok['form'];
    } ?>
</div>