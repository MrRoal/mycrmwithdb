<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Products_Module_Model extends Mycrm_Module_Model {

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		$supportedModulesList = array($this->getName(), 'Vendors', 'Leads', 'Accounts', 'Contacts', 'Potentials');
		if (($sourceModule == 'PriceBooks' && $field == 'priceBookRelatedList')
				|| in_array($sourceModule, $supportedModulesList)
				|| in_array($sourceModule, getInventoryModules())) {

			$condition = " mycrm_products.discontinued = 1 ";
			if ($sourceModule === $this->getName()) {
				$condition .= " AND mycrm_products.productid NOT IN (SELECT productid FROM mycrm_seproductsrel WHERE crmid = '$record' UNION SELECT crmid FROM mycrm_seproductsrel WHERE productid = '$record') AND mycrm_products.productid <> '$record' ";
			} elseif ($sourceModule === 'PriceBooks') {
				$condition .= " AND mycrm_products.productid NOT IN (SELECT productid FROM mycrm_pricebookproductrel WHERE pricebookid = '$record') ";
			} elseif ($sourceModule === 'Vendors') {
				$condition .= " AND mycrm_products.vendor_id != '$record' ";
			} elseif (in_array($sourceModule, $supportedModulesList)) {
				$condition .= " AND mycrm_products.productid NOT IN (SELECT productid FROM mycrm_seproductsrel WHERE crmid = '$record')";
			}

			$pos = stripos($listQuery, 'where');
			if ($pos) {
				$split = spliti('where', $listQuery);
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery. ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}

	/**
	 * Function to get Specific Relation Query for this Module
	 * @param <type> $relatedModule
	 * @return <type>
	 */
	public function getSpecificRelationQuery($relatedModule) {
		if ($relatedModule === 'Leads') {
			$specificQuery = 'AND mycrm_leaddetails.converted = 0';
			return $specificQuery;
		}
		return parent::getSpecificRelationQuery($relatedModule);
 	}

	/**
	 * Function to get prices for specified products with specific currency
	 * @param <Integer> $currenctId
	 * @param <Array> $productIdsList
	 * @return <Array>
	 */
	public function getPricesForProducts($currencyId, $productIdsList) {
		return getPricesForProducts($currencyId, $productIdsList, $this->getName());
	}
	
	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return false;
	}
	
	/**
	 * Function searches the records in the module, if parentId & parentModule
	 * is given then searches only those records related to them.
	 * @param <String> $searchValue - Search value
	 * @param <Integer> $parentId - parent recordId
	 * @param <String> $parentModule - parent module name
	 * @return <Array of Mycrm_Record_Model>
	 */
	public function searchRecord($searchValue, $parentId=false, $parentModule=false, $relatedModule=false) {
		if(!empty($searchValue) && empty($parentId) && empty($parentModule) && (in_array($relatedModule, getInventoryModules()))) {
			$matchingRecords = Products_Record_Model::getSearchResult($searchValue, $this->getName());
		}else {
			return parent::searchRecord($searchValue);
		}

		return $matchingRecords;
	}
	
	/**
	 * Function returns query for Product-PriceBooks relation
	 * @param <Mycrm_Record_Model> $recordModel
	 * @param <Mycrm_Record_Model> $relatedModuleModel
	 * @return <String>
	 */
	function get_product_pricebooks($recordModel, $relatedModuleModel) {
		$query = 'SELECT mycrm_pricebook.pricebookid, mycrm_pricebook.bookname, mycrm_pricebook.active, mycrm_crmentity.crmid, 
						mycrm_crmentity.smownerid, mycrm_pricebookproductrel.listprice, mycrm_products.unit_price
					FROM mycrm_pricebook
					INNER JOIN mycrm_pricebookproductrel ON mycrm_pricebook.pricebookid = mycrm_pricebookproductrel.pricebookid
					INNER JOIN mycrm_crmentity on mycrm_crmentity.crmid = mycrm_pricebook.pricebookid
					INNER JOIN mycrm_products on mycrm_products.productid = mycrm_pricebookproductrel.productid
					INNER JOIN mycrm_pricebookcf on mycrm_pricebookcf.pricebookid = mycrm_pricebook.pricebookid
					LEFT JOIN mycrm_users ON mycrm_users.id=mycrm_crmentity.smownerid
					LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid '
					. Users_Privileges_Model::getNonAdminAccessControlQuery($relatedModuleModel->getName()) .'
					WHERE mycrm_products.productid = '.$recordModel->getId().' and mycrm_crmentity.deleted = 0';
					
		return $query;
	}
}