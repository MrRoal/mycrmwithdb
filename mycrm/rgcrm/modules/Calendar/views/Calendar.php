<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_Calendar_View extends Mycrm_Index_View {

	public function preProcess(Mycrm_Request $request, $display = true) {
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $request->getModule());

		parent::preProcess($request, false);
		if($display) {
			$this->preProcessDisplay($request);
		}
	}

	protected function preProcessTplName(Mycrm_Request $request) {
		return 'CalendarViewPreProcess.tpl';
	}

	public function getHeaderScripts(Mycrm_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$jsFileNames = array(
			"modules.Calendar.resources.CalendarView",
			"~/libraries/fullcalendar/fullcalendar.js",
			"~/libraries/jquery/colorpicker/js/colorpicker.js"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function getHeaderCss(Mycrm_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request);


		$cssFileNames = array(
			'~/libraries/fullcalendar/fullcalendar.css',
			'~/libraries/fullcalendar/fullcalendar-bootstrap.css',
			'~/libraries/jquery/colorpicker/css/colorpicker.css'
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);

		return $headerCssInstances;
	}

	public function process(Mycrm_Request $request) {
		$mode = $request->getMode();
		if($mode == 'settings'){
			$this->getCalendarSettings($request);
		}
		$viewer = $this->getViewer($request);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if($request->getMode() == 'Settings'){
			return $this->getCalendarSettings($request);
		}
		$viewer->assign('CURRENT_USER', $currentUserModel);

		$viewer->view('CalendarView.tpl', $request->getModule());
	}
	
	/*
	 * Function to get the calendar settings view
	 */
	public function getCalendarSettings(Mycrm_Request $request){
		
		$viewer = $this->getViewer($request);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$module = $request->getModule();
		$detailViewModel = Mycrm_DetailView_Model::getInstance('Users', $currentUserModel->id);
		$userRecordStructure = Mycrm_RecordStructure_Model::getInstanceFromRecordModel($detailViewModel->getRecord(), Mycrm_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
		$recordStructure = $userRecordStructure->getStructure();
		$allUsers = Users_Record_Model::getAll(true);
		$sharedUsers = Calendar_Module_Model::getCaledarSharedUsers($currentUserModel->id);
		$sharedType = Calendar_Module_Model::getSharedType($currentUserModel->id);
		$dayStartPicklistValues = Users_Record_Model::getDayStartsPicklistValues($recordStructure);
		
        $hourFormatFeildModel = $recordStructure['LBL_CALENDAR_SETTINGS']['hour_format'];
        
		$viewer->assign('CURRENTUSER_MODEL',$currentUserModel);
		$viewer->assign('SHAREDUSERS', $sharedUsers);
		$viewer->assign("DAY_STARTS", Zend_Json::encode($dayStartPicklistValues));
		$viewer->assign('ALL_USERS',$allUsers);
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$viewer->assign('MODULE',$module);
		$viewer->assign('RECORD', $currentUserModel->id);
		$viewer->assign('SHAREDTYPE', $sharedType);
        $viewer->assign('HOUR_FORMAT_VALUE', $hourFormatFeildModel->get('fieldvalue'));
		
		$viewer->view('CalendarSettings.tpl', $request->getModule());
	}
	
	
}