<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Portal_DeleteAjax_Action extends Mycrm_DeleteAjax_Action {
    
    public function process(Mycrm_Request $request) {
        $recordId = $request->get('record');
        $module = $request->getModule();
        Portal_Module_Model::deleteRecord($recordId);
        
        $response = new Mycrm_Response();
		$response->setResult(array('message'=>  vtranslate('LBL_RECORD_DELETED_SUCCESSFULLY', $module)));
		$response->emit();
    }
}