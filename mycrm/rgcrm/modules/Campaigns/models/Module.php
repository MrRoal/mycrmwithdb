<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Campaigns_Module_Model extends Mycrm_Module_Model {

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
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return false;
	}

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		if (in_array($sourceModule, array('Leads', 'Accounts', 'Contacts'))) {
			switch($sourceModule) {
				case 'Leads'		: $tableName = 'mycrm_campaignleadrel';		$relatedFieldName = 'leadid';		break;
				case 'Accounts'		: $tableName = 'mycrm_campaignaccountrel';		$relatedFieldName = 'accountid';	break;
				case 'Contacts'		: $tableName = 'mycrm_campaigncontrel';		$relatedFieldName = 'contactid';	break;
			}

			$condition = " mycrm_campaign.campaignid NOT IN (SELECT campaignid FROM $tableName WHERE $relatedFieldName = '$record')";
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
}