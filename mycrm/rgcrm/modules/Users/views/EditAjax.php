<?php

/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */

Class Users_EditAjax_View extends Mycrm_IndexAjax_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('changePassword');
	}

	public function process(Mycrm_Request $request) {
		$mode = $request->get('mode');
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function changePassword(Mycrm_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->get('module');
		$userId = $request->get('recordId');

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USERID', $userId);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('ChangePassword.tpl', $moduleName);
	}

}