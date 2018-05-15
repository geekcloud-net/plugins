<?php
/**
 * Class MonsterInsights_Frontend_Custom_Dimensions.
 *
 * Outputs the custom dimensions.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MonsterInsights_Frontend_Custom_Dimensions {

    public function __construct() {
        add_filter( 'monsterinsights_frontend_tracking_options_analytics_before_pageview', array( $this, 'output_custom_dimensions' ) );
    }

    public function output_custom_dimensions( $options ) {
        $dimensions = monsterinsights_get_option( 'custom_dimensions', array() );
        if ( ! empty( $dimensions ) && is_array( $dimensions ) ) {
            // Sort by array key `id` value
            $id = array();
            foreach ( $dimensions as $key => $row ) {
                if  ( empty( $row['type'] ) || empty( $row['id'] ) ) {
                    unset( $dimensions[$key] );
                    continue;
                }
                $id[ $key ] = $row['id'];
            }
            array_multisort( $id, SORT_ASC, $dimensions );

            foreach( $dimensions as $dimension ) {
                if  ( empty( $dimension['type'] ) || empty( $dimension['id'] ) ) {
                    continue;
                }
                $type  = $dimension['type'];
                $id    = $dimension['id'];
                $value = '';
                switch ( $type ) {
                    case 'logged_in':
                        $value = $this->get_logged_in_dimension();
                        break;
                    case 'user_id':
                        $value = $this->get_user_id_dimension();
                        break;
                    case 'post_type':
                        $value = $this->get_post_type_dimension();
                        break;
                    case 'author':
                        $value = $this->get_author_dimension();
                        break;
                    case 'category':
                        $value = $this->get_category_dimension();
                        break;
                    case 'tags':
                        $value = $this->get_tags_dimension();
                        break;
                    case 'published_at':
                        $value = $this->get_published_at_dimension();
                        break;
                    case 'focus_keyword':
                        $value = $this->get_focus_keyword_dimension();
                        break;
                    case 'seo_score':
                        $value = $this->get_seo_score_dimension();
                        break;
                    case 'inactive':
                    default :
                        // don't do anything
                        break;
                }
                if ( ! empty( $value ) ) {
                    $options[ 'dimension' . $id ] = $this->get_dimension_output( $id, $value );
                }
            }
        }

        return $options;
    }

    protected function get_dimension_output( $id, $value ) {
        return "'set', 'dimension" . absint( $id ) . "', '" . esc_js( addslashes( $value ) ) . "'";

    }

    protected function get_logged_in_dimension() {
        $value = var_export( is_user_logged_in(), true );
        return $value;
    }

    protected function get_user_id_dimension() {
        $value = is_user_logged_in() ? get_current_user_id() : 0;
        return $value;
    }

    protected function get_post_type_dimension() {
        $post_type = '';
        if ( is_singular() ) {
            $post_type = get_post_type( get_the_ID() );
        }
        return $post_type;
    }

    protected function get_author_dimension() {
        $value = '';
        if ( is_singular() ) {
            if ( have_posts() ) {
                while ( have_posts() ) {
                    the_post();
                }
            }

            $firstname = get_the_author_meta( 'user_firstname' );
            $lastname  = get_the_author_meta( 'user_lastname' );

            if ( ! empty( $firstname ) || ! empty( $lastname ) ) {
                $value = trim( $firstname . ' ' . $lastname );
            } else {
                $value = 'user-' . get_the_author_meta( 'ID' );
            }
        }
        return $value;
    }

    protected function get_category_dimension() {
        $value = '';
        if ( is_single() ) {
            $categories = get_the_category( get_the_ID() );

            if ( $categories ) {
                foreach ( $categories as $category ) {
                    $category_names[] = $category->slug;
                }

                $value =  implode( ',', $category_names );
            }
        }
        return $value;
    }

    protected function get_tags_dimension() {
        $tag_names = '';
        if ( is_single() ) {
            $tag_names = 'untagged';
            $tags = get_the_tags( get_the_ID() );
            if ( $tags ) {
                $tag_names = implode( ',', wp_list_pluck( $tags, 'name' ) );
            }
        }
        return $tag_names;
    }

    protected function get_published_at_dimension( ) {
        $date = '';
        if ( is_singular() ) {
            $date = get_the_date( 'c' );
        }
        return $date;
    }

    protected function get_focus_keyword_dimension() {
        // Make sure WP SEO or WP SEO Premium is active and if a singular post is displayed
        $focus_keyword = '';
        if ( monsterinsights_is_wp_seo_active() && is_singular() ) {
            $focus_keyword = get_post_meta( get_the_ID(), '_yoast_wpseo_focuskw', true );

            if ( empty( $focus_keyword ) ) {
                /* translators: Default value shown in Google Analytics when no focus keyword has been set. Use underscores to differentiate from normal focus keywords. */
                $focus_keyword = esc_html__( 'focus_keyword_not_set', 'ga-premium' );
            }
        }
        return $focus_keyword;
    }

    /**
     * Handle the SEO scores in custom dimensions
     */
    protected function get_seo_score_dimension() {
        // Make sure WP SEO or WP SEO Premium is active and if a singular post is displayed
        $score_label = '';
        if ( monsterinsights_is_wp_seo_active() && is_singular() ) {
            $score_label = $this->get_wp_seo_score( get_the_ID() );
        }
        return $score_label;
    }

    /**
     * Get SEO score for post from WordPress SEO Plugin
     *
     * @param int $post_id
     *
     * @return string
     */
    protected function get_wp_seo_score( $post_id ) {
        // Get seo score from WordPress SEO
        $score = WPSEO_Metabox::get_value( 'linkdex', $post_id );
        if ( $score !== '' ) {
            return $this->wpseo_translate_score( $score );
        }

        return 'na';
    }

    /**
     * wpseo_translate_score has been deprecated in newer versions of wordpress-seo
     *
     * @param int $score
     *
     * @return mixed
     */
    protected function wpseo_translate_score( $score ) {
        if ( method_exists( 'WPSEO_Utils', 'translate_score' ) ) {
            return WPSEO_Utils::translate_score( $score );
        }
        return wpseo_translate_score( $score );
    }
}