<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class ModComments_MassSaveAjax_Action extends Mycrm_Mass_Action {

	function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Save')) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	public function process(Mycrm_Request $request) {
		$recordModels = $this->getRecordModelsFromRequest($request);
		foreach($recordModels as $recordId => $recordModel) {
			$recordModel->save();
		}
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Mycrm_Request $request
	 * @return Mycrm_Record_Model or Module specific Record Model instance
	 */
	private function getRecordModelsFromRequest(Mycrm_Request $request) {

		$moduleName = $request->getModule();
		$recordIds = $this->getRecordsListFromRequest($request);
		$recordModels = array();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		foreach($recordIds as $recordId) {
			$recordModel = Mycrm_Record_Model::getCleanInstance($moduleName);
			$recordModel->set('mode', '');
			$recordModel->set('commentcontent', $request->getRaw('commentcontent'));
			$recordModel->set('related_to', $recordId);
			$recordModel->set('assigned_user_id', $currentUserModel->getId());
			$recordModel->set('userid', $currentUserModel->getId());
			$recordModels[$recordId] = $recordModel;
		}
		return $recordModels;
	}
}
