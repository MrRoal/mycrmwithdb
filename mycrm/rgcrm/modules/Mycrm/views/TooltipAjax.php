<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Mycrm_TooltipAjax_View extends Mycrm_PopupAjax_View {

	function preProcess(Mycrm_Request $request) {
		return true;
	}

	function postProcess(Mycrm_Request $request) {
		return true;
	}

	function process (Mycrm_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();

		$this->initializeListViewContents($request, $viewer);

		echo $viewer->view('TooltipContents.tpl', $moduleName, true);
	}
	
	public function initializeListViewContents(Mycrm_Request $request, Mycrm_Viewer $viewer) {
		$moduleName = $this->getModule($request);
		
		$recordId = $request->get('record');
		$tooltipViewModel = Mycrm_TooltipView_Model::getInstance($moduleName, $recordId);

		$viewer->assign('MODULE', $moduleName);

		$viewer->assign('MODULE_MODEL', $tooltipViewModel->getRecord()->getModule());
		
		$viewer->assign('TOOLTIP_FIELDS', $tooltipViewModel->getFields());
		$viewer->assign('RECORD', $tooltipViewModel->getRecord());
		$viewer->assign('RECORD_STRUCTURE', $tooltipViewModel->getStructure());

		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
	}

}