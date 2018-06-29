<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/
chdir(dirname(__FILE__) . '/../../../');
include_once 'include/Webservices/Relation.php';
include_once 'vtlib/Mycrm/Module.php';
include_once 'includes/main/WebUI.php';
vimport('includes.http.Request');

class PBXManager_PBXManager_Callbacks {
    
    function validateRequest($mycrmsecretkey,$request) {
        if($mycrmsecretkey == $request->get('mycrmsignature')){
            return true;
        }
        return false;
    }

    function process($request){
	$pbxmanagerController = new PBXManager_PBXManager_Controller();
        $connector = $pbxmanagerController->getConnector();
        if($this->validateRequest($connector->getMycrmSecretKey(),$request)) {
            $pbxmanagerController->process($request);
        }else {
            $response = $connector->getXmlResponse();
            echo $response;
        }
    }
}
$pbxmanager = new PBXManager_PBXManager_Callbacks();
$pbxmanager->process(new Mycrm_Request($_REQUEST));
?>