<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Users_PreferenceDetail_View extends Mycrm_Detail_View {

	public function checkPermission(Mycrm_Request $request) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$record = $request->get('record');

		if($currentUserModel->isAdminUser() == true || $currentUserModel->get('id') == $record) {
			return true;
		} else {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Function to returns the preProcess Template Name
	 * @param <type> $request
	 * @return <String>
	 */
	public function preProcessTplName(Mycrm_Request $request) {
		return 'PreferenceDetailViewPreProcess.tpl';
	}

	/**
	 * Function shows basic detail for the record
	 * @param <type> $request
	 */
	public function showModuleBasicView($request) {
		return $this->showModuleDetailView($request);
	}

	public function preProcess(Mycrm_Request $request, $display=true) {
		if($this->checkPermission($request)) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$recordId = $request->get('record');
			$moduleName = $request->getModule();
			$detailViewModel = Mycrm_DetailView_Model::getInstance($moduleName, $recordId);
			$recordModel = $detailViewModel->getRecord();

			$detailViewLinkParams = array('MODULE'=>$moduleName,'RECORD'=>$recordId);
			$detailViewLinks = $detailViewModel->getDetailViewLinks($detailViewLinkParams);

			$viewer = $this->getViewer($request);
			$viewer->assign('RECORD', $recordModel);

			$viewer->assign('MODULE_MODEL', $detailViewModel->getModule());
			$viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);

			$viewer->assign('IS_EDITABLE', $detailViewModel->getRecord()->isEditable($moduleName));
			$viewer->assign('IS_DELETABLE', $detailViewModel->getRecord()->isDeletable($moduleName));

			$linkParams = array('MODULE'=>$moduleName, 'ACTION'=>$request->get('view'));
			$linkModels = $detailViewModel->getSideBarLinks($linkParams);
			$viewer->assign('QUICK_LINKS', $linkModels);
			$viewer->assign('PAGETITLE', $this->getPageTitle($request));
			$viewer->assign('SCRIPTS',$this->getHeaderScripts($request));
			$viewer->assign('STYLES',$this->getHeaderCss($request));
			$viewer->assign('LANGUAGE_STRINGS', $this->getJSLanguageStrings($request));
			$viewer->assign('SEARCHABLE_MODULES', Mycrm_Module_Model::getSearchableModules());

			$menuModelsList = Mycrm_Menu_Model::getAll(true);
			$selectedModule = $request->getModule();
			$menuStructure = Mycrm_MenuStructure_Model::getInstanceFromMenuList($menuModelsList, $selectedModule);

			// Order by pre-defined automation process for QuickCreate.
			uksort($menuModelsList, array('Mycrm_MenuStructure_Model', 'sortMenuItemsByProcess'));

			$companyDetails = Mycrm_CompanyDetails_Model::getInstanceById();
			$companyLogo = $companyDetails->getLogo();

			$viewer->assign('CURRENTDATE', date('Y-n-j'));
			$viewer->assign('MODULE', $selectedModule);
			$viewer->assign('PARENT_MODULE', $request->get('parent'));
            $viewer->assign('VIEW', $request->get('view'));
			$viewer->assign('MENUS', $menuModelsList);
			$viewer->assign('MENU_STRUCTURE', $menuStructure);
			$viewer->assign('MENU_SELECTED_MODULENAME', $selectedModule);
			$viewer->assign('MENU_TOPITEMS_LIMIT', $menuStructure->getLimit());
			$viewer->assign('COMPANY_LOGO',$companyLogo);
			$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

			$homeModuleModel = Mycrm_Module_Model::getInstance('Home');
			$viewer->assign('HOME_MODULE_MODEL', $homeModuleModel);
			$viewer->assign('HEADER_LINKS',$this->getHeaderLinks());
			$viewer->assign('ANNOUNCEMENT', $this->getAnnouncement());
			$viewer->assign('CURRENT_VIEW', $request->get('view'));
			$viewer->assign('SKIN_PATH', Mycrm_Theme::getCurrentUserThemePath());
			$viewer->assign('LANGUAGE', $currentUser->get('language'));

			if($display) {
				$this->preProcessDisplay($request);
			}
		}
	}

	protected function preProcessDisplay(Mycrm_Request $request) {
		$viewer = $this->getViewer($request);
		$viewer->view($this->preProcessTplName($request), $request->getModule());
	}

	public function process(Mycrm_Request $request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$recordModel = Mycrm_Record_Model::getInstanceById($recordId, $moduleName);

		$recordStructureInstance = Mycrm_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Mycrm_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);

		$dayStartPicklistValues = Users_Record_Model::getDayStartsPicklistValues($recordStructureInstance->getStructure());

		$viewer = $this->getViewer($request);
		$viewer->assign("DAY_STARTS", Zend_Json::encode($dayStartPicklistValues));
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());

		return parent::process($request);
	}

    public function getHeaderScripts(Mycrm_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();
        $moduleDetailFile = 'modules.'.$moduleName.'.resources.PreferenceDetail';
        unset($headerScriptInstances[$moduleDetailFile]);

		$jsFileNames = array(
                        "modules.Users.resources.Detail",
                        'modules.'.$moduleName.'.resources.PreferenceDetail',
                        'modules.'.$moduleName.'.resources.PreferenceEdit'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
