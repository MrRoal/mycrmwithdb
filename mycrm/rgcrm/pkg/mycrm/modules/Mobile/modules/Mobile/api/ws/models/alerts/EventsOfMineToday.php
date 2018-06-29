<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/../Alert.php';

/** Events for today alert */
class Mobile_WS_AlertModel_EventsOfMineToday extends Mobile_WS_AlertModel {
	function __construct() {
		parent::__construct();
		$this->name = 'Your events for the day';
		$this->moduleName = 'Calendar';
		$this->refreshRate= 1 * (24* 60 * 60); // 1 day
		$this->description='Alert sent when events are scheduled for the day';
	}
	
	function query() {
		$today = date('Y-m-d');
		$sql = "SELECT crmid, activitytype FROM mycrm_activity INNER JOIN 
				mycrm_crmentity ON mycrm_crmentity.crmid=mycrm_activity.activityid
				WHERE mycrm_crmentity.deleted=0 AND mycrm_crmentity.smownerid=? AND 
				mycrm_activity.activitytype <> 'Emails' AND 
				(mycrm_activity.date_start = '{$today}' OR mycrm_activity.due_date = '{$today}')";
		return $sql;
	}
	
	function queryParameters() {
		return array($this->getUser()->id);
	}
}
