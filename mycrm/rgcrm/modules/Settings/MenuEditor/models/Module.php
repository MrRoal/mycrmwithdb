<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_MenuEditor_Module_Model extends Settings_Mycrm_Module_Model {
	
	var $name = 'MenuEditor';

	/**
	 * Function to save the menu structure
	 */
	public function saveMenuStruncture() {
		$db = PearDatabase::getInstance();
		$selectedModulesList = $this->get('selectedModulesList');

		$updateQuery = "UPDATE mycrm_tab SET tabsequence = CASE tabid ";

		foreach ($selectedModulesList as $sequence => $tabId) {
			$updateQuery .= " WHEN $tabId THEN $sequence ";
		}
		$updateQuery .= "ELSE -1 END";

		$db->pquery($updateQuery, array());
	}
}
