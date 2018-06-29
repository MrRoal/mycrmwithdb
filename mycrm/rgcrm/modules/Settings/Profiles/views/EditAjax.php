<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

Class Settings_Profiles_EditAjax_View extends Settings_Profiles_Edit_View {

    public function preProcess(Mycrm_Request $request) {
        return true;
    }
    
    public function postProcess(Mycrm_Request $request) {
        return true;
    }
    
    public function process(Mycrm_Request $request) {
        echo $this->getContents($request);
    }
    
    public function getContents(Mycrm_Request $request) {
        $this->initialize($request);
		
        $qualifiedModuleName = $request->getModule(false);
        $viewer = $this->getViewer ($request);
		$viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
        return $viewer->view('EditViewContents.tpl',$qualifiedModuleName,true);
    }
	
	/**
	 * Function to get the list of Script models to be included
	 * @param Mycrm_Request $request
	 * @return <Array> - List of Mycrm_JsScript_Model instances
	 */
	function getHeaderScripts(Mycrm_Request $request) {
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.Profiles.resources.Profiles",
		);
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $jsScriptInstances;
	}
    
}
