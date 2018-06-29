<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_CustomerPortal_Save_Action extends Settings_Mycrm_Index_Action {

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$privileges = $request->get('privileges');
		$defaultAssignee = $request->get('defaultAssignee');
		$portalModulesInfo = $request->get('portalModulesInfo');

		if ($privileges && $defaultAssignee && $portalModulesInfo) {
			$moduleModel = Settings_CustomerPortal_Module_Model::getInstance($qualifiedModuleName);
			$moduleModel->set('privileges', $privileges);
			$moduleModel->set('defaultAssignee', $defaultAssignee);
			$moduleModel->set('portalModulesInfo', $portalModulesInfo);
			$moduleModel->save();
		}
		
		$responce = new Mycrm_Response();
        $responce->setResult(array('success'=>true));
        $responce->emit();
	}
}