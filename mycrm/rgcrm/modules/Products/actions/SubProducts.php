<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Products_SubProducts_Action extends Mycrm_Action_Controller {

	function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	function process(Mycrm_Request $request) {
		$productId = $request->get('record');
		$productModel = Mycrm_Record_Model::getInstanceById($productId, 'Products');
		$subProducts = $productModel->getSubProducts();
		$values = array();
		foreach($subProducts as $subProduct) {
			$values[$subProduct->getId()] = $subProduct->getName();
		}

		$response = new Mycrm_Response();
		$response->setResult($values);
		$response->emit();
	}
}
