<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Roles Record Model Class
 */
abstract class Settings_Mycrm_Record_Model extends Mycrm_Base_Model {

	abstract function getId();
	abstract function getName();

	public function getRecordLinks() {

		$links = array();
		$recordLinks = array();
		foreach ($recordLinks as $recordLink) {
			$links[] = Mycrm_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}
	
	public function getDisplayValue($key) {
		return $this->get($key);
	}
}
