<?php

$socials = array(

    'socials'  => array(

        'general-options' => array(
            'title' => __( 'Social Network Sites Options', 'yith-woocommerce-email-templates' ),
            'type' => 'title',
            'desc' => '',
            'id' => 'yith-wcet-socials-options'
        ),

        'facebook' => array(
            'id'        => 'yith-wcet-facebook',
            'name'      => __( 'Facebook Page Url', 'yith-woocommerce-email-templates' ),
            'type'      => 'text',
            'desc'      => __( 'Enter your Facebook page URL without http://', 'yith-woocommerce-email-templates' ),
        ),

        'twitter' => array(
            'id'        => 'yith-wcet-twitter',
            'name'      => __( 'Twitter Profile Url', 'yith-woocommerce-email-templates' ),
            'type'      => 'text',
            'desc'      => __( 'Enter your Twitter profile URL without http://', 'yith-woocommerce-email-templates' ),
        ),

        'google' => array(
            'id'        => 'yith-wcet-google',
            'name'      => __( 'Google+ Profile Url', 'yith-woocommerce-email-templates' ),
            'type'      => 'text',
            'desc'      => __( 'Enter your Google+ profile URL without http://', 'yith-woocommerce-email-templates' ),
        ),

        'linkedin' => array(
            'id'        => 'yith-wcet-linkedin',
            'name'      => __( 'LinkedIn Profile Url', 'yith-woocommerce-email-templates' ),
            'type'      => 'text',
            'desc'      => __( 'Enter your LinkedIn profile URL without http://', 'yith-woocommerce-email-templates' ),
        ),

        'instagram' => array(
            'id'        => 'yith-wcet-instagram',
            'name'      => __( 'Instagram Profile Url', 'yith-woocommerce-email-templates' ),
            'type'      => 'text',
            'desc'      => __( 'Enter your Instagram profile URL without http://', 'yith-woocommerce-email-templates' ),
        ),

        'flickr' => array(
            'id'        => 'yith-wcet-flickr',
            'name'      => __( 'Flickr Profile Url', 'yith-woocommerce-email-templates' ),
            'type'      => 'text',
            'desc'      => __( 'Enter your Flickr profile URL without http://', 'yith-woocommerce-email-templates' ),
        ),

        'pinterest' => array(
            'id'        => 'yith-wcet-pinterest',
            'name'      => __( 'Pinterest Profile Url', 'yith-woocommerce-email-templates' ),
            'type'      => 'text',
            'desc'      => __( 'Enter your Pinterest profile URL without http://', 'yith-woocommerce-email-templates' ),
        ),

        'general-options-end' => array(
            'type'      => 'sectionend',
            'id'        => 'yith-wcet-socials-options'
        )

    )
);

return $socials;