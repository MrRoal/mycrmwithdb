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
 * $Header: /advent/projects/wesat/mycrm_crm/sugarcrm/modules/Contacts/Contacts.php,v 1.70 2005/04/27 11:21:49 rank Exp $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
// Contact is used to store customer information.
class Contacts extends CRMEntity {
	var $log;
	var $db;

	var $table_name = "mycrm_contactdetails";
	var $table_index= 'contactid';
	var $tab_name = Array('mycrm_crmentity','mycrm_contactdetails','mycrm_contactaddress','mycrm_contactsubdetails','mycrm_contactscf','mycrm_customerdetails');
	var $tab_name_index = Array('mycrm_crmentity'=>'crmid','mycrm_contactdetails'=>'contactid','mycrm_contactaddress'=>'contactaddressid','mycrm_contactsubdetails'=>'contactsubscriptionid','mycrm_contactscf'=>'contactid','mycrm_customerdetails'=>'customerid');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('mycrm_contactscf', 'contactid');

	var $column_fields = Array();

	var $sortby_fields = Array('lastname','firstname','title','email','phone','smownerid','accountname');

	var $list_link_field= 'lastname';

	// This is the list of mycrm_fields that are in the lists.
	var $list_fields = Array(
	'First Name' => Array('contactdetails'=>'firstname'),
	'Last Name' => Array('contactdetails'=>'lastname'),
	'Title' => Array('contactdetails'=>'title'),
	'Account Name' => Array('account'=>'accountid'),
	'Email' => Array('contactdetails'=>'email'),
	'Office Phone' => Array('contactdetails'=>'phone'),
	'Assigned To' => Array('crmentity'=>'smownerid')
	);

	var $range_fields = Array(
		'first_name',
		'last_name',
		'primary_address_city',
		'account_name',
		'account_id',
		'id',
		'email1',
		'salutation',
		'title',
		'phone_mobile',
		'reports_to_name',
		'primary_address_street',
		'primary_address_city',
		'primary_address_state',
		'primary_address_postalcode',
		'primary_address_country',
		'alt_address_city',
		'alt_address_street',
		'alt_address_city',
		'alt_address_state',
		'alt_address_postalcode',
		'alt_address_country',
		'office_phone',
		'home_phone',
		'other_phone',
		'fax',
		'department',
		'birthdate',
		'assistant_name',
		'assistant_phone');


	var $list_fields_name = Array(
	'First Name' => 'firstname',
	'Last Name' => 'lastname',
	'Title' => 'title',
	'Account Name' => 'account_id',
	'Email' => 'email',
	'Office Phone' => 'phone',
	'Assigned To' => 'assigned_user_id'
	);

	var $search_fields = Array(
	'First Name' => Array('contactdetails'=>'firstname'),
	'Last Name' => Array('contactdetails'=>'lastname'),
	'Title' => Array('contactdetails'=>'title'),
	'Account Name'=>Array('contactdetails'=>'account_id'),
	'Assigned To'=>Array('crmentity'=>'smownerid'),
		);

	var $search_fields_name = Array(
	'First Name' => 'firstname',
	'Last Name' => 'lastname',
	'Title' => 'title',
	'Account Name'=>'account_id',
	'Assigned To'=>'assigned_user_id'
	);

	// This is the list of mycrm_fields that are required
	var $required_fields =  array("lastname"=>1);

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to mycrm_field.fieldname values.
	var $mandatory_fields = Array('assigned_user_id','lastname','createdtime' ,'modifiedtime');

	//Default Fields for Email Templates -- Pavani
	var $emailTemplate_defaultFields = array('firstname','lastname','salutation','title','email','department','phone','mobile','support_start_date','support_end_date');

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'lastname';
	var $default_sort_order = 'ASC';

	// For Alphabetical search
	var $def_basicsearch_col = 'lastname';

	var $related_module_table_index = array(
		'Potentials' => array('table_name' => 'mycrm_potential', 'table_index' => 'potentialid', 'rel_index' => 'contact_id'),
		'Quotes' => array('table_name' => 'mycrm_quotes', 'table_index' => 'quoteid', 'rel_index' => 'contactid'),
		'SalesOrder' => array('table_name' => 'mycrm_salesorder', 'table_index' => 'salesorderid', 'rel_index' => 'contactid'),
		'PurchaseOrder' => array('table_name' => 'mycrm_purchaseorder', 'table_index' => 'purchaseorderid', 'rel_index' => 'contactid'),
		'Invoice' => array('table_name' => 'mycrm_invoice', 'table_index' => 'invoiceid', 'rel_index' => 'contactid'),
		'HelpDesk' => array('table_name' => 'mycrm_troubletickets', 'table_index' => 'ticketid', 'rel_index' => 'contact_id'),
		'Products' => array('table_name' => 'mycrm_seproductsrel', 'table_index' => 'productid', 'rel_index' => 'crmid'),
		'Calendar' => array('table_name' => 'mycrm_cntactivityrel', 'table_index' => 'activityid', 'rel_index' => 'contactid'),
		'Documents' => array('table_name' => 'mycrm_senotesrel', 'table_index' => 'notesid', 'rel_index' => 'crmid'),
		'ServiceContracts' => array('table_name' => 'mycrm_servicecontracts', 'table_index' => 'servicecontractsid', 'rel_index' => 'sc_related_to'),
		'Services' => array('table_name' => 'mycrm_crmentityrel', 'table_index' => 'crmid', 'rel_index' => 'crmid'),
		'Campaigns' => array('table_name' => 'mycrm_campaigncontrel', 'table_index' => 'campaignid', 'rel_index' => 'contactid'),
		'Assets' => array('table_name' => 'mycrm_assets', 'table_index' => 'assetsid', 'rel_index' => 'contact'),
		'Project' => array('table_name' => 'mycrm_project', 'table_index' => 'projectid', 'rel_index' => 'linktoaccountscontacts'),
		'Emails' => array('table_name' => 'mycrm_seactivityrel', 'table_index' => 'crmid', 'rel_index' => 'activityid'),
	);

	function Contacts() {
		$this->log = LoggerManager::getLogger('contact');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Contacts');
	}

	// Mike Crowe Mod --------------------------------------------------------Default ordering for us
	/** Function to get the number of Contacts assigned to a particular User.
	*  @param varchar $user name - Assigned to User
	*  Returns the count of contacts assigned to user.
	*/
	function getCount($user_name)
	{
		global $log;
		$log->debug("Entering getCount(".$user_name.") method ...");
		$query = "select count(*) from mycrm_contactdetails  inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_contactdetails.contactid inner join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid where user_name=? and mycrm_crmentity.deleted=0";
		$result = $this->db->pquery($query,array($user_name),true,"Error retrieving contacts count");
		$rows_found =  $this->db->getRowCount($result);
		$row = $this->db->fetchByAssoc($result, 0);


		$log->debug("Exiting getCount method ...");
		return $row["count(*)"];
	}

