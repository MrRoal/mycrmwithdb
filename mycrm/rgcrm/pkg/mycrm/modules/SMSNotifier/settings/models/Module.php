<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_SMSNotifier_Module_Model extends Settings_Mycrm_Module_Model {

	var $baseTable = 'mycrm_smsnotifier_servers';
	var $nameFields = array();
	var $listFields = array('providertype' => 'Provider', 'username' => 'User Name', 'isactive' => 'Active');
	var $name = 'SMSNotifier';

	/**
	 * Function to get editable fields from this module
	 * @return <Array> list of editable fields
	 */
	public function getEditableFields() {
		$fieldsList = array(
				array('name' => 'providertype', 'label' => 'Provider',	'type' => 'picklist'),
				array('name' => 'isactive',		'label' => 'Active',	'type' => 'radio'),
				array('name' => 'username',		'label' => 'User Name',	'type' => 'text'),
				array('name' => 'password',		'label' => 'Password',	'type' => 'password')
		);

		$fieldModelsList = array();
		foreach ($fieldsList as $fieldInfo) {
			$fieldModelsList[$fieldInfo['name']] = Settings_SMSNotifier_Field_Model::getInstanceByRow($fieldInfo);
		}
		return $fieldModelsList;
	}

	/**
	 * Function to get Create view url
	 * @return <String> Url
	 */
	public function getCreateRecordUrl() {
		return 'javascript:Settings_SMSNotifier_List_Js.triggerEdit(event, "index.php?module='.$this->getName().'&parent='.$this->getParentName().'&view=Edit")';
	}

	/**
	 * Function to get List view url
	 * @return <String> Url
	 */
	public function getListViewUrl() {
		return "index.php?module=".$this->getName()."&parent=".$this->getParentName()."&view=List";
	}

	/**
	 * Function to get list of all providers
	 * @return <Array> list of all providers <SMSNotifier_Provider_Model>
	 */
	public function getAllProviders() {
		if (!$this->allProviders) {
			$this->allProviders = SMSNotifier_Provider_Model::getAll();
		}
		return $this->allProviders;
	}

	/**
	 * Function to delete records
	 * @param <Array> $recordIdsList
	 * @return <Boolean> true/false
	 */
	public static function deleteRecords($recordIdsList = array()) {
		if ($recordIdsList) {
			$db = PearDatabase::getInstance();
			$query = 'DELETE FROM mycrm_smsnotifier_servers WHERE id IN (' . generateQuestionMarks($recordIdsList). ')';
			$db->pquery($query, $recordIdsList);
			return true;
		}
		return false;
	}
}