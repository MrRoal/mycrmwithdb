<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Portal_MassDelete_Action extends Mycrm_MassDelete_Action {

    function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

    public function process(Mycrm_Request $request) {
        $module = $request->getModule();
        
        Portal_Module_Model::deleteRecords($request);
        
        $response = new Mycrm_Response();
        $result = array('message' => vtranslate('LBL_BOOKMARKS_DELETED_SUCCESSFULLY', $module));
        $response->setResult($result);
        $response->emit();
    }
}