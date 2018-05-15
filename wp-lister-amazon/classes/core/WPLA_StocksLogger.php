<?php

if ( ! class_exists('WPLA_StocksLogger') ) :

    class WPLA_StocksLogger {

        public function __construct() {

            // listens to update_post_meta calls
            add_action( 'update_postmeta', array( $this, 'logStockActivity' ), 1, 4 );

            // woocommerce product set_stock methods
            add_action( 'woocommerce_product_set_stock', array( $this, 'logProductStockUpdate' ) );
            add_action( 'woocommerce_variation_set_stock', array( $this, 'logProductStockUpdate' ) );
        }

        /**
         * Log all activities for the '_stock' meta key
         *
         * @param int       $meta_id
         * @param int       $object_id
         * @param string    $meta_key
         * @param mixed     $meta_value
         */
        public function logStockActivity( $meta_id, $object_id, $meta_key, $meta_value ) {
            if ( $meta_key != '_stock' ) {
                return;
            }

            $backtrace               = $this->getFormattedBacktrace();
            list( $caller, $method ) = $this->getCaller();

            $old_stock  = get_post_meta( $object_id, '_stock', true );
            $new_stock  = $meta_value;

            if ( $old_stock == $new_stock ) {
                return;
            }

            // built log record
            $data = array();
            $data['sku']        = get_post_meta( $object_id, '_sku', true );
            $data['old_stock']  = $old_stock;
            $data['new_stock']  = $new_stock;
            $data['product_id'] = $object_id;
            $data['caller']     = $caller;
            $data['method']     = $method;
            $data['backtrace']  = WPLA_DEBUG > 5 ? $backtrace : '';
            $this->insertLogRecord( $data );

            $stock_message = sprintf( "Stock updated from %d to %d for product_id: %d (%s via %s)\n", $old_stock, $new_stock, $object_id, $caller, $method );
            WPLA()->logger->notice( $stock_message );
        }

        /**
         * Log stock updates via WC's WC_Product::set_stock() method
         *
         * @param WC_Product|WC_Product_Variation $product
         */
        public function logProductStockUpdate( $product ) {
            $backtrace               = $this->getFormattedBacktrace();
            list( $caller, $method ) = $this->getCaller();

            if ( is_callable( array( $product, 'get_id' ) ) ) {
                $product_id = $product->get_id();
            } else {
                $product_id = empty( $product->variation_id ) ? $product->id : $product->variation_id;
            }

            // built log record
            $data = array();
            $data['sku']        = $product->get_sku();
            $data['new_stock']  = $product->get_stock_quantity();
            $data['product_id'] = $product_id;
            $data['caller']     = $caller;
            $data['method']     = $method;
            $data['backtrace']  = WPLA_DEBUG > 5 ? $backtrace : '';
            $this->insertLogRecord( $data );

            // In WC 3.0, product->variation_id is now the product->id
            if ( version_compare( WC_VERSION, '3.0', '>=' ) ) {
                $stock_message = sprintf( "New stock quantity: %d for product_id: %d (%s via %s)\n", $product->get_stock_quantity(), $product_id, $caller, $method );
            } else {
                if ( !empty( $product->variation_id ) ) {
                    $stock_message = sprintf( "New stock quantity: %d for product_id: %d (%s via %s)\n", $product->get_stock_quantity(), $product->variation_id, $caller, $method );
                } else {
                    $stock_message = sprintf( "New stock quantity: %d for product_id: %d (%s via %s)\n", $product->get_stock_quantity(), $product->id, $caller, $method );
                }
            }

            WPLA()->logger->notice( $stock_message );
        }

        /**
         * Return the backtrace as a formatted string
         * @return string
         */
        protected function getFormattedBacktrace() {
            $trace      = debug_backtrace();
            // $formatted  = str_repeat( '-', 50 ) . "\n";
            $formatted  = '';

            // shift the first two elements to remove the calls to this method and class
            array_shift( $trace );
            array_shift( $trace );

            $loop = 1;
            foreach ( $trace as $call ) {
                if ( !isset( $call['file'] ) ) {
                    $call['file'] = '';
                }

                if ( !isset( $call['line'] ) ) {
                    $call['line'] = '';
                }
                
                $converted_args = array();
                foreach ( $call['args'] as $arg ) {
                    if ( is_object( $arg ) ) {
                        $converted_args[] = get_class( $arg ) . ' object';
                    } else if ( is_array( $arg ) ) {
                        $converted_args[] = 'Array';
                    } else if ( is_scalar( $arg ) ) {
                        $converted_args[] = $arg;
                    }
                }

                if ( !empty( $call['class'] ) ) {
                    $formatted .= sprintf( "%d. %s%s%s(%s) in %s:%d\n", $loop, $call['class'], $call['type'], $call['function'], join( ',', $converted_args ), $call['file'], $call['line'] );
                } else {
                    $formatted .= sprintf( "%d. %s(%s) in %s:%d\n", $loop, $call['function'], join( ',', $converted_args ), $call['file'], $call['line'] );
                }
                $loop++;
            }

            // $formatted  .= str_repeat( '-', 50 );

            // make file path relative
            $formatted = str_replace( ABSPATH, '/', $formatted );
            $formatted = str_replace( '/wp-content/plugins/', '/', $formatted );

            return $formatted;
        }

        /**
         * Attempt to figure out the name of the plugin that updated the stock
         * @return string
         */
        protected function getCaller() {
            $method = '';
            $caller = '';
            $trace  = debug_backtrace();
            $trace  = array_slice( $trace, 3 );

            foreach ( $trace as $item ) {
                try {
                    if ( $this->isWCProductUpdate( $item ) ) {
                        if ( isset( $item['class'] ) ) {
                            // WC 2.6
                            // if the update came from a WC object, retrieve and log the calling plugin too
                            $method = $item['class'] .'::'. $item['function'] .'()';

                            $item = next( $trace );
                        } else {
                            $method = $item['function'] .'()';

                            next( $trace );
                            next ( $trace );
                            $item = current( $trace );
                        }

                        if ( $item['file'] ) {
                            $caller = $this->getPluginFromFile( $item['file'] );
                        } else {
                            $caller = $method;
                            $method = $item['class'] .'::'. $item['function'] .'()';
                        }

                        // append information about class and method that triggered WC_Product::set_stock() 
                        // $item = next( $trace ); // WC_Product->reduce_stock(1)
                        // $item = next( $trace ); // WPLA_ProductWrapper::decreaseStockBy(12345,1,100-1234567-456789)
                        if ( isset( $item['class'] ) ) {
                            $method .= ' via ' . $item['class'] .'::'. $item['function'] .'()';
                        } else {
                            $method .= ' via ' . $item['function'] .'()';
                        }
                        $item = next( $trace ); // WPLA_OrdersImporter->processListingItem()
                        if ( isset( $item['class'] ) ) {
                            $method .= ' from ' . $item['class'] .'::'. $item['function'] .'()';
                        } else {
                            $method .= ' from ' . $item['function'] .'()';
                        }

                        break;
                    } elseif ( $this->isPostMetaUpdate( $item ) ) {

                        $item = next( $trace );

                        if ( isset( $item['class'] ) ) {
                            $method = $item['class'] .'::'. $item['function'] .'()';
                        } else {
                            $method = $item['function'] .'() in '. $item['file'] .':'. $item['line'];
                        }

                        if ( $item['file'] ) {
                            $caller = $this->getPluginFromFile( $item['file'] );
                        } else {
                            $caller = $method;
                            $method = $item['class'] .'::'. $item['function'] .'()';
                        }

                        // append information about class and method that triggered update_post_meta() 
                        $item = next( $trace ); // WPLA_ImportHelper::processFBAReportPage()
                        if ( isset( $item['class'] ) ) {
                            $method .= ' from ' . $item['class'] .'::'. $item['function'] .'()';
                        } else {
                            $method .= ' from ' . $item['function'] .'()';
                        }

                        break;
                    }
                } catch ( ReflectionException $e ) {
                    # nothing
                } catch ( Exception $e ) {

                }
            }

            if ( $caller && $method ) {
                $method = str_replace( ABSPATH, '/', $method );
                $method = str_replace( '/wp-content/plugins/', '/', $method );
                return array( $caller, $method );
            }

            return array();
        }

        /**
         * Attempt to get the plugin or theme from the given file path
         *
         * @param string $file
         * @return string
         */
        protected function getPluginFromFile( $file ) {
            $file   = $this->standardDir( $file );
            $dirs   = $this->getFileDirs();

            foreach ( $dirs as $type => $dir ) {
                if ( $dir && ( 0 === strpos( $file, $dir ) ) ) {
                    break;
                }
            }

            switch ( $type ) {
                case 'plugin':
                case 'mu-plugin':
                    $plug = plugin_basename( $file );
                    if ( strpos( $plug, '/' ) ) {
                        $plug = explode( '/', $plug );
                        $plug = reset( $plug );
                    } else {
                        $plug = basename( $plug );
                    }
                    if ( 'mu-plugin' === $type ) {
                        $name = sprintf( 'MU Plugin: %s', $plug );
                    } else {
                        $name = sprintf( 'Plugin: %s', $plug );
                    }
                    break;
                case 'go-plugin':
                case 'vip-plugin':
                    $plug = str_replace( $dirs[ $type ], '', $file );
                    $plug = trim( $plug, '/' );
                    if ( strpos( $plug, '/' ) ) {
                        $plug = explode( '/', $plug );
                        $plug = reset( $plug );
                    } else {
                        $plug = basename( $plug );
                    }
                    $name    = sprintf( 'VIP Plugin: %s', $plug );
                    break;
                case 'stylesheet':
                    if ( is_child_theme() ) {
                        $name = 'Child Theme';
                    } else {
                        $name = 'Theme';
                    }
                    break;
                case 'template':
                    $name = 'Parent Theme';
                    break;
                case 'other':
                    $name    = $this->standardDir( $file, '' );
                    break;
                case 'core':
                    $name = 'Core';
                    break;
                case 'unknown':
                default:
                    $name = 'Unknown';
                    break;
            }

            return $name;
        }

        /**
         * Get all plugin and theme directories
         * @return array
         */
        protected function getFileDirs() {
            $file_dirs = array();

            $file_dirs['plugin']     = $this->standardDir( WP_PLUGIN_DIR );
            $file_dirs['go-plugin']  = $this->standardDir( WPMU_PLUGIN_DIR . '/shared-plugins' );
            $file_dirs['mu-plugin']  = $this->standardDir( WPMU_PLUGIN_DIR );
            $file_dirs['vip-plugin'] = $this->standardDir( get_theme_root() . '/vip/plugins' );
            $file_dirs['stylesheet'] = $this->standardDir( get_stylesheet_directory() );
            $file_dirs['template']   = $this->standardDir( get_template_directory() );
            $file_dirs['other']      = $this->standardDir( WP_CONTENT_DIR );
            $file_dirs['core']       = $this->standardDir( ABSPATH );
            $file_dirs['unknown']    = null;

            return $file_dirs;
        }

        /**
         * @param $dir
         * @param null $abspath_replace
         *
         * @return mixed|string
         */
        protected function standardDir( $dir, $abspath_replace = null ) {
            $dir = wp_normalize_path( $dir );

            if ( is_string( $abspath_replace ) ) {
                $dir = str_replace( wp_normalize_path( ABSPATH ), $abspath_replace, $dir );
            }

            return $dir;

        }

        /**
         * Returns true if the stack trace item is from WC_Product or WC_Product_Variation
         *
         * @param array $item
         * @return bool
         */
        private function isWCProductUpdate( $item ) {
            $value = false;
            if ( isset( $item['class'] ) && ( $item['class'] == 'WC_Product' || $item['class'] == 'WC_Product_Variation' ) ) {
                $value = true;
            }

            // Support for WC3.0
            if ( isset( $item['function'] ) && $item['function'] == 'wc_update_product_stock' ) {
                $value = true;
            }

            return $value;
        }

        /**
         * Returns true if the stack trace item is from an 'update_postmeta' hook
         *
         * @param array $item
         * @return bool
         */
        private function isPostMetaUpdate( $item ) {
            $value = false;

            if (
                $item['function'] == 'do_action' &&
                ( isset( $item['args'][0] ) && $item['args'][0] == 'update_postmeta' ) &&
                ( isset( $item['args'][3] ) && $item['args'][3] == '_stock' )
            ) {
                $value = true;
            }

            return $value;
        }

        /**
         * Store a new log record
         *
         * @param array $data
         */
        private function insertLogRecord( $data ) {
            global $wpdb;

            // set current user id and time stamp
            $user              = wp_get_current_user();
            $data['user_id']   = ( defined('DOING_CRON') && DOING_CRON ) ? 0 : $user->ID;
            $data['timestamp'] = gmdate( 'Y-m-d H:i:s' );

            // truncate fields if too long for sql
            if ( isset( $data['sku']    ) && strlen( $data['sku']    ) >  32 ) $data['sku']    = substr( $data['sku'],    0,  32 );
            if ( isset( $data['caller'] ) && strlen( $data['caller'] ) >  64 ) $data['caller'] = substr( $data['caller'], 0,  64 );
            if ( isset( $data['method'] ) && strlen( $data['method'] ) > 128 ) $data['method'] = substr( $data['method'], 0, 128 );
            if ( isset( $data['callback'] ) && strlen( $data['callback'] ) > 1000 ) $data['callback'] = substr( $data['callback'], 0, 1000 ); // truncate to avoid connectivity issues with mysql when sending a very large sql
            if ( isset( $data['backtrace'] ) && strlen( $data['backtrace'] ) > 3000 ) $data['backtrace'] = substr( $data['backtrace'], 0, 3000 ); // truncate to avoid connectivity issues with mysql when sending a very large sql

            // insert into db
            $wpdb->insert( $wpdb->prefix.'amazon_stock_log', $data );
            if ( $wpdb->last_error ) WPLA()->logger->error( 'Error in WPLA_StocksLogger::insertLogRecord(): '.$wpdb->last_error.' - SQL: '.$wpdb->last_query );

        } // insertLogRecord()

    } // class WPLA_StocksLogger

endif;