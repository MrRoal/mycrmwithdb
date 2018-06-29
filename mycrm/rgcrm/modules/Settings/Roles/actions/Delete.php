<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Roles_Delete_Action extends Settings_Mycrm_Basic_Action {

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$recordId = $request->get('record');
		$transferRecordId = $request->get('transfer_record');

		$moduleModel = Settings_Mycrm_Module_Model::getInstance($qualifiedModuleName);
		$recordModel = Settings_Roles_Record_Model::getInstanceById($recordId);
		$transferToRole = Settings_Roles_Record_Model::getInstanceById($transferRecordId);
		if($recordModel && $transferToRole) {
			$recordModel->delete($transferToRole);
		}

		$redirectUrl = $moduleModel->getDefaultUrl();
		header("Location: $redirectUrl");
	}
        
        public function validateRequest(Mycrm_Request $request) { 
            $request->validateWriteAccess(); 
        }
}
