<?php

/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Mycrm_CustomRecordNumbering_View extends Settings_Mycrm_Index_View {

	public function process(Mycrm_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$supportedModules = Settings_Mycrm_CustomRecordNumberingModule_Model::getSupportedModules();

		$sourceModule = $request->get('sourceModule');
		if ($sourceModule) {
			$defaultModuleModel = $supportedModules[getTabid($sourceModule)];
		} else {
			$defaultModuleModel = reset($supportedModules);
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('SUPPORTED_MODULES', $supportedModules);
		$viewer->assign('DEFAULT_MODULE_MODEL', $defaultModuleModel);
		$viewer->assign('QUALIFIED_MODULE',$qualifiedModuleName);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('CustomRecordNumbering.tpl', $qualifiedModuleName);
	}
	
	function getPageTitle(Mycrm_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		return vtranslate('LBL_CUSTOMIZE_RECORD_NUMBERING',$qualifiedModuleName);
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
			"modules.Settings.Mycrm.resources.CustomRecordNumbering"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}