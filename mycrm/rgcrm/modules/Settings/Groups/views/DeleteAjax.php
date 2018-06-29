<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Groups_DeleteAjax_View extends Settings_Mycrm_Index_View {

	function preProcess(Mycrm_Request $request) {
		return;
	}

	function postProcess(Mycrm_Request $request) {
		return;
	}

	public function process(Mycrm_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$recordId = $request->get('record');

		$recordModel = Settings_Groups_Record_Model::getInstance($recordId);

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('RECORD_MODEL', $recordModel);

		$viewer->assign('ALL_USERS', Users_Record_Model::getAll());
		$viewer->assign('ALL_GROUPS', Settings_Groups_Record_Model::getAll());

		echo $viewer->view('DeleteTransferForm.tpl', $qualifiedModuleName, true);
	}
}
