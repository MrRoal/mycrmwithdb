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
 * $Header: /advent/projects/wesat/mycrm_crm/sugarcrm/modules/Potentials/Potentials.php,v 1.65 2005/04/28 08:08:27 rank Exp $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

class Potentials extends CRMEntity {
	var $log;
	var $db;

	var $module_name="Potentials";
	var $table_name = "mycrm_potential";
	var $table_index= 'potentialid';

	var $tab_name = Array('mycrm_crmentity','mycrm_potential','mycrm_potentialscf');
	var $tab_name_index = Array('mycrm_crmentity'=>'crmid','mycrm_potential'=>'potentialid','mycrm_potentialscf'=>'potentialid');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('mycrm_potentialscf', 'potentialid');

	var $column_fields = Array();

	var $sortby_fields = Array('potentialname','amount','closingdate','smownerid','accountname');

	// This is the list of mycrm_fields that are in the lists.
	var $list_fields = Array(
			'Potential'=>Array('potential'=>'potentialname'),
			'Organization Name'=>Array('potential'=>'related_to'),
			'Contact Name'=>Array('potential'=>'contact_id'),
			'Sales Stage'=>Array('potential'=>'sales_stage'),
			'Amount'=>Array('potential'=>'amount'),
			'Expected Close Date'=>Array('potential'=>'closingdate'),
			'Assigned To'=>Array('crmentity','smownerid')
			);

	var $list_fields_name = Array(
			'Potential'=>'potentialname',
			'Organization Name'=>'related_to',
			'Contact Name'=>'contact_id',
			'Sales Stage'=>'sales_stage',
			'Amount'=>'amount',
			'Expected Close Date'=>'closingdate',
			'Assigned To'=>'assigned_user_id');

	var $list_link_field= 'potentialname';

	var $search_fields = Array(
			'Potential'=>Array('potential'=>'potentialname'),
			'Related To'=>Array('potential'=>'related_to'),
			'Expected Close Date'=>Array('potential'=>'closedate')
			);

	var $search_fields_name = Array(
			'Potential'=>'potentialname',
			'Related To'=>'related_to',
			'Expected Close Date'=>'closingdate'
			);

	var $required_fields =  array();

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to mycrm_field.fieldname values.
	var $mandatory_fields = Array('assigned_user_id', 'createdtime', 'modifiedtime', 'potentialname');

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'potentialname';
	var $default_sort_order = 'ASC';

	// For Alphabetical search
	var $def_basicsearch_col = 'potentialname';

	//var $groupTable = Array('mycrm_potentialgrouprelation','potentialid');
	function Potentials() {
		$this->log = LoggerManager::getLogger('potential');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Potentials');
	}

	function save_module($module)
	{
	}

	/** Function to create list query
	* @param reference variable - where condition is passed when the query is executed
	* Returns Query.
	*/
	function create_list_query($order_by, $where)
	{
		global $log,$current_user;
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
	        require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
        	$tab_id = getTabid("Potentials");
		$log->debug("Entering create_list_query(".$order_by.",". $where.") method ...");
		// Determine if the mycrm_account name is present in the where clause.
		$account_required = preg_match("/accounts\.name/", $where);

		if($account_required)
		{
			$query = "SELECT mycrm_potential.potentialid,  mycrm_potential.potentialname, mycrm_potential.dateclosed FROM mycrm_potential, mycrm_account ";
			$where_auto = "account.accountid = mycrm_potential.related_to AND mycrm_crmentity.deleted=0 ";
		}
		else
		{
			$query = 'SELECT mycrm_potential.potentialid, mycrm_potential.potentialname, mycrm_crmentity.smcreatorid, mycrm_potential.closingdate FROM mycrm_potential inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_potential.potentialid LEFT JOIN mycrm_groups on mycrm_groups.groupid = mycrm_crmentity.smownerid left join mycrm_users on mycrm_users.id = mycrm_crmentity.smownerid ';
			$where_auto = ' AND mycrm_crmentity.deleted=0';
		}

		$query .= $this->getNonAdminAccessControlQuery('Potentials',$current_user);
		if($where != "")
			$query .= " where $where ".$where_auto;
		else
			$query .= " where ".$where_auto;
		if($order_by != "")
			$query .= " ORDER BY $order_by";

		$log->debug("Exiting create_list_query method ...");
		return $query;
	}

