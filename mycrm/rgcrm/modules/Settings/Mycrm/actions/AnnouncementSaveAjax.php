<?php

/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Mycrm_AnnouncementSaveAjax_Action extends Settings_Mycrm_Basic_Action {
    
    public function process(Mycrm_Request $request) {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $annoucementModel = Settings_Mycrm_Announcement_Model::getInstanceByCreator($currentUser);
        $annoucementModel->set('announcement',$request->get('announcement'));
        $annoucementModel->save();
        $responce = new Mycrm_Response();
        $responce->setResult(array('success'=>true));
        $responce->emit();
    }
    
    public function validateRequest(Mycrm_Request $request) { 
        $request->validateWriteAccess(); 
    } 
}