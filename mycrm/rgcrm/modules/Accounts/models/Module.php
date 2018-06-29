<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * ************************************************************************************/

class Accounts_Module_Model extends Mycrm_Module_Model {

	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Mycrm_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$parentQuickLinks = parent::getSideBarLinks($linkParams);

		$quickLink = array(
			'linktype' => 'SIDEBARLINK',
			'linklabel' => 'LBL_DASHBOARD',
			'linkurl' => $this->getDashBoardUrl(),
			'linkicon' => '',
		);

		//Check profile permissions for Dashboards
		$moduleModel = Mycrm_Module_Model::getInstance('Dashboard');
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if($permission) {
			$parentQuickLinks['SIDEBARLINK'][] = Mycrm_Link_Model::getInstanceFromValues($quickLink);
		}
		
		return $parentQuickLinks;
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
		if (($sourceModule == 'Accounts' && $field == 'account_id' && $record)
				|| in_array($sourceModule, array('Campaigns', 'Products', 'Services', 'Emails'))) {

			if ($sourceModule === 'Campaigns') {
				$condition = " mycrm_account.accountid NOT IN (SELECT accountid FROM mycrm_campaignaccountrel WHERE campaignid = '$record')";
			} elseif ($sourceModule === 'Products') {
				$condition = " mycrm_account.accountid NOT IN (SELECT crmid FROM mycrm_seproductsrel WHERE productid = '$record')";
			} elseif ($sourceModule === 'Services') {
				$condition = " mycrm_account.accountid NOT IN (SELECT relcrmid FROM mycrm_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM mycrm_crmentityrel WHERE relcrmid = '$record') ";
			} elseif ($sourceModule === 'Emails') {
				$condition = ' mycrm_account.emailoptout = 0';
			} else {
				$condition = " mycrm_account.accountid != '$record'";
			}

			$position = stripos($listQuery, 'where');
			if($position) {
				$split = spliti('where', $listQuery);
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery. ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}

	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Mycrm_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule) {
		if ($functionName === 'get_activities') {
			$focus = CRMEntity::getInstance($this->getName());
			$focus->id = $recordId;
			$entityIds = $focus->getRelatedContactsIds();
			$entityIds = implode(',', $entityIds);

			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');

			$query = "SELECT CASE WHEN (mycrm_users.user_name not like '') THEN $userNameSql ELSE mycrm_groups.groupname END AS user_name,
						mycrm_crmentity.*, mycrm_activity.activitytype, mycrm_activity.subject, mycrm_activity.date_start, mycrm_activity.time_start,
						mycrm_activity.recurringtype, mycrm_activity.due_date, mycrm_activity.time_end, mycrm_activity.visibility, mycrm_seactivityrel.crmid AS parent_id,
						CASE WHEN (mycrm_activity.activitytype = 'Task') THEN (mycrm_activity.status) ELSE (mycrm_activity.eventstatus) END AS status
						FROM mycrm_activity
						INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_activity.activityid
						LEFT JOIN mycrm_seactivityrel ON mycrm_seactivityrel.activityid = mycrm_activity.activityid
						LEFT JOIN mycrm_cntactivityrel ON mycrm_cntactivityrel.activityid = mycrm_activity.activityid
						LEFT JOIN mycrm_users ON mycrm_users.id = mycrm_crmentity.smownerid
						LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
							WHERE mycrm_crmentity.deleted = 0 AND mycrm_activity.activitytype <> 'Emails'
								AND (mycrm_seactivityrel.crmid = ".$recordId;
			if($entityIds) {
				$query .= " OR mycrm_cntactivityrel.contactid IN (".$entityIds."))";
			} else {
				$query .= ")";
			}

			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);
			}

			// There could be more than one contact for an activity.
			$query .= ' GROUP BY mycrm_activity.activityid';
		} else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule);
		}

		return $query;
	}
}
