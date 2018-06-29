<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

// Import dependencies
include_once 'libraries/HTTP_Session2/HTTP/Session2.php';

/**
 * Session class
 */
class Mycrm_Session {

	/**
	 * Constructor
	 * Avoid creation of instances.
	 */
	private function __construct() {
	}

	/**
	 * Destroy session
	 */
	static function destroy($sessionid = false) {
		HTTP_Session2::destroy($sessionid);
	}

	/**
	 * Initialize session
	 */
	static function init($sessionid = false) {
		if(empty($sessionid)) {
			HTTP_Session2::start(null, null);
			$sessionid = HTTP_Session2::id();
		} else {
			HTTP_Session2::start(null, $sessionid);
		}

		if(HTTP_Session2::isIdle() || HTTP_Session2::isExpired()) {
			return false;
		}
		return $sessionid;
	}

	/**
	 * Is key defined in session?
	 */
	static function has($key) {
		return HTTP_Session2::is_set($key);
	}

	/**
	 * Get value for the key.
	 */
	static function get($key, $defvalue = '') {
		return HTTP_Session2::get($key, $defvalue);
	}

	/**
	 * Set value for the key.
	 */
	static function set($key, $value) {
		HTTP_Session2::set($key, $value);
	}

}