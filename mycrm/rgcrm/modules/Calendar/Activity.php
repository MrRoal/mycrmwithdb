<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header: /advent/projects/wesat/mycrm_crm/sugarcrm/modules/Activities/Activity.php,v 1.26 2005/03/26 10:42:13 rank Exp $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('modules/Calendar/RenderRelatedListUI.php');
require_once('modules/Calendar/CalendarCommon.php');

// Task is used to store customer information.
class Activity extends CRMEntity {
	var $log;
	var $db;
	var $table_name = "mycrm_activity";
	var $table_index= 'activityid';
	var $reminder_table = 'mycrm_activity_reminder';
	var $tab_name = Array('mycrm_crmentity','mycrm_activity','mycrm_activitycf');

	var $tab_name_index = Array('mycrm_crmentity'=>'crmid','mycrm_activity'=>'activityid','mycrm_seactivityrel'=>'activityid','mycrm_cntactivityrel'=>'activityid','mycrm_salesmanactivityrel'=>'activityid','mycrm_activity_reminder'=>'activity_id','mycrm_recurringevents'=>'activityid','mycrm_activitycf'=>'activityid');

	var $column_fields = Array();
	var $sortby_fields = Array('subject','due_date','date_start','smownerid','activitytype','lastname');	//Sorting is added for due date and start date

	// This is used to retrieve related mycrm_fields from form posts.
	var $additional_column_fields = Array('assigned_user_name', 'assigned_user_id', 'contactname', 'contact_phone', 'contact_email', 'parent_name');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('mycrm_activitycf', 'activityid');

	// This is the list of mycrm_fields that are in the lists.
	var $list_fields = Array(
       'Close'=>Array('activity'=>'status'),
       'Type'=>Array('activity'=>'activitytype'),
       'Subject'=>Array('activity'=>'subject'),
       'Related to'=>Array('seactivityrel'=>'parent_id'),
       'Start Date'=>Array('activity'=>'date_start'),
       'Start Time'=>Array('activity','time_start'),
       'End Date'=>Array('activity'=>'due_date'),
       'End Time'=>Array('activity','time_end'),
       'Recurring Type'=>Array('recurringevents'=>'recurringtype'),
       'Assigned To'=>Array('crmentity'=>'smownerid'),
       'Contact Name'=>Array('contactdetails'=>'lastname')
       );

       var $range_fields = Array(
		'name',
		'date_modified',
		'start_date',
		'id',
		'status',
		'date_due',
		'time_start',
		'description',
		'contact_name',
		'priority',
		'duehours',
		'dueminutes',
		'location'
	   );


       var $list_fields_name = Array(
       'Close'=>'status',
       'Type'=>'activitytype',
       'Subject'=>'subject',
       'Contact Name'=>'lastname',
       'Related to'=>'parent_id',
       'Start Date & Time'=>'date_start',
       'End Date & Time'=>'due_date',
	   'Recurring Type'=>'recurringtype',
       'Assigned To'=>'assigned_user_id',
       'Start Date'=>'date_start',
       'Start Time'=>'time_start',
       'End Date'=>'due_date',
       'End Time'=>'time_end');

       var $list_link_field= 'subject';

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'due_date';
	var $default_sort_order = 'ASC';

	//var $groupTable = Array('mycrm_activitygrouprelation','activityid');

	function Activity() {
		$this->log = LoggerManager::getLogger('Calendar');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Calendar');
	}

	function save_module($module)
	{
		global $adb;
        //Handling module specific save
		//Insert into seactivity rel
		$insertion_mode = $this->mode;
        if(isset($this->column_fields['parent_id']) && $this->column_fields['parent_id'] != '')
		{
			$this->insertIntoEntityTable("mycrm_seactivityrel", $module);
		}
		elseif($this->column_fields['parent_id']=='' && $insertion_mode=="edit")
		{
			$this->deleteRelation("mycrm_seactivityrel");
		}

        $recordId = $this->id;
		if(isset($_REQUEST['contactidlist']) && $_REQUEST['contactidlist'] != '') {
			$adb->pquery( 'DELETE from mycrm_cntactivityrel WHERE activityid = ?', array($recordId));

			$contactIdsList = explode (';', $_REQUEST['contactidlist']);
			$count = count($contactIdsList);

			$sql = 'INSERT INTO mycrm_cntactivityrel VALUES ';
			for($i=0; $i<$count; $i++) {
				$sql .= " ($contactIdsList[$i], $recordId)";
				if ($i != $count - 1) {
					$sql .= ',';
				}
			}
			$adb->pquery($sql, array());
		} else if ($_REQUEST['contactidlist'] == '' && $insertion_mode == "edit") {
        	$adb->pquery('DELETE FROM mycrm_cntactivityrel WHERE activityid = ?', array($recordId));
        }
        
        //Insert into cntactivity rel
        if(isset($this->column_fields['contact_id']) && $this->column_fields['contact_id'] != '' && !isset($_REQUEST['contactidlist']))
        {
                $this->insertIntoEntityTable('mycrm_cntactivityrel', $module);
        }
        elseif($this->column_fields['contact_id'] =='' && $insertion_mode=="edit" && !isset($_REQUEST['contactidlist']))
        {
                $this->deleteRelation('mycrm_cntactivityrel');
        }
        
		$recur_type='';
		if(($recur_type == "--None--" || $recur_type == '') && $this->mode == "edit")
		{
			$sql = 'delete  from mycrm_recurringevents where activityid=?';
			$adb->pquery($sql, array($this->id));
		}
		//Handling for recurring type
		//Insert into mycrm_recurring event table
		if(isset($this->column_fields['recurringtype']) && $this->column_fields['recurringtype']!='' && $this->column_fields['recurringtype']!='--None--')
		{
			$recur_type = trim($this->column_fields['recurringtype']);
			$recur_data = getrecurringObjValue();
			if(is_object($recur_data))
	      			$this->insertIntoRecurringTable($recur_data);
		}

		//Insert into mycrm_activity_remainder table

			$this->insertIntoReminderTable('mycrm_activity_reminder',$module,"");

		//Handling for invitees
			$selected_users_string =  $_REQUEST['inviteesid'];
			$invitees_array = explode(';',$selected_users_string);
			$this->insertIntoInviteeTable($module,$invitees_array);

		//Inserting into sales man activity rel
		$this->insertIntoSmActivityRel($module);

		$this->insertIntoActivityReminderPopup($module);
	}


