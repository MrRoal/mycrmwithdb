<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/
include_once 'modules/Webforms/config.captcha.php';

Class Settings_Webforms_ShowForm_View extends Settings_Mycrm_IndexAjax_View {

	public function checkPermission(Mycrm_Request $request) {
		parent::checkPermission($request);

		$recordId = $request->get('record');
		$moduleModel = Mycrm_Module_Model::getInstance($request->getModule());

		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$recordId || !$currentUserPrivilegesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Mycrm_Request $request) {
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);
		$moduleName = $request->getModule();

		$recordModel = Settings_Webforms_Record_Model::getInstanceById($recordId, $qualifiedModuleName);
		$selectedFieldsList = $recordModel->getSelectedFieldsList('showForm');
		foreach ($selectedFieldsList as $fieldName => $fieldModel) {
			if (Settings_Webforms_Record_Model::isCustomField($fieldName)) {
				$dataType = $fieldModel->getFieldDataType();
				if ($dataType != 'picklist' && $dataType != 'multipicklist') {
					$fieldModel->set('name', 'label:'.str_replace(' ', '_', $fieldModel->get('label')));
				}
			}
		}
        $action_path = vglobal('site_URL').'modules/Webforms/capture.php';
        $captchaPath = vglobal('site_URL').'modules/Settings/Webforms/actions/CheckCaptcha.php';
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('SELECTED_FIELD_MODELS_LIST', $selectedFieldsList);
                $viewer->assign('ACTION_PATH', $action_path);
                $viewer->assign('CAPTCHA_PATH', $captchaPath);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
                global $captchaConfig;
                $viewer->assign('MYCRM_RECAPTCHA_PUBLIC_KEY',$captchaConfig['MYCRM_RECAPTCHA_PUBLIC_KEY']);
		$viewer->view('ShowForm.tpl', $qualifiedModuleName);
	}
}
