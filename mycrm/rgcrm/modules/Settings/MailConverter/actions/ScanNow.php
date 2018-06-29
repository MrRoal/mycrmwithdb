<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_MailConverter_ScanNow_Action extends Settings_Mycrm_Index_Action {

	public function checkPermission(Mycrm_Request $request) {
		parent::checkPermission($request);
		$recordId = $request->get('record');

		if (!$recordId) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $request->getModule(false)));
		}
	}

	public function process(Mycrm_Request $request) {
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);

		$recordModel = Settings_MailConverter_Record_Model::getInstanceById($recordId);
		$status = $recordModel->scanNow();

		$response = new Mycrm_Response();
		if (is_bool($status) && $status) {
			$result = array('message'=> vtranslate('LBL_SCANNED_SUCCESSFULLY', $qualifiedModuleName));
            $result['id'] = $recordModel->getId();
			$response->setResult($result);
		} else if($status) {
                        $response->setError($status);
                } else {
			$response->setError(vtranslate($request->getModule(), $qualifiedModuleName). ' ' .vtranslate('LBL_IS_IN_RUNNING_STATE', $qualifiedModuleName));
		}
		$response->emit();
	}
}