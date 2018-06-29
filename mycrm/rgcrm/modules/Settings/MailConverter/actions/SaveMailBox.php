<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_MailConverter_SaveMailBox_Action extends Settings_Mycrm_Index_Action {

	public function process(Mycrm_Request $request) {
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);

		if ($recordId) {
			$recordModel = Settings_MailConverter_Record_Model::getInstanceById($recordId);
		} else {
			$recordModel = Settings_MailConverter_Record_Model::getCleanInstance();
		}

		$recordModel->set('scannerOldName', $request->get('scannerOldName'));
		$fieldsList = $recordModel->getModule()->getFields();
		foreach ($fieldsList as $fieldName=>$fieldModel) {
			$recordModel->set($fieldName, $request->get($fieldName));
		}

		$status = $recordModel->save();

		$response = new Mycrm_Response();
		if ($status) {
			$result = array('message' => vtranslate('LBL_SAVED_SUCCESSFULLY', $qualifiedModuleName));
			$result['id'] = $recordModel->getId();
			$result['listViewUrl'] = $recordModel->getListUrl();
			$response->setResult($result);
		} else {
			$response->setError(vtranslate('LBL_CONNECTION_TO_MAILBOX_FAILED', $qualifiedModuleName));
		}
		$response->emit();
	}
        
        public function validateRequest(Mycrm_Request $request) { 
            $request->validateWriteAccess(); 
        }
}