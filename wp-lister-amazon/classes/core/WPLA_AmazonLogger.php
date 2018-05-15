<?php

class WPLA_AmazonLogger {

    // debugging options
    protected $debugXmlBeautify = true;
    protected $debugLogDestination = 'db';
    protected $currentUserID = 0;
    protected $callname = '';
    protected $success = false;
    protected $id = 0;
    protected $enabled = null;
	
    function __construct( $beautfyXml = false, $destination = 'db' )
    {
        global $wpdb;

        $this->debugXmlBeautify    = $beautfyXml;
        $this->debugLogDestination = $destination;
        $this->enabled             = get_option('wpla_log_to_db');

        // do nothing if logging is disabled
        if ( ! $this->enabled ) return;

        // get current user id
        $user = wp_get_current_user();
        $this->currentUserID = $user->ID;

        // insert row into db
        $data = array();
        $data['timestamp'] = gmdate( 'Y-m-d H:i:s' );
        $data['user_id']   = ( defined('DOING_CRON') && DOING_CRON ) ? 'wp_cron' : $this->currentUserID;
        $wpdb->insert( $wpdb->prefix.'amazon_log', $data);

        if ( $wpdb->last_error ) echo 'Error in WPLA_AmazonLogger::__construct: '.$wpdb->last_error.'<br>'.$wpdb->last_query;
        $this->id = $wpdb->insert_id;

    }
    
    function updateLog( $data )
    {
        global $wpdb;

        // do nothing if logging is disabled
        if ( ! $this->enabled ) return;

        // truncate response if too long for sql
        if ( isset( $data['response'] ) ) {
            $msg = $data['response'];
            if ( strlen( $msg ) > 65000 ) {
                $limit = get_option( 'wplister_log_record_limit', 4096 );
                $msg   = substr($msg, 0, $limit ) . "\n\n-- result was bigger than 64k - truncated to $limit bytes";                
            }
            $data['response'] = $msg;           
        }

        if ($this->debugLogDestination == 'db') {
            
            // insert into db
            $wpdb->update($wpdb->prefix.'amazon_log', $data, array( 'id' => $this->id ));
            if ( $wpdb->last_error ) echo 'Error in WPLA_AmazonLogger::log() - subject '.$subject.' - '.$wpdb->last_error.'<br>'.$wpdb->last_query;

        }

    } // updateLog()

    
    function log($msg, $subject = null)
    {
        // do nothing if logging is disabled
        if ( ! $this->enabled ) return;

        $data = array();

        // extract Ack status from response
        if ( $subject == 'Response' ) {
            if ( preg_match("/<Ack>(.*)<\/Ack>/", $msg, $matches) ) {
                $this->success = $matches[1];
                $data['success'] = $this->success;
            } elseif ( preg_match("/<ErrorCode>(.*)<\/ErrorCode>/", $msg, $matches) ) {
                $this->success = 'Error '.$matches[1];
                $data['success'] = $this->success;
            }
        }

        // extract call name from request url
        if ( $subject == 'RequestUrl' ) {
            if ( preg_match("/callname=(.*)&/U", $msg, $matches) ) {
                $this->callname = $matches[1];
                $data['callname'] = $this->callname;
            }
        }

        $this->updateLog( $data );

    } // log()
    
    function logXml($xmlMsg, $subject = null)
    {              
        $this->log($xmlMsg, $subject);
    }

} // class WPLA_AmazonLogger

