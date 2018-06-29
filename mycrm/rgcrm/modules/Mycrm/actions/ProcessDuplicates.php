<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Mycrm_ProcessDuplicates_Action extends Mycrm_Action_Controller {
	function checkPermission(Mycrm_Request $request) {
		$module = $request->getModule();
		$records = $request->get('records');
		if($records) {
			foreach($records as $record) {
				$recordPermission = Users_Privileges_Model::isPermitted($module, 'EditView', $record);
				if(!$recordPermission) {
					throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
				}
			}
		}
	}

	function process (Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);
		$records = $request->get('records');
		$primaryRecord = $request->get('primaryRecord');
		$primaryRecordModel = Mycrm_Record_Model::getInstanceById($primaryRecord, $moduleName);

		$fields = $moduleModel->getFields();
		foreach($fields as $field) {
			$fieldValue = $request->get($field->getName());
			if($field->isEditable()) {
				$primaryRecordModel->set($field->getName(), $fieldValue);
			}
		}
		$primaryRecordModel->set('mode', 'edit');
		$primaryRecordModel->save();

		$deleteRecords = array_diff($records, array($primaryRecord));
		foreach($deleteRecords as $deleteRecord) {
			$recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'Delete', $deleteRecord);
			if($recordPermission) {
				$primaryRecordModel->transferRelationInfoOfRecords(array($deleteRecord));
				$record = Mycrm_Record_Model::getInstanceById($deleteRecord);
				$record->delete();
			}
		}

		$response = new Mycrm_Response();
		$response->setResult(true);
		$response->emit();
	}
        
        public function validateRequest(Mycrm_Request $request) { 
            $request->validateWriteAccess(); 
        } 
}