<?php

/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_PickListDependency_SaveAjax_Action extends Settings_Mycrm_Index_Action {
    
    public function process(Mycrm_Request $request) {
        $sourceModule = $request->get('sourceModule');
        $sourceField = $request->get('sourceField');
        $targetField = $request->get('targetField');
        $recordModel = Settings_PickListDependency_Record_Model::getInstance($sourceModule, $sourceField, $targetField);
        
        $response = new Mycrm_Response();
        try{
            $result = $recordModel->save($request->get('mapping'));
            $response->setResult(array('success'=>$result));
        } catch(Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
    
    public function validateRequest(Mycrm_Request $request) { 
        $request->validateWriteAccess(); 
    }
}