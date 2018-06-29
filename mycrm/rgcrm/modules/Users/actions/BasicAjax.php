<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Users_BasicAjax_Action extends Mycrm_BasicAjax_Action {

	function checkPermission(Mycrm_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if(!$currentUser->isAdminUser()) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Mycrm'));
		}
	}

	public function process(Mycrm_Request $request) {
		$searchValue = $request->get('search_value');
		$searchModule = $request->get('search_module');

		$parentRecordId = $request->get('parent_id');
		$parentModuleName = $request->get('parent_module');

		$searchModuleModel = Users_Module_Model::getInstance($searchModule);
		$records = $searchModuleModel->searchRecord($searchValue, $parentRecordId, $parentModuleName);

		$result = array();
		if(is_array($records)){
			foreach($records as $moduleName=>$recordModels) {
				foreach($recordModels as $recordModel) {
					$result[] = array('label'=>decode_html($recordModel->getName()), 'value'=>decode_html($recordModel->getName()), 'id'=>$recordModel->getId());
				}
			}
		}

		$response = new Mycrm_Response();
		$response->setResult($result);
		$response->emit();
	}
}
