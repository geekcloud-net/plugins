<?php

// don't load directly
if (!defined('ABSPATH'))
    die('-1');

class PortoContentTypesClass {

    function __construct() {

        // Load Functions
        include_once( PORTO_CONTENT_TYPES_LIB . 'general.php');

        // Register content types
        add_action('init', array( $this, 'addBlockContentType' ) );
        add_action('init', array( $this, 'addFaqContentType' ) );
        add_action('init', array( $this, 'addMemberContentType' ) );
        add_action('init', array( $this, 'addPortfolioContentType' ) );
        add_action('init', array( $this, 'addEventContentType' ) );
    }

    // Register block content type
    function addBlockContentType() {
        register_post_type(
            'block',
            array(
                'labels' => $this->getLabels(__('Block', 'porto-content-types'), __('Blocks', 'porto-content-types')),
                'exclude_from_search' => true,
                'has_archive' => false,
                'public' => true,
                'rewrite' => array('slug' => 'block'),
                'supports' => array('title', 'editor'),
                'can_export' => true,
                'show_in_nav_menus' => false
            )
        );
    }

    // Register portfolio content type
    function addPortfolioContentType() {
        global $porto_settings;

        $enable_content_type = (isset($porto_settings) && isset($porto_settings['enable-portfolio'])) ? $porto_settings['enable-portfolio'] : true;
        if (!$enable_content_type)
            return;

        $slug_name = (isset($porto_settings) && isset($porto_settings['portfolio-slug-name']) && $porto_settings['portfolio-slug-name']) ? esc_attr($porto_settings['portfolio-slug-name']) : 'portfolio';
        $name = (isset($porto_settings) && isset($porto_settings['portfolio-name']) && $porto_settings['portfolio-name']) ? $porto_settings['portfolio-name'] : __('Portfolios', 'porto-content-types');
        $singular_name = (isset($porto_settings) && isset($porto_settings['portfolio-singular-name']) && $porto_settings['portfolio-singular-name']) ? $porto_settings['portfolio-singular-name'] : __('Portfolio', 'porto-content-types');
        $cat_name = (isset($porto_settings) && isset($porto_settings['portfolio-singular-name']) && $porto_settings['portfolio-singular-name']) ? $porto_settings['portfolio-singular-name'] . ' ' . __('Category', 'porto-content-types') : __('Portfolio Category', 'porto-content-types');
        $cats_name = (isset($porto_settings) && isset($porto_settings['portfolio-singular-name']) && $porto_settings['portfolio-singular-name']) ? $porto_settings['portfolio-singular-name'] . ' ' . __('Categories', 'porto-content-types') : __('Portfolio Categories', 'porto-content-types');
        $skill_name = (isset($porto_settings) && isset($porto_settings['portfolio-singular-name']) && $porto_settings['portfolio-singular-name']) ? $porto_settings['portfolio-singular-name'] . ' ' . __('Skill', 'porto-content-types') : __('Portfolio Skill', 'porto-content-types');
        $skills_name = (isset($porto_settings) && isset($porto_settings['portfolio-singular-name']) && $porto_settings['portfolio-singular-name']) ? $porto_settings['portfolio-singular-name'] . ' ' . __('Skills', 'porto-content-types') : __('Portfolio Skills', 'porto-content-types');
        $cat_slug_name = (isset($porto_settings) && isset($porto_settings['portfolio-cat-slug-name']) && $porto_settings['portfolio-cat-slug-name']) ? esc_attr($porto_settings['portfolio-cat-slug-name']) : 'portfolio_cat';
        $skill_slug_name = (isset($porto_settings) && isset($porto_settings['portfolio-skill-slug-name']) && $porto_settings['portfolio-skill-slug-name']) ? esc_attr($porto_settings['portfolio-skill-slug-name']) : 'portfolio_skill';
        $archive_page_id = (isset($porto_settings) && isset($porto_settings['portfolio-archive-page']) && $porto_settings['portfolio-archive-page']) ? esc_attr($porto_settings['portfolio-archive-page']) : 0;
        $has_archive = true;
        if ($archive_page_id && get_post( $archive_page_id ))
            $has_archive = get_page_uri( $archive_page_id );

        register_post_type(
            'portfolio',
            array(
                'labels' => $this->getLabels($singular_name, $name),
                'exclude_from_search' => false,
                'has_archive' => $has_archive,
                'public' => true,
                'rewrite' => array('slug' => $slug_name),
                'supports' => array('title', 'editor', 'thumbnail', 'comments', 'page-attributes'),
                'can_export' => true,
                'show_in_nav_menus' => true
            )
        );

        register_taxonomy(
            'portfolio_cat',
            'portfolio',
            array(
                'hierarchical' => true,
                'show_in_nav_menus' => true,
                'labels' => $this->getTaxonomyLabels($cat_name, $cats_name),
                'query_var' => true,
                'rewrite' => array('slug' => $cat_slug_name)
            )
        );

        register_taxonomy(
            'portfolio_skills',
            'portfolio',
            array(
                'hierarchical' => false,
                'show_in_nav_menus' => true,
                'labels' => $this->getTaxonomyLabels($skill_name, $skills_name),
                'query_var' => true,
                'rewrite' => array('slug' => $skill_slug_name)
            )
        );

        include_once(PORTO_CONTENT_TYPES_LIB . 'portfolio.php');
    }

