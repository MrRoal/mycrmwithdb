<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_CheckDuplicate_Action extends Mycrm_Action_Controller {

	function checkPermission(Mycrm_Request $request) {
		return;
	}

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$reportName = $request->get('reportname');
		$record = $request->get('record');
		
		if ($record) {
			$recordModel = Mycrm_Record_Model::getInstanceById($record, $moduleName);
		} else {
			$recordModel = Mycrm_Record_Model::getCleanInstance($moduleName);
		}

		$recordModel->set('reportname', $reportName);
		$recordModel->set('reportid', $record);
		$recordModel->set('isDuplicate', $request->get('isDuplicate'));
		
		if (!$recordModel->checkDuplicate()) {
			$result = array('success'=>false);
		} else {
			$result = array('success'=>true, 'message'=>vtranslate('LBL_DUPLICATES_EXIST', $moduleName));
		}
		$response = new Mycrm_Response();
		$response->setResult($result);
		$response->emit();
	}
}
