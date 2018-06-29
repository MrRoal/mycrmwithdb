<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/
class Mycrm_PDF_Model {
	protected $values = array();
	
	function set($key, $value) {
		$this->values[$key] = $value;
	}

	function get($key, $defvalue='') {
		return (isset($this->values[$key]))? $this->values[$key] : $defvalue;
	}
	
	function count() {
		return count($this->values);
	}
	
	function keys() {
		return array_keys($this->values);
	}
}