	// This function doesn't seem to be used anywhere. Need to check and remove it.
	/** Function to get the Contact Details assigned to a particular User based on the starting count and the number of subsequent records.
	*  @param varchar $user_name - Assigned User
	*  @param integer $from_index - Initial record number to be displayed
	*  @param integer $offset - Count of the subsequent records to be displayed.
	*  Returns Query.
	*/
    function get_contacts($user_name,$from_index,$offset)
    {
	global $log;
	$log->debug("Entering get_contacts(".$user_name.",".$from_index.",".$offset.") method ...");
      $query = "select mycrm_users.user_name,mycrm_groups.groupname,mycrm_contactdetails.department department, mycrm_contactdetails.phone office_phone, mycrm_contactdetails.fax fax, mycrm_contactsubdetails.assistant assistant_name, mycrm_contactsubdetails.otherphone other_phone, mycrm_contactsubdetails.homephone home_phone,mycrm_contactsubdetails.birthday birthdate, mycrm_contactdetails.lastname last_name,mycrm_contactdetails.firstname first_name,mycrm_contactdetails.contactid as id, mycrm_contactdetails.salutation as salutation, mycrm_contactdetails.email as email1,mycrm_contactdetails.title as title,mycrm_contactdetails.mobile as phone_mobile,mycrm_account.accountname as account_name,mycrm_account.accountid as account_id, mycrm_contactaddress.mailingcity as primary_address_city,mycrm_contactaddress.mailingstreet as primary_address_street, mycrm_contactaddress.mailingcountry as primary_address_country,mycrm_contactaddress.mailingstate as primary_address_state, mycrm_contactaddress.mailingzip as primary_address_postalcode,   mycrm_contactaddress.othercity as alt_address_city,mycrm_contactaddress.otherstreet as alt_address_street, mycrm_contactaddress.othercountry as alt_address_country,mycrm_contactaddress.otherstate as alt_address_state, mycrm_contactaddress.otherzip as alt_address_postalcode  from mycrm_contactdetails inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_contactdetails.contactid inner join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid left join mycrm_account on mycrm_account.accountid=mycrm_contactdetails.accountid left join mycrm_contactaddress on mycrm_contactaddress.contactaddressid=mycrm_contactdetails.contactid left join mycrm_contactsubdetails on mycrm_contactsubdetails.contactsubscriptionid = mycrm_contactdetails.contactid left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid left join mycrm_users on mycrm_crmentity.smownerid=mycrm_users.id where user_name='" .$user_name ."' and mycrm_crmentity.deleted=0 limit " .$from_index ."," .$offset;

	$log->debug("Exiting get_contacts method ...");
      return $this->process_list_query1($query);
    }


    /** Function to process list query for a given query
    *  @param $query
    *  Returns the results of query in array format
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
		   $contact = Array();
               for($index = 0 , $row = $this->db->fetchByAssoc($result, $index); $row && $index <$rows_found;$index++, $row = $this->db->fetchByAssoc($result, $index))

             {
                foreach($this->range_fields as $columnName)
                {
                    if (isset($row[$columnName])) {

                        $contact[$columnName] = $row[$columnName];
                    }
                    else
                    {
                            $contact[$columnName] = "";
                    }
	     }
// TODO OPTIMIZE THE QUERY ACCOUNT NAME AND ID are set separetly for every mycrm_contactdetails and hence
// mycrm_account query goes for ecery single mycrm_account row

                    $list[] = $contact;
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


    /** Function to process list query for Plugin with Security Parameters for a given query
    *  @param $query
    *  Returns the results of query in array format
    */
    function plugin_process_list_query($query)
    {
          global $log,$adb,$current_user;
          $log->debug("Entering process_list_query1(".$query.") method ...");
          $permitted_field_lists = Array();
          require('user_privileges/user_privileges_'.$current_user->id.'.php');
          if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)
          {
              $sql1 = "select columnname from mycrm_field where tabid=4 and block <> 75 and mycrm_field.presence in (0,2)";
			  $params1 = array();
          }else
          {
              $profileList = getCurrentUserProfileList();
              $sql1 = "select columnname from mycrm_field inner join mycrm_profile2field on mycrm_profile2field.fieldid=mycrm_field.fieldid inner join mycrm_def_org_field on mycrm_def_org_field.fieldid=mycrm_field.fieldid where mycrm_field.tabid=4 and mycrm_field.block <> 6 and mycrm_field.block <> 75 and mycrm_field.displaytype in (1,2,4,3) and mycrm_profile2field.visible=0 and mycrm_def_org_field.visible=0 and mycrm_field.presence in (0,2)";
			  $params1 = array();
			  if (count($profileList) > 0) {
			  	 $sql1 .= " and mycrm_profile2field.profileid in (". generateQuestionMarks($profileList) .")";
			  	 array_push($params1, $profileList);
			  }
          }
          $result1 = $this->db->pquery($sql1, $params1);
          for($i=0;$i < $adb->num_rows($result1);$i++)
          {
              $permitted_field_lists[] = $adb->query_result($result1,$i,'columnname');
          }

