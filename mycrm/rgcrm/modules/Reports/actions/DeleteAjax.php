<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_DeleteAjax_Action extends Mycrm_DeleteAjax_Action {

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$response = new Mycrm_Response();

		$recordModel = Reports_Record_Model::getInstanceById($recordId, $moduleName);

		if (!$recordModel->isDefault() && $recordModel->isEditable()) {
			$recordModel->delete();
			$response->setResult(array(vtranslate('LBL_REPORTS_DELETED_SUCCESSFULLY', $parentModule)));
		} else {
			$response->setError(vtranslate('LBL_REPORT_DELETE_DENIED', $moduleName));
		}
		$response->emit();
	}
}
