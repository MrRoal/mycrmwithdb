<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Users_SystemSetup_View extends Mycrm_Index_View {
	
	public function preProcess(Mycrm_Request $request, $display=true) {
		return true;
	}
	
	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$userModel = Users_Record_Model::getCurrentUserModel();
		$isFirstUser = Users_CRMSetup::isFirstUser($userModel);
		
		if($isFirstUser) {
			$viewer->assign('IS_FIRST_USER', $isFirstUser);
			$viewer->assign('PACKAGES_LIST', Users_CRMSetup::getPackagesList());
			$viewer->view('SystemSetup.tpl', $moduleName);
		} else {
			header ('Location: index.php?module=Users&parent=Settings&view=UserSetup');
			exit();
		}
	}
	
	function postProcess(Mycrm_Request $request) {
		return true;
	}
	
}