          $result =& $this->db->query($query,true,"Error retrieving $this->object_name list: ");
          $list = Array();
          $rows_found =  $this->db->getRowCount($result);
          if($rows_found != 0)
          {
              for($index = 0 , $row = $this->db->fetchByAssoc($result, $index); $row && $index <$rows_found;$index++, $row = $this->db->fetchByAssoc($result, $index))
              {
                  $contact = Array();

		  $contact[lastname] = in_array("lastname",$permitted_field_lists) ? $row[lastname] : "";
		  $contact[firstname] = in_array("firstname",$permitted_field_lists)? $row[firstname] : "";
		  $contact[email] = in_array("email",$permitted_field_lists) ? $row[email] : "";


                  if(in_array("accountid",$permitted_field_lists))
                  {
                      $contact[accountname] = $row[accountname];
                      $contact[account_id] = $row[accountid];
                  }else
		  {
                      $contact[accountname] = "";
                      $contact[account_id] = "";
		  }
                  $contact[contactid] =  $row[contactid];
                  $list[] = $contact;
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


	/** Returns a list of the associated opportunities
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	*/
	function get_opportunities($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_opportunities(".$id.") method ...");
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
				$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.return_action.value=\"updateRelations\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		// Should Opportunities be listed on Secondary Contacts ignoring the boundaries of Organization.
		// Useful when the Reseller are working to gain Potential for other Organization.
		$ignoreOrganizationCheck = true;

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query ='select case when (mycrm_users.user_name not like "") then '.$userNameSql.' else mycrm_groups.groupname end as user_name,
		mycrm_contactdetails.accountid, mycrm_contactdetails.contactid , mycrm_potential.potentialid, mycrm_potential.potentialname,
		mycrm_potential.potentialtype, mycrm_potential.sales_stage, mycrm_potential.amount, mycrm_potential.closingdate,
		mycrm_potential.related_to, mycrm_potential.contact_id, mycrm_crmentity.crmid, mycrm_crmentity.smownerid, mycrm_account.accountname
		from mycrm_contactdetails
		left join mycrm_contpotentialrel on mycrm_contpotentialrel.contactid=mycrm_contactdetails.contactid
		left join mycrm_potential on (mycrm_potential.potentialid = mycrm_contpotentialrel.potentialid or mycrm_potential.contact_id=mycrm_contactdetails.contactid)
		inner join mycrm_crmentity on mycrm_crmentity.crmid = mycrm_potential.potentialid
		left join mycrm_account on mycrm_account.accountid=mycrm_contactdetails.accountid
		LEFT JOIN mycrm_potentialscf ON mycrm_potential.potentialid = mycrm_potentialscf.potentialid
		left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid
		left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid
		where  mycrm_crmentity.deleted=0 and mycrm_contactdetails.contactid ='.$id;

		if (!$ignoreOrganizationCheck) {
			// Restrict the scope of listing to only related contacts of the organization linked to potential via related_to of Potential
			$query .= ' and (mycrm_contactdetails.accountid = mycrm_potential.related_to or mycrm_contactdetails.contactid=mycrm_potential.contact_id)';
		}

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_opportunities method ...");
		return $return_value;
	}


	/** Returns a list of the associated tasks
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
				if(getFieldVisibilityPermission('Calendar',$current_user->id,'contact_id', 'readwrite') == '0') {
					$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString('LBL_TODO', $related_module) ."' class='crmbutton small create'" .
						" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.return_module.value=\"$this_module\";this.form.activity_mode.value=\"Task\";' type='submit' name='button'" .
						" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString('LBL_TODO', $related_module) ."'>&nbsp;";
				}
				if(getFieldVisibilityPermission('Events',$current_user->id,'contact_id', 'readwrite') == '0') {
					$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString('LBL_TODO', $related_module) ."' class='crmbutton small create'" .
						" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.return_module.value=\"$this_module\";this.form.activity_mode.value=\"Events\";' type='submit' name='button'" .
						" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString('LBL_EVENT', $related_module) ."'>";
				}
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "SELECT case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name," .
				" mycrm_contactdetails.lastname, mycrm_contactdetails.firstname,  mycrm_activity.activityid ," .
				" mycrm_activity.subject, mycrm_activity.activitytype, mycrm_activity.date_start, mycrm_activity.due_date," .
				" mycrm_activity.time_start,mycrm_activity.time_end, mycrm_cntactivityrel.contactid, mycrm_crmentity.crmid," .
				" mycrm_crmentity.smownerid, mycrm_crmentity.modifiedtime, mycrm_recurringevents.recurringtype," .
				" case when (mycrm_activity.activitytype = 'Task') then mycrm_activity.status else mycrm_activity.eventstatus end as status, " .
				" mycrm_seactivityrel.crmid as parent_id " .
				" from mycrm_contactdetails " .
				" inner join mycrm_cntactivityrel on mycrm_cntactivityrel.contactid = mycrm_contactdetails.contactid" .
				" inner join mycrm_activity on mycrm_cntactivityrel.activityid=mycrm_activity.activityid" .
				" inner join mycrm_crmentity on mycrm_crmentity.crmid = mycrm_cntactivityrel.activityid " .
				" left join mycrm_seactivityrel on mycrm_seactivityrel.activityid = mycrm_cntactivityrel.activityid " .
				" left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid" .
				" left outer join mycrm_recurringevents on mycrm_recurringevents.activityid=mycrm_activity.activityid" .
				" left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid" .
				" where mycrm_contactdetails.contactid=".$id." and mycrm_crmentity.deleted = 0" .
						" and ((mycrm_activity.activitytype='Task' and mycrm_activity.status not in ('Completed','Deferred'))" .
						" or (mycrm_activity.activitytype Not in ('Emails','Task') and  mycrm_activity.eventstatus not in ('','Held')))";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_activities method ...");
		return $return_value;
	}
	/**
	* Function to get Contact related Task & Event which have activity type Held, Completed or Deferred.
	* @param  integer   $id      - contactid
	* returns related Task or Event record in array format
	*/
	function get_history($id)
	{
		global $log;
		$log->debug("Entering get_history(".$id.") method ...");
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "SELECT mycrm_activity.activityid, mycrm_activity.subject, mycrm_activity.status
			, mycrm_activity.eventstatus,mycrm_activity.activitytype, mycrm_activity.date_start,
			mycrm_activity.due_date,mycrm_activity.time_start,mycrm_activity.time_end,
			mycrm_contactdetails.contactid, mycrm_contactdetails.firstname,
			mycrm_contactdetails.lastname, mycrm_crmentity.modifiedtime,
			mycrm_crmentity.createdtime, mycrm_crmentity.description,mycrm_crmentity.crmid,
			case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name
				from mycrm_activity
				inner join mycrm_cntactivityrel on mycrm_cntactivityrel.activityid= mycrm_activity.activityid
				inner join mycrm_contactdetails on mycrm_contactdetails.contactid= mycrm_cntactivityrel.contactid
				inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_activity.activityid
				left join mycrm_seactivityrel on mycrm_seactivityrel.activityid=mycrm_activity.activityid
                left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid
				left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid
				where (mycrm_activity.activitytype != 'Emails')
				and (mycrm_activity.status = 'Completed' or mycrm_activity.status = 'Deferred' or (mycrm_activity.eventstatus = 'Held' and mycrm_activity.eventstatus != ''))
				and mycrm_cntactivityrel.contactid=".$id."
                                and mycrm_crmentity.deleted = 0";
		//Don't add order by, because, for security, one more condition will be added with this query in include/RelatedListView.php
		$log->debug("Entering get_history method ...");
		return getHistory('Contacts',$query,$id);
	}
	/**
	* Function to get Contact related Tickets.
	* @param  integer   $id      - contactid
	* returns related Ticket records in array format
	*/
	function get_tickets($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_tickets(".$id.") method ...");
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'parent_id', 'readwrite') == '0') {
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
				mycrm_crmentity.crmid, mycrm_troubletickets.title, mycrm_contactdetails.contactid, mycrm_troubletickets.parent_id,
				mycrm_contactdetails.firstname, mycrm_contactdetails.lastname, mycrm_troubletickets.status, mycrm_troubletickets.priority,
				mycrm_crmentity.smownerid, mycrm_troubletickets.ticket_no, mycrm_troubletickets.contact_id
				from mycrm_troubletickets inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_troubletickets.ticketid
				left join mycrm_contactdetails on mycrm_contactdetails.contactid=mycrm_troubletickets.contact_id
				LEFT JOIN mycrm_ticketcf ON mycrm_troubletickets.ticketid = mycrm_ticketcf.ticketid
				left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid
				left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid
				where mycrm_crmentity.deleted=0 and mycrm_contactdetails.contactid=".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_tickets method ...");
		return $return_value;
	}

