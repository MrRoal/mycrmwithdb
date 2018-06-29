<?php
/**************************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 **************************************************************************************/

class Mycrm_MergeRecord_View extends Mycrm_Popup_View {
	function process(Mycrm_Request $request) {
		$records = $request->get('records');
		$records = explode(',', $records);
		$module = $request->getModule();
		$moduleModel = Mycrm_Module_Model::getInstance($module);
		$fieldModels =  $moduleModel->getFields();

		foreach($records as $record) {
			$recordModels[] = Mycrm_Record_Model::getInstanceById($record);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORDS', $records);
		$viewer->assign('RECORDMODELS', $recordModels);
		$viewer->assign('FIELDS', $fieldModels);
		$viewer->assign('MODULE', $module);
		$viewer->view('MergeRecords.tpl', $module);
	}
}
