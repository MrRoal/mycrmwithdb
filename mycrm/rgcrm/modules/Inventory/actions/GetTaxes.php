<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Inventory_GetTaxes_Action extends Mycrm_Action_Controller {

	function process(Mycrm_Request $request) {
		$recordId = $request->get('record');
		$idList = $request->get('idlist');
		$currencyId = $request->get('currency_id');

		$currencies = Inventory_Module_Model::getAllCurrencies();
		$conversionRate = 1;

		$response = new Mycrm_Response();

		if(empty($idList)) {
			$recordModel = Mycrm_Record_Model::getInstanceById($recordId);
			$taxes = $recordModel->getTaxes();
            $listPriceValues = $recordModel->getListPriceValues($recordModel->getId());

			$priceDetails = $recordModel->getPriceDetails();
			foreach ($priceDetails as $currencyDetails) {
				if ($currencyId == $currencyDetails['curid']) {
					$conversionRate = $currencyDetails['conversionrate'];
				}
			}
			$listPrice = (float)$recordModel->get('unit_price') * (float)$conversionRate;

			$response->setResult(array(
									$recordId => array(
										'id'=>$recordId, 'name'=>decode_html($recordModel->getName()),
										'taxes'=>$taxes, 'listprice'=>$listPrice, 'listpricevalues'=>$listPriceValues,
										'description' => decode_html($recordModel->get('description')),
										'quantityInStock' => $recordModel->get('qtyinstock')
									)));
		} else {
			foreach($idList as $id) {
				$recordModel = Mycrm_Record_Model::getInstanceById($id);
				$taxes = $recordModel->getTaxes();
                $listPriceValues = $recordModel->getListPriceValues($recordModel->getId());

				$priceDetails = $recordModel->getPriceDetails();
				foreach ($priceDetails as $currencyDetails) {
					if ($currencyId == $currencyDetails['curid']) {
						$conversionRate = $currencyDetails['conversionrate'];
					}
				}

				$listPrice = (float)$recordModel->get('unit_price') * (float)$conversionRate;
				$info[] = array(
							$id => array(
								'id'=>$id, 'name'=>decode_html($recordModel->getName()),
								'taxes'=>$taxes, 'listprice'=>$listPrice, 'listpricevalues'=>$listPriceValues,
								'description' => decode_html($recordModel->get('description')),
								'quantityInStock' => $recordModel->get('qtyinstock')
							));
			}
			$response->setResult($info);
		}
		$response->emit();
	}
}
