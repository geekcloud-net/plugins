<?php

?><html>
<head>
    <title>request details</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style type="text/css">
        pre {
        	background-color: #eee;
        	border: 1px solid #ccc;
        	padding: 20px;
        }
        table.wpla_profile_results {
            border-bottom: 1px solid #ccc;
            border-spacing: 0;
            margin-top: 1em;
        }
        .wpla_profile_results td {
            /*background-color:#ffc;*/
            /*vertical-align: top;*/
            border-top: 1px solid #ccc;
        }
        .wpla_profile_results td.info {
            padding: 10px 15px;
        }
        .wpla_profile_results td.info a {
            /*margin-top: 0.5em;*/
        }
        .wpla_profile_results tr:hover td.hover {
        	background-color:#ffc;
        }

    </style>
</head>

<body>

    <?php if ( ! empty( $wpl_profiles ) ) : ?>
    
        <h3 class="wpla_tb_title"><?php echo __('Select Profile','wpla') ?></h3>
 
        <table class="wpla_profile_results" style="width:100%">
        <?php foreach ($wpl_profiles as $profile ) : ?>
        
            <tr><td class="info hover">
                <big><?php echo $profile->profile_name ?></big><br>

                <small><?php echo $profile->profile_description ?></small>
                <br>

            </td><td class="info hover" style="text-align:right">
       
                <a href="#" onclick="WPLA.ProfileSelector.select(this,'<?php echo $profile->profile_id ?>');return false;" class="button button-secondary">
                    <?php echo __('Select Profile','wpla') ?>
                </a>

            </td></tr>

        <?php endforeach; ?>

        <tr><td class="info hover">
            <big><?php echo __('No Profile','wpla') ?></big><br>

            <small><?php echo __('Matched and imported listings do not need to have a profile assigned, so if you accidentally assigned a profile you can remove it using this option.','wpla') ?></small>
            <br>

        </td><td class="info hover" style="text-align:right">
   
            <a href="#" onclick="WPLA.ProfileSelector.select(this,'_NONE_');return false;" class="button button-secondary">
                <?php echo __('Remove Profile','wpla') ?>
            </a>

        </td></tr>

        </table>
    
    <?php else : ?>

        <h3 class="wpla_tb_title">No profiles found</h3>
 
        <p>
            You need to create a listing profile and assign a suitable feed template before you can start listing new products on Amazon.
        </p>
        <p>
            If your products already exist on Amazon, you can just search for matching products by clicking the magnifier icon on the right and link your WooCommerce products to existing Amazon products.
        </p>
    <?php endif; ?>

    <!-- <h3>Debug</h3> -->
    <!-- <pre><?php #print_r( $wpl_profiles ) ?></pre> -->



</body>
</html>
