<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Leads_Module_Model extends Mycrm_Module_Model {
	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Mycrm_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$links = parent::getSideBarLinks($linkParams);

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
			$links['SIDEBARLINK'][] = Mycrm_Link_Model::getInstanceFromValues($quickLink);
		}
		
		return $links;
	}

    /**
	 * Function returns Settings Links
	 * @return Array
	 */
	public function getSettingLinks() {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$settingLinks = parent::getSettingLinks();
		
		if($currentUserModel->isAdminUser()) {
			$settingLinks[] = array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'LBL_CUSTOM_FIELD_MAPPING',
					'linkurl' => 'index.php?parent=Settings&module=Leads&view=MappingDetail',
					'linkicon' => '');
			
		}
		return $settingLinks;
	}

    /**
    * Function returns deleted records condition
    */
    public function getDeletedRecordCondition() {
       return 'mycrm_crmentity.deleted = 0 AND mycrm_leaddetails.converted = 0';
    }

    /**
	 * Function to get the list of recently visisted records
	 * @param <Number> $limit
	 * @return <Array> - List of Mycrm_Record_Model or Module Specific Record Model instances
	 */
	public function getRecentRecords($limit=10) {
		$db = PearDatabase::getInstance();

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
        $deletedCondition = $this->getDeletedRecordCondition();
		$query = 'SELECT * FROM mycrm_crmentity '.
            ' INNER JOIN mycrm_leaddetails ON
                mycrm_leaddetails.leadid = mycrm_crmentity.crmid
                WHERE setype=? AND '.$deletedCondition.' AND modifiedby = ? ORDER BY modifiedtime DESC LIMIT ?';
		$params = array($this->get('name'), $currentUserModel->id, $limit);
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);

		$recentRecords = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			$recentRecords[$row['id']] = $this->getRecordFromArray($row);
		}
		return $recentRecords;
	}

	/**
	 * Function returns the Number of Leads created per week
	 * @param type $data
	 * @return <Array>
	 */
	public function getLeadsCreated($owner, $dateFilter) {
		$db = PearDatabase::getInstance();

		$ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
		if(!empty($ownerSql)) {
			$ownerSql = ' AND '.$ownerSql;
		}
		
		$params = array();
		if(!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start']. ' 00:00:00';
			$params[] = $dateFilter['end']. ' 23:59:59';
		}

		$result = $db->pquery('SELECT COUNT(*) AS count, date(createdtime) AS time FROM mycrm_leaddetails
						INNER JOIN mycrm_crmentity ON mycrm_leaddetails.leadid = mycrm_crmentity.crmid
						AND deleted=0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()).$ownerSql.' '.$dateFilterSql.' AND converted = 0 GROUP BY week(createdtime)',
					$params);

		$response = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$response[$i][0] = $row['count'];
			$response[$i][1] = $row['time'];
		}
		return $response;
	}

	/**
	 * Function returns Leads grouped by Status
	 * @param type $data
	 * @return <Array>
	 */
	public function getLeadsByStatus($owner,$dateFilter) {
		$db = PearDatabase::getInstance();

		$ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
		if(!empty($ownerSql)) {
			$ownerSql = ' AND '.$ownerSql;
		}
		
		$params = array();
		if(!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start']. ' 00:00:00';
			$params[] = $dateFilter['end']. ' 23:59:59';
		}

		$result = $db->pquery('SELECT COUNT(*) as count, CASE WHEN mycrm_leadstatus.leadstatus IS NULL OR mycrm_leadstatus.leadstatus = "" THEN "" ELSE 
						mycrm_leadstatus.leadstatus END AS leadstatusvalue FROM mycrm_leaddetails 
						INNER JOIN mycrm_crmentity ON mycrm_leaddetails.leadid = mycrm_crmentity.crmid
						AND deleted=0 AND converted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()). $ownerSql .' '.$dateFilterSql.
						'INNER JOIN mycrm_leadstatus ON mycrm_leaddetails.leadstatus = mycrm_leadstatus.leadstatus 
						GROUP BY leadstatusvalue ORDER BY mycrm_leadstatus.sortorderid', $params);

		$response = array();
		
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$response[$i][0] = $row['count'];
			$leadStatusVal = $row['leadstatusvalue'];
			if($leadStatusVal == '') {
				$leadStatusVal = 'LBL_BLANK';
			}
			$response[$i][1] = vtranslate($leadStatusVal, $this->getName());
			$response[$i][2] = $leadStatusVal;
		}
		return $response;
	}

	/**
	 * Function returns Leads grouped by Source
	 * @param type $data
	 * @return <Array>
	 */
	public function getLeadsBySource($owner,$dateFilter) {
		$db = PearDatabase::getInstance();

		$ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
		if(!empty($ownerSql)) {
			$ownerSql = ' AND '.$ownerSql;
		}
		
		$params = array();
		if(!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start']. ' 00:00:00';
			$params[] = $dateFilter['end']. ' 23:59:59';
		}
		
		$result = $db->pquery('SELECT COUNT(*) as count, CASE WHEN mycrm_leaddetails.leadsource IS NULL OR mycrm_leaddetails.leadsource = "" THEN "" 
						ELSE mycrm_leaddetails.leadsource END AS leadsourcevalue FROM mycrm_leaddetails 
						INNER JOIN mycrm_crmentity ON mycrm_leaddetails.leadid = mycrm_crmentity.crmid
						AND deleted=0 AND converted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()). $ownerSql .' '.$dateFilterSql.
						'INNER JOIN mycrm_leadsource ON mycrm_leaddetails.leadsource = mycrm_leadsource.leadsource 
						GROUP BY leadsourcevalue ORDER BY mycrm_leadsource.sortorderid', $params);
		
		$response = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$response[$i][0] = $row['count'];
			$leadSourceVal =  $row['leadsourcevalue'];
			if($leadSourceVal == '') {
				$leadSourceVal = 'LBL_BLANK';
			}
			$response[$i][1] = vtranslate($leadSourceVal, $this->getName());
			$response[$i][2] = $leadSourceVal;
		}
		return $response;
	}

	/**
	 * Function returns Leads grouped by Industry
	 * @param type $data
	 * @return <Array>
	 */
	public function getLeadsByIndustry($owner,$dateFilter) {
		$db = PearDatabase::getInstance();

		$ownerSql = $this->getOwnerWhereConditionForDashBoards($owner);
		if(!empty($ownerSql)) {
			$ownerSql = ' AND '.$ownerSql;
		}
		
		$params = array();
		if(!empty($dateFilter)) {
			$dateFilterSql = ' AND createdtime BETWEEN ? AND ? ';
			//client is not giving time frame so we are appending it
			$params[] = $dateFilter['start']. ' 00:00:00';
			$params[] = $dateFilter['end']. ' 23:59:59';
		}
		
		$result = $db->pquery('SELECT COUNT(*) as count, CASE WHEN mycrm_leaddetails.industry IS NULL OR mycrm_leaddetails.industry = "" THEN "" 
						ELSE mycrm_leaddetails.industry END AS industryvalue FROM mycrm_leaddetails 
						INNER JOIN mycrm_crmentity ON mycrm_leaddetails.leadid = mycrm_crmentity.crmid
						AND deleted=0 AND converted = 0 '.Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName()). $ownerSql .' '.$dateFilterSql.'
						INNER JOIN mycrm_industry ON mycrm_leaddetails.industry = mycrm_industry.industry 
						GROUP BY industryvalue ORDER BY mycrm_industry.sortorderid', $params);
		
		$response = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$response[$i][0] = $row['count'];
			$industyValue = $row['industryvalue'];
			if($industyValue == '') {
				$industyValue = 'LBL_BLANK';
			}
			$response[$i][1] = vtranslate($industyValue, $this->getName());
			$response[$i][2] = $industyValue;
		}
		return $response;
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
								AND mycrm_seactivityrel.crmid = ".$recordId;

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
	 * Function to get Converted Information for selected records
	 * @param <array> $recordIdsList
	 * @return <array> converted Info
	 */
	public static function getConvertedInfo($recordIdsList = array()) {
		$convertedInfo = array();
		if ($recordIdsList) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery("SELECT converted FROM mycrm_leaddetails WHERE leadid IN (".generateQuestionMarks($recordIdsList).")", $recordIdsList);
			$numOfRows = $db->num_rows($result);

			for ($i=0; $i<$numOfRows; $i++) {
				$convertedInfo[$recordIdsList[$i]] = $db->query_result($result, $i, 'converted');
			}
		}
		return $convertedInfo;
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
		if (in_array($sourceModule, array('Campaigns', 'Products', 'Services', 'Emails'))) {
			switch ($sourceModule) {
				case 'Campaigns'	: $tableName = 'mycrm_campaignleadrel';	$fieldName = 'leadid';	$relatedFieldName ='campaignid';	break;
				case 'Products'		: $tableName = 'mycrm_seproductsrel';		$fieldName = 'crmid';		$relatedFieldName ='productid';		break;
			}

			if ($sourceModule === 'Services') {
				$condition = " mycrm_leaddetails.leadid NOT IN (SELECT relcrmid FROM mycrm_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM mycrm_crmentityrel WHERE relcrmid = '$record') ";
			} elseif ($sourceModule === 'Emails') {
				$condition = ' mycrm_leaddetails.emailoptout = 0';
			} else {
				$condition = " mycrm_leaddetails.leadid NOT IN (SELECT $fieldName FROM $tableName WHERE $relatedFieldName = '$record')";
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