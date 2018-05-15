<?php

/**
 * Created by PhpStorm.
 * User: radu
 * Date: 08.02.2016
 * Time: 11:02
 */
class TU_Schedule_None extends TU_Schedule_Abstract {

	public function applies() {
		return false;
	}

	public function get_end_date() {
		return '';
	}

	public function get_duration() {
		return 0;
	}

	public function should_redirect_pre_access() {
		return false;
	}

	public function should_redirect_expired() {
		return false;
	}


}
