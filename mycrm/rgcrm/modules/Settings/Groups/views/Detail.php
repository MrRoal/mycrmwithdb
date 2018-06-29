<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

Class Settings_Groups_Detail_View extends Settings_Mycrm_Index_View {
    
    
    public function process(Mycrm_Request $request) {
        
        $groupId = $request->get('record');		
        $qualifiedModuleName = $request->getModule(false);
        
        $recordModel = Settings_Groups_Record_Model::getInstance($groupId);
        
        $viewer = $this->getViewer($request);

		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        
        $viewer->view('DetailView.tpl',$qualifiedModuleName);
        
        
    }
}