	  /**
	  * Function to get Contact related Quotes
	  * @param  integer   $id  - contactid
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'contact_id', 'readwrite') == '0') {
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
		$query = "select case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,mycrm_crmentity.*, mycrm_quotes.*,mycrm_potential.potentialname,mycrm_contactdetails.lastname,mycrm_account.accountname from mycrm_quotes inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_quotes.quoteid left outer join mycrm_contactdetails on mycrm_contactdetails.contactid=mycrm_quotes.contactid left outer join mycrm_potential on mycrm_potential.potentialid=mycrm_quotes.potentialid  left join mycrm_account on mycrm_account.accountid = mycrm_quotes.accountid LEFT JOIN mycrm_quotescf ON mycrm_quotescf.quoteid = mycrm_quotes.quoteid LEFT JOIN mycrm_quotesbillads ON mycrm_quotesbillads.quotebilladdressid = mycrm_quotes.quoteid LEFT JOIN mycrm_quotesshipads ON mycrm_quotesshipads.quoteshipaddressid = mycrm_quotes.quoteid left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid where mycrm_crmentity.deleted=0 and mycrm_contactdetails.contactid=".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_quotes method ...");
		return $return_value;
	  }
	/**
	 * Function to get Contact related SalesOrder
 	 * @param  integer   $id  - contactid
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'contact_id', 'readwrite') == '0') {
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
		$query = "select case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,mycrm_crmentity.*, mycrm_salesorder.*, mycrm_quotes.subject as quotename, mycrm_account.accountname, mycrm_contactdetails.lastname from mycrm_salesorder inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_salesorder.salesorderid LEFT JOIN mycrm_salesordercf ON mycrm_salesordercf.salesorderid = mycrm_salesorder.salesorderid LEFT JOIN mycrm_sobillads ON mycrm_sobillads.sobilladdressid = mycrm_salesorder.salesorderid LEFT JOIN mycrm_soshipads ON mycrm_soshipads.soshipaddressid = mycrm_salesorder.salesorderid left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid left outer join mycrm_quotes on mycrm_quotes.quoteid=mycrm_salesorder.quoteid left outer join mycrm_account on mycrm_account.accountid=mycrm_salesorder.accountid LEFT JOIN mycrm_invoice_recurring_info ON mycrm_invoice_recurring_info.start_period = mycrm_salesorder.salesorderid left outer join mycrm_contactdetails on mycrm_contactdetails.contactid=mycrm_salesorder.contactid left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid where mycrm_crmentity.deleted=0  and  mycrm_salesorder.contactid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_salesorder method ...");
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

		$query = 'SELECT mycrm_products.productid, mycrm_products.productname, mycrm_products.productcode,
		 		  mycrm_products.commissionrate, mycrm_products.qty_per_unit, mycrm_products.unit_price,
				  mycrm_crmentity.crmid, mycrm_crmentity.smownerid,mycrm_contactdetails.lastname
				FROM mycrm_products
				INNER JOIN mycrm_seproductsrel
					ON mycrm_seproductsrel.productid=mycrm_products.productid and mycrm_seproductsrel.setype="Contacts"
				INNER JOIN mycrm_productcf
					ON mycrm_products.productid = mycrm_productcf.productid
				INNER JOIN mycrm_crmentity
					ON mycrm_crmentity.crmid = mycrm_products.productid
				INNER JOIN mycrm_contactdetails
					ON mycrm_contactdetails.contactid = mycrm_seproductsrel.crmid
				LEFT JOIN mycrm_users
					ON mycrm_users.id=mycrm_crmentity.smownerid
				LEFT JOIN mycrm_groups
					ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			   WHERE mycrm_contactdetails.contactid = '.$id.' and mycrm_crmentity.deleted = 0';

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	 }

	/**
	 * Function to get Contact related PurchaseOrder
 	 * @param  integer   $id  - contactid
	 * returns related PurchaseOrder record in array format
	 */
	 function get_purchase_orders($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_purchase_orders(".$id.") method ...");
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'contact_id', 'readwrite') == '0') {
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
		$query = "select case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,mycrm_crmentity.*, mycrm_purchaseorder.*,mycrm_vendor.vendorname,mycrm_contactdetails.lastname from mycrm_purchaseorder inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_purchaseorder.purchaseorderid left outer join mycrm_vendor on mycrm_purchaseorder.vendorid=mycrm_vendor.vendorid left outer join mycrm_contactdetails on mycrm_contactdetails.contactid=mycrm_purchaseorder.contactid left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid LEFT JOIN mycrm_purchaseordercf ON mycrm_purchaseordercf.purchaseorderid = mycrm_purchaseorder.purchaseorderid LEFT JOIN mycrm_pobillads ON mycrm_pobillads.pobilladdressid = mycrm_purchaseorder.purchaseorderid LEFT JOIN mycrm_poshipads ON mycrm_poshipads.poshipaddressid = mycrm_purchaseorder.purchaseorderid left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid where mycrm_crmentity.deleted=0 and mycrm_purchaseorder.contactid=".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_purchase_orders method ...");
		return $return_value;
	 }

	/** Returns a list of the associated emails
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	*/
	function get_emails($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_emails(".$id.") method ...");
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

		$button .= '<input type="hidden" name="email_directing_module"><input type="hidden" name="record">';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."' accessyKey='F' class='crmbutton small create' onclick='fnvshobj(this,\"sendmail_cont\");sendmail(\"$this_module\",$id);' type='button' name='button' value='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."'></td>";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "select case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name," .
				" mycrm_activity.activityid, mycrm_activity.subject, mycrm_activity.activitytype, mycrm_crmentity.modifiedtime," .
				" mycrm_crmentity.crmid, mycrm_crmentity.smownerid, mycrm_activity.date_start, mycrm_activity.time_start, mycrm_seactivityrel.crmid as parent_id " .
				" from mycrm_activity, mycrm_seactivityrel, mycrm_contactdetails, mycrm_users, mycrm_crmentity" .
				" left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid" .
				" where mycrm_seactivityrel.activityid = mycrm_activity.activityid" .
				" and mycrm_contactdetails.contactid = mycrm_seactivityrel.crmid and mycrm_users.id=mycrm_crmentity.smownerid" .
				" and mycrm_crmentity.crmid = mycrm_activity.activityid  and mycrm_contactdetails.contactid = ".$id." and" .
						" mycrm_activity.activitytype='Emails' and mycrm_crmentity.deleted = 0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_emails method ...");
		return $return_value;
	}

	/** Returns a list of the associated Campaigns
	  * @param $id -- campaign id :: Type Integer
	  * @returns list of campaigns in array format
	  */

	function get_campaigns($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_campaigns(".$id.") method ...");
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

		$button .= '<input type="hidden" name="email_directing_module"><input type="hidden" name="record">';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."' accessyKey='F' class='crmbutton small create' onclick='fnvshobj(this,\"sendmail_cont\");sendmail(\"$this_module\",$id);' type='button' name='button' value='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."'></td>";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "SELECT case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,
					mycrm_campaign.campaignid, mycrm_campaign.campaignname, mycrm_campaign.campaigntype, mycrm_campaign.campaignstatus,
					mycrm_campaign.expectedrevenue, mycrm_campaign.closingdate, mycrm_crmentity.crmid, mycrm_crmentity.smownerid,
					mycrm_crmentity.modifiedtime from mycrm_campaign
					inner join mycrm_campaigncontrel on mycrm_campaigncontrel.campaignid=mycrm_campaign.campaignid
					inner join mycrm_crmentity on mycrm_crmentity.crmid = mycrm_campaign.campaignid
					inner join mycrm_campaignscf ON mycrm_campaignscf.campaignid = mycrm_campaign.campaignid
					left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid
					left join mycrm_users on mycrm_users.id = mycrm_crmentity.smownerid
					where mycrm_campaigncontrel.contactid=".$id." and mycrm_crmentity.deleted=0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_campaigns method ...");
		return $return_value;
	}

	/**
	* Function to get Contact related Invoices
	* @param  integer   $id      - contactid
	* returns related Invoices record in array format
	*/
	function get_invoices($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_invoices(".$id.") method ...");
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'contact_id', 'readwrite') == '0') {
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
		$query = "SELECT case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,
			mycrm_crmentity.*,
			mycrm_invoice.*,
			mycrm_contactdetails.lastname,mycrm_contactdetails.firstname,
			mycrm_salesorder.subject AS salessubject
			FROM mycrm_invoice
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_invoice.invoiceid
			LEFT OUTER JOIN mycrm_contactdetails
				ON mycrm_contactdetails.contactid = mycrm_invoice.contactid
			LEFT OUTER JOIN mycrm_salesorder
				ON mycrm_salesorder.salesorderid = mycrm_invoice.salesorderid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
            LEFT JOIN mycrm_invoicecf
                ON mycrm_invoicecf.invoiceid = mycrm_invoice.invoiceid
			LEFT JOIN mycrm_invoicebillads
				ON mycrm_invoicebillads.invoicebilladdressid = mycrm_invoice.invoiceid
			LEFT JOIN mycrm_invoiceshipads
				ON mycrm_invoiceshipads.invoiceshipaddressid = mycrm_invoice.invoiceid
			LEFT JOIN mycrm_users
				ON mycrm_crmentity.smownerid = mycrm_users.id
			WHERE mycrm_crmentity.deleted = 0
			AND mycrm_contactdetails.contactid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_invoices method ...");
		return $return_value;
	}

    /**
	* Function to get Contact related vendors.
	* @param  integer   $id      - contactid
	* returns related vendor records in array format
	*/
	function get_vendors($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_vendors(".$id.") method ...");
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'parent_id', 'readwrite') == '0') {
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
		$query = "SELECT case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,
				mycrm_crmentity.crmid, mycrm_vendor.*,  mycrm_vendorcf.*
				from mycrm_vendor inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_vendor.vendorid
                INNER JOIN mycrm_vendorcontactrel on mycrm_vendorcontactrel.vendorid=mycrm_vendor.vendorid
				LEFT JOIN mycrm_vendorcf on mycrm_vendorcf.vendorid=mycrm_vendor.vendorid
				LEFT JOIN mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid
				LEFT JOIN mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid
				WHERE mycrm_crmentity.deleted=0 and mycrm_vendorcontactrel.contactid=".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_vendors method ...");
		return $return_value;
	}

	/** Function to export the contact records in CSV Format
	* @param reference variable - where condition is passed when the query is executed
	* Returns Export Contacts Query.
	*/
        function create_export_query($where)
        {
		global $log;
		global $current_user;
		$log->debug("Entering create_export_query(".$where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Contacts", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT mycrm_contactdetails.salutation as 'Salutation',$fields_list,case when (mycrm_users.user_name not like '') then mycrm_users.user_name else mycrm_groups.groupname end as user_name
                                FROM mycrm_contactdetails
                                inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_contactdetails.contactid
                                LEFT JOIN mycrm_users ON mycrm_crmentity.smownerid=mycrm_users.id and mycrm_users.status='Active'
                                LEFT JOIN mycrm_account on mycrm_contactdetails.accountid=mycrm_account.accountid
				left join mycrm_contactaddress on mycrm_contactaddress.contactaddressid=mycrm_contactdetails.contactid
				left join mycrm_contactsubdetails on mycrm_contactsubdetails.contactsubscriptionid=mycrm_contactdetails.contactid
			        left join mycrm_contactscf on mycrm_contactscf.contactid=mycrm_contactdetails.contactid
			        left join mycrm_customerdetails on mycrm_customerdetails.customerid=mycrm_contactdetails.contactid
	                        LEFT JOIN mycrm_groups
                        	        ON mycrm_groups.groupid = mycrm_crmentity.smownerid
				LEFT JOIN mycrm_contactdetails mycrm_contactdetails2
					ON mycrm_contactdetails2.contactid = mycrm_contactdetails.reportsto";
		$query .= getNonAdminAccessControlQuery('Contacts',$current_user);
		$where_auto = " mycrm_crmentity.deleted = 0 ";

                if($where != "")
                   $query .= "  WHERE ($where) AND ".$where_auto;
                else
                   $query .= "  WHERE ".$where_auto;

		$log->info("Export Query Constructed Successfully");
		$log->debug("Exiting create_export_query method ...");
		return $query;
        }


/** Function to get the Columnnames of the Contacts
* Used By mycrmCRM Word Plugin
* Returns the Merge Fields for Word Plugin
*/
function getColumnNames()
{
	global $log, $current_user;
	$log->debug("Entering getColumnNames() method ...");
	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)
	{
	 $sql1 = "select fieldlabel from mycrm_field where tabid=4 and block <> 75 and mycrm_field.presence in (0,2)";
	 $params1 = array();
	}else
	{
	 $profileList = getCurrentUserProfileList();
	 $sql1 = "select mycrm_field.fieldid,fieldlabel from mycrm_field inner join mycrm_profile2field on mycrm_profile2field.fieldid=mycrm_field.fieldid inner join mycrm_def_org_field on mycrm_def_org_field.fieldid=mycrm_field.fieldid where mycrm_field.tabid=4 and mycrm_field.block <> 75 and mycrm_field.displaytype in (1,2,4,3) and mycrm_profile2field.visible=0 and mycrm_def_org_field.visible=0 and mycrm_field.presence in (0,2)";
	 $params1 = array();
	 if (count($profileList) > 0) {
	 	$sql1 .= " and mycrm_profile2field.profileid in (". generateQuestionMarks($profileList) .") group by fieldid";
  	 	array_push($params1, $profileList);
	 }
  }
	$result = $this->db->pquery($sql1, $params1);
	$numRows = $this->db->num_rows($result);
	for($i=0; $i < $numRows;$i++)
	{
	$custom_fields[$i] = $this->db->query_result($result,$i,"fieldlabel");
	$custom_fields[$i] = preg_replace("/\s+/","",$custom_fields[$i]);
	$custom_fields[$i] = strtoupper($custom_fields[$i]);
	}
	$mergeflds = $custom_fields;
	$log->debug("Exiting getColumnNames method ...");
	return $mergeflds;
}
//End
/** Function to get the Contacts assigned to a user with a valid email address.
* @param varchar $username - User Name
* @param varchar $emailaddress - Email Addr for each contact.
* Used By mycrmCRM Outlook Plugin
* Returns the Query
*/
function get_searchbyemailid($username,$emailaddress)
{
	global $log;
	global $current_user;
	require_once("modules/Users/Users.php");
	$seed_user=new Users();
	$user_id=$seed_user->retrieve_user_id($username);
	$current_user=$seed_user;
	$current_user->retrieve_entity_info($user_id, 'Users');
	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
	$log->debug("Entering get_searchbyemailid(".$username.",".$emailaddress.") method ...");
	$query = "select mycrm_contactdetails.lastname,mycrm_contactdetails.firstname,
					mycrm_contactdetails.contactid, mycrm_contactdetails.salutation,
					mycrm_contactdetails.email,mycrm_contactdetails.title,
					mycrm_contactdetails.mobile,mycrm_account.accountname,
					mycrm_account.accountid as accountid  from mycrm_contactdetails
						inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_contactdetails.contactid
						inner join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid
						left join mycrm_account on mycrm_account.accountid=mycrm_contactdetails.accountid
						left join mycrm_contactaddress on mycrm_contactaddress.contactaddressid=mycrm_contactdetails.contactid
			      LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid";
	$query .= getNonAdminAccessControlQuery('Contacts',$current_user);
	$query .= "where mycrm_crmentity.deleted=0";
	if(trim($emailaddress) != '') {
		$query .= " and ((mycrm_contactdetails.email like '". formatForSqlLike($emailaddress) .
		"') or mycrm_contactdetails.lastname REGEXP REPLACE('".$emailaddress.
		"',' ','|') or mycrm_contactdetails.firstname REGEXP REPLACE('".$emailaddress.
		"',' ','|'))  and mycrm_contactdetails.email != ''";
	} else {
		$query .= " and (mycrm_contactdetails.email like '". formatForSqlLike($emailaddress) .
		"' and mycrm_contactdetails.email != '')";
	}

	$log->debug("Exiting get_searchbyemailid method ...");
	return $this->plugin_process_list_query($query);
}

