<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_MassDelete_Action extends Mycrm_Mass_Action {

	public function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Reports_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	function preProcess(Mycrm_Request $request) {
		return true;
	}

	function postProcess(Mycrm_Request $request) {
		return true;
	}

	public function process(Mycrm_Request $request) {
		$parentModule = 'Reports';
		$recordIds = Reports_Record_Model::getRecordsListFromRequest($request);

		$reportsDeleteDenied = array();
		foreach($recordIds as $recordId) {
			$recordModel = Reports_Record_Model::getInstanceById($recordId);
			if (!$recordModel->isDefault() && $recordModel->isEditable()) {
				$success = $recordModel->delete();
				if(!$success) {
					$reportsDeleteDenied[] = vtranslate($recordModel->getName(), $parentModule);
				}
			} else {
				$reportsDeleteDenied[] = vtranslate($recordModel->getName(), $parentModule);
			}
		}

		$response = new Mycrm_Response();
		if (empty ($reportsDeleteDenied)) {
			$response->setResult(array(vtranslate('LBL_REPORTS_DELETED_SUCCESSFULLY', $parentModule)));
		} else {
			$response->setError($reportsDeleteDenied, vtranslate('LBL_DENIED_REPORTS', $parentModule));
		}

		$response->emit();
	}
}
