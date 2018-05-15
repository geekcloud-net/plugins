<?php
/**
 * Importer class
 *
 * @author  Yithemes
 * @package YITH WooCommerce Bulk Edit Products
 * @version 1.0.0
 */

if ( !defined( 'YITH_WCBEP' ) ) {
    exit;
} // Exit if accessed directly

// Load Importer API
require_once ABSPATH . 'wp-admin/includes/import.php';

if ( !class_exists( 'WP_Importer' ) ) {
    $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
    if ( file_exists( $class_wp_importer ) )
        require $class_wp_importer;
}

if ( !class_exists( 'YITH_WCBEP_Importer' ) ) {
    /**
     * Importer class.
     * The class manage Product Import
     *
     * @since    1.0.0
     * @author   Leanza Francesco <leanzafrancesco@gmail.com>
     */
    class YITH_WCBEP_Importer extends WP_Importer {

        public $version;
        public $authors;
        public $posts;
        public $terms;
        public $attributes;
        public $base_url;
        public $processed_terms  = array();
        public $processed_posts  = array();
        public $featured_images;
        public $gallery_images   = array();
        public $processed_images = array();
        public $url_remap;

        public $file = '';

        /**
         * Constructor
         *
         * @access public
         * @since  1.0.0
         */
        public function __construct() {

        }

        public function prepare( $file ) {
            $this->file = $file;
        }

        /**
         * Main import
         *
         * @param string $file path to the WXR file to import
         */
        public function import( $file = null ) {

            if ( !$file ) {
                $file = $this->file;
            }
            $this->import_start( $file );

            wp_suspend_cache_invalidation( true );
            $this->process_attributes();
            $this->process_terms();
            $this->process_posts();
            wp_suspend_cache_invalidation( false );

            $this->remap_images();

            $this->import_end();
        }

        /*
         * End of import process
         */

        public function import_end() {
            echo '<p><strong>' . __( 'Importing process completed!', 'yith-woocommerce-bulk-product-editing' ) . '</strong></p>';
        }

        /**
         * Parses the WXR file and fill variable of this class
         *
         * @param string $file path to the WXR file to import
         */
        function import_start( $file ) {
            if ( !is_file( $file ) ) {
                echo '<p><strong>' . __( 'Sorry, an error has occurred.', 'yith-woocommerce-bulk-product-editing' ) . '</strong><br />';
                echo __( 'The file does not exist, please try again.', 'yith-woocommerce-bulk-product-editing' ) . '</p>';
                die();
            }
            $import_data = $this->parse( $file );

            if ( is_wp_error( $import_data ) ) {
                echo '<p><strong>' . __( 'Sorry, an error has occurred.', 'yith-woocommerce-bulk-product-editing' ) . '</strong><br />';
                echo esc_html( $import_data->get_error_message() ) . '</p>';
                die();
            }

            $this->version    = $import_data[ 'version' ];
            $this->authors    = $import_data[ 'authors' ];
            $this->posts      = $import_data[ 'posts' ];
            $this->terms      = $import_data[ 'terms' ];
            $this->attributes = $import_data[ 'attributes' ];
            $this->base_url   = esc_url( $import_data[ 'base_url' ] );

            wp_defer_term_counting( true );
            wp_defer_comment_counting( true );
        }

        /**
         * Create product attributes based on import information
         *
         */
        function process_attributes() {
            $this->attributes = apply_filters( 'yith_wcbep_import_attributess', $this->attributes );

            if ( empty( $this->attributes ) )
                return;

            global $wpdb;

            $already_exists = array();

            foreach ( $this->attributes as $attribute ) {
                if (empty( $attribute[ 'attribute_label' ]) )
                    $attribute[ 'attribute_label' ] = $attribute[ 'attribute_name' ];
                if ( empty( $attribute[ 'attribute_name' ] ) || empty( $attribute[ 'attribute_label' ] ) ) {
                    printf( __( 'Failed to import an attribute because it has no valid name', 'yith-woocommerce-bulk-product-editing' ) );
                    continue;
                } elseif ( taxonomy_exists( wc_attribute_taxonomy_name( $attribute[ 'attribute_name' ] ) ) ) {
                    $already_exists[] = esc_html( $attribute[ 'attribute_name' ] );
                    continue;
                }

                unset( $attribute[ 'attribute_id' ] );

                $wpdb->insert( $wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute );

                do_action( 'woocommerce_attribute_added', $wpdb->insert_id, $attribute );

                flush_rewrite_rules();
                delete_transient( 'wc_attribute_taxonomies' );
            }

            if ( !empty( $already_exists ) ) {
                foreach ( $already_exists as $already_exist ) {
                    printf( __( 'Attribute <strong>%s</strong> already exists.', 'yith-woocommerce-bulk-product-editing' ), $already_exist );
                    echo '<br />';
                }
            }
        }

        /**
         * Create terms based on import information
         *
         */
        function process_terms() {
            $this->terms = apply_filters( 'yith_wcbep_import_terms', $this->terms );

            if ( empty( $this->terms ) )
                return;

            foreach ( $this->terms as $term ) {
                $term_id = term_exists( $term[ 'slug' ], $term[ 'term_taxonomy' ] );
                if ( $term_id ) {
                    if ( is_array( $term_id ) )
                        $term_id = $term_id[ 'term_id' ];
                    if ( isset( $term[ 'term_id' ] ) )
                        $this->processed_terms[ intval( $term[ 'term_id' ] ) ] = (int)$term_id;
                    continue;
                }

                if ( empty( $term[ 'term_parent' ] ) ) {
                    $parent = 0;
                } else {
                    $parent = term_exists( $term[ 'term_parent' ], $term[ 'term_taxonomy' ] );
                    if ( is_array( $parent ) )
                        $parent = $parent[ 'term_id' ];
                }
                $description = isset( $term[ 'term_description' ] ) ? $term[ 'term_description' ] : '';
                $term_array  = array(
                    'slug'        => $term[ 'slug' ],
                    'description' => $description,
                    'parent'      => intval( $parent )
                );

                // Register Taxonomy if not exists [for wc attributes]
                if ( isset( $term[ 'term_taxonomy' ] ) && !taxonomy_exists( $term[ 'term_taxonomy' ] ) ) {
                    $domain = $term[ 'term_taxonomy' ];
                    register_taxonomy( $domain, apply_filters( 'woocommerce_taxonomy_objects_' . $domain, array( 'product' ) ), apply_filters( 'woocommerce_taxonomy_args_' . $domain, array(
                        'hierarchical' => true,
                        'show_ui'      => false,
                        'query_var'    => true,
                        'rewrite'      => false,
                    ) ) );
                }

                $id = wp_insert_term( $term[ 'term_name' ], $term[ 'term_taxonomy' ], $term_array );
                if ( !is_wp_error( $id ) ) {
                    if ( isset( $term[ 'term_id' ] ) )
                        $this->processed_terms[ intval( $term[ 'term_id' ] ) ] = $id[ 'term_id' ];
                } else {
                    printf( __( 'Failed to import %s %s', 'yith-woocommerce-bulk-product-editing' ), esc_html( $term[ 'term_taxonomy' ] ), esc_html( $term[ 'term_name' ] ) );
                    if ( defined( 'YITH_WCBEP_IMPORT_DEBUG' ) && YITH_WCBEP_IMPORT_DEBUG )
                        echo ': ' . $id->get_error_message();
                    echo '<br />';
                    continue;
                }
            }

            unset( $this->terms );
        }

        /**
         * Create new products and images based on import
         *
         */
        function process_posts() {
            $this->posts = apply_filters( 'yith_wcbep_import_posts', $this->posts );

            foreach ( $this->posts as $post ) {

                if ( !post_type_exists( $post[ 'post_type' ] ) ) {
                    printf( __( 'Failed to import <strong>%s</strong>: Invalid post type %s', 'yith-woocommerce-bulk-product-editing' ), esc_html( $post[ 'post_title' ] ), esc_html( $post[ 'post_type' ] ) );
                    echo '<br />';
                    continue;
                }

                if ( isset( $this->processed_posts[ $post[ 'post_id' ] ] ) && !empty( $post[ 'post_id' ] ) )
                    continue;

                if ( $post[ 'status' ] == 'auto-draft' )
                    continue;

                $post_type_object = get_post_type_object( $post[ 'post_type' ] );

                $post_exists = post_exists( $post[ 'post_title' ], '', $post[ 'post_date' ] );
                if ( $post_exists && get_post_type( $post_exists ) == $post[ 'post_type' ] ) {
                    printf( __( '%s <strong>%s</strong> already exists.', 'yith-woocommerce-bulk-product-editing' ), $post_type_object->labels->singular_name, esc_html( $post[ 'post_title' ] ) );
                    echo '<br />';
                    $post_id = $post_exists;
                } else {
                    $post_parent = (int)$post[ 'post_parent' ];
                    if ( $post_parent ) {
                        // if we already know the parent, map it to the new local ID
                        if ( isset( $this->processed_posts[ $post_parent ] ) ) {
                            $post_parent = $this->processed_posts[ $post_parent ];
                        } else {
                            $post_parent = 0;
                        }
                    }

                    // Assign new products and images to current user
                    $author = (int)get_current_user_id();

                    $postdata = array(
                        'import_id'      => $post[ 'post_id' ],
                        'guid'           => $post[ 'guid' ],
                        'post_author'    => $author,
                        'post_date'      => $post[ 'post_date' ],
                        'post_date_gmt'  => $post[ 'post_date_gmt' ],
                        'post_content'   => $post[ 'post_content' ],
                        'post_excerpt'   => $post[ 'post_excerpt' ],
                        'post_title'     => $post[ 'post_title' ],
                        'post_status'    => $post[ 'status' ],
                        'post_name'      => $post[ 'post_name' ],
                        'comment_status' => $post[ 'comment_status' ],
                        'ping_status'    => $post[ 'ping_status' ],
                        'post_parent'    => $post_parent,
                        'menu_order'     => $post[ 'menu_order' ],
                        'post_type'      => $post[ 'post_type' ],
                        'post_password'  => $post[ 'post_password' ]
                    );

                    $original_post_ID = $post[ 'post_id' ];
                    $postdata         = apply_filters( 'yith_wcbep_import_post_data_processed', $postdata, $post );

                    if ( 'attachment' == $postdata[ 'post_type' ] ) {
                        $remote_url = !empty( $post[ 'attachment_url' ] ) ? $post[ 'attachment_url' ] : $post[ 'guid' ];

                        $postdata[ 'upload_date' ] = $post[ 'post_date' ];
                        if ( isset( $post[ 'postmeta' ] ) ) {
                            foreach ( $post[ 'postmeta' ] as $meta ) {
                                if ( $meta[ 'key' ] == '_wp_attached_file' ) {
                                    if ( preg_match( '%^[0-9]{4}/[0-9]{2}%', $meta[ 'value' ], $matches ) )
                                        $postdata[ 'upload_date' ] = $matches[ 0 ];
                                    break;
                                }
                            }
                        }

                        $post_id = $this->process_attachment( $postdata, $remote_url );
                        if ( is_wp_error( $post_id ) ) {
                            printf( __( 'Failed to import %s <strong>%s</strong>', 'yith-woocommerce-bulk-product-editing' ), $post_type_object->labels->singular_name, esc_html( $post[ 'post_title' ] ) );
                            if ( defined( 'YITH_WCBEP_IMPORT_DEBUG' ) && YITH_WCBEP_IMPORT_DEBUG )
                                echo ': ' . $post_id->get_error_message();
                            echo '<br />';
                            continue;
                        }
                        $this->processed_images[ intval( $original_post_ID ) ] = (int)$post_id;
                    } else {
                        $post_id = wp_insert_post( $postdata, true );
                        // Set the product_type for this imported product
                        wp_set_object_terms( $post_id, $post[ 'prod_type' ], 'product_type' );
                    }

                    if ( is_wp_error( $post_id ) ) {
                        printf( __( 'Failed to import %s <strong>%s</strong>', 'yith-woocommerce-bulk-product-editing' ), $post_type_object->labels->singular_name, esc_html( $post[ 'post_title' ] ) );
                        if ( defined( 'YITH_WCBEP_IMPORT_DEBUG' ) && YITH_WCBEP_IMPORT_DEBUG )
                            echo ': ' . $post_id->get_error_message();
                        echo '<br />';
                        continue;
                    }
                }

                // save pre-import ID and this new ID
                $this->processed_posts[ intval( $post[ 'post_id' ] ) ] = (int)$post_id;

                if ( !isset( $post[ 'terms' ] ) )
                    $post[ 'terms' ] = array();

                $post[ 'terms' ] = apply_filters( 'yith_wcbep_import_post_terms', $post[ 'terms' ], $post_id, $post );

                // add categories, tags and other terms
                if ( !empty( $post[ 'terms' ] ) ) {
                    $terms_to_set = array();
                    foreach ( $post[ 'terms' ] as $term ) {
                        $taxonomy    = ( 'tag' == $term[ 'domain' ] ) ? 'post_tag' : $term[ 'domain' ];
                        $term_exists = term_exists( $term[ 'slug' ], $taxonomy );
                        $term_id     = is_array( $term_exists ) ? $term_exists[ 'term_id' ] : $term_exists;
                        if ( !$term_id ) {
                            if ( isset( $term[ 'domain' ] ) && !taxonomy_exists( $term[ 'domain' ] ) ) {
                                $domain = $term[ 'domain' ];
                                register_taxonomy( $domain, apply_filters( 'woocommerce_taxonomy_objects_' . $domain, array( 'product' ) ), apply_filters( 'woocommerce_taxonomy_args_' . $domain, array(
                                    'hierarchical' => true,
                                    'show_ui'      => false,
                                    'query_var'    => true,
                                    'rewrite'      => false,
                                ) ) );
                            }

                            $t = wp_insert_term( $term[ 'name' ], $taxonomy, array( 'slug' => $term[ 'slug' ] ) );
                            if ( !is_wp_error( $t ) ) {
                                $term_id = $t[ 'term_id' ];
                                do_action( 'wp_import_insert_term', $t, $term, $post_id, $post );
                            } else {
                                printf( __( 'Failed to import %s [%s]', 'yith-woocommerce-bulk-product-editing' ), esc_html( $taxonomy ), esc_html( $term[ 'name' ] ) );
                                if ( defined( 'YITH_WCBEP_IMPORT_DEBUG' ) && YITH_WCBEP_IMPORT_DEBUG )
                                    echo ': ' . $t->get_error_message();
                                echo '<br />';
                                continue;
                            }
                        }
                        $terms_to_set[ $taxonomy ][] = intval( $term_id );
                    }

                    foreach ( $terms_to_set as $tax => $ids ) {
                        $tt_ids = wp_set_post_terms( $post_id, $ids, $tax );
                        do_action( 'yith_wcbep_import_set_post_terms', $tt_ids, $ids, $tax, $post_id, $post );
                    }
                    unset( $post[ 'terms' ], $terms_to_set );
                }

                if ( !isset( $post[ 'postmeta' ] ) )
                    $post[ 'postmeta' ] = array();

                $post[ 'postmeta' ] = apply_filters( 'yith_wcbep_import_post_meta', $post[ 'postmeta' ], $post_id, $post );

                // add/update post meta
                if ( !empty( $post[ 'postmeta' ] ) ) {
                    foreach ( $post[ 'postmeta' ] as $meta ) {
                        $key   = apply_filters( 'yith_wcbep_import_post_meta_key', $meta[ 'key' ], $post_id, $post );
                        $value = false;

                        if ( $key ) {
                            if ( !$value )
                                $value = maybe_unserialize( $meta[ 'value' ] );

                            add_post_meta( $post_id, $key, $value );
                            do_action( 'import_post_meta', $post_id, $key, $value );

                            // if the post has a featured image or image gallery, take note of these for remap
                            if ( '_thumbnail_id' == $key )
                                $this->featured_images[ $post_id ] = (int)$value;

                            if ( '_product_image_gallery' == $key ) {
                                $this->gallery_images[ $post_id ] = $value;
                            }
                        }
                    }
                }
            }

            unset( $this->posts );
        }

        /**
         * Create new attachment if not exist in base of import data
         *
         * @param array  $post Attachment post details
         * @param string $url  URL to fetch attachment from
         *
         * @return int|WP_Error Post ID on success, WP_Error otherwise
         */
        function process_attachment( $post, $url ) {
            // if the URL is absolute, but does not contain address, then upload it assuming base_site_url
            if ( preg_match( '|^/[\w\W]+$|', $url ) )
                $url = rtrim( $this->base_url, '/' ) . $url;

            $upload = $this->fetch_remote_file( $url, $post );
            if ( is_wp_error( $upload ) )
                return $upload;

            if ( $info = wp_check_filetype( $upload[ 'file' ] ) )
                $post[ 'post_mime_type' ] = $info[ 'type' ]; else
                return new WP_Error( 'attachment_processing_error', __( 'Invalid file type', 'yith-woocommerce-bulk-product-editing' ) );

            $post[ 'guid' ] = $upload[ 'url' ];

            $post_id = wp_insert_attachment( $post, $upload[ 'file' ] );
            wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload[ 'file' ] ) );

            // remap resized image URLs
            if ( preg_match( '!^image/!', $info[ 'type' ] ) ) {
                $parts = pathinfo( $url );
                $name  = basename( $parts[ 'basename' ], ".{$parts['extension']}" );

                $parts_new = pathinfo( $upload[ 'url' ] );
                $name_new  = basename( $parts_new[ 'basename' ], ".{$parts_new['extension']}" );

                $this->url_remap[ $parts[ 'dirname' ] . '/' . $name ] = $parts_new[ 'dirname' ] . '/' . $name_new;
            }

            return $post_id;
        }

        /**
         * Attempt to download a remote file attachment
         *
         * @param string $url  URL of item to fetch
         * @param array  $post Attachment details
         *
         * @return array|WP_Error Local file location details on success, WP_Error otherwise
         */
        function fetch_remote_file( $url, $post ) {
            // extract the file name and extension from the url
            $file_name = basename( $url );

            // get placeholder file in the upload dir with a unique, sanitized filename
            $upload = wp_upload_bits( $file_name, 0, '', $post[ 'upload_date' ] );
            if ( $upload[ 'error' ] )
                return new WP_Error( 'upload_dir_error', $upload[ 'error' ] );

            // fetch the remote url and write it to the placeholder file
            $headers = wp_get_http( $url, $upload[ 'file' ] );

            // request failed
            if ( !$headers ) {
                @unlink( $upload[ 'file' ] );

                return new WP_Error( 'import_file_error', __( 'Remote server did not respond', 'yith-woocommerce-bulk-product-editing' ) );
            }

            // make sure the fetch was successful
            if ( $headers[ 'response' ] != '200' ) {
                @unlink( $upload[ 'file' ] );

                return new WP_Error( 'import_file_error', sprintf( __( 'Remote server returned error response %1$d %2$s', 'yith-woocommerce-bulk-product-editing' ), esc_html( $headers[ 'response' ] ), get_status_header_desc( $headers[ 'response' ] ) ) );
            }

            $filesize = filesize( $upload[ 'file' ] );

            if ( isset( $headers[ 'content-length' ] ) && $filesize != $headers[ 'content-length' ] ) {
                @unlink( $upload[ 'file' ] );

                return new WP_Error( 'import_file_error', __( 'Remote file with incorrect size', 'yith-woocommerce-bulk-product-editing' ) );
            }

            if ( 0 == $filesize ) {
                @unlink( $upload[ 'file' ] );

                return new WP_Error( 'import_file_error', __( 'Zero size file downloaded', 'yith-woocommerce-bulk-product-editing' ) );
            }

            $max_size = (int)apply_filters( 'import_attachment_size_limit', 0 );
            if ( !empty( $max_size ) && $filesize > $max_size ) {
                @unlink( $upload[ 'file' ] );

                return new WP_Error( 'import_file_error', sprintf( __( 'Remote file is too large, limit is %s', 'yith-woocommerce-bulk-product-editing' ), size_format( $max_size ) ) );
            }

            // keep track of the old and new urls so we can substitute them later
            $this->url_remap[ $url ]            = $upload[ 'url' ];
            $this->url_remap[ $post[ 'guid' ] ] = $upload[ 'url' ]; // r13735, really needed?
            // keep track of the destination if the remote url is redirected somewhere else
            if ( isset( $headers[ 'x-final-location' ] ) && $headers[ 'x-final-location' ] != $url )
                $this->url_remap[ $headers[ 'x-final-location' ] ] = $upload[ 'url' ];

            return $upload;
        }

        /**
         * Update _thumbnail_id and _product_image_gallery meta to new, imported attachment IDs
         */
        function remap_images() {
            // cycle through posts that have a featured image
            foreach ( $this->featured_images as $post_id => $value ) {
                if ( isset( $this->processed_posts[ $value ] ) ) {
                    $new_id = $this->processed_posts[ $value ];
                    // only update if there's a difference
                    if ( $new_id != $value )
                        update_post_meta( $post_id, '_thumbnail_id', $new_id );
                }
            }

            foreach ( $this->gallery_images as $post_id => $value ) {
                $new_array = array();
                $v_array   = explode( ',', $value );
                foreach ( $v_array as $single_id ) {
                    $single_id = intval( $single_id );
                    if ( isset( $this->processed_posts[ $single_id ] ) ) {
                        $new_id      = $this->processed_posts[ $single_id ];
                        $new_array[] = $new_id;
                    } else {
                        $new_array[] = $single_id;
                    }
                }
                $new_array = implode( ',', $new_array );
                update_post_meta( $post_id, '_product_image_gallery', $new_array );
            }
        }

        /**
         * Parse a WXR file
         *
         * @param string $file Path to WXR file for parsing
         *
         * @return array Information gathered from the WXR file
         */
        function parse( $file ) {
            $parser = new YITH_WCBEP_XMLParser();

            return $parser->parse( $file );
        }

    }
}
?>