    // Register faq content type
    function addFaqContentType() {
        global $porto_settings;

        $enable_content_type = (isset($porto_settings) && isset($porto_settings['enable-faq'])) ? $porto_settings['enable-faq'] : true;
        if (!$enable_content_type)
            return;

        $slug_name = (isset($porto_settings) && isset($porto_settings['faq-slug-name']) && $porto_settings['faq-slug-name']) ? esc_attr($porto_settings['faq-slug-name']) : 'faq';
        $name = (isset($porto_settings) && isset($porto_settings['faq-name']) && $porto_settings['faq-name']) ? $porto_settings['faq-name'] : __('FAQs', 'porto-content-types');
        $singular_name = (isset($porto_settings) && isset($porto_settings['faq-singular-name']) && $porto_settings['faq-singular-name']) ? $porto_settings['faq-singular-name'] : __('FAQ', 'porto-content-types');
        $cat_name = (isset($porto_settings) && isset($porto_settings['faq-singular-name']) && $porto_settings['faq-singular-name']) ? $porto_settings['faq-singular-name'] . ' ' . __('Category', 'porto-content-types') : __('FAQ Category', 'porto-content-types');
        $cats_name = (isset($porto_settings) && isset($porto_settings['faq-singular-name']) && $porto_settings['faq-singular-name']) ? $porto_settings['faq-singular-name'] . ' ' . __('Categories', 'porto-content-types') : __('FAQ Categories', 'porto-content-types');
        $cat_slug_name = (isset($porto_settings) && isset($porto_settings['faq-cat-slug-name']) && $porto_settings['faq-cat-slug-name']) ? esc_attr($porto_settings['faq-cat-slug-name']) : 'faq_cat';
        $archive_page_id = (isset($porto_settings) && isset($porto_settings['faq-archive-page']) && $porto_settings['faq-archive-page']) ? esc_attr($porto_settings['faq-archive-page']) : 0;
        $has_archive = true;
        if ($archive_page_id && get_post( $archive_page_id ))
            $has_archive = get_page_uri( $archive_page_id );

        register_post_type(
            'faq',
            array(
                'labels' => $this->getLabels($singular_name, $name),
                'exclude_from_search' => false,
                'has_archive' => $has_archive,
                'public' => true,
                'rewrite' => array('slug' => $slug_name),
                'supports' => array('title', 'editor'),
                'can_export' => true,
                'show_in_nav_menus' => true
            )
        );

        register_taxonomy(
            'faq_cat',
            'faq',
            array(
                'hierarchical' => true,
                'show_in_nav_menus' => true,
                'labels' => $this->getTaxonomyLabels($cat_name, $cats_name),
                'query_var' => true,
                'rewrite' => array('slug' => $cat_slug_name)
            )
        );

        include_once(PORTO_CONTENT_TYPES_LIB . 'faq.php');
    }

