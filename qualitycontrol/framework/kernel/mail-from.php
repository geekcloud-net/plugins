<?php
/**
 * Modify WP email sender name and email address,
 * Defaults to admin email and blog name.
 *
 * @package Framework\Mail-From
 */

class APP_Mail_From {

	private static $args = array();
	private static $tmp = array();

	/**
	 * Initialize custom email headers
	 *
	 * @param array $args
	 * - 'email' an sender email address
	 * - 'name' an sender name
	 * - 'reply' should the 'Reply-To' header be sent
	 */
	static function init( $args = array() ) {

		self::$args = wp_parse_args( $args, self::get_defaults() );
		self::add();
	}

	/**
	 * Changes custom email header, applies just once
	 *
	 * @param array $args
	 * - 'email' an sender email address
	 * - 'name' an sender name
	 * - 'reply' should the 'Reply-To' header be sent
	 */
	static function apply_once( $args = array() ) {
		self::$tmp = ( ! empty( self::$args ) ) ? self::$args : false;
		self::init( $args );
		add_action( 'phpmailer_init', array( __CLASS__, '_reset' ) );
	}

	/**
	 * Disables custom email header, applies just once
	 */
	static function disable_once() {
		self::remove();
		add_action( 'phpmailer_init', array( __CLASS__, '_reset' ) );
	}

	/**
	 * Adds filters on wp_mail()
	 */
	static function add() {
		if ( ! self::_is_valid() )
			return;

		add_filter( 'wp_mail', array( __CLASS__, 'mail_reply' ) );
		add_filter( 'wp_mail_from', array( __CLASS__, 'mail_from' ) );
		add_filter( 'wp_mail_from_name', array( __CLASS__, 'mail_from_name' ) );
	}

	/**
	 * Removes filters from wp_mail()
	 */
	static function remove() {
		remove_filter( 'wp_mail', array( __CLASS__, 'mail_reply' ) );
		remove_filter( 'wp_mail_from', array( __CLASS__, 'mail_from' ) );
		remove_filter( 'wp_mail_from_name', array( __CLASS__, 'mail_from_name' ) );
	}

	/**
	 * Reverts headers to previous state after use of apply_once() & disable_once()
	 */
	static function _reset() {
		if ( ! empty( self::$tmp ) ) {
			self::$args = self::$tmp;
			self::$tmp = array();
		} elseif ( self::$tmp === false ) {
			self::$args = array();
			self::$tmp = array();
			self::remove();
		} else {
			self::add();
		}
		remove_action( 'phpmailer_init', array( __CLASS__, '_reset' ) );
	}

	/**
	 * Checks if args are valid and exists
	 *
	 * @return bool
	 */
	static function _is_valid() {
		$args = array( 'name', 'email', 'reply' );
		foreach ( $args as $arg ) {
			if ( ! isset( self::$args[ $arg ] ) )
				return false;
		}

		if ( ! is_email( self::$args['email'] ) )
			return false;

		if ( empty( self::$args['name'] ) )
			return false;

		return true;
	}

	/**
	 * Adds 'Reply-To' header, applies on 'wp_mail' hook
	 *
	 * @param array $mail
	 * @return array
	 */
	static function mail_reply( $mail ) {
		if ( ! self::_is_valid() )
			return $mail;

		if ( ! self::$args['reply'] )
			return $mail;

		$replyto = sprintf( "Reply-To: %s <%s> \r\n", self::$args['name'], self::$args['email'] );
		if ( is_array( $mail['headers'] ) )
			$mail['headers'][] = $replyto;
		else
			$mail['headers'] .= $replyto;

		return $mail;
	}

	/**
	 * Returns sender email, applies on 'wp_mail_from' hook
	 *
	 * @param string $from_email
	 * @return string
	 */
	static function mail_from( $from_email ) {
		if ( self::_is_valid() )
			return self::$args['email'];

		return $from_email;
	}

	/**
	 * Returns sender name, applies on 'wp_mail_from_name' hook
	 *
	 * @param string $from_name
	 * @return string
	 */
	static function mail_from_name( $from_name ) {
		if ( self::_is_valid() )
			return self::$args['name'];

		return $from_name;
	}

	/**
	 * Returns defaults
	 *
	 * @uses apply_filters() Calls 'appthemes_mail_from_defaults' hook
	 * @return array
	 */
	static function get_defaults() {
		$defaults = array(
			'email' => get_option( 'admin_email' ),
			'name' => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			'reply' => false,
		);
		return apply_filters( 'appthemes_mail_from_defaults', $defaults );
	}

}

