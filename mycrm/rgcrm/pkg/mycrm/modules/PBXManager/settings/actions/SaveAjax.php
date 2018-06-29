<?php

/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_PBXManager_SaveAjax_Action extends Mycrm_SaveAjax_Action {

    // To save Mapping of user from mapping popup
    public function process(Mycrm_Request $request) {
        $id = $request->get('id');
        $qualifiedModuleName = 'PBXManager';
        
        $recordModel = Settings_PBXManager_Record_Model::getCleanInstance();
        $recordModel->set('gateway',$qualifiedModuleName);
        if($id) {
            $recordModel->set('id',$id);
        }
        
        $connector = new PBXManager_PBXManager_Connector;
        foreach ($connector->getSettingsParameters() as $field => $type) {
                $recordModel->set($field, $request->get($field));
        }
        
        $response = new Mycrm_Response();
        try {
                $recordModel->save();
                $response->setResult(true);
        } catch (Exception $e) {
                $response->setError($e->getMessage());
        }
        $response->emit();
    }
}
