<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Users_Logout_Action extends Mycrm_Action_Controller {

	function checkPermission(Mycrm_Request $request) {
		return true;
	}

	function process(Mycrm_Request $request) {
		session_regenerate_id(true); // to overcome session id reuse.
		Mycrm_Session::destroy();
		
		//Track the logout History
		$moduleName = $request->getModule();
		$moduleModel = Users_Module_Model::getInstance($moduleName);
		$moduleModel->saveLogoutHistory();
		//End
		
		header ('Location: index.php');
	}
}
