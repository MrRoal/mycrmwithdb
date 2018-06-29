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
 * Mycrm Action Model Class
 */
class Mycrm_Utility_Model extends Mycrm_Action_Model {

	public function isUtilityTool() {
		return true;
	}

	public function isModuleEnabled($module) {
		$db = PearDatabase::getInstance();
		if(!$module->isEntityModule()) {
            if(!$module->isUtilityActionEnabled())
                return false;
		}
		$tabId = $module->getId();
		$sql = 'SELECT 1 FROM mycrm_profile2utility WHERE tabid = ? AND activityid = ? LIMIT 1';
		$params = array($tabId, $this->getId());
		$result = $db->pquery($sql, $params);
		if($result && $db->num_rows($result) > 0) {
			return true;
		}
		return false;
	}

}