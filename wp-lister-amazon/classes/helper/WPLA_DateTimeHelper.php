<?php

class WPLA_DateTimeHelper {
	

    static public function getLocalTimeZone() {

        // get the local timezone from WP
        $tz = get_option('timezone_string');
        if ( ! $tz ) $tz = wc_timezone_string(); // 'Europe/London'

        return $tz;
    }

    // not used (yet)
    static public function getCurrentLocalTime( $format = 'H:i' ) {

        // create the DateTimeZone object using local timezone from WP
        $dtime = new DateTime( 'now', new DateTimeZone( self::getLocalTimeZone() ) );

        // return the time using the preferred format
        $time = $dtime->format( $format );

        return $time;
    }

    static public function convertTimestampToLocalTime( $timestamp ) {

        // set this to the time zone provided by the user
        // $tz = get_option('wpla_local_timezone');
        $tz = get_option('timezone_string');
        if ( ! $tz ) $tz = wc_timezone_string(); // 'Europe/London'
         
        // create the DateTimeZone object for later
        $dtzone = new DateTimeZone($tz);
         
        // first convert the timestamp into a string representing the local time
        $time = date('r', $timestamp);
         
        // now create the DateTime object for this time
        $dtime = new DateTime($time);
         
        // convert this to the user's timezone using the DateTimeZone object
        $dtime->setTimeZone($dtzone);
         
        // print the time using your preferred format
        // $time = $dtime->format('g:i A m/d/y');
        $time = $dtime->format('Y-m-d H:i:s'); // SQL date format

        return $time;
    }

    static public function convertLocalTimeToTimestamp( $time ) {

        // time to convert (just an example)
        // $time = 'Tuesday, April 21, 2009 2:32:46 PM';
         
        // set this to the time zone provided by the user
        // $tz = get_option('wpla_local_timezone');
        $tz = get_option('timezone_string');
        if ( ! $tz ) $tz = wc_timezone_string(); // 'Europe/London'
         
        // create the DateTimeZone object for later
        $dtzone = new DateTimeZone($tz);
         
        // now create the DateTime object for this time and user time zone
        $dtime = new DateTime($time, $dtzone);
         
        // print the timestamp
        $timestamp = $dtime->format('U');

        return $timestamp;
    }


} // class WPLA_DateTimeHelper
