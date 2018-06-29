<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Mycrm_TermsAndConditionsSaveAjax_Action extends Settings_Mycrm_Basic_Action {
    
    public function process(Mycrm_Request $request) {
        $model = Settings_Mycrm_TermsAndConditions_Model::getInstance();
        $model->setText($request->get('tandc'));
        $model->save();
        
        $response = new Mycrm_Response();
        $response->emit();
    }
    
    public function validateRequest(Mycrm_Request $request) { 
        $request->validateWriteAccess(); 
    } 
}