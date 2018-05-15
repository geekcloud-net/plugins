<?php

/**
 * Class WC_POS_Cache for working with POS cache
 */
class WC_POS_Cache
{
    private $source;

    public function __construct()
    {
        $this->initHooks();
    }

    public function setSource(CacheSource $source)
    {
        $this->source = $source;
    }

    protected function initHooks()
    {
        add_action('admin_init', array($this, 'cachePosData'));
        //add_action('woocommerce_api_check_authentication', array($this, 'authenticate'), 100);
    }

    public function authenticate($obj)
    {
        include_once(WC_POS()->plugin_path() . '/includes/api/class-wc-pos-api.php');
        new WC_Pos_API();
        $user = new WP_User($_GET['user_id']);
        return $user;
    }

    public function cachePosData()
    {
        $user_id = get_current_user_id();
        if (isset($_GET['action']) && $_GET['action'] == 'wc_pos-cache-pos-data') {
            if (!isset($_GET['wp-nonce']) || empty($_GET['wp-nonce']) || !wp_verify_nonce($_GET['wp-nonce'], 'cache_data')) {
                return false;
            }
            $products_json = array();
            //Find total products count
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => home_url() . '/wc-api/v3/products/count?user_id=' . $user_id,
            ]);
            $res_count = curl_exec($curl);
            curl_close($curl);
            $res_count = json_decode($res_count);
            $limit = 100;
            for ($offset = 0; $offset < $res_count->count; $offset = $offset + $limit) {
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_URL => home_url() . '/wc-api/v3/products?user_id=' . $user_id . '&filter[limit]=' . $limit . '&filter[offset]=' . $offset,
                ]);
                $res = curl_exec($curl);
                curl_close($curl);
                $products = json_decode($res);
                $products_json = array_merge($products_json, $products->products);
            }

            $products_json = json_encode($products_json);

            if ($products_json) {
                $this->insert_data('products', $products_json);
            }
        }
    }

    public function insert_data($key, $data)
    {
        global $wpdb;
        $sql = $wpdb->prepare("UPDATE {$wpdb->prefix}wc_poin_of_sale_cache SET `data` = %s WHERE `key` = %s", $data, $key);
        $wpdb->query($sql);
    }

    public function get_data($key)
    {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT `data` FROM {$wpdb->prefix}wc_poin_of_sale_cache WHERE `key` = %s", $key);
        $result = $wpdb->get_var($sql);
        return $result;
    }
}

return new WC_POS_Cache();