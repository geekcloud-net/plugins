<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

/**
 * Class TVD_SM_Frontend
 */
class TVD_SM_Frontend {

	const HOOK_HEAD = 'tcb_landing_head';
	const HOOK_BODY_OPEN = 'tcb_landing_body_open';
	const HOOK_BODY_CLOSE = 'tcb_landing_body_close';

	private $scripts = array();
	private $lp_head_code = '';
	private $lp_body_open_code = '';
	private $lp_body_close_code = '';

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		/* update the script code for the 3 lp sections */
		$this->update_script_code();

		/* hooks for adding scripts to specific sections of the landing pages */
		add_action( self::HOOK_HEAD, array( $this, 'lp_head_script' ) );
		add_action( self::HOOK_BODY_OPEN, array( $this, 'lp_body_open_script' ) );
		add_action( self::HOOK_BODY_CLOSE, array( $this, 'lp_body_close_script' ) );
	}

	public function update_script_code() {
		/* get all the scripts  */
		$this->scripts = tah()->tvd_sm_get_scripts();

		/* sort the array according to the 'order' field */
		usort( $this->scripts, array( tah(), 'sort_by_order' ) );

		/* update the section strings */
		foreach ( $this->scripts as $val ) {
			if ( $val['status'] ) {
				switch ( $val['placement'] ) {
					case 'head' :
						$this->lp_head_code .= $val['code'];
						break;
					case 'body_open' :
						$this->lp_body_open_code .= $val['code'];
						break;
					case 'body_close' :
						$this->lp_body_close_code .= $val['code'];
						break;
				}
			}
		}
	}

	public function lp_head_script() {
		/* add all the head scripts */
		echo $this->lp_head_code;
	}

	public function lp_body_open_script( $id ) {
		/* add all the body start scripts */
		echo $this->lp_body_open_code;

		return $id;
	}

	public function lp_body_close_script( $id ) {
		/* add all the body end scripts */
		echo $this->lp_body_close_code;

		return $id;
	}
}

return new TVD_SM_Frontend();
