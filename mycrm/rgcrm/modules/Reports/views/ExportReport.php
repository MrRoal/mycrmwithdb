<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Reports_ExportReport_View extends Mycrm_View_Controller {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('GetPrintReport');
		$this->exposeMethod('GetXLS');
		$this->exposeMethod('GetCSV');
	}

	function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Reports_Module_Model::getInstance($moduleName);

		$record = $request->get('record');
		$reportModel = Reports_Record_Model::getCleanInstance($record);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	function preProcess(Mycrm_Request $request) {
		return false;
	}

	function postProcess(Mycrm_Request $request) {
		return false;
	}

	function process(Mycrm_request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	/**
	 * Function exports the report in a Excel sheet
	 * @param Mycrm_Request $request
	 */
	function GetXLS(Mycrm_Request $request) {
		$recordId = $request->get('record');
		$reportModel = Reports_Record_Model::getInstanceById($recordId);
        $reportModel->set('advancedFilter', $request->get('advanced_filter'));
		$reportModel->getReportXLS();
	}

	/**
	 * Function exports report in a CSV file
	 * @param Mycrm_Request $request
	 */
	function GetCSV(Mycrm_Request $request) {
		$recordId = $request->get('record');
		$reportModel = Reports_Record_Model::getInstanceById($recordId);
        $reportModel->set('advancedFilter', $request->get('advanced_filter'));
		$reportModel->getReportCSV();
	}

	/**
	 * Function displays the report in printable format
	 * @param Mycrm_Request $request
	 */
	function GetPrintReport(Mycrm_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$recordId = $request->get('record');
		$reportModel = Reports_Record_Model::getInstanceById($recordId);
        $reportModel->set('advancedFilter', $request->get('advanced_filter'));
		$printData = $reportModel->getReportPrint();

		$viewer->assign('REPORT_NAME', $reportModel->getName());
		$viewer->assign('PRINT_DATA', $printData['data'][0]);
		$viewer->assign('TOTAL', $printData['total']);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('ROW', $printData['data'][1]);

		$viewer->view('PrintReport.tpl', $moduleName);
	}
}