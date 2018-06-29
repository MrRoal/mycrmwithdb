<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/
vimport('~~/include/Webservices/ConvertLead.php');

class Leads_SaveConvertLead_View extends Mycrm_View_Controller {

	function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);

		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPrivilegesModel->hasModuleActionPermission($moduleModel->getId(), 'ConvertLead')) {
			throw new AppException(vtranslate('LBL_CONVERT_LEAD_PERMISSION_DENIED', $moduleName));
		}
	}

	public function preProcess(Mycrm_Request $request) {
	}

	public function process(Mycrm_Request $request) {
		$recordId = $request->get('record');
		$modules = $request->get('modules');
		$assignId = $request->get('assigned_user_id');
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$entityValues = array();

		$entityValues['transferRelatedRecordsTo'] = $request->get('transferModule');
		$entityValues['assignedTo'] = vtws_getWebserviceEntityId(vtws_getOwnerType($assignId), $assignId);
		$entityValues['leadId'] =  vtws_getWebserviceEntityId($request->getModule(), $recordId);

		$recordModel = Mycrm_Record_Model::getInstanceById($recordId, $request->getModule());
		$convertLeadFields = $recordModel->getConvertLeadFields();

		$availableModules = array('Accounts', 'Contacts', 'Potentials');
		foreach ($availableModules as $module) {
			if(vtlib_isModuleActive($module)&& in_array($module, $modules)) {
				$entityValues['entities'][$module]['create'] = true;
				$entityValues['entities'][$module]['name'] = $module;

				foreach ($convertLeadFields[$module] as $fieldModel) {
					$fieldName = $fieldModel->getName();
					$fieldValue = $request->get($fieldName);

					//Potential Amount Field value converting into DB format
					if ($fieldModel->getFieldDataType() === 'currency') {
						$fieldValue = Mycrm_Currency_UIType::convertToDBFormat($fieldValue);
					} elseif ($fieldModel->getFieldDataType() === 'date') {
						$fieldValue = DateTimeField::convertToDBFormat($fieldValue);
					} elseif ($fieldModel->getFieldDataType() === 'reference' && $fieldValue) {
						$ids = vtws_getIdComponents($fieldValue);
						if (count($ids) === 1) {
							$fieldValue = vtws_getWebserviceEntityId(getSalesEntityType($fieldValue), $fieldValue);
						}
					}
					$entityValues['entities'][$module][$fieldName] = $fieldValue;
				}
			}
		}
		try {
			$result = vtws_convertlead($entityValues, $currentUser);
		} catch(Exception $e) {
			$this->showError($request, $e);
			exit;
		}

		if(!empty($result['Accounts'])) {
			$accountIdComponents = vtws_getIdComponents($result['Accounts']);
			$accountId = $accountIdComponents[1];
		}
		if(!empty($result['Contacts'])) {
			$contactIdComponents = vtws_getIdComponents($result['Contacts']);
			$contactId = $contactIdComponents[1];
		}

		if(!empty($accountId)) {
			header("Location: index.php?view=Detail&module=Accounts&record=$accountId");
		} elseif (!empty($contactId)) {
			header("Location: index.php?view=Detail&module=Contacts&record=$contactId");
		} else {
			$this->showError($request);
			exit;
		}
	}

	function showError($request, $exception=false) {
		$viewer = $this->getViewer($request);
		if($exception != false) {
			$viewer->assign('EXCEPTION', $exception->getMessage());
		}

		$moduleName = $request->getModule();
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$viewer->assign('CURRENT_USER', $currentUser);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('ConvertLeadError.tpl', $moduleName);
	}
        
        public function validateRequest(Mycrm_Request $request) { 
            $request->validateWriteAccess(); 
        }
}
