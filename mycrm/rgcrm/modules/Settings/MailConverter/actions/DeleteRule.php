<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_MailConverter_DeleteRule_Action extends Settings_Mycrm_Index_Action {

	public function checkPermission(Mycrm_Request $request) {
		parent::checkPermission($request);
		$recordId = $request->get('record');
		$scannerId = $request->get('scannerId');

		if (!$recordId || !$scannerId) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $request->getModule(false)));
		}
	}

	public function process(Mycrm_Request $request) {
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);

		$recordModel = Settings_MailConverter_RuleRecord_Model::getInstanceById($recordId);
		$scannerId = $recordModel->getScannerId();

		$response = new Mycrm_Response();
		if ($scannerId === $request->get('scannerId')) {
			$recordModel->delete();
			$response->setResult(vtranslate('LBL_DELETED_SUCCESSFULLY', $qualifiedModuleName));
		} else {
			$response->setError(vtranslate('LBL_RULE_DELETION_FAILED', $qualifiedModuleName));
		}
		$response->emit();
	}
        
        public function validateRequest(Mycrm_Request $request) { 
            $request->validateWriteAccess(); 
        } 
}