<?php

/**
 * Description of WPEAE_Log
 *
 * @author Geometrix
 */
if (!class_exists('WPEAE_Log')):

    class WPEAE_Log {

        private $module;
        
        private $log_max_records = 10000;
        
        function __construct($module) {
            $this->module = $module;        
        }
        
        public function load($start_id=0, $type="message"){
            /** @var wpdb $wpdb */
            global $wpdb;
            
            if (!is_array($type)) $type = array($type);
            foreach($type as $key => $val) {
                $type[$key] = "'" . $wpdb->_real_escape($val) . "'";
            }    
            
             $type_sql = implode(',', $type);
            
            $results_count = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->prefix . WPEAE_TABLE_LOG . " WHERE `module` = '{$this->module}' AND `type` IN ({$type_sql}) AND `id` > {$start_id}" );
            
            if ($results_count >= $this->log_max_records) {
                
                wpeae_arvi_clear_log();
                
                $this->add("Log Records count limit (" . $this->log_max_records . ') has been exceeded. The log was cleared automatically.');
             
            } 
                               
            $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . WPEAE_TABLE_LOG . " WHERE `module` = '{$this->module}' AND `type` IN ({$type_sql}) AND `id` > {$start_id}");    

        
            return  $results;
        }
        
        public function add($text, $type="message"){
            /** @var wpdb $wpdb */
            global $wpdb;
            $wpdb->insert($wpdb->prefix . WPEAE_TABLE_LOG, array( 'text' => $text, 'type' => $type, 'module' => $this->module, 'time' => date("Y-m-d H:i:s", time()) ));
        }

        public function clear(){
            /** @var wpdb $wpdb */
            global $wpdb;
            $wpdb->delete($wpdb->prefix . WPEAE_TABLE_LOG, array( 'module' => $this->module )); 
               
        }
        
    }

    endif;