    // Register member content type
    function addMemberContentType() {
        global $porto_settings;

        $enable_content_type = (isset($porto_settings) && isset($porto_settings['enable-member'])) ? $porto_settings['enable-member'] : true;
        if (!$enable_content_type)
            return;

        $slug_name = (isset($porto_settings) && isset($porto_settings['member-slug-name']) && $porto_settings['member-slug-name']) ? esc_attr($porto_settings['member-slug-name']) : 'member';
        $name = (isset($porto_settings) && isset($porto_settings['member-name']) && $porto_settings['member-name']) ? $porto_settings['member-name'] : __('Members', 'porto-content-types');
        $singular_name = (isset($porto_settings) && isset($porto_settings['member-singular-name']) && $porto_settings['member-singular-name']) ? $porto_settings['member-singular-name'] : __('Member', 'porto-content-types');
        $cat_name = (isset($porto_settings) && isset($porto_settings['member-singular-name']) && $porto_settings['member-singular-name']) ? $porto_settings['member-singular-name'] . ' ' . __('Category', 'porto-content-types') : __('Member Category', 'porto-content-types');
        $cats_name = (isset($porto_settings) && isset($porto_settings['member-singular-name']) && $porto_settings['member-singular-name']) ? $porto_settings['member-singular-name'] . ' ' . __('Categories', 'porto-content-types') : __('Member Categories', 'porto-content-types');
        $cat_slug_name = (isset($porto_settings) && isset($porto_settings['member-cat-slug-name']) && $porto_settings['member-cat-slug-name']) ? esc_attr($porto_settings['member-cat-slug-name']) : 'member_cat';
        $archive_page_id = (isset($porto_settings) && isset($porto_settings['member-archive-page']) && $porto_settings['member-archive-page']) ? esc_attr($porto_settings['member-archive-page']) : 0;
        $has_archive = true;
        if ($archive_page_id && get_post( $archive_page_id ))
            $has_archive = get_page_uri( $archive_page_id );

        register_post_type(
            'member',
            array(
                'labels' => $this->getLabels($singular_name, $name),
                'exclude_from_search' => false,
                'has_archive' => $has_archive,
                'public' => true,
                'rewrite' => array('slug' => $slug_name),
                'supports' => array('title', 'editor', 'thumbnail', 'comments', 'page-attributes'),
                'can_export' => true,
                'show_in_nav_menus' => true
            )
        );

        register_taxonomy(
            'member_cat',
            'member',
            array(
                'hierarchical' => true,
                'show_in_nav_menus' => true,
                'labels' => $this->getTaxonomyLabels($cat_name, $cats_name),
                'query_var' => true,
                'rewrite' => array('slug' => $cat_slug_name)
            )
        );

        include_once(PORTO_CONTENT_TYPES_LIB . 'member.php');
    }

