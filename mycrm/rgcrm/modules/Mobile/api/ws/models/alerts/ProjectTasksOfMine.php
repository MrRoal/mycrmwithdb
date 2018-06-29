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
class Mobile_WS_AlertModel_ProjectTasksOfMine extends Mobile_WS_AlertModel {
	function __construct() {
		parent::__construct();
		$this->name = 'My Project Task';
		$this->moduleName = 'ProjectTask';
		$this->refreshRate= 1 * (24* 60 * 60); // 1 day
		$this->description='Project Task Assigned To Me';
	}

	function query() {
		$sql = "SELECT crmid FROM mycrm_crmentity INNER JOIN mycrm_projecttask ON 
                    mycrm_projecttask.projecttaskid=mycrm_crmentity.crmid WHERE mycrm_crmentity.deleted=0 AND mycrm_crmentity.smownerid=? AND
                    mycrm_projecttask.projecttaskprogress <> '100%';";
		return $sql;
	}
        function queryParameters() {
		return array($this->getUser()->id);
	}

	
}

