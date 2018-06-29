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
 * Mycrm QuickCreate Record Structure Model
 */
class Mycrm_QuickCreateRecordStructure_Model extends Mycrm_RecordStructure_Model {

	/**
	 * Function to get the values in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure() {
		if(!empty($this->structuredValues)) {
			return $this->structuredValues;
		}

		$values = array();
		$recordModel = $this->getRecord();
		$moduleModel = $this->getModule();

		$fieldModelList = $moduleModel->getQuickCreateFields();
		foreach($fieldModelList as $fieldName=>$fieldModel) {
            $recordModelFieldValue = $recordModel->get($fieldName);
            if(!empty($recordModelFieldValue)) {
                $fieldModel->set('fieldvalue', $recordModelFieldValue);
            } else if($fieldName == 'eventstatus') {
                    $currentUserModel = Users_Record_Model::getCurrentUserModel();
                    $defaulteventstatus = $currentUserModel->get('defaulteventstatus');
                    $fieldValue = $defaulteventstatus;
                    $fieldModel->set('fieldvalue', $fieldValue);
            } else if($fieldName == 'activitytype') {
                    $currentUserModel = Users_Record_Model::getCurrentUserModel();
                    $defaultactivitytype = $currentUserModel->get('defaultactivitytype');
                    $fieldValue = $defaultactivitytype;
                    $fieldModel->set('fieldvalue', $fieldValue);
            } else{
                $defaultValue = $fieldModel->getDefaultFieldValue();
                if($defaultValue) {
                    $fieldModel->set('fieldvalue', $defaultValue);
                }
            }
			$values[$fieldName] = $fieldModel;
		}
		$this->structuredValues = $values;
		return $values;
	}
}