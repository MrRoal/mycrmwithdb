<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * ************************************************************************************/

class Contacts_Module_Model extends Mycrm_Module_Model {
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
	 * Function returns the Calendar Events for the module
	 * @param <Mycrm_Paging_Model> $pagingModel
	 * @return <Array>
	 */
	public function getCalendarActivities($mode, $pagingModel, $user, $recordId = false) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		if (!$user) {
			$user = $currentUser->getId();
		}

		$nowInUserFormat = Mycrm_Datetime_UIType::getDisplayDateValue(date('Y-m-d H:i:s'));
		$nowInDBFormat = Mycrm_Datetime_UIType::getDBDateTimeValue($nowInUserFormat);
		list($currentDate, $currentTime) = explode(' ', $nowInDBFormat);

		$query = "SELECT mycrm_crmentity.crmid, crmentity2.crmid AS contact_id, mycrm_crmentity.smownerid, mycrm_crmentity.setype, mycrm_activity.* FROM mycrm_activity
					INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_activity.activityid
					INNER JOIN mycrm_cntactivityrel ON mycrm_cntactivityrel.activityid = mycrm_activity.activityid
					INNER JOIN mycrm_crmentity AS crmentity2 ON mycrm_cntactivityrel.contactid = crmentity2.crmid AND crmentity2.deleted = 0 AND crmentity2.setype = ?
					LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid";

		$query .= Users_Privileges_Model::getNonAdminAccessControlQuery('Calendar');

		$query .= " WHERE mycrm_crmentity.deleted=0
					AND (mycrm_activity.activitytype NOT IN ('Emails'))
					AND (mycrm_activity.status is NULL OR mycrm_activity.status NOT IN ('Completed', 'Deferred'))
					AND (mycrm_activity.eventstatus is NULL OR mycrm_activity.eventstatus NOT IN ('Held'))";

		if ($recordId) {
			$query .= " AND mycrm_cntactivityrel.contactid = ?";
		} elseif ($mode === 'upcoming') {
			$query .= " AND due_date >= '$currentDate'";
		} elseif ($mode === 'overdue') {
			$query .= " AND due_date < '$currentDate'";
		}

		$params = array($this->getName());
		if ($recordId) {
			array_push($params, $recordId);
		}

		if($user != 'all' && $user != '') {
			if($user === $currentUser->id) {
				$query .= " AND mycrm_crmentity.smownerid = ?";
				array_push($params, $user);
			}
		}

		$query .= " ORDER BY date_start, time_start LIMIT ". $pagingModel->getStartIndex() .", ". ($pagingModel->getPageLimit()+1);

		$result = $db->pquery($query, $params);
		$numOfRows = $db->num_rows($result);
		
		$groupsIds = Mycrm_Util_Helper::getGroupsIdsForUsers($currentUser->getId());
		$activities = array();
		for($i=0; $i<$numOfRows; $i++) {
			$newRow = $db->query_result_rowdata($result, $i);
			$model = Mycrm_Record_Model::getCleanInstance('Calendar');
			$ownerId = $newRow['smownerid'];
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$visibleFields = array('activitytype','date_start','time_start','due_date','time_end','assigned_user_id','visibility','smownerid','crmid');
			$visibility = true;
			if(in_array($ownerId, $groupsIds)) {
				$visibility = false;
			} else if($ownerId == $currentUser->getId()){
				$visibility = false;
			}
			if(!$currentUser->isAdminUser() && $newRow['activitytype'] != 'Task' && $newRow['visibility'] == 'Private' && $ownerId && $visibility) {
				foreach($newRow as $data => $value) {
					if(in_array($data, $visibleFields) != -1) {
						unset($newRow[$data]);
					}
				}
				$newRow['subject'] = vtranslate('Busy','Events').'*';
			}
			if($newRow['activitytype'] == 'Task') {
				unset($newRow['visibility']);
			}
			
			$model->setData($newRow);
			$model->setId($newRow['crmid']);
			$activities[] = $model;
		}
		
		$pagingModel->calculatePageRange($activities);
		if($numOfRows > $pagingModel->getPageLimit()){
			array_pop($activities);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}

