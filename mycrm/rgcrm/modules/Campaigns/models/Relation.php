<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Campaigns_Relation_Model extends Mycrm_Relation_Model {

	/**
	 * Function to get Email enabled modules list for detail view of record
	 * @return <array> List of modules
	 */
	public function getEmailEnabledModulesInfoForDetailView() {
		return array(
				'Leads' => array('fieldName' => 'leadid', 'tableName' => 'mycrm_campaignleadrel'),
				'Accounts' => array('fieldName' => 'accountid', 'tableName' => 'mycrm_campaignaccountrel'),
				'Contacts' => array('fieldName' => 'contactid', 'tableName' => 'mycrm_campaigncontrel')
		);
	}

	/**
	 * Function to get Campaigns Relation status values
	 * @return <array> List of status values
	 */
	public function getCampaignRelationStatusValues() {
		$statusValues = array();
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT campaignrelstatusid, campaignrelstatus FROM mycrm_campaignrelstatus", array());
		$numOfRows = $db->num_rows($result);

		for ($i=0; $i<$numOfRows; $i++) {
			$statusValues[$db->query_result($result, $i, 'campaignrelstatusid')] = $db->query_result($result, $i, 'campaignrelstatus');
		}
		return $statusValues;
	}

	/**
	 * Function to update the status of relation
	 * @param <Number> Campaign record id
	 * @param <array> $statusDetails
	 */
	public function updateStatus($sourceRecordId, $statusDetails = array()) {
		if ($sourceRecordId && $statusDetails) {
			$relatedModuleName = $this->getRelationModuleModel()->getName();
			$emailEnabledModulesInfo = $this->getEmailEnabledModulesInfoForDetailView();

			if (array_key_exists($relatedModuleName, $emailEnabledModulesInfo)) {
				$fieldName = $emailEnabledModulesInfo[$relatedModuleName]['fieldName'];
				$tableName = $emailEnabledModulesInfo[$relatedModuleName]['tableName'];
				$db = PearDatabase::getInstance();

				$updateQuery = "UPDATE $tableName SET campaignrelstatusid = CASE $fieldName ";
				foreach ($statusDetails as $relatedRecordId => $status) {
					$updateQuery .= " WHEN $relatedRecordId THEN $status ";
				}
				$updateQuery .= "ELSE campaignrelstatusid END WHERE campaignid = ?";
				$db->pquery($updateQuery, array($sourceRecordId));
			}
		}
	}
}