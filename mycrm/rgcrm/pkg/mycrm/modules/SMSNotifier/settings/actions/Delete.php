<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_SMSNotifier_Delete_Action extends Settings_Mycrm_Index_Action {

	public function process(Mycrm_Request $request) {
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);

		$response = new Mycrm_Response();
		if ($recordId) {
			$status = Settings_SMSNotifier_Module_Model::deleteRecords(array($recordId));
			if ($status) {
				$response->setResult(array(vtranslate('LBL_DELETED_SUCCESSFULLY'), $qualifiedModuleName));
			} else {
				$response->setError(vtranslate('LBL_DELETE_FAILED', $qualifiedModuleName));
			}
		} else {
			$response->setError(vtranslate('LBL_INVALID_RECORD', $qualifiedModuleName));
		}
		$response->emit();
	}
        
        public function validateRequest(Mycrm_Request $request) { 
            $request->validateWriteAccess(); 
        } 
}