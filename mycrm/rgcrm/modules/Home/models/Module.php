<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Home_Module_Model extends Mycrm_Module_Model {

	/**
	 * Function returns the default view for the Home module
	 * @return <String>
	 */
	public function getDefaultViewName() {
		return 'DashBoard';
	}

	/**
	 * Function returns latest comments across CRM
	 * @param <Mycrm_Paging_Model> $pagingModel
	 * @return <Array>
	 */
	public function getComments($pagingModel) {
		$db = PearDatabase::getInstance();

		$nonAdminAccessQuery = Users_Privileges_Model::getNonAdminAccessControlQuery('ModComments');

		$result = $db->pquery('SELECT *, mycrm_crmentity.createdtime AS createdtime, mycrm_crmentity.smownerid AS smownerid,
						crmentity2.crmid AS parentId, crmentity2.setype AS parentModule FROM mycrm_modcomments
						INNER JOIN mycrm_crmentity ON mycrm_modcomments.modcommentsid = mycrm_crmentity.crmid
							AND mycrm_crmentity.deleted = 0
						INNER JOIN mycrm_crmentity crmentity2 ON mycrm_modcomments.related_to = crmentity2.crmid
							AND crmentity2.deleted = 0
						 '.$nonAdminAccessQuery.'
						ORDER BY mycrm_crmentity.crmid DESC LIMIT ?, ?',
				array($pagingModel->getStartIndex(), $pagingModel->getPageLimit()));

		$comments = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			if(Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['related_to'])){
				$commentModel = Mycrm_Record_Model::getCleanInstance('ModComments');
				$commentModel->setData($row);
				$time = $commentModel->get('createdtime');
				$comments[$time] = $commentModel;
			}
		}

		return $comments;
	}

        /**
	 * Function returns part of the query to  fetch only  activity
	 * @param <String> $type - comments, updates or all
	 * @return <String> $query 
	 */
          public function getActivityQuery($type)
        {
             if($type == 'updates'){
                 $query=' AND module != "ModComments" ';
		return $query;	
             }
            
        }

        
	/**
	 * Function returns comments and recent activities across CRM
	 * @param <Mycrm_Paging_Model> $pagingModel
	 * @param <String> $type - comments, updates or all
	 * @return <Array>
	 */
	public function getHistory($pagingModel, $type=false) {
		if(empty($type)) {
			$type = 'all';
		}
		//TODO: need to handle security
		$comments = array();
		if( $type == 'comments') {
			$modCommentsModel = Mycrm_Module_Model::getInstance('ModComments'); 
			if($modCommentsModel->isPermitted('DetailView')){
				$comments = $this->getComments($pagingModel);
			}
			if($type == 'comments') {
				return $comments;
			}
		}
		//As getComments api is used to get comment infomation,no need of getting
		//comment information again,so avoiding from modtracker
               //updateActivityQuery api is used to update a query to fetch a only activity
		
                else if($type == 'updates' || $type == 'all' )
                {
                     $db = PearDatabase::getInstance();
                     $queryforActivity= $this->getActivityQuery($type);
                     $result = $db->pquery('SELECT mycrm_modtracker_basic.*
								FROM mycrm_modtracker_basic
								INNER JOIN mycrm_crmentity ON mycrm_modtracker_basic.crmid = mycrm_crmentity.crmid
								AND deleted = 0 ' .  $queryforActivity .'
								ORDER BY mycrm_modtracker_basic.id DESC LIMIT ?, ?',array($pagingModel->getStartIndex(), $pagingModel->getPageLimit()));

                     $history = array();
		     for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$moduleName = $row['module'];
			$recordId = $row['crmid'];
			if(Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordId)){
				$modTrackerRecorModel = new ModTracker_Record_Model();
				$modTrackerRecorModel->setData($row)->setParent($recordId, $moduleName);
				$time = $modTrackerRecorModel->get('changedon');
				$history[$time] = $modTrackerRecorModel;
			      }
		    }  
                    
                    return $history;
                }
		return false;
	}

	/**
	 * Function returns the Calendar Events for the module
	 * @param <String> $mode - upcoming/overdue mode
	 * @param <Mycrm_Paging_Model> $pagingModel - $pagingModel
	 * @param <String> $user - all/userid
	 * @param <String> $recordId - record id
	 * @return <Array>
	 */
	function getCalendarActivities($mode, $pagingModel, $user, $recordId = false) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		if (!$user) {
			$user = $currentUser->getId();
		}

		$nowInUserFormat = Mycrm_Datetime_UIType::getDisplayDateTimeValue(date('Y-m-d H:i:s'));
		$nowInDBFormat = Mycrm_Datetime_UIType::getDBDateTimeValue($nowInUserFormat);
		list($currentDate, $currentTime) = explode(' ', $nowInDBFormat);

		$query = "SELECT mycrm_crmentity.crmid, mycrm_crmentity.smownerid, mycrm_crmentity.setype, mycrm_activity.* FROM mycrm_activity
					INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_activity.activityid
					LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid";

		$query .= Users_Privileges_Model::getNonAdminAccessControlQuery('Calendar');

		$query .= " WHERE mycrm_crmentity.deleted=0
					AND (mycrm_activity.activitytype NOT IN ('Emails'))
					AND (mycrm_activity.status is NULL OR mycrm_activity.status NOT IN ('Completed', 'Deferred'))
					AND (mycrm_activity.eventstatus is NULL OR mycrm_activity.eventstatus NOT IN ('Held'))";

		if ($mode === 'upcoming') {
			$query .= " AND CASE WHEN mycrm_activity.activitytype='Task' THEN due_date >= '$currentDate' ELSE CONCAT(due_date,' ',time_end) >= '$nowInDBFormat' END";
		} elseif ($mode === 'overdue') {
			$query .= " AND CASE WHEN mycrm_activity.activitytype='Task' THEN due_date < '$currentDate' ELSE CONCAT(due_date,' ',time_end) < '$nowInDBFormat' END";
		}

		$params = array();
		if($user != 'all' && $user != '') {
			if($user === $currentUser->id) {
				$query .= " AND mycrm_crmentity.smownerid = ?";
				$params[] = $user;
			}
		}

		$query .= " ORDER BY date_start, time_start LIMIT ?, ?";
		$params[] = $pagingModel->getStartIndex();
		$params[] = $pagingModel->getPageLimit()+1;

		$result = $db->pquery($query, $params);
		$numOfRows = $db->num_rows($result);

		$activities = array();
		for($i=0; $i<$numOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$model = Mycrm_Record_Model::getCleanInstance('Calendar');
			$model->setData($row);
            if($row['activitytype'] == 'Task'){
                $due_date = $row["due_date"];
                $dayEndTime = "23:59:59";
                $EndDateTime = Mycrm_Datetime_UIType::getDBDateTimeValue($due_date." ".$dayEndTime);
                $dueDateTimeInDbFormat = explode(' ',$EndDateTime);
                $dueTimeInDbFormat = $dueDateTimeInDbFormat[1];
                $model->set('time_end',$dueTimeInDbFormat);
            }
			$model->setId($row['crmid']);
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
}
