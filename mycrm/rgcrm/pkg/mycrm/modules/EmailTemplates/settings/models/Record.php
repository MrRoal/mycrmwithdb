<?php

/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_EmailTemplates_Record_Model extends Settings_Mycrm_Record_Model {

	function getId() {
		return $this->get('templateid');
	}

	function getName() {
		return $this->get('templatename');
	}

	public static function getInstance() {
		return new self();
	}
}
