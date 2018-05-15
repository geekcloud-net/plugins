<?php
/**
 * Exporter class
 *
 * @author  Yithemes
 * @package YITH WooCommerce Bulk Edit Products
 * @version 1.0.0
 */

if ( !defined( 'YITH_WCBEP' ) ) {
    exit;
} // Exit if accessed directly

define( 'YITH_WXR_VERSION', '1.2' );

if ( !class_exists( 'YITH_WCBEP_Exporter' ) ) {
    /**
     * Exporter class.
     * The class manage Product Export
     *
     * @since    1.0.0
     * @author   Leanza Francesco <leanzafrancesco@gmail.com>
     */
    class YITH_WCBEP_Exporter {

        public $sitename;

        public $all_images = array();

        /**
         * Constructor
         *
         * @access public
         * @since  1.0.0
         */
        public function __construct() {
            $this->sitename = sanitize_key( get_bloginfo( 'name' ) );
        }


        /**
         * Wrap given string in XML CDATA tag.
         *
         * @since 1.0.0
         *
         * @param string $str String to wrap in XML CDATA tag.
         *
         * @return string
         */
        public function wxr_cdata( $str ) {
            if ( seems_utf8( $str ) == false )
                $str = utf8_encode( $str );

            // $str = ent2ncr(esc_html($str));
            $str = '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $str ) . ']]>';

            return $str;
        }

        /**
         * Output list of authors with posts
         *
         * @since 1.0.0
         *
         * @param array $post_ids Array of post IDs to filter the query by. Optional.
         */
        function wxr_authors_list( array $post_ids = null ) {
            global $wpdb;

            if ( !empty( $post_ids ) ) {
                $post_ids = array_map( 'absint', $post_ids );
                $and      = 'AND ID IN ( ' . implode( ', ', $post_ids ) . ')';
            } else {
                $and = '';
            }

            $authors = array();
            $results = $wpdb->get_results( "SELECT DISTINCT post_author FROM $wpdb->posts WHERE post_status != 'auto-draft' $and" );
            foreach ( (array)$results as $result )
                $authors[] = get_userdata( $result->post_author );

            $authors = array_filter( $authors );

            foreach ( $authors as $author ) {
                echo "\t<wp:author>";
                echo '<wp:author_id>' . $author->ID . '</wp:author_id>';
                echo '<wp:author_login>' . $author->user_login . '</wp:author_login>';
                echo '<wp:author_email>' . $author->user_email . '</wp:author_email>';
                echo '<wp:author_display_name>' . $this->wxr_cdata( $author->display_name ) . '</wp:author_display_name>';
                echo '<wp:author_first_name>' . $this->wxr_cdata( $author->user_firstname ) . '</wp:author_first_name>';
                echo '<wp:author_last_name>' . $this->wxr_cdata( $author->user_lastname ) . '</wp:author_last_name>';
                echo "</wp:author>\n";
            }
        }

        /**
         * Output a term_name XML tag from a given term object
         *
         * @since 1.0.0
         *
         * @param object $term Term Object
         */
        function wxr_term_name( $term ) {
            if ( empty( $term->name ) ) {
                echo '<wp:term_name>' . $this->wxr_cdata( "" ) . '</wp:term_name>';

                return;
            }

            echo '<wp:term_name>' . $this->wxr_cdata( $term->name ) . '</wp:term_name>';
        }

        /**
         * Output a term_description XML tag from a given term object
         *
         * @since 1.0.0
         *
         * @param object $term Term Object
         */
        function wxr_term_description( $term ) {
            if ( empty( $term->description ) )
                return;

            echo '<wp:term_description>' . $this->wxr_cdata( $term->description ) . '</wp:term_description>';
        }

        /**
         * Output list of taxonomy terms, in XML tag format, associated with a post
         *
         * @since 2.3.0
         */
        function wxr_post_taxonomy( $id ) {
            $post = get_post( $id );

            $taxonomies = get_object_taxonomies( $post->post_type );
            if ( empty( $taxonomies ) )
                return;
            $terms = wp_get_object_terms( $post->ID, $taxonomies );

            foreach ( (array)$terms as $term ) {
                echo "\t\t<category domain=\"{$term->taxonomy}\" nicename=\"{$term->slug}\">" . $this->wxr_cdata( $term->name ) . "</category>\n";
            }
        }

        /**
         * @param WP_Post    $post
         * @param WC_Product $prod
         */
        public function render_post( $post, $prod ) {
            global $wpdb;
            if ( !$post )
                return;
            ?>
            <item>
                <title><?php echo $this->wxr_cdata( $post->post_title ) ?></title>
                <pubDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true, $post->ID ), false ); ?></pubDate>
                <guid isPermaLink="false"><?php the_guid( $post->ID ); ?></guid>
                <description></description>
                <content:encoded><?php
                    echo $this->wxr_cdata( apply_filters( 'yith_wcbep_the_content_export', $post->post_content ) );
                    ?></content:encoded>
                <excerpt:encoded><?php
                    echo $this->wxr_cdata( apply_filters( 'yith_wcbep_the_excerpt_export', $post->post_excerpt ) );
                    ?></excerpt:encoded>
                <wp:post_id><?php echo $post->ID; ?></wp:post_id>
                <wp:post_date><?php echo $post->post_date; ?></wp:post_date>
                <wp:post_date_gmt><?php echo $post->post_date_gmt; ?></wp:post_date_gmt>
                <wp:comment_status><?php echo $post->comment_status; ?></wp:comment_status>
                <wp:ping_status><?php echo $post->ping_status; ?></wp:ping_status>
                <wp:post_name><?php echo $post->post_name; ?></wp:post_name>
                <wp:status><?php echo $post->post_status; ?></wp:status>
                <wp:post_parent><?php echo $post->post_parent; ?></wp:post_parent>
                <wp:menu_order><?php echo $post->menu_order; ?></wp:menu_order>
                <wp:post_type><?php echo $post->post_type; ?></wp:post_type>
                <?php if ( $prod ) : ?>
                    <wp:prod_type><?php echo $prod->get_type(); ?></wp:prod_type>
                <?php endif; ?>
                <wp:post_password><?php echo $post->post_password; ?></wp:post_password>
                <?php $this->wxr_post_taxonomy( $post->ID ); ?>
                <?php $postmeta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d", $post->ID ) );
                foreach ( $postmeta as $meta ) : ?>
                    <wp:postmeta>
                        <wp:meta_key><?php echo $meta->meta_key; ?></wp:meta_key>
                        <wp:meta_value><?php echo $this->wxr_cdata( $meta->meta_value ); ?></wp:meta_value>
                    </wp:postmeta>
                <?php endforeach; ?>

            </item>
            <?php
        }

        public function export_products( $ids ) {
            if ( !empty( $this->sitename ) )
                $this->sitename .= '.';
            $filename = $this->sitename . 'yith_products.' . date( 'Y-m-d' ) . '.xml';

            $terms             = array();
            $custom_taxonomies = get_taxonomies( array( '_builtin' => false ) );
            $custom_terms      = (array)get_terms( $custom_taxonomies, array( 'get' => 'all' ) );

            // Put terms in order with no child going before its parent.
            while ( $t = array_shift( $custom_terms ) ) {
                if ( $t->parent == 0 || isset( $terms[ $t->parent ] ) )
                    $terms[ $t->term_id ] = $t; else
                    $custom_terms[] = $t;
            }

            $attributes = wc_get_attribute_taxonomies();

            header( 'Content-Description: File Transfer' );
            header( 'Content-Disposition: attachment; filename=' . $filename );
            header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

            echo '<?xml version="1.0" encoding="' . get_bloginfo( 'charset' ) . "\" ?>\n";
            ?>
            <rss version="2.0" xmlns:excerpt="http://wordpress.org/export/<?php echo YITH_WXR_VERSION; ?>/excerpt/"
                 xmlns:content="http://purl.org/rss/1.0/modules/content/"
                 xmlns:wfw="http://wellformedweb.org/CommentAPI/"
                 xmlns:dc="http://purl.org/dc/elements/1.1/"
                 xmlns:wp="http://wordpress.org/export/<?php echo YITH_WXR_VERSION; ?>/">

                <channel>
                    <title><?php bloginfo_rss( 'name' ); ?></title>
                    <link><?php bloginfo_rss( 'url' ); ?></link>
                    <description><?php bloginfo_rss( 'description' ); ?></description>
                    <pubDate><?php echo date( 'D, d M Y H:i:s +0000' ); ?></pubDate>
                    <language><?php bloginfo_rss( 'language' ); ?></language>
                    <wp:wxr_version><?php echo YITH_WXR_VERSION; ?></wp:wxr_version>

                    <?php $this->wxr_authors_list( $ids ); ?>

                    <?php foreach ( $attributes as $a ) : ?>
                        <wp:attribute>
                            <wp:attribute_id><?php echo $a->attribute_id ?></wp:attribute_id>
                            <wp:attribute_name><?php echo $a->attribute_name ?></wp:attribute_name>
                            <wp:attribute_label><?php echo $a->attribute_label ?></wp:attribute_label>
                            <wp:attribute_type><?php echo $a->attribute_type ?></wp:attribute_type>
                            <wp:attribute_orderby><?php echo $a->attribute_orderby ?></wp:attribute_orderby>
                            <wp:attribute_public><?php echo $a->attribute_public ?></wp:attribute_public>
                        </wp:attribute>
                    <?php endforeach; ?>

                    <?php foreach ( $terms as $t ) : ?>
                        <wp:term>
                            <wp:term_id><?php echo $t->term_id ?></wp:term_id>
                            <wp:term_taxonomy><?php echo $t->taxonomy; ?></wp:term_taxonomy>
                            <wp:term_slug><?php echo $t->slug; ?></wp:term_slug>
                            <wp:term_parent><?php echo $t->parent ? $terms[ $t->parent ]->slug : ''; ?></wp:term_parent><?php $this->wxr_term_name( $t ); ?><?php $this->wxr_term_description( $t ); ?>
                        </wp:term>
                    <?php endforeach; ?>

                    <?php
                    if ( $ids ) {
                        foreach ( $ids as $id ) {
                            $post    = get_post( $id );
                            $product = wc_get_product( $id );

                            // Save Thumbnail and image gallery id
                            $images = array();

                            $thumb = get_post_thumbnail_id( $id );
                            if ( !empty( $thumb ) ) {
                                $images[] = $thumb;
                            }
                            $image_gallery = $product instanceof WC_Data ? $product->get_gallery_image_ids() : $product->get_gallery_attachment_ids();
                            $images        = array_merge( $images, $image_gallery );
                            if ( !empty( $images ) ) {
                                foreach ( $images as $i_id ) {
                                    if ( !isset( $this->all_images[ $i_id ] ) ) {
                                        $i_post                    = get_post( $i_id );
                                        $image                     = array(
                                            'id'    => $i_id,
                                            'src'   => $i_post->guid,
                                            'title' => $i_post->post_title,
                                            'alt'   => get_post_meta( $i_id, '_wp_attachment_image_alt', true )
                                        );
                                        $this->all_images[ $i_id ] = $image;
                                    }
                                }
                            }

                            if ( !$post || !$product || $product->is_type( 'variation' ) )
                                continue;

                            $this->render_post( $post, $product );

                            if ( $product->is_type( 'variable' ) && $product->has_child() ) {
                                $children = $product->get_children();
                                if ( !empty( $children ) ) {
                                    foreach ( $children as $child_id ) {
                                        $c_post = get_post( $child_id );
                                        $c_prod = wc_get_product( $child_id );

                                        if ( !$c_prod )
                                            continue;

                                        // SAVE Images for variations
                                        $thumb = get_post_thumbnail_id( $child_id );
                                        if ( !empty( $thumb ) && !isset( $this->all_images[ $thumb ] ) ) {
                                            $i_post                     = get_post( $thumb );
                                            $image                      = array(
                                                'id'    => $thumb,
                                                'src'   => $i_post->guid,
                                                'title' => $i_post->post_title,
                                                'alt'   => get_post_meta( $thumb, '_wp_attachment_image_alt', true )
                                            );
                                            $this->all_images[ $thumb ] = $image;
                                        }

                                        $this->render_post( $c_post, $c_prod );
                                    }
                                }
                            }
                            ?>
                            <?php
                        }
                    }

                    // Images
                    if ( !empty( $this->all_images ) ) {
                        foreach ( $this->all_images as $id => $i ) {
                            $i_post = get_post( $id );
                            $this->render_post( $i_post, null );
                        }
                    }
                    ?>

                </channel>
            </rss>
            <?php
        }
    }
}
?>