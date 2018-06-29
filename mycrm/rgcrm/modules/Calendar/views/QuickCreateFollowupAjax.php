<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_QuickCreateFollowupAjax_View extends Mycrm_QuickCreateAjax_View {

	public function  process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
        $recordId = $request->get('record');
        
        $recordModel = Mycrm_Record_Model::getInstanceById($recordId);
        $moduleModel = $recordModel->getModule();
        $actionname = "EditView";
        
        if(isPermitted($moduleName, $actionname, $recordId) === 'yes'){
            //Start date Field required for validation
            $startDateFieldModel = $moduleModel->getField("date_start");
            $startDateTime = $recordModel->getDisplayValue('date_start');
            $startDate = explode(" ", $startDateTime);
            $startDate = $startDate[0];

            $viewer = $this->getViewer($request);
            $viewer->assign('STARTDATEFIELDMODEL',$startDateFieldModel);
            $viewer->assign('STARTDATE',$startDate);
            $viewer->assign('CURRENTDATE', date('Y-n-j'));
            $viewer->assign('MODULE', $moduleName);
            $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
            $viewer->assign('RECORD_MODEL', $recordModel);

            $viewer->view('QuickCreateFollowup.tpl', $moduleName);
        }        
	}
    
}
