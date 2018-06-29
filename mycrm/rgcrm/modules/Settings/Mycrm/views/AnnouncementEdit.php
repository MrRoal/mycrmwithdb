<?php

/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Mycrm_AnnouncementEdit_View extends Settings_Mycrm_Index_View {
    
    public function process(Mycrm_Request $request) {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $annoucementModel = Settings_Mycrm_Announcement_Model::getInstanceByCreator($currentUser);
        
        $qualifiedModuleName = $request->getModule(false);
        
        $viewer = $this->getViewer($request);
		
        $viewer->assign('ANNOUNCEMENT',$annoucementModel);
        $viewer->view('Announcement.tpl',$qualifiedModuleName);
    }
	
	function getPageTitle(Mycrm_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		return vtranslate('LBL_ANNOUNCEMENT',$qualifiedModuleName);
	}
	
	/**
	 * Function to get the list of Script models to be included
	 * @param Mycrm_Request $request
	 * @return <Array> - List of Mycrm_JsScript_Model instances
	 */
	function getHeaderScripts(Mycrm_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.Announcement"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}