	/** Function to insert values in mycrm_activity_reminder_popup table for the specified module
  	  * @param $cbmodule -- module:: Type varchar
 	 */
	function insertIntoActivityReminderPopup($cbmodule) {

		global $adb;

		$cbrecord = $this->id;
		unset($_SESSION['next_reminder_time']);
		if(isset($cbmodule) && isset($cbrecord)) {
			$cbdate = getValidDBInsertDateValue($this->column_fields['date_start']);
			$cbtime = $this->column_fields['time_start'];

			$reminder_query = "SELECT reminderid FROM mycrm_activity_reminder_popup WHERE semodule = ? and recordid = ?";
			$reminder_params = array($cbmodule, $cbrecord);
			$reminderidres = $adb->pquery($reminder_query, $reminder_params);

			$reminderid = null;
			if($adb->num_rows($reminderidres) > 0) {
				$reminderid = $adb->query_result($reminderidres, 0, "reminderid");
			}

            $current_date = new DateTime();
            $record_date = new DateTime($cbdate.' '.$cbtime);

            $current = $current_date->format('Y-m-d H:i:s');
			$record = $record_date->format('Y-m-d H:i:s');

			$reminder = false;
			if(strtotime($record) > strtotime($current)){
				$status = 0;
				$reminder = true;
			} else {
				$status = 1;
			}

            if(isset($reminderid)){
                $callback_query = "UPDATE mycrm_activity_reminder_popup set status = 0, date_start = ?, time_start = ? WHERE reminderid = ?";
                $callback_params = array($cbdate, $cbtime, $reminderid);
			} else if ($reminder) {
				$callback_query = "INSERT INTO mycrm_activity_reminder_popup (recordid, semodule, date_start, time_start, status) VALUES (?,?,?,?,?)";
				$callback_params = array($cbrecord, $cbmodule, $cbdate, $cbtime, $status);
			}

            if($callback_query)
                $adb->pquery($callback_query, $callback_params);
		}
	}


	/** Function to insert values in mycrm_activity_remainder table for the specified module,
  	  * @param $table_name -- table name:: Type varchar
  	  * @param $module -- module:: Type varchar
 	 */
	function insertIntoReminderTable($table_name,$module,$recurid)
	{
	 	global $log;
		$log->info("in insertIntoReminderTable  ".$table_name."    module is  ".$module);
		if($_REQUEST['set_reminder'] == 'Yes')
		{
			unset($_SESSION['next_reminder_time']);
			$log->debug("set reminder is set");
			$rem_days = $_REQUEST['remdays'];
			$log->debug("rem_days is ".$rem_days);
			$rem_hrs = $_REQUEST['remhrs'];
			$log->debug("rem_hrs is ".$rem_hrs);
			$rem_min = $_REQUEST['remmin'];
			$log->debug("rem_minutes is ".$rem_min);
			$reminder_time = $rem_days * 24 * 60 + $rem_hrs * 60 + $rem_min;
			$log->debug("reminder_time is ".$reminder_time);
			if ($recurid == "")
			{
				if($_REQUEST['mode'] == 'edit')
				{
					$this->activity_reminder($this->id,$reminder_time,0,$recurid,'edit');
				}
				else
				{
					$this->activity_reminder($this->id,$reminder_time,0,$recurid,'');
				}
			}
			else
			{
				$this->activity_reminder($this->id,$reminder_time,0,$recurid,'');
			}
		}
		elseif($_REQUEST['set_reminder'] == 'No')
		{
			$this->activity_reminder($this->id,'0',0,$recurid,'delete');
		}
	}


	// Code included by Jaguar - starts
	/** Function to insert values in mycrm_recurringevents table for the specified tablename,module
  	  * @param $recurObj -- Recurring Object:: Type varchar
 	 */
function insertIntoRecurringTable(& $recurObj)
{
	global $log,$adb;
	$st_date = $recurObj->startdate->get_DB_formatted_date();
	$end_date = $recurObj->enddate->get_DB_formatted_date();
	if(!empty($recurObj->recurringenddate)){
		$recurringenddate = $recurObj->recurringenddate->get_DB_formatted_date();
	}
	$type = $recurObj->getRecurringType();
	$flag="true";

	if($_REQUEST['mode'] == 'edit')
	{
		$activity_id=$this->id;

		$sql='select min(recurringdate) AS min_date,max(recurringdate) AS max_date, recurringtype, activityid from mycrm_recurringevents where activityid=? group by activityid, recurringtype';
		$result = $adb->pquery($sql, array($activity_id));
		$noofrows = $adb->num_rows($result);
		for($i=0; $i<$noofrows; $i++)
		{
			$recur_type_b4_edit = $adb->query_result($result,$i,"recurringtype");
			$date_start_b4edit = $adb->query_result($result,$i,"min_date");
			$end_date_b4edit = $adb->query_result($result,$i,"max_date");
		}
		if(($st_date == $date_start_b4edit) && ($end_date==$end_date_b4edit) && ($type == $recur_type_b4_edit))
		{
			if($_REQUEST['set_reminder'] == 'Yes')
			{
				$sql = 'delete from mycrm_activity_reminder where activity_id=?';
				$adb->pquery($sql, array($activity_id));
				$sql = 'delete  from mycrm_recurringevents where activityid=?';
				$adb->pquery($sql, array($activity_id));
				$flag="true";
			}
			elseif($_REQUEST['set_reminder'] == 'No')
			{
				$sql = 'delete  from mycrm_activity_reminder where activity_id=?';
				$adb->pquery($sql, array($activity_id));
				$flag="false";
			}
			else
				$flag="false";
		}
		else
		{
			$sql = 'delete from mycrm_activity_reminder where activity_id=?';
			$adb->pquery($sql, array($activity_id));
			$sql = 'delete  from mycrm_recurringevents where activityid=?';
			$adb->pquery($sql, array($activity_id));
		}
	}

	$recur_freq = $recurObj->getRecurringFrequency();
	$recurringinfo = $recurObj->getDBRecurringInfoString();

	if($flag=="true") {
		$max_recurid_qry = 'select max(recurringid) AS recurid from mycrm_recurringevents;';
		$result = $adb->pquery($max_recurid_qry, array());
		$noofrows = $adb->num_rows($result);
		$recur_id = 0;
		if($noofrows > 0) {
			$recur_id = $adb->query_result($result,0,"recurid");
		}
		$current_id =$recur_id+1;
		$recurring_insert = "insert into mycrm_recurringevents values (?,?,?,?,?,?,?)";
		$rec_params = array($current_id, $this->id, $st_date, $type, $recur_freq, $recurringinfo,$recurringenddate);
		$adb->pquery($recurring_insert, $rec_params);
		unset($_SESSION['next_reminder_time']);
		if($_REQUEST['set_reminder'] == 'Yes') {
			$this->insertIntoReminderTable("mycrm_activity_reminder",$module,$current_id,'');
		}
	}
}


