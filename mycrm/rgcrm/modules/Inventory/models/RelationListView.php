<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Inventory_RelationListView_Model extends Mycrm_RelationListView_Model {
	
	public function getAddRelationLinks() {
		$relationModel = $this->getRelationModel();
		$addLinkModel = array();

		if(!$relationModel->isAddActionSupported()) {
			return $addLinkModel;
		}
		$relatedModel = $relationModel->getRelationModuleModel();

		if($relatedModel->get('label') == 'Calendar'){
			$addLinkList[] = array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => vtranslate('LBL_ADD_TASK'),
					'linkurl' => $this->getCreateTaskRecordUrl(),
					'linkicon' => '',
			);
		}else{
			$addLinkList = array(
				array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => vtranslate('LBL_ADD')." ".vtranslate($relatedModel->get('label')),
					'linkurl' => $this->getCreateViewUrl(),
					'linkicon' => '',
				)
			);
		}
		
		foreach($addLinkList as $addLink) {
			$addLinkModel[] = Mycrm_Link_Model::getInstanceFromValues($addLink);
		}
		return $addLinkModel;
	}
    
    public function getCreateViewUrl(){
        $createViewUrl = parent::getCreateViewUrl();
		$currentUserModel				= Users_Record_Model::getCurrentUserModel();
        $parentRecordModel				= $this->getParentRecordModel();
		$currencyValue					= $parentRecordModel->get('hdnGrandTotal');
		$parentRecordModelCurrencyId	= $parentRecordModel->get('currency_id');

		if($parentRecordModelCurrencyId == $currentUserModel->get('currency_id')) {
            $amount = CurrencyField::convertToUserFormat($currencyValue, null, true);
        } else {
			$baseCurrencyId = CurrencyField::getDBCurrencyId();
			$allCurrencies = getAllCurrencies();

			foreach ($allCurrencies as $currencyInfo) {
				if ($parentRecordModelCurrencyId == $currencyInfo['currency_id']) {
					$currencyValue = CurrencyField::convertToDollar($currencyValue, $currencyInfo['conversionrate']);
				}
			}

			foreach ($allCurrencies as $currencyInfo) {
				if ($baseCurrencyId == $currencyInfo['currency_id']) {
					$currencyValue = CurrencyField::convertFromMasterCurrency($currencyValue, $currencyInfo['conversionrate']);
				}
			}

            $amount = CurrencyField::convertToUserFormat($currencyValue);
        }

        return $createViewUrl.'&relatedcontact='.$parentRecordModel->get('contact_id').
                           '&relatedorganization='. $parentRecordModel->get('account_id').'&amount='.$amount;
	}

}
?>
