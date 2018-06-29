<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Profiles_DeleteAjax_View extends Settings_Profiles_IndexAjax_View {

	public function process(Mycrm_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$recordId = $request->get('record');

		$recordModel = Settings_Profiles_Record_Model::getInstanceById($recordId);

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('ALL_RECORDS', Settings_Profiles_Record_Model::getAll());
		$viewer->assign('RECORD_MODEL', $recordModel);

		echo $viewer->view('DeleteTransferForm.tpl', $qualifiedModuleName, true);
	}
}
