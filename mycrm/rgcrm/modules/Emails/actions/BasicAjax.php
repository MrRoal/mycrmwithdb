<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Emails_BasicAjax_Action extends Mycrm_Action_Controller {

	public function checkPermission(Mycrm_Request $request) {
		return;
	}

	public function process(Mycrm_Request $request) {
		$moduleName = $request->get('module');
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);
		$searchValue = $request->get('searchValue');

		$emailsResult = array();
		if ($searchValue) {
			$emailsResult = $moduleModel->searchEmails($request->get('searchValue'));
		}

		$response = new Mycrm_Response();
		$response->setResult($emailsResult);
		$response->emit();
	}
}

?>
