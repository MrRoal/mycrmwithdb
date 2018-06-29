<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_ActivityReminder_Action extends Mycrm_Action_Controller{

	function __construct() {
		$this->exposeMethod('getReminders');
		$this->exposeMethod('postpone');
	}

	public function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);

		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());

		if(!$permission) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Mycrm_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

	}

	function getReminders(Mycrm_Request $request) {
		$recordModels = Calendar_Module_Model::getCalendarReminder();
		foreach($recordModels as $record) {
			$records[] = $record->getDisplayableValues();
			$record->updateReminderStatus();
		}

		$response = new Mycrm_Response();
		$response->setResult($records);
		$response->emit();
	}

	function postpone(Mycrm_Request $request) {
		$recordId = $request->get('record');
		$module = $request->getModule();
		$recordModel = Mycrm_Record_Model::getInstanceById($recordId, $module);
		$recordModel->updateReminderStatus(0);
	}
}