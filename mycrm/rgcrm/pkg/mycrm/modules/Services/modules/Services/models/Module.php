<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Services_Module_Model extends Products_Module_Model {
	
	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		$supportedModulesList = array('Leads', 'Accounts', 'HelpDesk', 'Potentials');
		if (($sourceModule == 'PriceBooks' && $field == 'priceBookRelatedList')
				|| in_array($sourceModule, $supportedModulesList)
				|| in_array($sourceModule, getInventoryModules())) {

			$condition = " mycrm_service.discontinued = 1 ";

			if ($sourceModule == 'PriceBooks' && $field == 'priceBookRelatedList') {
				$condition .= " AND mycrm_service.serviceid NOT IN (SELECT productid FROM mycrm_pricebookproductrel WHERE pricebookid = '$record') ";
			} elseif (in_array($sourceModule, $supportedModulesList)) {
				$condition .= " AND mycrm_service.serviceid NOT IN (SELECT relcrmid FROM mycrm_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM mycrm_crmentityrel WHERE relcrmid = '$record') ";
			}

			$pos = stripos($listQuery, 'where');
			if ($pos) {
				$split = spliti('where', $listQuery);
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery . ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}
	
	/**
	 * Function returns query for Services-PriceBooks Relationship
	 * @param <Mycrm_Record_Model> $recordModel
	 * @param <Mycrm_Record_Model> $relatedModuleModel
	 * @return <String>
	 */
	function get_service_pricebooks($recordModel, $relatedModuleModel) {
		$query = 'SELECT mycrm_pricebook.pricebookid, mycrm_pricebook.bookname, mycrm_pricebook.active, mycrm_crmentity.crmid, 
						mycrm_crmentity.smownerid, mycrm_pricebookproductrel.listprice, mycrm_service.unit_price
					FROM mycrm_pricebook
					INNER JOIN mycrm_pricebookproductrel ON mycrm_pricebook.pricebookid = mycrm_pricebookproductrel.pricebookid
					INNER JOIN mycrm_crmentity on mycrm_crmentity.crmid = mycrm_pricebook.pricebookid
					INNER JOIN mycrm_service on mycrm_service.serviceid = mycrm_pricebookproductrel.productid
					INNER JOIN mycrm_pricebookcf on mycrm_pricebookcf.pricebookid = mycrm_pricebook.pricebookid
					LEFT JOIN mycrm_users ON mycrm_users.id=mycrm_crmentity.smownerid
					LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid '
					. Users_Privileges_Model::getNonAdminAccessControlQuery($relatedModuleModel->getName()) .'
					WHERE mycrm_service.serviceid = '.$recordModel->getId().' and mycrm_crmentity.deleted = 0';
		
		return $query;
	}
}