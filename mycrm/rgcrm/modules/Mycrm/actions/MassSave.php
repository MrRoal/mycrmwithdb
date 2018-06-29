<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_MassSave_Action extends Mycrm_Mass_Action {

	function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Save')) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);
		$recordModels = $this->getRecordModelsFromRequest($request);
        $allRecordSave= true;
		foreach($recordModels as $recordId => $recordModel) {
			if(Users_Privileges_Model::isPermitted($moduleName, 'Save', $recordId)) {
				$recordModel->save();
			}
            else {
                $allRecordSave= false;
            }
		}
        
        $response = new Mycrm_Response();
        if($allRecordSave) {
           $response->setResult(true);
        } else {
           $response->setResult(false);
        }
   	$response->emit();
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Mycrm_Request $request
	 * @return Mycrm_Record_Model or Module specific Record Model instance
	 */
	function getRecordModelsFromRequest(Mycrm_Request $request) {

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
				if($fieldDataType == 'time'){
					$fieldValue = Mycrm_Time_UIType::getTimeValueWithSeconds($fieldValue);
				}
				if(isset($fieldValue) && $fieldValue != null) {
					if(!is_array($fieldValue)) {
						$fieldValue = trim($fieldValue);
					}
					$recordModel->set($fieldName, $fieldValue);
				} else {
                    $uiType = $fieldModel->get('uitype');
                    if($uiType == 70) {
                        $recordModel->set($fieldName, $recordModel->get($fieldName));
                    }  else {
                        $uiTypeModel = $fieldModel->getUITypeModel();
                        $recordModel->set($fieldName, $uiTypeModel->getUserRequestValue($recordModel->get($fieldName)));
                    }
				}
			}
			$recordModels[$recordId] = $recordModel;
		}
		return $recordModels;
	}
}