	/** Function to insert values in mycrm_invitees table for the specified module,tablename ,invitees_array
  	  * @param $table_name -- table name:: Type varchar
  	  * @param $module -- module:: Type varchar
	  * @param $invitees_array Array
 	 */
	function insertIntoInviteeTable($module,$invitees_array)
	{
		global $log,$adb;
		$log->debug("Entering insertIntoInviteeTable(".$module.",".$invitees_array.") method ...");
		if($this->mode == 'edit'){
			$sql = "delete from mycrm_invitees where activityid=?";
			$adb->pquery($sql, array($this->id));
		}
		foreach($invitees_array as $inviteeid)
		{
			if($inviteeid != '')
			{
				$query="insert into mycrm_invitees values(?,?)";
				$adb->pquery($query, array($this->id, $inviteeid));
			}
		}
		$log->debug("Exiting insertIntoInviteeTable method ...");

	}


	/** Function to insert values in mycrm_salesmanactivityrel table for the specified module
  	  * @param $module -- module:: Type varchar
 	 */

  	function insertIntoSmActivityRel($module)
  	{
    		global $adb;
    		global $current_user;
    		if($this->mode == 'edit'){
      			$sql = "delete from mycrm_salesmanactivityrel where activityid=?";
      			$adb->pquery($sql, array($this->id));
    		}

		$user_sql = $adb->pquery("select count(*) as count from mycrm_users where id=?", array($this->column_fields['assigned_user_id']));
    	if($adb->query_result($user_sql, 0, 'count') != 0) {
		$sql_qry = "insert into mycrm_salesmanactivityrel (smid,activityid) values(?,?)";
    		$adb->pquery($sql_qry, array($this->column_fields['assigned_user_id'], $this->id));

		if(isset($_REQUEST['inviteesid']) && $_REQUEST['inviteesid']!='')
		{
			$selected_users_string =  $_REQUEST['inviteesid'];
			$invitees_array = explode(';',$selected_users_string);
			foreach($invitees_array as $inviteeid)
			{
				if($inviteeid != '')
				{
					$resultcheck = $adb->pquery("select * from mycrm_salesmanactivityrel where activityid=? and smid=?",array($this->id,$inviteeid));
					if($adb->num_rows($resultcheck) != 1){
						$query="insert into mycrm_salesmanactivityrel values(?,?)";
						$adb->pquery($query, array($inviteeid, $this->id));
					}
				}
			}
		}
	}
}

	/**
	 *
	 * @param String $tableName
	 * @return String
	 */
	public function getJoinClause($tableName) {
        if($tableName == "mycrm_activity_reminder")
            return 'LEFT JOIN';
		return parent::getJoinClause($tableName);
	}


	// Mike Crowe Mod --------------------------------------------------------Default ordering for us
	/**
	 * Function to get sort order
	 * return string  $sorder    - sortorder string either 'ASC' or 'DESC'
	 */
	function getSortOrder()
	{
		global $log;
		$log->debug("Entering getSortOrder() method ...");
		if(isset($_REQUEST['sorder']))
			$sorder = $this->db->sql_escape_string($_REQUEST['sorder']);
		else
			$sorder = (($_SESSION['ACTIVITIES_SORT_ORDER'] != '')?($_SESSION['ACTIVITIES_SORT_ORDER']):($this->default_sort_order));
		$log->debug("Exiting getSortOrder method ...");
		return $sorder;
	}

	/**
	 * Function to get order by
	 * return string  $order_by    - fieldname(eg: 'subject')
	 */
	function getOrderBy()
	{
		global $log;
		$log->debug("Entering getOrderBy() method ...");

		$use_default_order_by = '';
		if(PerformancePrefs::getBoolean('LISTVIEW_DEFAULT_SORTING', true)) {
			$use_default_order_by = $this->default_order_by;
		}

		if (isset($_REQUEST['order_by']))
			$order_by = $this->db->sql_escape_string($_REQUEST['order_by']);
		else
			$order_by = (($_SESSION['ACTIVITIES_ORDER_BY'] != '')?($_SESSION['ACTIVITIES_ORDER_BY']):($use_default_order_by));
		$log->debug("Exiting getOrderBy method ...");
		return $order_by;
	}
	// Mike Crowe Mod --------------------------------------------------------



//Function Call for Related List -- Start
	/**
	 * Function to get Activity related Contacts
	 * @param  integer   $id      - activityid
	 * returns related Contacts record in array format
	 */
	function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_contacts(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		$returnset = '&return_module='.$this_module.'&return_action=DetailView&activity_mode=Events&return_id='.$id;

		$search_string = '';
		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab$search_string','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
		}

