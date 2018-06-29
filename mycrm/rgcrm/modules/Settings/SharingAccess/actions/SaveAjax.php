<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

Class Settings_SharingAccess_SaveAjax_Action extends Mycrm_SaveAjax_Action {

         public function checkPermission(Mycrm_Request $request) { 
            $currentUser = Users_Record_Model::getCurrentUserModel(); 
            if(!$currentUser->isAdminUser()) { 
                    throw new AppException('LBL_PERMISSION_DENIED'); 
            } 
        } 
	public function process(Mycrm_Request $request) {
		$modulePermissions = $request->get('permissions');
		$modulePermissions[4] = $modulePermissions[6];

		foreach($modulePermissions as $tabId => $permission) {
			$moduleModel = Settings_SharingAccess_Module_Model::getInstance($tabId);
			$moduleModel->set('permission', $permission);

			try {
				$moduleModel->save();
			} catch (AppException $e) {
				
			}
		}
		Settings_SharingAccess_Module_Model::recalculateSharingRules();

		$response = new Mycrm_Response();
		$response->setEmitType(Mycrm_Response::$EMIT_JSON);
		$response->emit();
	}
}