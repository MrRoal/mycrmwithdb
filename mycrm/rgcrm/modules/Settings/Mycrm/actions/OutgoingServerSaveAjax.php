<?php

/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Mycrm_OutgoingServerSaveAjax_Action extends Settings_Mycrm_Basic_Action {
    
    public function process(Mycrm_Request $request) {
        $outgoingServerSettingsModel = Settings_Mycrm_Systems_Model::getInstanceFromServerType('email', 'OutgoingServer');
        $loadDefaultSettings = $request->get('default');
        if($loadDefaultSettings == "true") {
            $outgoingServerSettingsModel->loadDefaultValues();
        }else{
            $outgoingServerSettingsModel->setData($request->getAll());
        }
        $response = new Mycrm_Response();
        try{
            $id = $outgoingServerSettingsModel->save($request);
            $data = $outgoingServerSettingsModel->getData();
            $response->setResult($data);
        }catch(Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
    
    public function validateRequest(Mycrm_Request $request) { 
        $request->validateWriteAccess(); 
    }
}