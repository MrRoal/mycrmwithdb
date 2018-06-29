<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class EmailTemplates_MassDelete_Action extends Mycrm_Mass_Action {

	function checkPermission(){
		return true;
	}

	function preProcess(Mycrm_Request $request) {
		return true;
	}

	function postProcess(Mycrm_Request $request) {
		return true;
	}

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();

		$recordModel = new EmailTemplates_Record_Model();
		$recordModel->setModule($moduleName);
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		if($selectedIds == 'all' && empty($excludedIds)){
			$recordModel->deleteAllRecords();
		}else{
			$recordIds = $this->getRecordsListFromRequest($request, $recordModel);
			foreach($recordIds as $recordId) {
				$recordModel = EmailTemplates_Record_Model::getInstanceById($recordId);
				$recordModel->delete();
			}
		}
		
		$response = new Mycrm_Response();
		$response->setResult(array('module'=>$moduleName));
		$response->emit();
	}
	
	public function getRecordsListFromRequest(Mycrm_Request $request, $recordModel) {
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		
		if(!empty($selectedIds) && $selectedIds != 'all') {
			if(!empty($selectedIds) && count($selectedIds) > 0) {
				return $selectedIds;
			}
		}
		if(!empty($excludedIds)){
			$moduleModel = $recordModel->getModule();
			$recordIds  = $moduleModel->getRecordIds($excludedIds);
			return $recordIds;
		}
	}
}
