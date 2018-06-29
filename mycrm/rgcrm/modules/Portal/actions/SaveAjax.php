<?php
/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * ************************************************************************************/

class Portal_SaveAjax_Action extends Mycrm_SaveAjax_Action {
    
    public function process(Mycrm_Request $request) {
        $module = $request->getModule();
        $recordId = $request->get('record');
        $bookmarkName = $request->get('bookmarkName');
        $bookmarkUrl = $request->get('bookmarkUrl');
        
        Portal_Module_Model::savePortalRecord($recordId, $bookmarkName, $bookmarkUrl);
        
        $response = new Mycrm_Response();
        $result = array('message' => vtranslate('LBL_BOOKMARK_SAVED_SUCCESSFULLY', $module));
        $response->setResult($result);
        $response->emit();
    }
}