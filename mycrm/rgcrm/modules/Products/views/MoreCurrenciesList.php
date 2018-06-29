<?php

/* +**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * ********************************************************************************** */

class Products_MoreCurrenciesList_View extends Mycrm_IndexAjax_View {

	public function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();

		if (!Users_Privileges_Model::isPermitted($moduleName, 'EditView')) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $moduleName));
		}
	}

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$currencyName = $request->get('currency');

		if (!empty($recordId)) {
			$recordModel = Mycrm_Record_Model::getInstanceById($recordId, $moduleName);
			$priceDetails = $recordModel->getPriceDetails();
		} else {
			$recordModel = Mycrm_Record_Model::getCleanInstance($moduleName);
			$priceDetails = $recordModel->getPriceDetails();

			foreach ($priceDetails as $key => $currencyDetails) {
				if ($currencyDetails['curname'] === $currencyName) {
					$baseCurrencyConversionRate = $currencyDetails['conversionrate'];
					break;
				}
			}

			foreach ($priceDetails as $key => $currencyDetails) {
				if ($currencyDetails['curname'] === $currencyName) {
					$currencyDetails['conversionrate'] = 1;
					$currencyDetails['is_basecurrency'] = 1;
				} else {
					$currencyDetails['conversionrate'] = $currencyDetails['conversionrate'] / $baseCurrencyConversionRate;
					$currencyDetails['is_basecurrency'] = 0;
				}
				$priceDetails[$key] = $currencyDetails;
			}
		}

		$viewer = $this->getViewer($request);

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('PRICE_DETAILS', $priceDetails);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$viewer->view('MoreCurrenciesList.tpl', 'Products');
	}

}