<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_CronTasks_Module_Model extends Settings_Mycrm_Module_Model {

	var $baseTable = 'mycrm_cron_task';
	var $baseIndex = 'id';
	var $listFields = array('sequence' => 'Sequence', 'name' => 'Cron Job', 'frequency' => 'Frequency(H:M)', 'status' => 'Status', 'laststart' => 'Last Start', 'lastend' => 'Last End');
	var $nameFields = array('');
	var $name = 'CronTasks';

	/**
	 * Function to get editable fields from this module
	 * @return <Array> List of fieldNames
	 */
	public function getEditableFieldsList() {
		return array('frequency', 'status');
	}

	/**
	 * Function to update sequence of several records
	 * @param <Array> $sequencesList
	 */
	public function updateSequence($sequencesList) {
		$db = PearDatabase::getInstance();

		$updateQuery = "UPDATE mycrm_cron_task SET sequence = CASE";

		foreach ($sequencesList as $sequence => $recordId) {
			$updateQuery .= " WHEN id = $recordId THEN $sequence ";
		}
		$updateQuery .= " END";
		$db->pquery($updateQuery, array());
	}
	
	public function hasCreatePermissions() {
		return false;
	}
	
	public function isPagingSupported() {
		return false;
	}

}
