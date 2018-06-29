<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_PBXManager_Index_View extends Settings_Mycrm_Index_View{
    
    function __construct() {
        $this->exposeMethod('gatewayInfo');
    }

    public function process(Mycrm_Request $request) {
        $this->gatewayInfo($request);
    }
    
    public function gatewayInfo(Mycrm_Request $request){
        $recordModel = Settings_PBXManager_Record_Model::getInstance();
        $moduleModel = Settings_PBXManager_Module_Model::getCleanInstance();
        $viewer = $this->getViewer($request);  
        
        $viewer->assign('RECORD_ID', $recordModel->get('id'));
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('MODULE', $request->getModule(false));
        $viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->view('index.tpl', $request->getModule(false));
    }


}