	// Register event content type
    function addEventContentType() {
        global $porto_settings;

        $enable_content_type = (isset($porto_settings) && isset($porto_settings['enable-event'])) ? $porto_settings['enable-event'] : true;
        if (!$enable_content_type)
            return;

        $slug_name = (isset($porto_settings) && isset($porto_settings['event-slug-name']) && $porto_settings['event-slug-name']) ? esc_attr($porto_settings['event-slug-name']) : 'event';
        $name = (isset($porto_settings) && isset($porto_settings['event-name']) && $porto_settings['event-name']) ? $porto_settings['event-name'] : __('Events', 'porto-content-types');
        $singular_name = (isset($porto_settings) && isset($porto_settings['event-singular-name']) && $porto_settings['event-singular-name']) ? $porto_settings['event-singular-name'] : __('Event', 'porto-content-types');
        $cat_name = (isset($porto_settings) && isset($porto_settings['event-singular-name']) && $porto_settings['event-singular-name']) ? $porto_settings['event-singular-name'] . ' ' . __('Category', 'porto-content-types') : __('Event Category', 'porto-content-types');
        $cats_name = (isset($porto_settings) && isset($porto_settings['event-singular-name']) && $porto_settings['event-singular-name']) ? $porto_settings['event-singular-name'] . ' ' . __('Categories', 'porto-content-types') : __('Event Categories', 'porto-content-types');
        $skill_name = (isset($porto_settings) && isset($porto_settings['event-singular-name']) && $porto_settings['event-singular-name']) ? $porto_settings['event-singular-name'] . ' ' . __('Skill', 'porto-content-types') : __('Event Skill', 'porto-content-types');
        $skills_name = (isset($porto_settings) && isset($porto_settings['event-singular-name']) && $porto_settings['event-singular-name']) ? $porto_settings['event-singular-name'] . ' ' . __('Skills', 'porto-content-types') : __('Event Skills', 'porto-content-types');
        $cat_slug_name = (isset($porto_settings) && isset($porto_settings['event-cat-slug-name']) && $porto_settings['event-cat-slug-name']) ? esc_attr($porto_settings['event-cat-slug-name']) : 'event_cat';
        $skill_slug_name = (isset($porto_settings) && isset($porto_settings['event-skill-slug-name']) && $porto_settings['event-skill-slug-name']) ? esc_attr($porto_settings['event-skill-slug-name']) : 'event_skill';
        $archive_page_id = (isset($porto_settings) && isset($porto_settings['event-archive-page']) && $porto_settings['event-archive-page']) ? esc_attr($porto_settings['event-archive-page']) : 0;
        $has_archive = true;
        if ($archive_page_id && get_post( $archive_page_id ))
            $has_archive = get_page_uri( $archive_page_id );

        register_post_type(
            'event',
            array(
                'labels' => $this->getLabels($singular_name, $name),
                'exclude_from_search' => false,
                'has_archive' => $has_archive,
                'public' => true,
                'rewrite' => array('slug' => $slug_name),
                'supports' => array('title', 'editor', 'thumbnail', 'page-attributes'),
                'can_export' => true,
                'show_in_nav_menus' => true
            )
        );
        include_once(PORTO_CONTENT_TYPES_LIB . 'event.php');
    }


    // Get content type labels
    function getLabels($singular_name, $name, $title = FALSE) {
        if( !$title )
            $title = $name;

        return array(
            "name" => $title,
            "singular_name" => $singular_name,
            "add_new" => __("Add New", 'porto-content-types'),
            "add_new_item" => sprintf( __("Add New %s", 'porto-content-types'), $singular_name),
            "edit_item" => sprintf( __("Edit %s", 'porto-content-types'), $singular_name),
            "new_item" => sprintf( __("New %s", 'porto-content-types'), $singular_name),
            "view_item" => sprintf( __("View %s", 'porto-content-types'), $singular_name),
            "search_items" => sprintf( __("Search %s", 'porto-content-types'), $name),
            "not_found" => sprintf( __("No %s found", 'porto-content-types'), $name),
            "not_found_in_trash" => sprintf( __("No %s found in Trash", 'porto-content-types'), $name),
            "parent_item_colon" => ""
        );
    }

    // Get content type taxonomy labels
    function getTaxonomyLabels($singular_name, $name) {
        return array(
            "name" => $name,
            "singular_name" => $singular_name,
            "search_items" => sprintf( __("Search %s", 'porto-content-types'), $name),
            "all_items" => sprintf( __("All %s", 'porto-content-types'), $name),
            "parent_item" => sprintf( __("Parent %s", 'porto-content-types'), $singular_name),
            "parent_item_colon" => sprintf( __("Parent %s:", 'porto-content-types'), $singular_name),
            "edit_item" => sprintf( __("Edit %", 'porto-content-types'), $singular_name),
            "update_item" => sprintf( __("Update %s", 'porto-content-types'), $singular_name),
            "add_new_item" => sprintf( __("Add New %s", 'porto-content-types'), $singular_name),
            "new_item_name" => sprintf( __("New %s Name", 'porto-content-types'), $singular_name),
            "menu_name" => $name,
        );
    }
}

// Finally initialize code
new PortoContentTypesClass();
