<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class EmailTemplates_Delete_Action extends Mycrm_Delete_Action {
	
	function checkPermission(Mycrm_Request $request) {
		return true;
	}

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$ajaxDelete = $request->get('ajaxDelete');
		
		$recordModel = EmailTemplates_Record_Model::getInstanceById($recordId);
		$moduleModel = $recordModel->getModule();

		$recordModel->delete($recordId);

		$listViewUrl = $moduleModel->getListViewUrl();
		if($ajaxDelete) {
			$response = new Mycrm_Response();
			$response->setResult($listViewUrl);
			return $response;
		} else {
			header("Location: $listViewUrl");
		}
	}
}
