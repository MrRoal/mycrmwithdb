<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Groups_Save_Action extends Settings_Mycrm_Index_Action {

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$recordId = $request->get('record');

		$moduleModel = Settings_Mycrm_Module_Model::getInstance($qualifiedModuleName);
		if(!empty($recordId)) {
			$recordModel = Settings_Groups_Record_Model::getInstance($recordId);
		} else {
			$recordModel = new Settings_Groups_Record_Model();
		}
		if($recordModel) {
			$recordModel->set('groupname', decode_html($request->get('groupname')));
			$recordModel->set('description', $request->get('description'));
			$recordModel->set('group_members', $request->get('members'));
			$recordModel->save();
		}

		$redirectUrl = $recordModel->getDetailViewUrl();
		header("Location: $redirectUrl");
	}
        
        public function validateRequest(Mycrm_Request $request) { 
            $request->validateWriteAccess(); 
        } 
}