	/** Function to export the Opportunities records in CSV Format
	* @param reference variable - order by is passed when the query is executed
	* @param reference variable - where condition is passed when the query is executed
	* Returns Export Potentials Query.
	*/
	function create_export_query($where)
	{
		global $log;
		global $current_user;
		$log->debug("Entering create_export_query(". $where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Potentials", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "SELECT $fields_list,case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name
				FROM mycrm_potential
				inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_potential.potentialid
				LEFT JOIN mycrm_users ON mycrm_crmentity.smownerid=mycrm_users.id
				LEFT JOIN mycrm_account on mycrm_potential.related_to=mycrm_account.accountid
				LEFT JOIN mycrm_contactdetails on mycrm_potential.contact_id=mycrm_contactdetails.contactid
				LEFT JOIN mycrm_potentialscf on mycrm_potentialscf.potentialid=mycrm_potential.potentialid
                LEFT JOIN mycrm_groups
        	        ON mycrm_groups.groupid = mycrm_crmentity.smownerid
				LEFT JOIN mycrm_campaign
					ON mycrm_campaign.campaignid = mycrm_potential.campaignid";

		$query .= $this->getNonAdminAccessControlQuery('Potentials',$current_user);
		$where_auto = "  mycrm_crmentity.deleted = 0 ";

                if($where != "")
                   $query .= "  WHERE ($where) AND ".$where_auto;
                else
                   $query .= "  WHERE ".$where_auto;

		$log->debug("Exiting create_export_query method ...");
		return $query;

	}



	/** Returns a list of the associated contacts
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
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

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		$accountid = $this->column_fields['related_to'];
		$search_string = "&fromPotential=true&acc_id=$accountid";

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab$search_string','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = 'select case when (mycrm_users.user_name not like "") then '.$userNameSql.' else mycrm_groups.groupname end as user_name,
					mycrm_contactdetails.accountid,mycrm_potential.potentialid, mycrm_potential.potentialname, mycrm_contactdetails.contactid,
					mycrm_contactdetails.lastname, mycrm_contactdetails.firstname, mycrm_contactdetails.title, mycrm_contactdetails.department,
					mycrm_contactdetails.email, mycrm_contactdetails.phone, mycrm_crmentity.crmid, mycrm_crmentity.smownerid,
					mycrm_crmentity.modifiedtime , mycrm_account.accountname from mycrm_potential
					left join mycrm_contpotentialrel on mycrm_contpotentialrel.potentialid = mycrm_potential.potentialid
					inner join mycrm_contactdetails on ((mycrm_contactdetails.contactid = mycrm_contpotentialrel.contactid) or (mycrm_contactdetails.contactid = mycrm_potential.contact_id))
					INNER JOIN mycrm_contactaddress ON mycrm_contactdetails.contactid = mycrm_contactaddress.contactaddressid
					INNER JOIN mycrm_contactsubdetails ON mycrm_contactdetails.contactid = mycrm_contactsubdetails.contactsubscriptionid
					INNER JOIN mycrm_customerdetails ON mycrm_contactdetails.contactid = mycrm_customerdetails.customerid
					INNER JOIN mycrm_contactscf ON mycrm_contactdetails.contactid = mycrm_contactscf.contactid
					inner join mycrm_crmentity on mycrm_crmentity.crmid = mycrm_contactdetails.contactid
					left join mycrm_account on mycrm_account.accountid = mycrm_contactdetails.accountid
					left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid
					left join mycrm_users on mycrm_crmentity.smownerid=mycrm_users.id
					where mycrm_potential.potentialid = '.$id.' and mycrm_crmentity.deleted=0';

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
	}

	/** Returns a list of the associated calls
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	 */
	function get_activities($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_activities(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/Activity.php");
		$other = new Activity();
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		$button .= '<input type="hidden" name="activity_mode">';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				if(getFieldVisibilityPermission('Calendar',$current_user->id,'parent_id', 'readwrite') == '0') {
					$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString('LBL_TODO', $related_module) ."' class='crmbutton small create'" .
						" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.return_module.value=\"$this_module\";this.form.activity_mode.value=\"Task\";' type='submit' name='button'" .
						" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString('LBL_TODO', $related_module) ."'>&nbsp;";
				}
				if(getFieldVisibilityPermission('Events',$current_user->id,'parent_id', 'readwrite') == '0') {
					$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString('LBL_TODO', $related_module) ."' class='crmbutton small create'" .
						" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.return_module.value=\"$this_module\";this.form.activity_mode.value=\"Events\";' type='submit' name='button'" .
						" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString('LBL_EVENT', $related_module) ."'>";
				}
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "SELECT mycrm_activity.activityid as 'tmp_activity_id',mycrm_activity.*,mycrm_seactivityrel.crmid as parent_id, mycrm_contactdetails.lastname,mycrm_contactdetails.firstname,
					mycrm_crmentity.crmid, mycrm_crmentity.smownerid, mycrm_crmentity.modifiedtime,
					case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,
					mycrm_recurringevents.recurringtype from mycrm_activity
					inner join mycrm_seactivityrel on mycrm_seactivityrel.activityid=mycrm_activity.activityid
					inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_activity.activityid
					left join mycrm_cntactivityrel on mycrm_cntactivityrel.activityid = mycrm_activity.activityid
					left join mycrm_contactdetails on mycrm_contactdetails.contactid = mycrm_cntactivityrel.contactid
					inner join mycrm_potential on mycrm_potential.potentialid=mycrm_seactivityrel.crmid
					left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid
					left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid
					left outer join mycrm_recurringevents on mycrm_recurringevents.activityid=mycrm_activity.activityid
					where mycrm_seactivityrel.crmid=".$id." and mycrm_crmentity.deleted=0
					and ((mycrm_activity.activitytype='Task' and mycrm_activity.status not in ('Completed','Deferred'))
					or (mycrm_activity.activitytype NOT in ('Emails','Task') and  mycrm_activity.eventstatus not in ('','Held'))) ";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_activities method ...");
		return $return_value;
	}

	 /**
	 * Function to get Contact related Products
	 * @param  integer   $id  - contactid
	 * returns related Products record in array format
	 */
	function get_products($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_products(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$query = "SELECT mycrm_products.productid, mycrm_products.productname, mycrm_products.productcode,
				mycrm_products.commissionrate, mycrm_products.qty_per_unit, mycrm_products.unit_price,
				mycrm_crmentity.crmid, mycrm_crmentity.smownerid
				FROM mycrm_products
				INNER JOIN mycrm_seproductsrel ON mycrm_products.productid = mycrm_seproductsrel.productid and mycrm_seproductsrel.setype = 'Potentials'
				INNER JOIN mycrm_productcf
				ON mycrm_products.productid = mycrm_productcf.productid
				INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_products.productid
				INNER JOIN mycrm_potential ON mycrm_potential.potentialid = mycrm_seproductsrel.crmid
				LEFT JOIN mycrm_users
					ON mycrm_users.id=mycrm_crmentity.smownerid
				LEFT JOIN mycrm_groups
					ON mycrm_groups.groupid = mycrm_crmentity.smownerid
				WHERE mycrm_crmentity.deleted = 0 AND mycrm_potential.potentialid = $id";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	}

	/**	Function used to get the Sales Stage history of the Potential
	 *	@param $id - potentialid
	 *	return $return_data - array with header and the entries in format Array('header'=>$header,'entries'=>$entries_list) where as $header and $entries_list are array which contains all the column values of an row
	 */
	function get_stage_history($id)
	{
		global $log;
		$log->debug("Entering get_stage_history(".$id.") method ...");

		global $adb;
		global $mod_strings;
		global $app_strings;

		$query = 'select mycrm_potstagehistory.*, mycrm_potential.potentialname from mycrm_potstagehistory inner join mycrm_potential on mycrm_potential.potentialid = mycrm_potstagehistory.potentialid inner join mycrm_crmentity on mycrm_crmentity.crmid = mycrm_potential.potentialid where mycrm_crmentity.deleted = 0 and mycrm_potential.potentialid = ?';
		$result=$adb->pquery($query, array($id));
		$noofrows = $adb->num_rows($result);

		$header[] = $app_strings['LBL_AMOUNT'];
		$header[] = $app_strings['LBL_SALES_STAGE'];
		$header[] = $app_strings['LBL_PROBABILITY'];
		$header[] = $app_strings['LBL_CLOSE_DATE'];
		$header[] = $app_strings['LBL_LAST_MODIFIED'];

		//Getting the field permission for the current user. 1 - Not Accessible, 0 - Accessible
		//Sales Stage, Expected Close Dates are mandatory fields. So no need to do security check to these fields.
		global $current_user;

		//If field is accessible then getFieldVisibilityPermission function will return 0 else return 1
		$amount_access = (getFieldVisibilityPermission('Potentials', $current_user->id, 'amount') != '0')? 1 : 0;
		$probability_access = (getFieldVisibilityPermission('Potentials', $current_user->id, 'probability') != '0')? 1 : 0;
		$picklistarray = getAccessPickListValues('Potentials');

		$potential_stage_array = $picklistarray['sales_stage'];
		//- ==> picklist field is not permitted in profile
		//Not Accessible - picklist is permitted in profile but picklist value is not permitted
		$error_msg = 'Not Accessible';

		while($row = $adb->fetch_array($result))
		{
			$entries = Array();

			$entries[] = ($amount_access != 1)? $row['amount'] : 0;
			$entries[] = (in_array($row['stage'], $potential_stage_array))? $row['stage']: $error_msg;
			$entries[] = ($probability_access != 1) ? $row['probability'] : 0;
			$entries[] = DateTimeField::convertToUserFormat($row['closedate']);
			$date = new DateTimeField($row['lastmodified']);
			$entries[] = $date->getDisplayDate();

			$entries_list[] = $entries;
		}

		$return_data = Array('header'=>$header,'entries'=>$entries_list);

	 	$log->debug("Exiting get_stage_history method ...");

		return $return_data;
	}

	/**
	* Function to get Potential related Task & Event which have activity type Held, Completed or Deferred.
	* @param  integer   $id
	* returns related Task or Event record in array format
	*/
	function get_history($id)
	{
			global $log;
			$log->debug("Entering get_history(".$id.") method ...");
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
			$query = "SELECT mycrm_activity.activityid, mycrm_activity.subject, mycrm_activity.status,
		mycrm_activity.eventstatus, mycrm_activity.activitytype,mycrm_activity.date_start,
		mycrm_activity.due_date, mycrm_activity.time_start,mycrm_activity.time_end,
		mycrm_crmentity.modifiedtime, mycrm_crmentity.createdtime,
		mycrm_crmentity.description,case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name
				from mycrm_activity
				inner join mycrm_seactivityrel on mycrm_seactivityrel.activityid=mycrm_activity.activityid
				inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_activity.activityid
				left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid
				left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid
				where (mycrm_activity.activitytype != 'Emails')
				and (mycrm_activity.status = 'Completed' or mycrm_activity.status = 'Deferred' or (mycrm_activity.eventstatus = 'Held' and mycrm_activity.eventstatus != ''))
				and mycrm_seactivityrel.crmid=".$id."
                                and mycrm_crmentity.deleted = 0";
		//Don't add order by, because, for security, one more condition will be added with this query in include/RelatedListView.php

		$log->debug("Exiting get_history method ...");
		return getHistory('Potentials',$query,$id);
	}


	  /**
	  * Function to get Potential related Quotes
	  * @param  integer   $id  - potentialid
	  * returns related Quotes record in array format
	  */
	function get_quotes($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_quotes(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'potential_id', 'readwrite') == '0') {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "select case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,
					mycrm_account.accountname, mycrm_crmentity.*, mycrm_quotes.*, mycrm_potential.potentialname from mycrm_quotes
					inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_quotes.quoteid
					left outer join mycrm_potential on mycrm_potential.potentialid=mycrm_quotes.potentialid
					left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid
                    LEFT JOIN mycrm_quotescf ON mycrm_quotescf.quoteid = mycrm_quotes.quoteid
					LEFT JOIN mycrm_quotesbillads ON mycrm_quotesbillads.quotebilladdressid = mycrm_quotes.quoteid
					LEFT JOIN mycrm_quotesshipads ON mycrm_quotesshipads.quoteshipaddressid = mycrm_quotes.quoteid
					left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid
					LEFT join mycrm_account on mycrm_account.accountid=mycrm_quotes.accountid
					where mycrm_crmentity.deleted=0 and mycrm_potential.potentialid=".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_quotes method ...");
		return $return_value;
	}

	/**
	 * Function to get Potential related SalesOrder
 	 * @param  integer   $id  - potentialid
	 * returns related SalesOrder record in array format
	 */
	function get_salesorder($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_salesorder(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'potential_id', 'readwrite') == '0') {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "select mycrm_crmentity.*, mycrm_salesorder.*, mycrm_quotes.subject as quotename
			, mycrm_account.accountname, mycrm_potential.potentialname,case when
			(mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname
			end as user_name from mycrm_salesorder
			inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_salesorder.salesorderid
			left outer join mycrm_quotes on mycrm_quotes.quoteid=mycrm_salesorder.quoteid
			left outer join mycrm_account on mycrm_account.accountid=mycrm_salesorder.accountid
			left outer join mycrm_potential on mycrm_potential.potentialid=mycrm_salesorder.potentialid
			left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid
            LEFT JOIN mycrm_salesordercf ON mycrm_salesordercf.salesorderid = mycrm_salesorder.salesorderid
            LEFT JOIN mycrm_invoice_recurring_info ON mycrm_invoice_recurring_info.start_period = mycrm_salesorder.salesorderid
			LEFT JOIN mycrm_sobillads ON mycrm_sobillads.sobilladdressid = mycrm_salesorder.salesorderid
			LEFT JOIN mycrm_soshipads ON mycrm_soshipads.soshipaddressid = mycrm_salesorder.salesorderid
			left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid
			 where mycrm_crmentity.deleted=0 and mycrm_potential.potentialid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_salesorder method ...");
		return $return_value;
	}

	/**
	 * Move the related records of the specified list of id's to the given record.
	 * @param String This module name
	 * @param Array List of Entity Id's from which related records need to be transfered
	 * @param Integer Id of the the Record to which the related records are to be moved
	 */
	function transferRelatedRecords($module, $transferEntityIds, $entityId) {
		global $adb,$log;
		$log->debug("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$rel_table_arr = Array("Activities"=>"mycrm_seactivityrel","Contacts"=>"mycrm_contpotentialrel","Products"=>"mycrm_seproductsrel",
						"Attachments"=>"mycrm_seattachmentsrel","Quotes"=>"mycrm_quotes","SalesOrder"=>"mycrm_salesorder",
						"Documents"=>"mycrm_senotesrel");

		$tbl_field_arr = Array("mycrm_seactivityrel"=>"activityid","mycrm_contpotentialrel"=>"contactid","mycrm_seproductsrel"=>"productid",
						"mycrm_seattachmentsrel"=>"attachmentsid","mycrm_quotes"=>"quoteid","mycrm_salesorder"=>"salesorderid",
						"mycrm_senotesrel"=>"notesid");

		$entity_tbl_field_arr = Array("mycrm_seactivityrel"=>"crmid","mycrm_contpotentialrel"=>"potentialid","mycrm_seproductsrel"=>"crmid",
						"mycrm_seattachmentsrel"=>"crmid","mycrm_quotes"=>"potentialid","mycrm_salesorder"=>"potentialid",
						"mycrm_senotesrel"=>"crmid");

		foreach($transferEntityIds as $transferId) {
			foreach($rel_table_arr as $rel_module=>$rel_table) {
				$id_field = $tbl_field_arr[$rel_table];
				$entity_id_field = $entity_tbl_field_arr[$rel_table];
				// IN clause to avoid duplicate entries
				$sel_result =  $adb->pquery("select $id_field from $rel_table where $entity_id_field=? " .
						" and $id_field not in (select $id_field from $rel_table where $entity_id_field=?)",
						array($transferId,$entityId));
				$res_cnt = $adb->num_rows($sel_result);
				if($res_cnt > 0) {
					for($i=0;$i<$res_cnt;$i++) {
						$id_field_value = $adb->query_result($sel_result,$i,$id_field);
						$adb->pquery("update $rel_table set $entity_id_field=? where $entity_id_field=? and $id_field=?",
							array($entityId,$transferId,$id_field_value));
					}
				}
			}
		}
		parent::transferRelatedRecords($module, $transferEntityIds, $entityId);
		$log->debug("Exiting transferRelatedRecords...");
	}

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule,$queryplanner){
		$matrix = $queryplanner->newDependencyMatrix();
		$matrix->setDependency('mycrm_crmentityPotentials',array('mycrm_groupsPotentials','mycrm_usersPotentials','mycrm_lastModifiedByPotentials'));
		$matrix->setDependency('mycrm_potential', array('mycrm_crmentityPotentials','mycrm_accountPotentials',
											'mycrm_contactdetailsPotentials','mycrm_campaignPotentials','mycrm_potentialscf'));


		if (!$queryplanner->requireTable("mycrm_potential",$matrix)){
			return '';
		}

		$query = $this->getRelationQuery($module,$secmodule,"mycrm_potential","potentialid", $queryplanner);

		if ($queryplanner->requireTable("mycrm_crmentityPotentials",$matrix)){
			$query .= " left join mycrm_crmentity as mycrm_crmentityPotentials on mycrm_crmentityPotentials.crmid=mycrm_potential.potentialid and mycrm_crmentityPotentials.deleted=0";
		}
		if ($queryplanner->requireTable("mycrm_accountPotentials")){
			$query .= " left join mycrm_account as mycrm_accountPotentials on mycrm_potential.related_to = mycrm_accountPotentials.accountid";
		}
		if ($queryplanner->requireTable("mycrm_contactdetailsPotentials")){
			$query .= " left join mycrm_contactdetails as mycrm_contactdetailsPotentials on mycrm_potential.contact_id = mycrm_contactdetailsPotentials.contactid";
		}
		if ($queryplanner->requireTable("mycrm_potentialscf")){
			$query .= " left join mycrm_potentialscf on mycrm_potentialscf.potentialid = mycrm_potential.potentialid";
		}
		if ($queryplanner->requireTable("mycrm_groupsPotentials")){
			$query .= " left join mycrm_groups mycrm_groupsPotentials on mycrm_groupsPotentials.groupid = mycrm_crmentityPotentials.smownerid";
		}
		if ($queryplanner->requireTable("mycrm_usersPotentials")){
			$query .= " left join mycrm_users as mycrm_usersPotentials on mycrm_usersPotentials.id = mycrm_crmentityPotentials.smownerid";
		}
		if ($queryplanner->requireTable("mycrm_campaignPotentials")){
			$query .= " left join mycrm_campaign as mycrm_campaignPotentials on mycrm_potential.campaignid = mycrm_campaignPotentials.campaignid";
		}
		if ($queryplanner->requireTable("mycrm_lastModifiedByPotentials")){
			$query .= " left join mycrm_users as mycrm_lastModifiedByPotentials on mycrm_lastModifiedByPotentials.id = mycrm_crmentityPotentials.modifiedby ";
		}
        if ($queryplanner->requireTable("mycrm_createdbyPotentials")){
			$query .= " left join mycrm_users as mycrm_createdbyPotentials on mycrm_createdbyPotentials.id = mycrm_crmentityPotentials.smcreatorid ";
		}
		return $query;
	}

	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */
	function setRelationTables($secmodule){
		$rel_tables = array (
			"Calendar" => array("mycrm_seactivityrel"=>array("crmid","activityid"),"mycrm_potential"=>"potentialid"),
			"Products" => array("mycrm_seproductsrel"=>array("crmid","productid"),"mycrm_potential"=>"potentialid"),
			"Quotes" => array("mycrm_quotes"=>array("potentialid","quoteid"),"mycrm_potential"=>"potentialid"),
			"SalesOrder" => array("mycrm_salesorder"=>array("potentialid","salesorderid"),"mycrm_potential"=>"potentialid"),
			"Documents" => array("mycrm_senotesrel"=>array("crmid","notesid"),"mycrm_potential"=>"potentialid"),
			"Accounts" => array("mycrm_potential"=>array("potentialid","related_to")),
			"Contacts" => array("mycrm_potential"=>array("potentialid","contact_id")),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink all the dependent entities of the given Entity by Id
	function unlinkDependencies($module, $id) {
		global $log;
		/*//Backup Activity-Potentials Relation
		$act_q = "select activityid from mycrm_seactivityrel where crmid = ?";
		$act_res = $this->db->pquery($act_q, array($id));
		if ($this->db->num_rows($act_res) > 0) {
			for($k=0;$k < $this->db->num_rows($act_res);$k++)
			{
				$act_id = $this->db->query_result($act_res,$k,"activityid");
				$params = array($id, RB_RECORD_DELETED, 'mycrm_seactivityrel', 'crmid', 'activityid', $act_id);
				$this->db->pquery("insert into mycrm_relatedlists_rb values (?,?,?,?,?,?)", $params);
			}
		}
		$sql = 'delete from mycrm_seactivityrel where crmid = ?';
		$this->db->pquery($sql, array($id));*/

		parent::unlinkDependencies($module, $id);
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Accounts') {
			$this->trash($this->module_name, $id);
		} elseif($return_module == 'Campaigns') {
			$sql = 'UPDATE mycrm_potential SET campaignid = ? WHERE potentialid = ?';
			$this->db->pquery($sql, array(null, $id));
		} elseif($return_module == 'Products') {
			$sql = 'DELETE FROM mycrm_seproductsrel WHERE crmid=? AND productid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif($return_module == 'Contacts') {
			$sql = 'DELETE FROM mycrm_contpotentialrel WHERE potentialid=? AND contactid=?';
			$this->db->pquery($sql, array($id, $return_id));

			//If contact related to potential through edit of record,that entry will be present in
			//mycrm_potential contact_id column,which should be set to zero
			$sql = 'UPDATE mycrm_potential SET contact_id = ? WHERE potentialid=? AND contact_id=?';
			$this->db->pquery($sql, array(0,$id, $return_id));

			// Potential directly linked with Contact (not through Account - mycrm_contpotentialrel)
			$directRelCheck = $this->db->pquery('SELECT related_to FROM mycrm_potential WHERE potentialid=? AND contact_id=?', array($id, $return_id));
			if($this->db->num_rows($directRelCheck)) {
				$this->trash($this->module_name, $id);
			}

		} else {
			$sql = 'DELETE FROM mycrm_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}

	function save_related_module($module, $crmid, $with_module, $with_crmids) {
		$adb = PearDatabase::getInstance();

		if(!is_array($with_crmids)) $with_crmids = Array($with_crmids);
		foreach($with_crmids as $with_crmid) {
			if($with_module == 'Contacts') { //When we select contact from potential related list
				$sql = "insert into mycrm_contpotentialrel values (?,?)";
				$adb->pquery($sql, array($with_crmid, $crmid));

			} elseif($with_module == 'Products') {//when we select product from potential related list
				$sql = "insert into mycrm_seproductsrel values (?,?,?)";
				$adb->pquery($sql, array($crmid, $with_crmid,'Potentials'));

			} else {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid);
			}
		}
	}

}
?>