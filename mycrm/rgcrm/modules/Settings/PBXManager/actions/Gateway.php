<?php

/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_PBXManager_Gateway_Action extends Settings_Mycrm_IndexAjax_View{
    
    function __construct() {
        $this->exposeMethod('getSecretKey');
    }
    
    public function process(Mycrm_Request $request) {
        $this->getSecretKey($request);
    }
    
    public function getSecretKey(Mycrm_Request $request) {
        $serverModel = PBXManager_Server_Model::getInstance();
        $response = new Mycrm_Response();
        $mycrmsecretkey = $serverModel->get('mycrmsecretkey');
        if($mycrmsecretkey) {
            $connector = $serverModel->getConnector();
            $mycrmsecretkey = $connector->getMycrmSecretKey();
            $response->setResult($mycrmsecretkey);
        }else {
            $mycrmsecretkey = PBXManager_Server_Model::generateMycrmSecretKey();
            $response->setResult($mycrmsecretkey);
        }
        $response->emit();
    }
}
