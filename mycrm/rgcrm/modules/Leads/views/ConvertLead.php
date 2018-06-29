<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Leads_ConvertLead_View extends Mycrm_Index_View {

	function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'ConvertLead')) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $moduleName));
		}
	}

	function process(Mycrm_Request $request) {
		$currentUserPriviligeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$viewer = $this->getViewer($request);
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$recordModel = Mycrm_Record_Model::getInstanceById($recordId);
		$moduleModel = $recordModel->getModule();
		
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('CURRENT_USER_PRIVILEGE', $currentUserPriviligeModel);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('CONVERT_LEAD_FIELDS', $recordModel->getConvertLeadFields());

		$assignedToFieldModel = $moduleModel->getField('assigned_user_id');
		$assignedToFieldModel->set('fieldvalue', $recordModel->get('assigned_user_id'));
		$viewer->assign('ASSIGN_TO', $assignedToFieldModel);

		$potentialModuleModel = Mycrm_Module_Model::getInstance('Potentials');
		$accountField = Mycrm_Field_Model::getInstance('related_to', $potentialModuleModel);
		$contactField = Mycrm_Field_Model::getInstance('contact_id', $potentialModuleModel);
		$viewer->assign('ACCOUNT_FIELD_MODEL', $accountField);
		$viewer->assign('CONTACT_FIELD_MODEL', $contactField);
		
		$contactsModuleModel = Mycrm_Module_Model::getInstance('Contacts');
		$accountField = Mycrm_Field_Model::getInstance('account_id', $contactsModuleModel);
		$viewer->assign('CONTACT_ACCOUNT_FIELD_MODEL', $accountField);
		
		$viewer->view('ConvertLead.tpl', $moduleName);
	}
}