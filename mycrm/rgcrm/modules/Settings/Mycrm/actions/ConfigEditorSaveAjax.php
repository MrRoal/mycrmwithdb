<?php

/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Mycrm_ConfigEditorSaveAjax_Action extends Settings_Mycrm_Basic_Action {

	public function process(Mycrm_Request $request) {
		$response = new Mycrm_Response();
		$qualifiedModuleName = $request->getModule(false);
		$updatedFields = $request->get('updatedFields');
		$moduleModel = Settings_Mycrm_ConfigModule_Model::getInstance();

		if ($updatedFields) {
			$moduleModel->set('updatedFields', $updatedFields);
			$status = $moduleModel->save();

			if ($status === true) {
				$response->setResult(array($status));
			} else {
				$response->setError(vtranslate($status, $qualifiedModuleName));
			}
		} else {
			$response->setError(vtranslate('LBL_FIELDS_INFO_IS_EMPTY', $qualifiedModuleName));
		}
		$response->emit();
	}
        
        public function validateRequest(Mycrm_Request $request) { 
            $request->validateWriteAccess(); 
        }
}