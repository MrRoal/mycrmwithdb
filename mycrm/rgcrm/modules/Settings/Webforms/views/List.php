<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Webforms_List_View extends Settings_Mycrm_List_View {
	
	function preProcess(Mycrm_Request $request, $display=true) {
		$viewer = $this->getViewer($request);
		$viewer->assign('DESCRIPTION', 'LBL_ALLOWS_YOU_TO_MANAGE_WEBFORMS');
		parent::preProcess($request, false);
	}

	public function checkPermission(Mycrm_Request $request) {
		parent::checkPermission($request);

		$moduleModel = Mycrm_Module_Model::getInstance($request->getModule());
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if(!$currentUserPrivilegesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
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
			'modules.Mycrm.resources.List',
			'modules.Settings.Mycrm.resources.List',
			"modules.Settings.$moduleName.resources.List",
			"modules.Settings.$moduleName.resources.Edit",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}