<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_DeleteImage_Action extends Mycrm_Action_Controller {

	public function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('id');

		if (!(Users_Privileges_Model::isPermitted($moduleName, 'EditView', $record) && Users_Privileges_Model::isPermitted($moduleName, 'Delete', $record))) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$imageId = $request->get('imageid');

		$response = new Mycrm_Response();
		if ($recordId) {
			$recordModel = Mycrm_Record_Model::getInstanceById($recordId, $moduleModel);
			$status = $recordModel->deleteImage($imageId);
			if ($status) {
				$response->setResult(array(vtranslate('LBL_IMAGE_DELETED_SUCCESSFULLY', $moduleName)));
			}
		} else {
			$response->setError(vtranslate('LBL_IMAGE_NOT_DELETED', $moduleName));
		}

		$response->emit();
	}
}
