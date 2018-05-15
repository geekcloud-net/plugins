<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

?>
<div class="wrap">
    <h2 class="uk-heading-divider"><?php echo __("Overview", "woomelly"); ?><span class="uk-align-right" style="font-size: 12px;"></h2>
    <br>
    <?php if ( $l_is_ok['result'] ) { ?>
        <div class="uk-card uk-card-default uk-card-body">
            <div class="row">
                <ul class="uk-tab-left col-xs-12 col-sm-12 col-md-4 col-lg-4" uk-tab>
                    <li class="uk-active"><a href="#"><?php echo __("Authorization with Mercadolibre", "woomelly"); ?></a></li>
                    <li><a href="#"><?php echo __("General configuration", "woomelly"); ?></a></li>
                    <li><a href="#"><?php echo __("Configuration Connection Templates", "woomelly"); ?></a></li>
                    <li><a href="#"><?php echo __("Synchronization with Mercadolibre", "woomelly"); ?></a></li>
                    <li><a href="#"><?php echo __("Extensions of Woomelly", "woomelly"); ?></a></li>
                    <li><a href="#"><?php echo __("Support and Suggestions", "woomelly"); ?></a></li>
                </ul>
                <ul class="uk-switcher uk-padding-small col-xs-12 col-sm-12 col-md-8 col-lg-8" >
                    <li>
                        <p><?php echo __("Once the Woomelly plugin is installed, the first step is to set up your website with your store in Mercadolibre. This step is very important for a correct link of both stores and for this you have to follow the following steps:", "woomelly"); ?></p>
                        <ul class="uk-list uk-list-bullet">
                            <li><?php echo sprintf( __( "Go to the %s configuration and follow the steps shown on the 'Help' button.", "woomelly"), '<a href="'.admin_url( "admin.php?page=woomelly-settings" ).'">'.__("site", "woomelly").'</a>' ); ?></li>
                            <li><?php echo sprintf( __( "This authorization lasts only 6 hours but quiet, Woomelly is responsible for updating it automatically and in case of error you will receive a notification to the administrator's mail. To change this email, as well as activate and deactivate it, you have to go to %s.", "woomelly"), '<a href="'.admin_url( "admin.php?page=woomelly-settings" ).'">'.__("Settings > Security", "woomelly").'</a>' ); ?></li>
                            <li><?php echo __("You can also unlink your store whenever you want. This step is easily reversible but remember that when your store is unlinked, no synchronization with Mercadolibre will take effect.", "woomelly"); ?></li>
                        </ul>
                    </li>
                    <li>
                        <p><?php echo __("Once your store is authorized with Mercadolibre it is time to configure the basic data for your publications. For this you have to take into account these options:", "woomelly"); ?></p>
                        <ul class="uk-list uk-list-bullet">
                            <li><?php echo sprintf( __("In %s you will find very important configurations such as the currency in which you want to market your products and the template of how you want the data to be organized in the description of your publication. Keep in mind that Mercadolibre is only working with plain text in the description of its publications and that Woomelly has certain tags, which you can use to place dynamic information in the template.", "woomelly"), '<a href="'.admin_url( "admin.php?page=woomelly-settings" ).'">'.__("Settings > Publication Tab", "woomelly").'</a>' ); ?>
                            <li><?php echo sprintf( __("In %s you will find the management of system notifications, as well as the configuration of your automatic mass synchronizer. Remember that this service currently works with a Wordpress Task Scheduler / CRON and often has certain limitations. We recommend using the Server Task Scheduler service to ensure proper operation. If you need support with this, write to makeplugins@gmail.com.", "woomelly"), '<a href="'.admin_url( "admin.php?page=woomelly-settings" ).'">'.__("Settings > Security Tab", "woomelly").'</a>' ); ?></li>
                        </ul>
                    </li>
                    <li>
                        <p><?php echo __("This step is very important since you need to configure the publications with mandatory data requested by Mercadolibre. The configuration templates are a solution to generalize this process but many times you will need the configuration of each of your products. We are currently working to make it as automatic as possible while we will explain the steps you have to perform for proper operation:", "woomelly"); ?></p>
                        <ul class="uk-list uk-list-bullet">
                            <li><?php echo sprintf( __("In %s you will find the list of existing templates so far on your website. If you do not have any template you can add it by pressing the 'Add New' button. Then select the categories to publish your product and fill in all the fields requested by that category. This template can be added to all the products you want but keep in mind that fields like the 'Technical Sheet' and 'Variations' have to be configured by products individually given the characteristics of your publication.", "woomelly"), '<a href="'.admin_url( "admin.php?page=woomelly-templatesync" ).'">'.__("Connection Template", "woomelly").'</a>' ); ?>
                            <li><?php echo sprintf( __("You can update and delete your template whenever you like. To assign this template to your products you have to go to %s and select the products you want. Then press the 'Actions in batch' button and you will find a field with all your synchronization templates. Select the template you want and then press the 'modify' button. Ready! With this you can start synchronizing your products correctly.", "woomelly"), '<a href="'.admin_url( "edit.php?post_type=product" ).'">'.__("List of Products", "woomelly").'</a>' ); ?>
                            <li><?php echo __("IMPORTANT: You have to take into account that fields such as the technical sheet and categories that require attributes and variations have to be configured by products individually. To do this enter the products, go to the 'WM Tab' tab and place the fields for your technical sheet. In the case of variations you have to address each of the variations of your variable products and configure these variations with Mercadolibre.", "woomelly"); ?></li>
                        </ul>
                    </li>
                    <li>
                        <p><?php echo __("At this point you can now successfully synchronize all your products with Mercadolibre. Woomely has three (3) types of synchronizations: Individual, Massive and Automatic Massive. We will explain what it is and how it works:", "woomelly"); ?></p>
                        <ul class="uk-list uk-list-bullet">
                            <li><?php echo __("For an individual synchronization: you have to enter the product you want and inside you will find a button to the right of your green panel. With it you can synchronize and unlink such product with your Mercadolibre store. If there is any problem in that same section, it will give you a message of what you have to correct.", "woomelly"); ?></li>
                            <li><?php echo sprintf( __("For a massive synchronization: you have to go to %s. Then you can see the details of it in the tab of Last Connection and All Connections.", "woomelly"), '<a href="'.admin_url( "admin.php?page=woomelly-connection" ).'">'.__("Connection", "woomelly").'</a>' ); ?></li>
                            <li><?php echo sprintf( __("In the case of automatic mass synchronization: you have to go to %s and adjust the status of the synchronization (default is Active) and the execution time of it. In the tab of Last Connection and All Connections you can find the detail of the result of these synchronizations.", "woomelly"), '<a href="'.admin_url( "admin.php?page=woomelly-settings" ).'">'.__("Settings > Security Tab", "woomelly").'</a>' ); ?></li>
                        </ul>                        
                    </li>
                    <li>

                    </li>
                    <li>
                        <p><?php echo sprintf( __("To have all the features of Woomelly, we ask you to activate your purchase license %s. Additionally you can for any questions or suggestions you can write to makeplugins@gmail.com and we will answer you as soon as possible. Thank you for trusting us and acquiring our product.", "woomelly"), '<a href="'.admin_url( "admin.php?page=woomelly-license" ).'">'.__("here", "woomelly").'</a>' ); ?></p>
                    </li>
                </ul>
            </div>
        </div>
        <br>
        <div class="uk-child-width-1-3@m uk-grid-small uk-grid-match" uk-grid>
            <div>
                <div class="uk-card uk-card-default uk-card-body">
                    <h3 class="uk-card-title"><?php echo __("Mercadolibre", "woomelly"); ?></h3>
                    <ul class="uk-list uk-list-divider">
                        <?php echo $get_me_meli; ?>
                    </ul>
                </div>
            </div>

            <div>
                <div class="uk-card uk-card-primary uk-card-body">
                    <h3 class="uk-card-title"><?php echo __("My Account", "woomelly"); ?> <?php echo $permalink; ?></h3>
                    <ul class="uk-list uk-list-divider">
                        <?php echo $get_me_user; ?>
                    </ul>
                </div>
            </div>

            <div>
                <div class="uk-card uk-card-primary uk-card-body">
                    <h3 class="uk-card-title"><?php echo __("Transactions", "woomelly"); ?></h3>
                    <ul class="uk-list uk-list-divider">
                        <?php echo $get_me_user_transactions; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php } else {
            echo $l_is_ok['form'];
        } ?>
</div>