		return $activities;
	}

	/**
	 * Function returns query for module record's search
	 * @param <String> $searchValue - part of record name (label column of crmentity table)
	 * @param <Integer> $parentId - parent record id
	 * @param <String> $parentModule - parent module name
	 * @return <String> - query
	 */
	function getSearchRecordsQuery($searchValue, $parentId=false, $parentModule=false) {
		if($parentId && $parentModule == 'Accounts') {
			$query = "SELECT * FROM mycrm_crmentity
						INNER JOIN mycrm_contactdetails ON mycrm_contactdetails.contactid = mycrm_crmentity.crmid
						WHERE deleted = 0 AND mycrm_contactdetails.accountid = $parentId AND label like '%$searchValue%'";
			return $query;
		} else if($parentId && $parentModule == 'Potentials') {
			$query = "SELECT * FROM mycrm_crmentity
						INNER JOIN mycrm_contactdetails ON mycrm_contactdetails.contactid = mycrm_crmentity.crmid
						LEFT JOIN mycrm_contpotentialrel ON mycrm_contpotentialrel.contactid = mycrm_contactdetails.contactid
						LEFT JOIN mycrm_potential ON mycrm_potential.contact_id = mycrm_contactdetails.contactid
						WHERE deleted = 0 AND (mycrm_contpotentialrel.potentialid = $parentId OR mycrm_potential.potentialid = $parentId)
						AND label like '%$searchValue%'";
			
				return $query;
		} else if ($parentId && $parentModule == 'HelpDesk') {
            $query = "SELECT * FROM mycrm_crmentity
                        INNER JOIN mycrm_contactdetails ON mycrm_contactdetails.contactid = mycrm_crmentity.crmid
                        INNER JOIN mycrm_troubletickets ON mycrm_troubletickets.contact_id = mycrm_contactdetails.contactid
                        WHERE deleted=0 AND mycrm_troubletickets.ticketid  = $parentId  AND label like '%$searchValue%'";

            return $query;
        } else if($parentId && $parentModule == 'Campaigns') {
            $query = "SELECT * FROM mycrm_crmentity
                        INNER JOIN mycrm_contactdetails ON mycrm_contactdetails.contactid = mycrm_crmentity.crmid
                        INNER JOIN mycrm_campaigncontrel ON mycrm_campaigncontrel.contactid = mycrm_contactdetails.contactid
                        WHERE deleted=0 AND mycrm_campaigncontrel.campaignid = $parentId AND label like '%$searchValue%'";

            return $query;
        } else if($parentId && $parentModule == 'Vendors') {
            $query = "SELECT mycrm_crmentity.* FROM mycrm_crmentity
                        INNER JOIN mycrm_contactdetails ON mycrm_contactdetails.contactid = mycrm_crmentity.crmid
                        INNER JOIN mycrm_vendorcontactrel ON mycrm_vendorcontactrel.contactid = mycrm_contactdetails.contactid
                        WHERE deleted=0 AND mycrm_vendorcontactrel.vendorid = $parentId AND label like '%$searchValue%'";

            return $query;
        } else if ($parentId && $parentModule == 'Quotes') {
            $query = "SELECT * FROM mycrm_crmentity
                        INNER JOIN mycrm_contactdetails ON mycrm_contactdetails.contactid = mycrm_crmentity.crmid
                        INNER JOIN mycrm_quotes ON mycrm_quotes.contactid = mycrm_contactdetails.contactid
                        WHERE deleted=0 AND mycrm_quotes.quoteid  = $parentId  AND label like '%$searchValue%'";

            return $query;
        } else if ($parentId && $parentModule == 'PurchaseOrder') {
            $query = "SELECT * FROM mycrm_crmentity
                        INNER JOIN mycrm_contactdetails ON mycrm_contactdetails.contactid = mycrm_crmentity.crmid
                        INNER JOIN mycrm_purchaseorder ON mycrm_purchaseorder.contactid = mycrm_contactdetails.contactid
                        WHERE deleted=0 AND mycrm_purchaseorder.purchaseorderid  = $parentId  AND label like '%$searchValue%'";

            return $query;
        } else if ($parentId && $parentModule == 'SalesOrder') {
            $query = "SELECT * FROM mycrm_crmentity
                        INNER JOIN mycrm_contactdetails ON mycrm_contactdetails.contactid = mycrm_crmentity.crmid
                        INNER JOIN mycrm_salesorder ON mycrm_salesorder.contactid = mycrm_contactdetails.contactid
                        WHERE deleted=0 AND mycrm_salesorder.salesorderid  = $parentId  AND label like '%$searchValue%'";

            return $query;
        } else if ($parentId && $parentModule == 'Invoice') {
            $query = "SELECT * FROM mycrm_crmentity
                        INNER JOIN mycrm_contactdetails ON mycrm_contactdetails.contactid = mycrm_crmentity.crmid
                        INNER JOIN mycrm_invoice ON mycrm_invoice.contactid = mycrm_contactdetails.contactid
                        WHERE deleted=0 AND mycrm_invoice.invoiceid  = $parentId  AND label like '%$searchValue%'";

            return $query;
        }

		return parent::getSearchRecordsQuery($parentId, $parentModule);
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
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');

			$query = "SELECT CASE WHEN (mycrm_users.user_name not like '') THEN $userNameSql ELSE mycrm_groups.groupname END AS user_name,
						mycrm_cntactivityrel.contactid, mycrm_seactivityrel.crmid AS parent_id,
						mycrm_crmentity.*, mycrm_activity.activitytype, mycrm_activity.subject, mycrm_activity.date_start, mycrm_activity.time_start,
						mycrm_activity.recurringtype, mycrm_activity.due_date, mycrm_activity.time_end, mycrm_activity.visibility,
						CASE WHEN (mycrm_activity.activitytype = 'Task') THEN (mycrm_activity.status) ELSE (mycrm_activity.eventstatus) END AS status
						FROM mycrm_activity
						INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_activity.activityid
						INNER JOIN mycrm_cntactivityrel ON mycrm_cntactivityrel.activityid = mycrm_activity.activityid
						LEFT JOIN mycrm_seactivityrel ON mycrm_seactivityrel.activityid = mycrm_activity.activityid
						LEFT JOIN mycrm_users ON mycrm_users.id = mycrm_crmentity.smownerid
						LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
							WHERE mycrm_cntactivityrel.contactid = ".$recordId." AND mycrm_crmentity.deleted = 0
								AND mycrm_activity.activitytype <> 'Emails'";

			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);
			}
		} else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule);
		}

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
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		if (in_array($sourceModule, array('Campaigns', 'Potentials', 'Vendors', 'Products', 'Services', 'Emails'))
				|| ($sourceModule === 'Contacts' && $field === 'contact_id' && $record)) {
			switch ($sourceModule) {
				case 'Campaigns'	: $tableName = 'mycrm_campaigncontrel';	$fieldName = 'contactid';	$relatedFieldName ='campaignid';	break;
				case 'Potentials'	: $tableName = 'mycrm_contpotentialrel';	$fieldName = 'contactid';	$relatedFieldName ='potentialid';	break;
				case 'Vendors'		: $tableName = 'mycrm_vendorcontactrel';	$fieldName = 'contactid';	$relatedFieldName ='vendorid';		break;
				case 'Products'		: $tableName = 'mycrm_seproductsrel';		$fieldName = 'crmid';		$relatedFieldName ='productid';		break;
			}

			if ($sourceModule === 'Services') {
				$condition = " mycrm_contactdetails.contactid NOT IN (SELECT relcrmid FROM mycrm_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM mycrm_crmentityrel WHERE relcrmid = '$record') ";
			} elseif ($sourceModule === 'Emails') {
				$condition = ' mycrm_contactdetails.emailoptout = 0';
			} elseif ($sourceModule === 'Contacts' && $field === 'contact_id') {
				$condition = " mycrm_contactdetails.contactid != '$record'";
			} else {
				$condition = " mycrm_contactdetails.contactid NOT IN (SELECT $fieldName FROM $tableName WHERE $relatedFieldName = '$record')";
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
    
    public function getDefaultSearchField(){
        return "lastname";
    }
    
}