<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Documents_MoveDocuments_Action extends Mycrm_Mass_Action {

	public function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();

		if(!Users_Privileges_Model::isPermitted($moduleName, 'EditView')) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$documentIdsList = $this->getRecordsListFromRequest($request);
		$folderId = $request->get('folderid');

		if (!empty ($documentIdsList)) {
			foreach ($documentIdsList as $documentId) {
				$documentModel = Mycrm_Record_Model::getInstanceById($documentId, $moduleName);
				if (Users_Privileges_Model::isPermitted($moduleName, 'EditView', $documentId)) {
					$documentModel->set('folderid', $folderId);
					$documentModel->set('mode', 'edit');
					$documentModel->save();
				} else {
					$documentsMoveDenied[] = $documentModel->getName();
				}
			}
		}
		if (empty ($documentsMoveDenied)) {
			$result = array('success'=>true, 'message'=>vtranslate('LBL_DOCUMENTS_MOVED_SUCCESSFULLY', $moduleName));
		} else {
			$result = array('success'=>false, 'message'=>vtranslate('LBL_DENIED_DOCUMENTS', $moduleName), 'LBL_RECORDS_LIST'=>$documentsMoveDenied);
		}

		$response = new Mycrm_Response();
		$response->setResult($result);
		$response->emit();
	}
}