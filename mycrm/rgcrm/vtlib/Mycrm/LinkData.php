<?php

/* +*******************************************************************************
 *  The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *
 * ******************************************************************************* */

/**
 * @author MAK
 */

/**
 * Description of LinkData
 *
 * @author MAK
 */
class Mycrm_LinkData {
	protected $input;
	protected $link;
	protected $user;
	protected $module;

	public function __construct($link, $user, $input = null) {
		global $currentModule;
		$this->link = $link;
		$this->user = $user;
		$this->module = $currentModule;
		if(empty($input)) {
			$this->input = $_REQUEST;
		} else {
			$this->input = $input;
		}
	}

	public function getInputParameter($name) {
		return $this->input[$name];
	}

	/**
	 *
	 * @return Mycrm_Link
	 */
	public function getLink() {
		return $this->link;
	}

	/**
	 *
	 * @return Users 
	 */
	public function getUser() {
		return $this->user;
	}

	public function getModule() {
		return $this->module;
	}

}

?>