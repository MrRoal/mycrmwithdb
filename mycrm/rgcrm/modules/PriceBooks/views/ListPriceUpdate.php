<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class PriceBooks_ListPriceUpdate_View extends Mycrm_View_Controller {

	function checkPermssion(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	function preProcess(Mycrm_Request $request, $display = true) {
	}

	function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$priceBookId = $request->get('record');
		$relId = $request->get('relid');
		$currentPrice = $request->get('currentPrice');

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE',$moduleName);
		$viewer->assign('PRICEBOOK_ID', $priceBookId);
		$viewer->assign('REL_ID', $relId);
		$viewer->assign('CURRENT_PRICE', $currentPrice);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('ListPriceUpdate.tpl', $moduleName);
	}

	function postProcess(Mycrm_Request $request) {
	}
}

?>