/** Function to get the Contacts associated with the particular User Name.
*  @param varchar $user_name - User Name
*  Returns query
*/

function get_contactsforol($user_name)
{
	global $log,$adb;
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
    $sql1 = "select tablename,columnname from mycrm_field where tabid=4 and mycrm_field.presence in (0,2)";
	$params1 = array();
  }else
  {
    $profileList = getCurrentUserProfileList();
    $sql1 = "select tablename,columnname from mycrm_field inner join mycrm_profile2field on mycrm_profile2field.fieldid=mycrm_field.fieldid inner join mycrm_def_org_field on mycrm_def_org_field.fieldid=mycrm_field.fieldid where mycrm_field.tabid=4 and mycrm_field.displaytype in (1,2,4,3) and mycrm_profile2field.visible=0 and mycrm_def_org_field.visible=0 and mycrm_field.presence in (0,2)";
	$params1 = array();
	if (count($profileList) > 0) {
		$sql1 .= " and mycrm_profile2field.profileid in (". generateQuestionMarks($profileList) .")";
		array_push($params1, $profileList);
	}
  }
  $result1 = $adb->pquery($sql1, $params1);
  for($i=0;$i < $adb->num_rows($result1);$i++)
  {
      $permitted_lists[] = $adb->query_result($result1,$i,'tablename');
      $permitted_lists[] = $adb->query_result($result1,$i,'columnname');
      if($adb->query_result($result1,$i,'columnname') == "accountid")
      {
        $permitted_lists[] = 'mycrm_account';
        $permitted_lists[] = 'accountname';
      }
  }
	$permitted_lists = array_chunk($permitted_lists,2);
	$column_table_lists = array();
	for($i=0;$i < count($permitted_lists);$i++)
	{
	   $column_table_lists[] = implode(".",$permitted_lists[$i]);
  }

	$log->debug("Entering get_contactsforol(".$user_name.") method ...");
	$query = "select mycrm_contactdetails.contactid as id, ".implode(',',$column_table_lists)." from mycrm_contactdetails
						inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_contactdetails.contactid
						inner join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid
						left join mycrm_customerdetails on mycrm_customerdetails.customerid=mycrm_contactdetails.contactid
						left join mycrm_account on mycrm_account.accountid=mycrm_contactdetails.accountid
						left join mycrm_contactaddress on mycrm_contactaddress.contactaddressid=mycrm_contactdetails.contactid
						left join mycrm_contactsubdetails on mycrm_contactsubdetails.contactsubscriptionid = mycrm_contactdetails.contactid
                        left join mycrm_campaigncontrel on mycrm_contactdetails.contactid = mycrm_campaigncontrel.contactid
                        left join mycrm_campaignrelstatus on mycrm_campaignrelstatus.campaignrelstatusid = mycrm_campaigncontrel.campaignrelstatusid
			      LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
						where mycrm_crmentity.deleted=0 and mycrm_users.user_name='".$user_name."'";
  $log->debug("Exiting get_contactsforol method ...");
	return $query;
}


	/** Function to handle module specific operations when saving a entity
	*/
	function save_module($module)
	{
		$this->insertIntoAttachment($this->id,$module);
	}

	/**
	 *      This function is used to add the mycrm_attachments. This will call the function uploadAndSaveFile which will upload the attachment into the server and save that attachment information in the database.
	 *      @param int $id  - entity id to which the mycrm_files to be uploaded
	 *      @param string $module  - the current module name
	*/
	function insertIntoAttachment($id,$module)
	{
		global $log, $adb,$upload_badext;
		$log->debug("Entering into insertIntoAttachment($id,$module) method.");

		$file_saved = false;
		//This is to added to store the existing attachment id of the contact where we should delete this when we give new image
		$old_attachmentid = $adb->query_result($adb->pquery("select mycrm_crmentity.crmid from mycrm_seattachmentsrel inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_seattachmentsrel.attachmentsid where  mycrm_seattachmentsrel.crmid=?", array($id)),0,'crmid');
		foreach($_FILES as $fileindex => $files)
		{
			if($files['name'] != '' && $files['size'] > 0)
			{
				$files['original_name'] = vtlib_purify($_REQUEST[$fileindex.'_hidden']);
				$file_saved = $this->uploadAndSaveFile($id,$module,$files);
			}
		}

		$imageNameSql = 'SELECT name FROM mycrm_seattachmentsrel INNER JOIN mycrm_attachments ON
								mycrm_seattachmentsrel.attachmentsid = mycrm_attachments.attachmentsid LEFT JOIN mycrm_contactdetails ON
								mycrm_contactdetails.contactid = mycrm_seattachmentsrel.crmid WHERE mycrm_seattachmentsrel.crmid = ?';
		$imageNameResult = $adb->pquery($imageNameSql,array($id));
		$imageName = decode_html($adb->query_result($imageNameResult, 0, "name"));

		//Inserting image information of record into base table
		$adb->pquery('UPDATE mycrm_contactdetails SET imagename = ? WHERE contactid = ?',array($imageName,$id));

		//This is to handle the delete image for contacts
		if($module == 'Contacts' && $file_saved)
		{
			if($old_attachmentid != '')
			{
				$setype = $adb->query_result($adb->pquery("select setype from mycrm_crmentity where crmid=?", array($old_attachmentid)),0,'setype');
				if($setype == 'Contacts Image')
				{
					$del_res1 = $adb->pquery("delete from mycrm_attachments where attachmentsid=?", array($old_attachmentid));
					$del_res2 = $adb->pquery("delete from mycrm_seattachmentsrel where attachmentsid=?", array($old_attachmentid));
				}
			}
		}

		$log->debug("Exiting from insertIntoAttachment($id,$module) method.");
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

		$rel_table_arr = Array("Potentials"=>"mycrm_contpotentialrel","Potentials"=>"mycrm_potential","Activities"=>"mycrm_cntactivityrel",
				"Emails"=>"mycrm_seactivityrel","HelpDesk"=>"mycrm_troubletickets","Quotes"=>"mycrm_quotes","PurchaseOrder"=>"mycrm_purchaseorder",
				"SalesOrder"=>"mycrm_salesorder","Products"=>"mycrm_seproductsrel","Documents"=>"mycrm_senotesrel",
				"Attachments"=>"mycrm_seattachmentsrel","Campaigns"=>"mycrm_campaigncontrel",'Invoice'=>'mycrm_invoice',
                'ServiceContracts'=>'mycrm_servicecontracts','Project'=>'mycrm_project','Assets'=>'mycrm_assets');

		$tbl_field_arr = Array("mycrm_contpotentialrel"=>"potentialid","mycrm_potential"=>"potentialid","mycrm_cntactivityrel"=>"activityid",
				"mycrm_seactivityrel"=>"activityid","mycrm_troubletickets"=>"ticketid","mycrm_quotes"=>"quoteid","mycrm_purchaseorder"=>"purchaseorderid",
				"mycrm_salesorder"=>"salesorderid","mycrm_seproductsrel"=>"productid","mycrm_senotesrel"=>"notesid",
				"mycrm_seattachmentsrel"=>"attachmentsid","mycrm_campaigncontrel"=>"campaignid",'mycrm_invoice'=>'invoiceid',
                'mycrm_servicecontracts'=>'servicecontractsid','mycrm_project'=>'projectid','mycrm_assets'=>'assetsid',
                'mycrm_payments'=>'paymentsid');

		$entity_tbl_field_arr = Array("mycrm_contpotentialrel"=>"contactid","mycrm_potential"=>"contact_id","mycrm_cntactivityrel"=>"contactid",
				"mycrm_seactivityrel"=>"crmid","mycrm_troubletickets"=>"contact_id","mycrm_quotes"=>"contactid","mycrm_purchaseorder"=>"contactid",
				"mycrm_salesorder"=>"contactid","mycrm_seproductsrel"=>"crmid","mycrm_senotesrel"=>"crmid",
				"mycrm_seattachmentsrel"=>"crmid","mycrm_campaigncontrel"=>"contactid",'mycrm_invoice'=>'contactid',
                'mycrm_servicecontracts'=>'sc_related_to','mycrm_project'=>'linktoaccountscontacts','mycrm_assets'=>'contact',
                'mycrm_payments'=>'relatedcontact');

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
			$adb->pquery("UPDATE mycrm_potential SET related_to = ? WHERE related_to = ?", array($entityId, $transferId));
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
		$matrix->setDependency('mycrm_crmentityContacts',array('mycrm_groupsContacts','mycrm_usersContacts','mycrm_lastModifiedByContacts'));
		$matrix->setDependency('mycrm_contactdetails', array('mycrm_crmentityContacts','mycrm_contactaddress',
								'mycrm_customerdetails','mycrm_contactsubdetails','mycrm_contactscf'));

		if (!$queryplanner->requireTable('mycrm_contactdetails', $matrix)) {
			return '';
		}


		$query = $this->getRelationQuery($module,$secmodule,"mycrm_contactdetails","contactid", $queryplanner);

		if ($queryplanner->requireTable("mycrm_crmentityContacts",$matrix)){
			$query .= " left join mycrm_crmentity as mycrm_crmentityContacts on mycrm_crmentityContacts.crmid = mycrm_contactdetails.contactid  and mycrm_crmentityContacts.deleted=0";
		}
		if ($queryplanner->requireTable("mycrm_contactdetailsContacts")){
			$query .= " left join mycrm_contactdetails as mycrm_contactdetailsContacts on mycrm_contactdetailsContacts.contactid = mycrm_contactdetails.reportsto";
		}
		if ($queryplanner->requireTable("mycrm_contactaddress")){
			$query .= " left join mycrm_contactaddress on mycrm_contactdetails.contactid = mycrm_contactaddress.contactaddressid";
		}
		if ($queryplanner->requireTable("mycrm_customerdetails")){
			$query .= " left join mycrm_customerdetails on mycrm_customerdetails.customerid = mycrm_contactdetails.contactid";
		}
		if ($queryplanner->requireTable("mycrm_contactsubdetails")){
			$query .= " left join mycrm_contactsubdetails on mycrm_contactdetails.contactid = mycrm_contactsubdetails.contactsubscriptionid";
		}
		if ($queryplanner->requireTable("mycrm_accountContacts")){
			$query .= " left join mycrm_account as mycrm_accountContacts on mycrm_accountContacts.accountid = mycrm_contactdetails.accountid";
		}
		if ($queryplanner->requireTable("mycrm_contactscf")){
			$query .= " left join mycrm_contactscf on mycrm_contactdetails.contactid = mycrm_contactscf.contactid";
		}
		if ($queryplanner->requireTable("mycrm_email_trackContacts")){
			$query .= " LEFT JOIN mycrm_email_track AS mycrm_email_trackContacts ON mycrm_email_trackContacts.crmid = mycrm_contactdetails.contactid";
		}
		if ($queryplanner->requireTable("mycrm_groupsContacts")){
			$query .= " left join mycrm_groups as mycrm_groupsContacts on mycrm_groupsContacts.groupid = mycrm_crmentityContacts.smownerid";
		}
		if ($queryplanner->requireTable("mycrm_usersContacts")){
			$query .= " left join mycrm_users as mycrm_usersContacts on mycrm_usersContacts.id = mycrm_crmentityContacts.smownerid";
		}
		if ($queryplanner->requireTable("mycrm_lastModifiedByContacts")){
			$query .= " left join mycrm_users as mycrm_lastModifiedByContacts on mycrm_lastModifiedByContacts.id = mycrm_crmentityContacts.modifiedby ";
		}
        if ($queryplanner->requireTable("mycrm_createdbyContacts")){
			$query .= " left join mycrm_users as mycrm_createdbyContacts on mycrm_createdbyContacts.id = mycrm_crmentityContacts.smcreatorid ";
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
			"Calendar" => array("mycrm_cntactivityrel"=>array("contactid","activityid"),"mycrm_contactdetails"=>"contactid"),
			"HelpDesk" => array("mycrm_troubletickets"=>array("contact_id","ticketid"),"mycrm_contactdetails"=>"contactid"),
			"Quotes" => array("mycrm_quotes"=>array("contactid","quoteid"),"mycrm_contactdetails"=>"contactid"),
			"PurchaseOrder" => array("mycrm_purchaseorder"=>array("contactid","purchaseorderid"),"mycrm_contactdetails"=>"contactid"),
			"SalesOrder" => array("mycrm_salesorder"=>array("contactid","salesorderid"),"mycrm_contactdetails"=>"contactid"),
			"Products" => array("mycrm_seproductsrel"=>array("crmid","productid"),"mycrm_contactdetails"=>"contactid"),
			"Campaigns" => array("mycrm_campaigncontrel"=>array("contactid","campaignid"),"mycrm_contactdetails"=>"contactid"),
			"Documents" => array("mycrm_senotesrel"=>array("crmid","notesid"),"mycrm_contactdetails"=>"contactid"),
			"Accounts" => array("mycrm_contactdetails"=>array("contactid","accountid")),
			"Invoice" => array("mycrm_invoice"=>array("contactid","invoiceid"),"mycrm_contactdetails"=>"contactid"),
			"Emails" => array("mycrm_seactivityrel"=>array("crmid","activityid"),"mycrm_contactdetails"=>"contactid"),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink all the dependent entities of the given Entity by Id
	function unlinkDependencies($module, $id) {
		global $log;

		//Deleting Contact related Potentials.
		$pot_q = 'SELECT mycrm_crmentity.crmid FROM mycrm_crmentity
			INNER JOIN mycrm_potential ON mycrm_crmentity.crmid=mycrm_potential.potentialid
			LEFT JOIN mycrm_account ON mycrm_account.accountid=mycrm_potential.related_to
			WHERE mycrm_crmentity.deleted=0 AND mycrm_potential.related_to=?';
		$pot_res = $this->db->pquery($pot_q, array($id));
		$pot_ids_list = array();
		for($k=0;$k < $this->db->num_rows($pot_res);$k++)
		{
			$pot_id = $this->db->query_result($pot_res,$k,"crmid");
			$pot_ids_list[] = $pot_id;
			$sql = 'UPDATE mycrm_crmentity SET deleted = 1 WHERE crmid = ?';
			$this->db->pquery($sql, array($pot_id));
		}
		//Backup deleted Contact related Potentials.
		$params = array($id, RB_RECORD_UPDATED, 'mycrm_crmentity', 'deleted', 'crmid', implode(",", $pot_ids_list));
		$this->db->pquery('INSERT INTO mycrm_relatedlists_rb VALUES(?,?,?,?,?,?)', $params);

		//Backup Contact-Trouble Tickets Relation
		$tkt_q = 'SELECT ticketid FROM mycrm_troubletickets WHERE contact_id=?';
		$tkt_res = $this->db->pquery($tkt_q, array($id));
		if ($this->db->num_rows($tkt_res) > 0) {
			$tkt_ids_list = array();
			for($k=0;$k < $this->db->num_rows($tkt_res);$k++)
			{
				$tkt_ids_list[] = $this->db->query_result($tkt_res,$k,"ticketid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'mycrm_troubletickets', 'contact_id', 'ticketid', implode(",", $tkt_ids_list));
			$this->db->pquery('INSERT INTO mycrm_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
		}
		//removing the relationship of contacts with Trouble Tickets
		$this->db->pquery('UPDATE mycrm_troubletickets SET contact_id=0 WHERE contact_id=?', array($id));

		//Backup Contact-PurchaseOrder Relation
		$po_q = 'SELECT purchaseorderid FROM mycrm_purchaseorder WHERE contactid=?';
		$po_res = $this->db->pquery($po_q, array($id));
		if ($this->db->num_rows($po_res) > 0) {
			$po_ids_list = array();
			for($k=0;$k < $this->db->num_rows($po_res);$k++)
			{
				$po_ids_list[] = $this->db->query_result($po_res,$k,"purchaseorderid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'mycrm_purchaseorder', 'contactid', 'purchaseorderid', implode(",", $po_ids_list));
			$this->db->pquery('INSERT INTO mycrm_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
		}
		//removing the relationship of contacts with PurchaseOrder
		$this->db->pquery('UPDATE mycrm_purchaseorder SET contactid=0 WHERE contactid=?', array($id));

		//Backup Contact-SalesOrder Relation
		$so_q = 'SELECT salesorderid FROM mycrm_salesorder WHERE contactid=?';
		$so_res = $this->db->pquery($so_q, array($id));
		if ($this->db->num_rows($so_res) > 0) {
			$so_ids_list = array();
			for($k=0;$k < $this->db->num_rows($so_res);$k++)
			{
				$so_ids_list[] = $this->db->query_result($so_res,$k,"salesorderid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'mycrm_salesorder', 'contactid', 'salesorderid', implode(",", $so_ids_list));
			$this->db->pquery('INSERT INTO mycrm_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
		}
		//removing the relationship of contacts with SalesOrder
		$this->db->pquery('UPDATE mycrm_salesorder SET contactid=0 WHERE contactid=?', array($id));

		//Backup Contact-Quotes Relation
		$quo_q = 'SELECT quoteid FROM mycrm_quotes WHERE contactid=?';
		$quo_res = $this->db->pquery($quo_q, array($id));
		if ($this->db->num_rows($quo_res) > 0) {
			$quo_ids_list = array();
			for($k=0;$k < $this->db->num_rows($quo_res);$k++)
			{
				$quo_ids_list[] = $this->db->query_result($quo_res,$k,"quoteid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'mycrm_quotes', 'contactid', 'quoteid', implode(",", $quo_ids_list));
			$this->db->pquery('INSERT INTO mycrm_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
		}
		//removing the relationship of contacts with Quotes
		$this->db->pquery('UPDATE mycrm_quotes SET contactid=0 WHERE contactid=?', array($id));
		//remove the portal info the contact
		$this->db->pquery('DELETE FROM mycrm_portalinfo WHERE id = ?', array($id));
		$this->db->pquery('UPDATE mycrm_customerdetails SET portal=0,support_start_date=NULL,support_end_date=NULl WHERE customerid=?', array($id));
		parent::unlinkDependencies($module, $id);
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Accounts') {
			$sql = 'UPDATE mycrm_contactdetails SET accountid = ? WHERE contactid = ?';
			$this->db->pquery($sql, array(null, $id));
		} elseif($return_module == 'Potentials') {
			$sql = 'DELETE FROM mycrm_contpotentialrel WHERE contactid=? AND potentialid=?';
			$this->db->pquery($sql, array($id, $return_id));

			//If contact related to potential through edit of record,that entry will be present in
			//mycrm_potential contact_id column,which should be set to zero
			$sql = 'UPDATE mycrm_potential SET contact_id = ? WHERE contact_id=? AND potentialid=?';
			$this->db->pquery($sql, array(0,$id, $return_id));
		} elseif($return_module == 'Campaigns') {
			$sql = 'DELETE FROM mycrm_campaigncontrel WHERE contactid=? AND campaignid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif($return_module == 'Products') {
			$sql = 'DELETE FROM mycrm_seproductsrel WHERE crmid=? AND productid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif($return_module == 'Vendors') {
			$sql = 'DELETE FROM mycrm_vendorcontactrel WHERE vendorid=? AND contactid=?';
			$this->db->pquery($sql, array($return_id, $id));
		} else {
			$sql = 'DELETE FROM mycrm_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}

	//added to get mail info for portal user
	//type argument included when when addin customizable tempalte for sending portal login details
	public static function getPortalEmailContents($entityData, $password, $type='') {
        require_once 'config.inc.php';
		global $PORTAL_URL, $HELPDESK_SUPPORT_EMAIL_ID;

		$adb = PearDatabase::getInstance();
		$moduleName = $entityData->getModuleName();

		$companyDetails = getCompanyDetails();

		$portalURL = '<a href="'.$PORTAL_URL.'" style="font-family:Arial, Helvetica, sans-serif;font-size:12px; font-weight:bolder;text-decoration:none;color: #4242FD;">'.getTranslatedString('Please Login Here', $moduleName).'</a>';

		//here id is hardcoded with 5. it is for support start notification in mycrm_notificationscheduler
		$query='SELECT mycrm_emailtemplates.subject,mycrm_emailtemplates.body
					FROM mycrm_notificationscheduler
						INNER JOIN mycrm_emailtemplates ON mycrm_emailtemplates.templateid=mycrm_notificationscheduler.notificationbody
					WHERE schedulednotificationid=5';

		$result = $adb->pquery($query, array());
		$body=decode_html($adb->query_result($result,0,'body'));
		$contents=$body;
		$contents = str_replace('$contact_name$',$entityData->get('firstname')." ".$entityData->get('lastname'),$contents);
		$contents = str_replace('$login_name$',$entityData->get('email'),$contents);
		$contents = str_replace('$password$',$password,$contents);
		$contents = str_replace('$URL$',$portalURL,$contents);
		$contents = str_replace('$support_team$',getTranslatedString('Support Team', $moduleName),$contents);
		$contents = str_replace('$logo$','<img src="cid:logo" />',$contents);

		//Company Details
		$contents = str_replace('$address$',$companyDetails['address'],$contents);
		$contents = str_replace('$companyname$',$companyDetails['companyname'],$contents);
		$contents = str_replace('$phone$',$companyDetails['phone'],$contents);
		$contents = str_replace('$companywebsite$',$companyDetails['website'],$contents);
		$contents = str_replace('$supportemail$',$HELPDESK_SUPPORT_EMAIL_ID,$contents);

		if($type == "LoginDetails") {
			$temp=$contents;
			$value["subject"]=decode_html($adb->query_result($result,0,'subject'));
			$value["body"]=$temp;
			return $value;
		}
		return $contents;
	}

	function save_related_module($module, $crmid, $with_module, $with_crmids) {
		$adb = PearDatabase::getInstance();

		if(!is_array($with_crmids)) $with_crmids = Array($with_crmids);
		foreach($with_crmids as $with_crmid) {
			if($with_module == 'Products') {
				$adb->pquery("insert into mycrm_seproductsrel values (?,?,?)", array($crmid, $with_crmid, 'Contacts'));

			} elseif($with_module == 'Campaigns') {
				$adb->pquery("insert into mycrm_campaigncontrel values(?,?,1)", array($with_crmid, $crmid));

			} elseif($with_module == 'Potentials') {
				$adb->pquery("insert into mycrm_contpotentialrel values(?,?)", array($crmid, $with_crmid));

			}
            else if($with_module == 'Vendors'){
        		$adb->pquery("insert into mycrm_vendorcontactrel values (?,?)", array($with_crmid,$crmid));
            }else {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid);
			}
		}
	}

	function getListButtons($app_strings,$mod_strings = false) {
		$list_buttons = Array();

		if(isPermitted('Contacts','Delete','') == 'yes') {
			$list_buttons['del'] = $app_strings[LBL_MASS_DELETE];
		}
		if(isPermitted('Contacts','EditView','') == 'yes') {
			$list_buttons['mass_edit'] = $app_strings[LBL_MASS_EDIT];
			$list_buttons['c_owner'] = $app_strings[LBL_CHANGE_OWNER];
		}
		if(isPermitted('Emails','EditView','') == 'yes'){
			$list_buttons['s_mail'] = $app_strings[LBL_SEND_MAIL_BUTTON];
		}
		return $list_buttons;
	}
}

?>