		$query = 'select mycrm_users.user_name,mycrm_contactdetails.accountid,mycrm_contactdetails.contactid, mycrm_contactdetails.firstname,mycrm_contactdetails.lastname, mycrm_contactdetails.department, mycrm_contactdetails.title, mycrm_contactdetails.email, mycrm_contactdetails.phone, mycrm_crmentity.crmid, mycrm_crmentity.smownerid, mycrm_crmentity.modifiedtime from mycrm_contactdetails inner join mycrm_cntactivityrel on mycrm_cntactivityrel.contactid=mycrm_contactdetails.contactid inner join mycrm_crmentity on mycrm_crmentity.crmid = mycrm_contactdetails.contactid left join mycrm_users on mycrm_users.id = mycrm_crmentity.smownerid left join mycrm_groups on mycrm_groups.groupid = mycrm_crmentity.smownerid where mycrm_cntactivityrel.activityid='.$id.' and mycrm_crmentity.deleted=0';

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
	}

	/**
	 * Function to get Activity related Users
	 * @param  integer   $id      - activityid
	 * returns related Users record in array format
	 */

	function get_users($id) {
		global $log;
                $log->debug("Entering get_contacts(".$id.") method ...");
		global $app_strings;

		$focus = new Users();

		$button = '<input title="Change" accessKey="" tabindex="2" type="button" class="crmbutton small edit"
					value="'.getTranslatedString('LBL_SELECT_USER_BUTTON_LABEL').'" name="button" LANGUAGE=javascript
					onclick=\'return window.open("index.php?module=Users&return_module=Calendar&return_action={$return_modname}&activity_mode=Events&action=Popup&popuptype=detailview&form=EditView&form_submit=true&select=enable&return_id='.$id.'&recordid='.$id.'","test","width=640,height=525,resizable=0,scrollbars=0")\';>';

		$returnset = '&return_module=Calendar&return_action=CallRelatedList&return_id='.$id;

		$query = 'SELECT mycrm_users.id, mycrm_users.first_name,mycrm_users.last_name, mycrm_users.user_name, mycrm_users.email1, mycrm_users.email2, mycrm_users.status, mycrm_users.is_admin, mycrm_user2role.roleid, mycrm_users.secondaryemail, mycrm_users.phone_home, mycrm_users.phone_work, mycrm_users.phone_mobile, mycrm_users.phone_other, mycrm_users.phone_fax,mycrm_activity.date_start,mycrm_activity.due_date,mycrm_activity.time_start,mycrm_activity.duration_hours,mycrm_activity.duration_minutes from mycrm_users inner join mycrm_salesmanactivityrel on mycrm_salesmanactivityrel.smid=mycrm_users.id  inner join mycrm_activity on mycrm_activity.activityid=mycrm_salesmanactivityrel.activityid inner join mycrm_user2role on mycrm_user2role.userid=mycrm_users.id where mycrm_activity.activityid='.$id;

		$return_data = GetRelatedList('Calendar','Users',$focus,$query,$button,$returnset);

		if($return_data == null) $return_data = Array();
		$return_data['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_users method ...");
		return $return_data;
	}

 	 /**
	  * Function to get activities for given criteria
	  * @param   string   $order_by     - query string
	  * @param   string   $where     - query string
	  * returns  activity records in array format($list) or null value
	  */
  	function get_full_list($order_by = '', $where = '') {
			global $log;
			$log->debug("Entering get_full_list(".$order_by.", ".$where.") method ...");
	    $query = "select mycrm_crmentity.crmid,mycrm_crmentity.smownerid,mycrm_crmentity.setype, mycrm_activity.*,
	    		mycrm_contactdetails.lastname, mycrm_contactdetails.firstname, mycrm_contactdetails.contactid
	    		from mycrm_activity
	    		inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_activity.activityid
	    		left join mycrm_cntactivityrel on mycrm_cntactivityrel.activityid= mycrm_activity.activityid
	    		left join mycrm_contactdetails on mycrm_contactdetails.contactid= mycrm_cntactivityrel.contactid
	    		left join mycrm_seactivityrel on mycrm_seactivityrel.activityid = mycrm_activity.activityid
	    		WHERE mycrm_crmentity.deleted=0 ".$order_by;
    	$result =& $this->db->query($query);

      if($this->db->getRowCount($result) > 0){
				// We have some data.
				while ($row = $this->db->fetchByAssoc($result)) {
					foreach($this->list_fields_name as $field){
						if (isset($row[$field])) {
							$this->$field = $row[$field];
						} else {
							$this->$field = '';
						}
					}
					$list[] = $this;
				}
			}
			if (isset($list)){
				$log->debug("Exiting get_full_list method ...");
				return $list;
			} else {
				$log->debug("Exiting get_full_list method ...");
				return null;
			}
		}


//calendarsync
    /**
     * Function to get meeting count
     * @param  string   $user_name        - User Name
     * return  integer  $row["count(*)"]  - count
     */
    function getCount_Meeting($user_name)
	{
		global $log;
	        $log->debug("Entering getCount_Meeting(".$user_name.") method ...");
      $query = "select count(*) from mycrm_activity inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_activity.activityid inner join mycrm_salesmanactivityrel on mycrm_salesmanactivityrel.activityid=mycrm_activity.activityid inner join mycrm_users on mycrm_users.id=mycrm_salesmanactivityrel.smid where user_name=? and mycrm_crmentity.deleted=0 and mycrm_activity.activitytype='Meeting'";
      $result = $this->db->pquery($query, array($user_name),true,"Error retrieving contacts count");
      $rows_found =  $this->db->getRowCount($result);
      $row = $this->db->fetchByAssoc($result, 0);
	$log->debug("Exiting getCount_Meeting method ...");
      return $row["count(*)"];
    }

    function get_calendars($user_name,$from_index,$offset)
    {
	    global $log;
            $log->debug("Entering get_calendars(".$user_name.",".$from_index.",".$offset.") method ...");
		$query = "select mycrm_activity.location as location,mycrm_activity.duration_hours as duehours, mycrm_activity.duration_minutes as dueminutes,mycrm_activity.time_start as time_start, mycrm_activity.subject as name,mycrm_crmentity.modifiedtime as date_modified, mycrm_activity.date_start start_date,mycrm_activity.activityid as id,mycrm_activity.status as status, mycrm_crmentity.description as description, mycrm_activity.priority as mycrm_priority, mycrm_activity.due_date as date_due ,mycrm_contactdetails.firstname cfn, mycrm_contactdetails.lastname cln from mycrm_activity inner join mycrm_salesmanactivityrel on mycrm_salesmanactivityrel.activityid=mycrm_activity.activityid inner join mycrm_users on mycrm_users.id=mycrm_salesmanactivityrel.smid left join mycrm_cntactivityrel on mycrm_cntactivityrel.activityid=mycrm_activity.activityid left join mycrm_contactdetails on mycrm_contactdetails.contactid=mycrm_cntactivityrel.contactid inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_activity.activityid where user_name='" .$user_name ."' and mycrm_crmentity.deleted=0 and mycrm_activity.activitytype='Meeting' limit " .$from_index ."," .$offset;
	$log->debug("Exiting get_calendars method ...");
	    return $this->process_list_query1($query);
    }
//calendarsync
	/**
	 * Function to get task count
	 * @param  string   $user_name        - User Name
	 * return  integer  $row["count(*)"]  - count
	 */
    function getCount($user_name)
    {
	    global $log;
            $log->debug("Entering getCount(".$user_name.") method ...");
        $query = "select count(*) from mycrm_activity inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_activity.activityid inner join mycrm_salesmanactivityrel on mycrm_salesmanactivityrel.activityid=mycrm_activity.activityid inner join mycrm_users on mycrm_users.id=mycrm_salesmanactivityrel.smid where user_name=? and mycrm_crmentity.deleted=0 and mycrm_activity.activitytype='Task'";
        $result = $this->db->pquery($query,array($user_name), true,"Error retrieving contacts count");
        $rows_found =  $this->db->getRowCount($result);
        $row = $this->db->fetchByAssoc($result, 0);

	$log->debug("Exiting getCount method ...");
        return $row["count(*)"];
    }

    /**
     * Function to get list of task for user with given limit
     * @param  string   $user_name        - User Name
     * @param  string   $from_index       - query string
     * @param  string   $offset           - query string
     * returns tasks in array format
     */
    function get_tasks($user_name,$from_index,$offset)
    {
	global $log;
        $log->debug("Entering get_tasks(".$user_name.",".$from_index.",".$offset.") method ...");
	 $query = "select mycrm_activity.subject as name,mycrm_crmentity.modifiedtime as date_modified, mycrm_activity.date_start start_date,mycrm_activity.activityid as id,mycrm_activity.status as status, mycrm_crmentity.description as description, mycrm_activity.priority as priority, mycrm_activity.due_date as date_due ,mycrm_contactdetails.firstname cfn, mycrm_contactdetails.lastname cln from mycrm_activity inner join mycrm_salesmanactivityrel on mycrm_salesmanactivityrel.activityid=mycrm_activity.activityid inner join mycrm_users on mycrm_users.id=mycrm_salesmanactivityrel.smid left join mycrm_cntactivityrel on mycrm_cntactivityrel.activityid=mycrm_activity.activityid left join mycrm_contactdetails on mycrm_contactdetails.contactid=mycrm_cntactivityrel.contactid inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_activity.activityid where user_name='" .$user_name ."' and mycrm_crmentity.deleted=0 and mycrm_activity.activitytype='Task' limit " .$from_index ."," .$offset;
	 $log->debug("Exiting get_tasks method ...");
    return $this->process_list_query1($query);

    }

    /**
     * Function to process the activity list query
     * @param  string   $query     - query string
     * return  array    $response  - activity lists
     */
    function process_list_query1($query)
    {
	    global $log;
            $log->debug("Entering process_list_query1(".$query.") method ...");
        $result =& $this->db->query($query,true,"Error retrieving $this->object_name list: ");
        $list = Array();
        $rows_found =  $this->db->getRowCount($result);
        if($rows_found != 0)
        {
            $task = Array();
              for($index = 0 , $row = $this->db->fetchByAssoc($result, $index); $row && $index <$rows_found;$index++, $row = $this->db->fetchByAssoc($result, $index))

             {
                foreach($this->range_fields as $columnName)
                {
					if (isset($row[$columnName])) {
						if($columnName == 'time_start'){
							$startDate = new DateTimeField($row['date_start'].' '.
									$row[$columnName]);
							$task[$columnName] = $startDate->getDBInsertTimeValue();
						}else{
							$task[$columnName] = $row[$columnName];
						}
                    }
                    else
                    {
                            $task[$columnName] = "";
                    }
	            }

                $task[contact_name] = return_name($row, 'cfn', 'cln');

                    $list[] = $task;
                }
         }

        $response = Array();
        $response['list'] = $list;
        $response['row_count'] = $rows_found;
        $response['next_offset'] = $next_offset;
        $response['previous_offset'] = $previous_offset;


	$log->debug("Exiting process_list_query1 method ...");
        return $response;
    }

    	/**
	 * Function to get reminder for activity
	 * @param  integer   $activity_id     - activity id
	 * @param  string    $reminder_time   - reminder time
	 * @param  integer   $reminder_sent   - 0 or 1
	 * @param  integer   $recurid         - recuring eventid
	 * @param  string    $remindermode    - string like 'edit'
	 */
	function activity_reminder($activity_id,$reminder_time,$reminder_sent=0,$recurid,$remindermode='')
	{
		global $log;
		$log->debug("Entering mycrm_activity_reminder(".$activity_id.",".$reminder_time.",".$reminder_sent.",".$recurid.",".$remindermode.") method ...");
		//Check for mycrm_activityid already present in the reminder_table
		$query_exist = "SELECT activity_id FROM ".$this->reminder_table." WHERE activity_id = ?";
		$result_exist = $this->db->pquery($query_exist, array($activity_id));

		if($remindermode == 'edit')
		{
			if($this->db->num_rows($result_exist) > 0)
			{
				$query = "UPDATE ".$this->reminder_table." SET";
				$query .=" reminder_sent = ?, reminder_time = ? WHERE activity_id =?";
				$params = array($reminder_sent, $reminder_time, $activity_id);
			}
			else
			{
				$query = "INSERT INTO ".$this->reminder_table." VALUES (?,?,?,?)";
				$params = array($activity_id, $reminder_time, 0, $recurid);
			}
		}
		elseif(($remindermode == 'delete') && ($this->db->num_rows($result_exist) > 0))
		{
			$query = "DELETE FROM ".$this->reminder_table." WHERE activity_id = ?";
			$params = array($activity_id);
		}
		else
		{
			if($_REQUEST['set_reminder'] == 'Yes'){
				$query = "INSERT INTO ".$this->reminder_table." VALUES (?,?,?,?)";
				$params = array($activity_id, $reminder_time, 0, $recurid);
			}
		}
		if(!empty($query)){
			$this->db->pquery($query,$params,true,"Error in processing mycrm_table $this->reminder_table");
		}
		$log->debug("Exiting mycrm_activity_reminder method ...");
	}

	//Used for mycrmCRM Outlook Add-In
	/**
 	* Function to get tasks to display in outlookplugin
 	* @param   string    $username     -  User name
 	* return   string    $query        -  sql query
 	*/
	function get_tasksforol($username)
	{
		global $log,$adb;
		$log->debug("Entering get_tasksforol(".$username.") method ...");
		global $current_user;
		require_once("modules/Users/Users.php");
		$seed_user=new Users();
		$user_id=$seed_user->retrieve_user_id($username);
		$current_user=$seed_user;
		$current_user->retrieve_entity_info($user_id, 'Users');
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		require('user_privileges/sharing_privileges_'.$current_user->id.'.php');

		if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)
  		{
    		$sql1 = "select tablename,columnname from mycrm_field where tabid=9 and tablename <> 'mycrm_recurringevents' and tablename <> 'mycrm_activity_reminder' and mycrm_field.presence in (0,2)";
			$params1 = array();
  		}else
  	{
    	$profileList = getCurrentUserProfileList();
    	$sql1 = "select tablename,columnname from mycrm_field inner join mycrm_profile2field on mycrm_profile2field.fieldid=mycrm_field.fieldid inner join mycrm_def_org_field on mycrm_def_org_field.fieldid=mycrm_field.fieldid where mycrm_field.tabid=9 and tablename <> 'mycrm_recurringevents' and tablename <> 'mycrm_activity_reminder' and mycrm_field.displaytype in (1,2,4,3) and mycrm_profile2field.visible=0 and mycrm_def_org_field.visible=0 and mycrm_field.presence in (0,2)";
		$params1 = array();
		if (count($profileList) > 0) {
  			$sql1 .= " and mycrm_profile2field.profileid in (". generateQuestionMarks($profileList) .")";
			array_push($params1, $profileList);
		}
  	}
  	$result1 = $adb->pquery($sql1,$params1);
  	for($i=0;$i < $adb->num_rows($result1);$i++)
  	{
		$permitted_lists[] = $adb->query_result($result1,$i,'tablename');
      	$permitted_lists[] = $adb->query_result($result1,$i,'columnname');
      	/*if($adb->query_result($result1,$i,'columnname') == "parentid")
      	{
        	$permitted_lists[] = 'mycrm_account';
        	$permitted_lists[] = 'accountname';
      	}*/
  		}
		$permitted_lists = array_chunk($permitted_lists,2);
		$column_table_lists = array();
		for($i=0;$i < count($permitted_lists);$i++)
		{
	   		$column_table_lists[] = implode(".",$permitted_lists[$i]);
  		}

		$query = "select mycrm_activity.activityid as taskid, ".implode(',',$column_table_lists)." from mycrm_activity inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_activity.activityid
			 inner join mycrm_users on mycrm_users.id = mycrm_crmentity.smownerid
			 left join mycrm_cntactivityrel on mycrm_cntactivityrel.activityid=mycrm_activity.activityid
			 left join mycrm_contactdetails on mycrm_contactdetails.contactid=mycrm_cntactivityrel.contactid
			 left join mycrm_seactivityrel on mycrm_seactivityrel.activityid = mycrm_activity.activityid
			 where mycrm_users.user_name='".$username."' and mycrm_crmentity.deleted=0 and mycrm_activity.activitytype='Task'";
		$log->debug("Exiting get_tasksforol method ...");
		return $query;
	}

	/**
 	* Function to get calendar query for outlookplugin
 	* @param   string    $username     -  User name                                                                            * return   string    $query        -  sql query                                                                            */
	function get_calendarsforol($user_name)
	{
		global $log,$adb;
		$log->debug("Entering get_calendarsforol(".$user_name.") method ...");
		global $current_user;
		require_once("modules/Users/Users.php");
		$seed_user=new Users();
		$user_id=$seed_user->retrieve_user_id($user_name);
		$current_user=$seed_user;
		$current_user->retrieve_entity_info($user_id, 'Users');
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		require('user_privileges/sharing_privileges_'.$current_user->id.'.php');

		if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)
  		{
    		$sql1 = "select tablename,columnname from mycrm_field where tabid=9 and tablename <> 'mycrm_recurringevents' and tablename <> 'mycrm_activity_reminder' and mycrm_field.presence in (0,2)";
  			$params1 = array();
  		}else
  		{
    		$profileList = getCurrentUserProfileList();
    		$sql1 = "select tablename,columnname from mycrm_field inner join mycrm_profile2field on mycrm_profile2field.fieldid=mycrm_field.fieldid inner join mycrm_def_org_field on mycrm_def_org_field.fieldid=mycrm_field.fieldid where mycrm_field.tabid=9 and tablename <> 'mycrm_recurringevents' and tablename <> 'mycrm_activity_reminder' and mycrm_field.displaytype in (1,2,4,3) and mycrm_profile2field.visible=0 and mycrm_def_org_field.visible=0 and mycrm_field.presence in (0,2)";
			$params1 = array();
			if (count($profileList) > 0) {
				$sql1 .= " and mycrm_profile2field.profileid in (". generateQuestionMarks($profileList) .")";
				array_push($params1,$profileList);
			}
  		}
  		$result1 = $adb->pquery($sql1, $params1);
  		for($i=0;$i < $adb->num_rows($result1);$i++)
  		{
			$permitted_lists[] = $adb->query_result($result1,$i,'tablename');
      		$permitted_lists[] = $adb->query_result($result1,$i,'columnname');
      		if($adb->query_result($result1,$i,'columnname') == "date_start")
      		{
        		$permitted_lists[] = 'mycrm_activity';
        		$permitted_lists[] = 'time_start';
      		}
      		if($adb->query_result($result1,$i,'columnname') == "due_date")
      		{
				$permitted_lists[] = 'mycrm_activity';
        		$permitted_lists[] = 'time_end';
      		}
  		}
		$permitted_lists = array_chunk($permitted_lists,2);
		$column_table_lists = array();
		for($i=0;$i < count($permitted_lists);$i++)
		{
	   		$column_table_lists[] = implode(".",$permitted_lists[$i]);
  		}

	  	$query = "select mycrm_activity.activityid as clndrid, ".implode(',',$column_table_lists)." from mycrm_activity
				inner join mycrm_salesmanactivityrel on mycrm_salesmanactivityrel.activityid=mycrm_activity.activityid
				inner join mycrm_users on mycrm_users.id=mycrm_salesmanactivityrel.smid
				left join mycrm_cntactivityrel on mycrm_cntactivityrel.activityid=mycrm_activity.activityid
				left join mycrm_contactdetails on mycrm_contactdetails.contactid=mycrm_cntactivityrel.contactid
				left join mycrm_seactivityrel on mycrm_seactivityrel.activityid = mycrm_activity.activityid
				inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_activity.activityid
				where mycrm_users.user_name='".$user_name."' and mycrm_crmentity.deleted=0 and mycrm_activity.activitytype='Meeting'";
		$log->debug("Exiting get_calendarsforol method ...");
		return $query;
	}

	// Function to unlink all the dependent entities of the given Entity by Id
	function unlinkDependencies($module, $id) {
		global $log;

		$sql = 'DELETE FROM mycrm_activity_reminder WHERE activity_id=?';
		$this->db->pquery($sql, array($id));

		$sql = 'DELETE FROM mycrm_recurringevents WHERE activityid=?';
		$this->db->pquery($sql, array($id));

		$sql = 'DELETE FROM mycrm_cntactivityrel WHERE activityid = ?';
		$this->db->pquery($sql, array($id));

		parent::unlinkDependencies($module, $id);
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Contacts') {
			$sql = 'DELETE FROM mycrm_cntactivityrel WHERE contactid = ? AND activityid = ?';
			$this->db->pquery($sql, array($return_id, $id));
		} elseif($return_module == 'HelpDesk') {
			$sql = 'DELETE FROM mycrm_seactivityrel WHERE crmid = ? AND activityid = ?';
			$this->db->pquery($sql, array($return_id, $id));
		} elseif($return_module == 'Accounts') {
			$sql = 'DELETE FROM mycrm_seactivityrel WHERE crmid = ? AND activityid = ?';
			$this->db->pquery($sql, array($return_id, $id));
			$sql = 'DELETE FROM mycrm_cntactivityrel WHERE activityid = ? AND contactid IN	(SELECT contactid from mycrm_contactdetails where accountid=?)';
			$this->db->pquery($sql, array($id, $return_id));
		} else {
			$sql='DELETE FROM mycrm_seactivityrel WHERE activityid=?';
			$this->db->pquery($sql, array($id));

			$sql = 'DELETE FROM mycrm_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}

	/**
	 * this function sets the status flag of activity to true or false depending on the status passed to it
	 * @param string $status - the status of the activity flag to set
	 * @return:: true if successful; false otherwise
	 */
	function setActivityReminder($status){
		global $adb;
		if($status == "on"){
			$flag = 0;
		}elseif($status == "off"){
			$flag = 1;
		}else{
			return false;
		}
		$sql = "update mycrm_activity_reminder_popup set status=1 where recordid=?";
		$adb->pquery($sql, array($this->id));
		return true;
	}

	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */
	function setRelationTables($secmodule){
		$rel_tables = array (
			"Contacts" => array("mycrm_cntactivityrel"=>array("activityid","contactid"),"mycrm_activity"=>"activityid"),
			"Leads" => array("mycrm_seactivityrel"=>array("activityid","crmid"),"mycrm_activity"=>"activityid"),
			"Accounts" => array("mycrm_seactivityrel"=>array("activityid","crmid"),"mycrm_activity"=>"activityid"),
			"Potentials" => array("mycrm_seactivityrel"=>array("activityid","crmid"),"mycrm_activity"=>"activityid"),
		);
		return $rel_tables[$secmodule];
	}

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule,$queryPlanner){
		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('mycrm_crmentityCalendar',array('mycrm_groupsCalendar','mycrm_usersCalendar','mycrm_lastModifiedByCalendar'));
		$matrix->setDependency('mycrm_cntactivityrel',array('mycrm_contactdetailsCalendar'));
		$matrix->setDependency('mycrm_seactivityrel',array('mycrm_crmentityRelCalendar'));
		$matrix->setDependency('mycrm_crmentityRelCalendar',array('mycrm_accountRelCalendar','mycrm_leaddetailsRelCalendar','mycrm_potentialRelCalendar',
								'mycrm_quotesRelCalendar','mycrm_purchaseorderRelCalendar','mycrm_invoiceRelCalendar',
								'mycrm_salesorderRelCalendar','mycrm_troubleticketsRelCalendar','mycrm_campaignRelCalendar'));
		$matrix->setDependency('mycrm_activity',array('mycrm_crmentityCalendar','mycrm_cntactivityrel','mycrm_activitycf',
								'mycrm_seactivityrel','mycrm_activity_reminder','mycrm_recurringevents'));

		if (!$queryPlanner->requireTable('mycrm_activity', $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module,$secmodule,"mycrm_activity","activityid", $queryPlanner);

		if ($queryPlanner->requireTable("mycrm_crmentityCalendar",$matrix)){
			$query .=" left join mycrm_crmentity as mycrm_crmentityCalendar on mycrm_crmentityCalendar.crmid=mycrm_activity.activityid and mycrm_crmentityCalendar.deleted=0";
		}
		if ($queryPlanner->requireTable("mycrm_cntactivityrel",$matrix)){
			$query .=" 	left join mycrm_cntactivityrel on mycrm_cntactivityrel.activityid= mycrm_activity.activityid";
		}
		if ($queryPlanner->requireTable("mycrm_contactdetailsCalendar")){
			$query .=" 	left join mycrm_contactdetails as mycrm_contactdetailsCalendar on mycrm_contactdetailsCalendar.contactid= mycrm_cntactivityrel.contactid";
		}
		if ($queryPlanner->requireTable("mycrm_activitycf")){
			$query .=" 	left join mycrm_activitycf on mycrm_activitycf.activityid = mycrm_activity.activityid";
		}
		if ($queryPlanner->requireTable("mycrm_seactivityrel",$matrix)){
			$query .=" 	left join mycrm_seactivityrel on mycrm_seactivityrel.activityid = mycrm_activity.activityid";
		}
		if ($queryPlanner->requireTable("mycrm_activity_reminder")){
			$query .=" 	left join mycrm_activity_reminder on mycrm_activity_reminder.activity_id = mycrm_activity.activityid";
		}
		if ($queryPlanner->requireTable("mycrm_recurringevents")){
			$query .=" 	left join mycrm_recurringevents on mycrm_recurringevents.activityid = mycrm_activity.activityid";
		}
		if ($queryPlanner->requireTable("mycrm_crmentityRelCalendar",$matrix)){
			$query .=" 	left join mycrm_crmentity as mycrm_crmentityRelCalendar on mycrm_crmentityRelCalendar.crmid = mycrm_seactivityrel.crmid and mycrm_crmentityRelCalendar.deleted=0";
		}
		if ($queryPlanner->requireTable("mycrm_accountRelCalendar")){
			$query .=" 	left join mycrm_account as mycrm_accountRelCalendar on mycrm_accountRelCalendar.accountid=mycrm_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("mycrm_leaddetailsRelCalendar")){
			$query .=" 	left join mycrm_leaddetails as mycrm_leaddetailsRelCalendar on mycrm_leaddetailsRelCalendar.leadid = mycrm_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("mycrm_potentialRelCalendar")){
			$query .=" 	left join mycrm_potential as mycrm_potentialRelCalendar on mycrm_potentialRelCalendar.potentialid = mycrm_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("mycrm_quotesRelCalendar")){
			$query .=" 	left join mycrm_quotes as mycrm_quotesRelCalendar on mycrm_quotesRelCalendar.quoteid = mycrm_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("mycrm_purchaseorderRelCalendar")){
			$query .=" 	left join mycrm_purchaseorder as mycrm_purchaseorderRelCalendar on mycrm_purchaseorderRelCalendar.purchaseorderid = mycrm_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("mycrm_invoiceRelCalendar")){
			$query .=" 	left join mycrm_invoice as mycrm_invoiceRelCalendar on mycrm_invoiceRelCalendar.invoiceid = mycrm_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("mycrm_salesorderRelCalendar")){
			$query .=" 	left join mycrm_salesorder as mycrm_salesorderRelCalendar on mycrm_salesorderRelCalendar.salesorderid = mycrm_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("mycrm_troubleticketsRelCalendar")){
			$query .=" left join mycrm_troubletickets as mycrm_troubleticketsRelCalendar on mycrm_troubleticketsRelCalendar.ticketid = mycrm_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("mycrm_campaignRelCalendar")){
			$query .=" 	left join mycrm_campaign as mycrm_campaignRelCalendar on mycrm_campaignRelCalendar.campaignid = mycrm_crmentityRelCalendar.crmid";
		}
		if ($queryPlanner->requireTable("mycrm_groupsCalendar")){
			$query .=" left join mycrm_groups as mycrm_groupsCalendar on mycrm_groupsCalendar.groupid = mycrm_crmentityCalendar.smownerid";
		}
		if ($queryPlanner->requireTable("mycrm_usersCalendar")){
			$query .=" 	left join mycrm_users as mycrm_usersCalendar on mycrm_usersCalendar.id = mycrm_crmentityCalendar.smownerid";
		}
		if ($queryPlanner->requireTable("mycrm_lastModifiedByCalendar")){
			$query .="  left join mycrm_users as mycrm_lastModifiedByCalendar on mycrm_lastModifiedByCalendar.id = mycrm_crmentityCalendar.modifiedby ";
		}
        if ($queryPlanner->requireTable("mycrm_createdbyCalendar")){
			$query .= " left join mycrm_users as mycrm_createdbyCalendar on mycrm_createdbyCalendar.id = mycrm_crmentityCalendar.smcreatorid ";
		}
		return $query;
	}

	public function getNonAdminAccessControlQuery($module, $user,$scope='') {
		require('user_privileges/user_privileges_'.$user->id.'.php');
		require('user_privileges/sharing_privileges_'.$user->id.'.php');
		$query = ' ';
		$tabId = getTabid($module);
		if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2]
				== 1 && $defaultOrgSharingPermission[$tabId] == 3) {
			$tableName = 'vt_tmp_u'.$user->id.'_t'.$tabId;
			$sharingRuleInfoVariable = $module.'_share_read_permission';
			$sharingRuleInfo = $$sharingRuleInfoVariable;
			$sharedTabId = null;
			$this->setupTemporaryTable($tableName, $sharedTabId, $user,
					$current_user_parent_role_seq, $current_user_groups);

			$sharedUsers = $this->getListViewAccessibleUsers($user->id);
            // we need to include group id's in $sharedUsers list to get the current user's group records
            if($current_user_groups){
                $sharedUsers = $sharedUsers.','. implode(',',$current_user_groups);
            }
			$query = " INNER JOIN $tableName $tableName$scope ON ($tableName$scope.id = ".
					"mycrm_crmentity$scope.smownerid and $tableName$scope.shared=0 and $tableName$scope.id IN ($sharedUsers)) ";
		}
		return $query;
	}

    /**
     * To get non admin access query for Reports generation
     * @param type $tableName
     * @param type $tabId
     * @param type $user
     * @param type $parent_roles
     * @param type $groups
     * @return $query
     */
    public function getReportsNonAdminAccessControlQuery($tableName, $tabId, $user, $parent_roles,$groups){
        $sharedUsers = $this->getListViewAccessibleUsers($user->id);
        $this->setupTemporaryTable($tableName, $tabId, $user, $parent_roles,$groups);
        $query = "SELECT id FROM $tableName WHERE $tableName.shared=0 AND $tableName.id IN ($sharedUsers)";
        return $query;
    }

	protected function setupTemporaryTable($tableName, $tabId, $user, $parentRole, $userGroups) {
		$module = null;
		if (!empty($tabId)) {
			$module = getTabname($tabId);
		}
		$query = $this->getNonAdminAccessQuery($module, $user, $parentRole, $userGroups);
		$query = "create temporary table IF NOT EXISTS $tableName(id int(11) primary key, shared ".
			"int(1) default 0) ignore ".$query;
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array());
		if(is_object($result)) {
			$query = "REPLACE INTO $tableName (id) SELECT userid as id FROM mycrm_sharedcalendar WHERE sharedid = ?";
			$result = $db->pquery($query, array($user->id));

			//For newly created users, entry will not be there in mycrm_sharedcalendar table
			//so, consider the users whose having the calendarsharedtype is public
			$query = "REPLACE INTO $tableName (id) SELECT id FROM mycrm_users WHERE calendarsharedtype = ?";
			$result = $db->pquery($query, array('public'));

			if(is_object($result)) {
				return true;
			}
		}
		return false;
	}

	protected function getListViewAccessibleUsers($sharedid) {
		$db = PearDatabase::getInstance();;
		$query = "SELECT mycrm_users.id as userid FROM mycrm_sharedcalendar
					RIGHT JOIN mycrm_users ON mycrm_sharedcalendar.userid=mycrm_users.id and status= 'Active'
					WHERE sharedid=? OR (mycrm_users.status='Active' AND mycrm_users.calendarsharedtype='public' AND mycrm_users.id <> ?);";
			$result = $db->pquery($query, array($sharedid, $sharedid));
			$rows = $db->num_rows($result);
		if($db->num_rows($result)!=0)
		{
			for($j=0;$j<$db->num_rows($result);$j++) {
				$userid[] = $db->query_result($result,$j,'userid');
			}
			$shared_ids = implode (",",$userid);
		}
		$userid[] = $sharedid;
		$shared_ids = implode (",",$userid);
		return $shared_ids;
	}
}
?>
