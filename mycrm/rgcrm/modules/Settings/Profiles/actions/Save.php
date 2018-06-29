<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Profiles_Save_Action extends Mycrm_Action_Controller {
	
	public function checkPermission(Mycrm_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if(!$currentUser->isAdminUser()) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Mycrm_Request $request) {
		$recordId = $request->get('record');

		if(!empty($recordId)) {
			$recordModel = Settings_Profiles_Record_Model::getInstanceById($recordId);
		} else {
			$recordModel = new Settings_Profiles_Record_Model();
		}
		if($recordModel) {
			$recordModel->set('profilename', $request->get('profilename'));
			$recordModel->set('description', $request->get('description'));
			$recordModel->set('viewall', $request->get('viewall'));
			$recordModel->set('editall', $request->get('editall'));
			$recordModel->set('profile_permissions', $request->get('permissions'));
			$recordModel->save();
		}
		
		$redirectUrl = $recordModel->getDetailViewUrl();
		header("Location: $redirectUrl");
	}
        
        public function validateRequest(Mycrm_Request $request) { 
            $request->validateWriteAccess(); 
        }
}
