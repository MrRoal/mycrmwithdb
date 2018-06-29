<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Mycrm_CompanyDetailsEdit_View extends Settings_Mycrm_Index_View {

	public function process(Mycrm_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Mycrm_CompanyDetails_Model::getInstance();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('QUALIFIED_MODULE_NAME', $qualifiedModuleName);
		$viewer->assign('ERROR_MESSAGE', $request->get('error'));

		$viewer->view('CompanyDetailsEdit.tpl', $qualifiedModuleName);//For Open Source
	}
		
	function getPageTitle(Mycrm_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		return vtranslate('LBL_CONFIG_EDITOR',$qualifiedModuleName);
	}
	
}