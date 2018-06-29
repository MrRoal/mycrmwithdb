<?php
/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */

class PBXManager_OutgoingCall_Action extends Mycrm_Action_Controller{
    
    public function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());

		if(!$permission) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}
    
    public function process(Mycrm_Request $request) {
        $serverModel = PBXManager_Server_Model::getInstance();
        $gateway = $serverModel->get("gateway");
        $response = new Mycrm_Response();
        $user = Users_Record_Model::getCurrentUserModel();
	$userNumber = $user->phone_crm_extension;
        
        if($gateway && $userNumber){
            try{
                $number = $request->get('number');
                $recordId = $request->get('record');
                $connector = $serverModel->getConnector();
                $result = $connector->call($number, $recordId);
                $response->setResult($result);
            }catch(Exception $e){
                throw new Exception($e);
            }
        }else{
            $response->setResult(false);
        }
        $response->emit();
    }
    
}