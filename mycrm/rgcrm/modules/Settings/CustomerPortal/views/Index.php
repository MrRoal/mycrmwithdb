<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_CustomerPortal_Index_View extends Settings_Mycrm_Index_View {

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);

		$moduleModel = Settings_Mycrm_Module_Model::getInstance($qualifiedModuleName);

		$viewer->assign('PORTAL_URL', vglobal('PORTAL_URL'));
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULES_MODELS', $moduleModel->getModulesList());

		$viewer->assign('USER_MODELS', Users_Record_Model::getAll(true));
		$viewer->assign('GROUP_MODELS', Settings_Groups_Record_Model::getAll());
		$viewer->assign('CURRENT_PORTAL_USER', $moduleModel->getCurrentPortalUser());
		$viewer->assign('CURRENT_DEFAULT_ASSIGNEE', $moduleModel->getCurrentDefaultAssignee());

		$viewer->view('Index.tpl', $qualifiedModuleName);
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
			"modules.Settings.$moduleName.resources.CustomerPortal"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}