<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_DeleteAjax_Action extends Mycrm_Delete_Action {

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$recordModel = Mycrm_Record_Model::getInstanceById($recordId, $moduleName);
		$recordModel->delete();

		$cvId = $request->get('viewname');
		$response = new Mycrm_Response();
		$response->setResult(array('viewname'=>$cvId, 'module'=>$moduleName));
		$response->emit();
	}
}
