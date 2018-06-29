<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Webforms_Detail_View extends Settings_Mycrm_Index_View {

	public function checkPermission(Mycrm_Request $request) {
		parent::checkPermission($request);

		$recordId = $request->get('record');
		$moduleModel = Mycrm_Module_Model::getInstance($request->getModule());

		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$recordId || !$currentUserPrivilegesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Mycrm_Request $request) {
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);

		$recordModel = Settings_Webforms_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
		$trailing_slash_URL =  vglobal('site_URL') . (substr(vglobal('site_URL'),-1) == '/' ? '' : '/'); 
                $recordModel->set('posturl', $trailing_slash_URL.'modules/Webforms/capture.php');
	
		$recordStructure = Mycrm_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Mycrm_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		$moduleModel = $recordModel->getModule();
		
		$navigationInfo = ListViewSession::getListViewNavigation($recordId);

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('MODULE_NAME', $qualifiedModuleName);
		$viewer->assign('RECORD_STRUCTURE', $recordStructure->getStructure());
		$viewer->assign('MODULE_MODEL', $moduleModel);

		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('SOURCE_MODULE', $recordModel->get('targetmodule'));
		$viewer->assign('DETAILVIEW_LINKS', $recordModel->getDetailViewLinks());
		$viewer->assign('SELECTED_FIELD_MODELS_LIST', $recordModel->getSelectedFieldsList());
		$viewer->assign('NO_PAGINATION',true);

		$viewer->view('DetailView.tpl', $qualifiedModuleName);
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
			"modules.Settings.Mycrm.resources.Detail",
			"modules.Settings.$moduleName.resources.Detail"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

}