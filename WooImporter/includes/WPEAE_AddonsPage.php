<?php

/**
 * Description of WPEAE_AddonsPage
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_AddonsPage')):

    class WPEAE_AddonsPage {

        public $addons;

        function __construct() {
            $this->addons = get_option('wpeae_addons_json_content', array());

            if (!isset($this->addons['last_update']) || ($this->addons['last_update'] + 60 * 60 * 12) < time()) {
                $this->update_addons_json();
            }
        }

        private function update_addons_json() {
            $this->addons = array();
            $response = wp_remote_get("http://gmetrixteam.com/addons/get.php");
            if (!is_wp_error($response)) {
                $body = json_decode($response['body'], true);

                $this->addons['addons'] = $body;
                $this->addons['last_update'] = time();

                update_option('wpeae_addons_json_content', $this->addons);
            }
        }

        public function get_new_addons_count() {
            $viewed_addons = explode(',', get_option('wpeae_viewed_addons', ''));
            $viewed_cnt = 0;
            $addons = isset($this->addons['addons']) ? $this->addons['addons'] : array();
            foreach ($addons as $addon) {
                if (in_array($addon['type'] . '-' . $addon['type_name'], $viewed_addons)) {
                    $viewed_cnt++;
                }
            }
            return count($addons) - $viewed_cnt;
        }

        private function set_viewed_addons() {
            $viewed_addons = explode(',', get_option('wpeae_viewed_addons', ''));
            foreach ($this->addons['addons'] as $addon) {
                if (!in_array($addon['type'] . '-' . $addon['type_name'], $viewed_addons)) {
                    $viewed_addons[] = $addon['type'] . '-' . $addon['type_name'];
                }
            }
            update_option('wpeae_viewed_addons', implode(',', $viewed_addons));
        }

        private function sort_addons() {
            $viewed_addons = explode(',', get_option('wpeae_viewed_addons', ''));
            $new_addons = array();
            $addons = array();
            foreach ($this->addons['addons'] as $addon) {
                if (!in_array($addon['type'] . '-' . $addon['type_name'], $viewed_addons)) {
                    $new_addons[] = $addon;
                } else {
                    $addons[] = $addon;
                }
            }
            $this->addons['addons'] = array_merge($new_addons, $addons);
        }

        function render() {
            $this->sort_addons();
            $this->set_viewed_addons();
            include(WPEAE_ROOT_PATH . '/view/addons.php' );
        }

    }

    

       

endif;