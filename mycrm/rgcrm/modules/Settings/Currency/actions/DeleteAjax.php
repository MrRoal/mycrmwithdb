<?php

/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Currency_DeleteAjax_Action extends Settings_Mycrm_Basic_Action {
    
    public function process(Mycrm_Request $request) {
        $response = new Mycrm_Response();
        try{
            $record = $request->get('record');
            $transforCurrencyToId = $request->get('transform_to_id');
            if(empty($transforCurrencyToId)) {
                throw new Exception('Transfer currency id cannot be empty');
            }
            Settings_Currency_Module_Model::tranformCurrency($record, $transforCurrencyToId);
            Settings_Currency_Module_Model::delete($record);
            $response->setResult(array('success'=>'true'));
        }catch(Exception $e){
           $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
    
    public function validateRequest(Mycrm_Request $request) { 
        $request->validateWriteAccess(); 
    }
}