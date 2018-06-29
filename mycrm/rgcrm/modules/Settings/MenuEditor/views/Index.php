<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_MenuEditor_Index_View extends Settings_Mycrm_Index_View {

	public function process(Mycrm_Request $request) {
		$allModelsList = Mycrm_Menu_Model::getAll(true);
		$menuModelStructure = Mycrm_MenuStructure_Model::getInstanceFromMenuList($allModelsList);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		
		$viewer = $this->getViewer($request);
		$viewer->assign('ALL_MODULES', $menuModelStructure->getMore());
		$viewer->assign('SELECTED_MODULES', $menuModelStructure->getTop());
		$viewer->assign('MODULE_NAME', $moduleName);
		
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}
