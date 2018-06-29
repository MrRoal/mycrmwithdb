<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

Class Settings_Profiles_Edit_View extends Settings_Mycrm_Index_View {

	public function process(Mycrm_Request $request) {
        $this->initialize($request);
        $qualifiedModuleName = $request->getModule(false);
        
        $viewer = $this->getViewer($request);
		$viewer->view('EditView.tpl', $qualifiedModuleName);
	}
    
    public function initialize(Mycrm_Request $request) {
        $viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$record = $request->get('record');
		$fromRecord = $request->get('from_record');

		if(!empty($record)) {
			$recordModel = Settings_Profiles_Record_Model::getInstanceById($record);
			$viewer->assign('MODE', 'edit');
		} elseif(!empty($fromRecord)) {
			$recordModel = Settings_Profiles_Record_Model::getInstanceById($fromRecord);
			$recordModel->getModulePermissions();
            $recordModel->getGlobalPermissions();
			$recordModel->set('profileid', '');
			$viewer->assign('MODE', '');
			$viewer->assign('IS_DUPLICATE_RECORD',$fromRecord);
		} else {
			$recordModel = new Settings_Profiles_Record_Model();
			$viewer->assign('MODE', '');
		}
		$viewer->assign('ALL_PROFILES',$recordModel->getAll());
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('ALL_BASIC_ACTIONS', Mycrm_Action_Model::getAllBasic(true));
		$viewer->assign('ALL_UTILITY_ACTIONS', Mycrm_Action_Model::getAllUtility(true)); 
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
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
			'modules.Settings.Mycrm.resources.Edit',
			"modules.Settings.$moduleName.resources.Edit"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}