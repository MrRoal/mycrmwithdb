<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_GetData_Action extends Mycrm_IndexAjax_View {

	public function process(Mycrm_Request $request) {
		$record = $request->get('record');
		$sourceModule = $request->get('source_module');
		$response = new Mycrm_Response();

		$permitted = Users_Privileges_Model::isPermitted($sourceModule, 'DetailView', $record);
		if($permitted) {
			$recordModel = Mycrm_Record_Model::getInstanceById($record, $sourceModule);
			$data = $recordModel->getData();
			$response->setResult(array('success'=>true, 'data'=>array_map('decode_html',$data)));
		} else {
			$response->setResult(array('success'=>false, 'message'=>vtranslate('LBL_PERMISSION_DENIED')));
		}
		$response->emit();
	}
}
