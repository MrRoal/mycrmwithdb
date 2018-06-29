<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class PriceBooks_Module_Model extends Mycrm_Module_Model {

	/**
	 * Function returns query for PriceBook-Product relation
	 * @param <Mycrm_Record_Model> $recordModel
	 * @param <Mycrm_Record_Model> $relatedModuleModel
	 * @return <String>
	 */
	function get_pricebook_products($recordModel, $relatedModuleModel) {
		$query = 'SELECT mycrm_products.productid, mycrm_products.productname, mycrm_products.productcode, mycrm_products.commissionrate,
						mycrm_products.qty_per_unit, mycrm_products.unit_price, mycrm_crmentity.crmid, mycrm_crmentity.smownerid,
						mycrm_pricebookproductrel.listprice
				FROM mycrm_products
				INNER JOIN mycrm_pricebookproductrel ON mycrm_products.productid = mycrm_pricebookproductrel.productid
				INNER JOIN mycrm_crmentity on mycrm_crmentity.crmid = mycrm_products.productid
				INNER JOIN mycrm_pricebook on mycrm_pricebook.pricebookid = mycrm_pricebookproductrel.pricebookid
				INNER JOIN mycrm_productcf on mycrm_productcf.productid = mycrm_products.productid
				LEFT JOIN mycrm_users ON mycrm_users.id=mycrm_crmentity.smownerid
				LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid '
				. Users_Privileges_Model::getNonAdminAccessControlQuery($relatedModuleModel->getName()) .'
				WHERE mycrm_pricebook.pricebookid = '.$recordModel->getId().' and mycrm_crmentity.deleted = 0';
		return $query;
	}


	/**
	 * Function returns query for PriceBooks-Services Relationship
	 * @param <Mycrm_Record_Model> $recordModel
	 * @param <Mycrm_Record_Model> $relatedModuleModel
	 * @return <String>
	 */
	function get_pricebook_services($recordModel, $relatedModuleModel) {
		$query = 'SELECT mycrm_service.serviceid, mycrm_service.servicename, mycrm_service.service_no, mycrm_service.commissionrate,
					mycrm_service.qty_per_unit, mycrm_service.unit_price, mycrm_crmentity.crmid, mycrm_crmentity.smownerid,
					mycrm_pricebookproductrel.listprice
			FROM mycrm_service
			INNER JOIN mycrm_pricebookproductrel on mycrm_service.serviceid = mycrm_pricebookproductrel.productid
			INNER JOIN mycrm_crmentity on mycrm_crmentity.crmid = mycrm_service.serviceid
			INNER JOIN mycrm_pricebook on mycrm_pricebook.pricebookid = mycrm_pricebookproductrel.pricebookid
			INNER JOIN mycrm_servicecf on mycrm_servicecf.serviceid = mycrm_service.serviceid
			LEFT JOIN mycrm_users ON mycrm_users.id=mycrm_crmentity.smownerid
			LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid '
			. Users_Privileges_Model::getNonAdminAccessControlQuery($relatedModuleModel->getName()) .'
			WHERE mycrm_pricebook.pricebookid = '.$recordModel->getId().' and mycrm_crmentity.deleted = 0';
		return $query;
	}

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery, $currencyId = false) {
		$relatedModulesList = array('Products', 'Services');
		if (in_array($sourceModule, $relatedModulesList)) {
			$pos = stripos($listQuery, ' where ');
			if ($currencyId && in_array($field, array('productid', 'serviceid'))) {
				$condition = " mycrm_pricebook.pricebookid IN (SELECT pricebookid FROM mycrm_pricebookproductrel WHERE productid = $record)
								AND mycrm_pricebook.currency_id = $currencyId AND mycrm_pricebook.active = 1";
			} else if($field == 'productsRelatedList') {
				$condition = "mycrm_pricebook.pricebookid NOT IN (SELECT pricebookid FROM mycrm_pricebookproductrel WHERE productid = $record)
								AND mycrm_pricebook.active = 1";
			}
			if ($pos) {
				$split = spliti(' where ', $listQuery);
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery . ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}
	
	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return false;
	}
	
	/**
	 * Funtion that returns fields that will be showed in the record selection popup
	 * @return <Array of fields>
	 */
	public function getPopupViewFieldsList() {
		$popupFileds = $this->getSummaryViewFieldsList();
		$reqPopUpFields = array('Currency' => 'currency_id'); 
		foreach ($reqPopUpFields as $fieldLabel => $fieldName) {
			$fieldModel = Mycrm_Field_Model::getInstance($fieldName,$this); 
			if ($fieldModel->getPermissions('readwrite')) { 
				$popupFileds[$fieldName] = $fieldModel; 
			}
		}
		return array_keys($popupFileds);
	}
}