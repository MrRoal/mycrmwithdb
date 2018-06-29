<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

Class Settings_Webforms_Edit_View extends Settings_Mycrm_Index_View {

	public function checkPermission(Mycrm_Request $request) {
		parent::checkPermission($request);

		$moduleModel = Mycrm_Module_Model::getInstance($request->getModule());
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if (!$currentUserPrivilegesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Mycrm_Request $request) {
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);
		$mode = '';
		$selectedFieldsList = $allFieldsList = array();
		$viewer = $this->getViewer($request);
		$supportedModules = Settings_Webforms_Module_Model::getSupportedModulesList();

		if ($recordId) {
			$recordModel = Settings_Webforms_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
			$selectedFieldsList = $recordModel->getSelectedFieldsList();
			
			$sourceModule = $recordModel->get('targetmodule');
			$mode = 'edit';
		} else {
			$recordModel = Settings_Webforms_Record_Model::getCleanInstance($qualifiedModuleName);
			$sourceModule = $request->get('sourceModule');
			if (!$sourceModule) {
				$sourceModule = reset(array_keys($supportedModules));
			}
            $recordModel->set('targetmodule',$sourceModule);
		}
		if(!$supportedModules[$sourceModule]){
			$message = vtranslate('LBL_ENABLE_TARGET_MODULES_FOR_WEBFORM',$qualifiedModuleName);
			$viewer->assign('MESSAGE', $message);
			$viewer->view('OperationNotPermitted.tpl', 'Mycrm');
			return false;
		}

		$allFieldsList = $recordModel->getAllFieldsList($sourceModule);
		$recordStructure = Mycrm_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Mycrm_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);

		$viewer->assign('MODE', $mode);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);

		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('ALL_FIELD_MODELS_LIST', $allFieldsList);
		$viewer->assign('SELECTED_FIELD_MODELS_LIST', $selectedFieldsList);
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructure);
		$viewer->assign('RECORD_STRUCTURE', $recordStructure->getStructure());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		
		$viewer->view('EditView.tpl', $qualifiedModuleName);
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
			"modules.Settings.$moduleName.resources.Field",
			"modules.Settings.$moduleName.resources.Edit"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

}