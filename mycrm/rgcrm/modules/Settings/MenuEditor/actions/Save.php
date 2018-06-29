<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_MenuEditor_Save_Action extends Settings_Mycrm_Index_Action {

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule(false);
		$menuEditorModuleModel = Settings_Mycrm_Module_Model::getInstance($moduleName);
		$selectedModulesList = $request->get('selectedModulesList');

		if ($selectedModulesList) {
			$menuEditorModuleModel->set('selectedModulesList', $selectedModulesList);
			$menuEditorModuleModel->saveMenuStruncture();
		}
		$loadUrl = $menuEditorModuleModel->getIndexViewUrl();
		header("Location: $loadUrl");
	}

        public function validateRequest(Mycrm_Request $request) { 
            $request->validateWriteAccess(); 
        }
}
