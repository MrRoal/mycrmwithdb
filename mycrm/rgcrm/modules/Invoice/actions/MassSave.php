<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Invoice_MassSave_Action extends Inventory_MassSave_Action {

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$recordModels = $this->getRecordModelsFromRequest($request);

		foreach($recordModels as $recordId => $recordModel) {
			if(Users_Privileges_Model::isPermitted($moduleName, 'Save', $recordId)) {
				//Inventory line items getting wiped out
				$_REQUEST['action'] = 'MassEditSave';
				$recordModel->save();
			}
		}

		$response = new Mycrm_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Mycrm_Request $request
	 * @return Mycrm_Record_Model or Module specific Record Model instance
	 */
	public function getRecordModelsFromRequest(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);

		$recordIds = $this->getRecordsListFromRequest($request);
		$recordModels = array();

		$fieldModelList = $moduleModel->getFields();
		foreach($recordIds as $recordId) {
			$recordModel = Mycrm_Record_Model::getInstanceById($recordId, $moduleModel);
			$recordModel->set('id', $recordId);
			$recordModel->set('mode', 'edit');

			foreach ($fieldModelList as $fieldName => $fieldModel) {
				$fieldValue = $request->get($fieldName, null);
				$fieldDataType = $fieldModel->getFieldDataType();

				if($fieldDataType == 'time') {
					$fieldValue = Mycrm_Time_UIType::getTimeValueWithSeconds($fieldValue);
				} else if($fieldDataType === 'date') {
					$fieldValue = $fieldModel->getUITypeModel()->getDBInsertValue($fieldValue);
				}

				if(isset($fieldValue) && $fieldValue != null && !is_array($fieldValue)) {
					$fieldValue = trim($fieldValue);
					$recordModel->set($fieldName, $fieldValue);
				}
			}
			$recordModels[$recordId] = $recordModel;
		}
		return $recordModels;
	}
}