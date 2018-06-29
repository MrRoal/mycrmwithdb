<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Roles_MoveAjax_Action extends Settings_Mycrm_Basic_Action {

	public function preProcess(Mycrm_Request $request) {
		return;
	}

	public function postProcess(Mycrm_Request $request) {
		return;
	}

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$parentRoleId = $request->get('parent_roleid');

		$parentRole = Settings_Roles_Record_Model::getInstanceById($parentRoleId);
		$recordModel = Settings_Roles_Record_Model::getInstanceById($recordId);

		$response = new Mycrm_Response();
		$response->setEmitType(Mycrm_Response::$EMIT_JSON);
		try {
			$recordModel->moveTo($parentRole);
		} catch (AppException $e) {
			$response->setError('Move Role Failed');
		}
		$response->emit();
	}
}
