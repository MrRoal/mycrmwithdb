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
 * $Header: /advent/projects/wesat/mycrm_crm/sugarcrm/modules/Accounts/Accounts.php,v 1.53 2005/04/28 08:06:45 rank Exp $
 * Description:  Defines the Account SugarBean Account entity with the necessary
 * methods and variables.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
class Accounts extends CRMEntity {
	var $log;
	var $db;
	var $table_name = "mycrm_account";
	var $table_index= 'accountid';
	var $tab_name = Array('mycrm_crmentity','mycrm_account','mycrm_accountbillads','mycrm_accountshipads','mycrm_accountscf');
	var $tab_name_index = Array('mycrm_crmentity'=>'crmid','mycrm_account'=>'accountid','mycrm_accountbillads'=>'accountaddressid','mycrm_accountshipads'=>'accountaddressid','mycrm_accountscf'=>'accountid');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('mycrm_accountscf', 'accountid');
	var $entity_table = "mycrm_crmentity";

	var $column_fields = Array();

	var $sortby_fields = Array('accountname','bill_city','website','phone','smownerid');

	//var $groupTable = Array('mycrm_accountgrouprelation','accountid');

	// This is the list of mycrm_fields that are in the lists.
	var $list_fields = Array(
			'Account Name'=>Array('mycrm_account'=>'accountname'),
			'Billing City'=>Array('mycrm_accountbillads'=>'bill_city'),
			'Website'=>Array('mycrm_account'=>'website'),
			'Phone'=>Array('mycrm_account'=> 'phone'),
			'Assigned To'=>Array('mycrm_crmentity'=>'smownerid')
			);

	var $list_fields_name = Array(
			'Account Name'=>'accountname',
			'Billing City'=>'bill_city',
			'Website'=>'website',
			'Phone'=>'phone',
			'Assigned To'=>'assigned_user_id'
			);
	var $list_link_field= 'accountname';

	var $search_fields = Array(
			'Account Name'=>Array('mycrm_account'=>'accountname'),
			'Billing City'=>Array('mycrm_accountbillads'=>'bill_city'),
			'Assigned To'=>Array('mycrm_crmentity'=>'smownerid'),
			);

	var $search_fields_name = Array(
			'Account Name'=>'accountname',
			'Billing City'=>'bill_city',
			'Assigned To'=>'assigned_user_id',
			);
	// This is the list of mycrm_fields that are required
	var $required_fields =  array();

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to mycrm_field.fieldname values.
	var $mandatory_fields = Array('assigned_user_id', 'createdtime', 'modifiedtime', 'accountname');

	//Default Fields for Email Templates -- Pavani
	var $emailTemplate_defaultFields = array('accountname','account_type','industry','annualrevenue','phone','email1','rating','website','fax');

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'accountname';
	var $default_sort_order = 'ASC';

	// For Alphabetical search
	var $def_basicsearch_col = 'accountname';

	var $related_module_table_index = array(
		'Contacts' => array('table_name' => 'mycrm_contactdetails', 'table_index' => 'contactid', 'rel_index' => 'accountid'),
		'Potentials' => array('table_name' => 'mycrm_potential', 'table_index' => 'potentialid', 'rel_index' => 'related_to'),
		'Quotes' => array('table_name' => 'mycrm_quotes', 'table_index' => 'quoteid', 'rel_index' => 'accountid'),
		'SalesOrder' => array('table_name' => 'mycrm_salesorder', 'table_index' => 'salesorderid', 'rel_index' => 'accountid'),
		'Invoice' => array('table_name' => 'mycrm_invoice', 'table_index' => 'invoiceid', 'rel_index' => 'accountid'),
		'HelpDesk' => array('table_name' => 'mycrm_troubletickets', 'table_index' => 'ticketid', 'rel_index' => 'parent_id'),
		'Products' => array('table_name' => 'mycrm_seproductsrel', 'table_index' => 'productid', 'rel_index' => 'crmid'),
		'Calendar' => array('table_name' => 'mycrm_seactivityrel', 'table_index' => 'activityid', 'rel_index' => 'crmid'),
		'Documents' => array('table_name' => 'mycrm_senotesrel', 'table_index' => 'notesid', 'rel_index' => 'crmid'),
		'ServiceContracts' => array('table_name' => 'mycrm_servicecontracts', 'table_index' => 'servicecontractsid', 'rel_index' => 'sc_related_to'),
		'Services' => array('table_name' => 'mycrm_crmentityrel', 'table_index' => 'crmid', 'rel_index' => 'crmid'),
		'Campaigns' => array('table_name' => 'mycrm_campaignaccountrel', 'table_index' => 'campaignid', 'rel_index' => 'accountid'),
		'Assets' => array('table_name' => 'mycrm_assets', 'table_index' => 'assetsid', 'rel_index' => 'account'),
		'Project' => array('table_name' => 'mycrm_project', 'table_index' => 'projectid', 'rel_index' => 'linktoaccountscontacts'),
	);

	function Accounts() {
		$this->log =LoggerManager::getLogger('account');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Accounts');
	}

	/** Function to handle module specific operations when saving a entity
	*/
	function save_module($module) {

	}


	// Mike Crowe Mod --------------------------------------------------------Default ordering for us
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
		}

		$entityIds = $this->getRelatedContactsIds();
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');

		$query = "SELECT case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,
				mycrm_campaign.campaignid, mycrm_campaign.campaignname, mycrm_campaign.campaigntype, mycrm_campaign.campaignstatus,
				mycrm_campaign.expectedrevenue, mycrm_campaign.closingdate, mycrm_crmentity.crmid, mycrm_crmentity.smownerid,
				mycrm_crmentity.modifiedtime
				from mycrm_campaign
				INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_campaign.campaignid
				INNER JOIN mycrm_campaignscf ON mycrm_campaignscf.campaignid = mycrm_campaign.campaignid
				LEFT JOIN mycrm_campaignaccountrel ON mycrm_campaignaccountrel.campaignid=mycrm_campaign.campaignid
				LEFT JOIN mycrm_campaigncontrel ON mycrm_campaigncontrel.campaignid=mycrm_campaign.campaignid
				LEFT JOIN mycrm_groups ON mycrm_groups.groupid=mycrm_crmentity.smownerid
				LEFT JOIN mycrm_users ON mycrm_users.id = mycrm_crmentity.smownerid
				WHERE mycrm_crmentity.deleted=0 AND (mycrm_campaignaccountrel.accountid=$id";

		if(!empty ($entityIds)){
			$query .= " OR mycrm_campaigncontrel.contactid IN (".$entityIds."))";
		} else {
			$query .= ")";
		}

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_campaigns method ...");
		return $return_value;
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'account_id','readwrite') == '0') {
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
		$query = "SELECT mycrm_contactdetails.*,
			mycrm_crmentity.crmid,
                        mycrm_crmentity.smownerid,
			mycrm_account.accountname,
			case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name
			FROM mycrm_contactdetails
			INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_contactdetails.contactid
			LEFT JOIN mycrm_account ON mycrm_account.accountid = mycrm_contactdetails.accountid
			INNER JOIN mycrm_contactaddress ON mycrm_contactdetails.contactid = mycrm_contactaddress.contactaddressid
			INNER JOIN mycrm_contactsubdetails ON mycrm_contactdetails.contactid = mycrm_contactsubdetails.contactsubscriptionid
			INNER JOIN mycrm_customerdetails ON mycrm_contactdetails.contactid = mycrm_customerdetails.customerid
			INNER JOIN mycrm_contactscf ON mycrm_contactdetails.contactid = mycrm_contactscf.contactid
			LEFT JOIN mycrm_groups	ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_users ON mycrm_crmentity.smownerid = mycrm_users.id
			WHERE mycrm_crmentity.deleted = 0
			AND mycrm_contactdetails.accountid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
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
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		// TODO: We need to add pull contacts if its linked as secondary in Potentials too.
		// These relations are captued in mycrm_contpotentialrel
		// Better to provide switch to turn-on / off this feature like in
		// Contacts::get_opportunities

		$entityIds = $this->getRelatedContactsIds();
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');

		$query = "SELECT mycrm_potential.potentialid, mycrm_potential.related_to, mycrm_potential.potentialname, mycrm_potential.sales_stage,mycrm_potential.contact_id,
				mycrm_potential.potentialtype, mycrm_potential.amount, mycrm_potential.closingdate, mycrm_potential.potentialtype, mycrm_account.accountname,
				case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,mycrm_crmentity.crmid, mycrm_crmentity.smownerid
				FROM mycrm_potential
				INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_potential.potentialid
				LEFT JOIN mycrm_account ON mycrm_account.accountid = mycrm_potential.related_to
				INNER JOIN mycrm_potentialscf ON mycrm_potential.potentialid = mycrm_potentialscf.potentialid
				LEFT JOIN mycrm_users ON mycrm_crmentity.smownerid = mycrm_users.id
				LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
				WHERE mycrm_crmentity.deleted = 0 AND (mycrm_potential.related_to = $id ";
		if(!empty($entityIds)) {
			$query .= " OR mycrm_potential.contact_id IN (".$entityIds.")";
		}

		$query .= ')';

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

		$entityIds = $this->getRelatedContactsIds();
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');

		$query = "SELECT mycrm_activity.*, mycrm_cntactivityrel.*, mycrm_seactivityrel.crmid as parent_id, mycrm_contactdetails.lastname,
				mycrm_contactdetails.firstname, mycrm_crmentity.crmid, mycrm_crmentity.smownerid, mycrm_crmentity.modifiedtime,
				case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,
				mycrm_recurringevents.recurringtype
				FROM mycrm_activity
				INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_activity.activityid
				LEFT JOIN mycrm_seactivityrel ON mycrm_seactivityrel.activityid = mycrm_activity.activityid
				LEFT JOIN mycrm_cntactivityrel ON mycrm_cntactivityrel.activityid = mycrm_activity.activityid
				LEFT JOIN mycrm_contactdetails ON mycrm_contactdetails.contactid = mycrm_cntactivityrel.contactid
				LEFT JOIN mycrm_users ON mycrm_users.id = mycrm_crmentity.smownerid
				LEFT OUTER JOIN mycrm_recurringevents ON mycrm_recurringevents.activityid = mycrm_activity.activityid
				LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
				WHERE mycrm_crmentity.deleted = 0
				AND ((mycrm_activity.activitytype='Task' and mycrm_activity.status not in ('Completed','Deferred'))
				OR (mycrm_activity.activitytype not in ('Emails','Task') and  mycrm_activity.eventstatus not in ('','Held')))
				AND (mycrm_seactivityrel.crmid = $id";

		if(!empty ($entityIds)){
			$query .= " OR mycrm_cntactivityrel.contactid IN (".$entityIds."))";
		} else {
			$query .= ")";
        }
        // There could be more than one contact for an activity.
        $query .= ' GROUP BY mycrm_activity.activityid';

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_activities method ...");
		return $return_value;
	}

	/**
	 * Function to get Account related Task & Event which have activity type Held, Completed or Deferred.
 	 * @param  integer   $id      - accountid
 	 * returns related Task or Event record in array format
 	 */
	function get_history($id)
	{
		global $log;
                $log->debug("Entering get_history(".$id.") method ...");

		$entityIds = $this->getRelatedContactsIds();
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');

		$query = "SELECT DISTINCT(mycrm_activity.activityid), mycrm_activity.subject, mycrm_activity.status, mycrm_activity.eventstatus,
				mycrm_activity.activitytype, mycrm_activity.date_start, mycrm_activity.due_date, mycrm_activity.time_start, mycrm_activity.time_end,
				mycrm_crmentity.modifiedtime, mycrm_crmentity.createdtime, mycrm_crmentity.description,
				case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name
				FROM mycrm_activity
				INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_activity.activityid
				LEFT JOIN mycrm_seactivityrel ON mycrm_seactivityrel.activityid = mycrm_activity.activityid
				LEFT JOIN mycrm_cntactivityrel ON mycrm_cntactivityrel.activityid = mycrm_activity.activityid
				LEFT JOIN mycrm_contactdetails ON mycrm_contactdetails.contactid = mycrm_cntactivityrel.contactid
				LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
				LEFT JOIN mycrm_users ON mycrm_users.id=mycrm_crmentity.smownerid
				WHERE (mycrm_activity.activitytype != 'Emails')
				AND (mycrm_activity.status = 'Completed'
					OR mycrm_activity.status = 'Deferred'
					OR (mycrm_activity.eventstatus = 'Held' AND mycrm_activity.eventstatus != ''))
				AND mycrm_crmentity.deleted = 0 AND (mycrm_seactivityrel.crmid = $id";

		if(!empty ($entityIds)){
			$query .= " OR mycrm_cntactivityrel.contactid IN (".$entityIds."))";
		} else {
			$query .= ")";
		}

		//Don't add order by, because, for security, one more condition will be added with this query in include/RelatedListView.php
		$log->debug("Exiting get_history method ...");
		return getHistory('Accounts',$query,$id);
	}

	/** Returns a list of the associated emails
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
	 * All Rights Reserved..
	 * Contributor(s): ______________________________________..
	*/
	function get_emails($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user, $adb;
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

		$entityIds = $this->getRelatedContactsIds();
		array_push($entityIds, $id);
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');

		$query = "SELECT case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,
			mycrm_activity.activityid, mycrm_activity.subject, mycrm_activity.activitytype, mycrm_crmentity.modifiedtime,
			mycrm_crmentity.crmid, mycrm_crmentity.smownerid, mycrm_activity.date_start,mycrm_activity.time_start, mycrm_seactivityrel.crmid as parent_id
			FROM mycrm_activity, mycrm_seactivityrel, mycrm_account, mycrm_users, mycrm_crmentity
			LEFT JOIN mycrm_groups ON mycrm_groups.groupid=mycrm_crmentity.smownerid
			WHERE mycrm_seactivityrel.activityid = mycrm_activity.activityid
				AND mycrm_seactivityrel.crmid IN (".$entityIds.")
				AND mycrm_users.id=mycrm_crmentity.smownerid
				AND mycrm_crmentity.crmid = mycrm_activity.activityid
				AND mycrm_activity.activitytype='Emails'
				AND mycrm_account.accountid = ".$id."
				AND mycrm_crmentity.deleted = 0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_emails method ...");
		return $return_value;
	}


	/**
	* Function to get Account related Quotes
	* @param  integer   $id      - accountid
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'account_id','readwrite') == '0') {
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

		$entityIds = $this->getRelatedContactsIds();
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');

		$query = "SELECT case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,
				mycrm_crmentity.*, mycrm_quotes.*, mycrm_potential.potentialname, mycrm_account.accountname
				FROM mycrm_quotes
				INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_quotes.quoteid
				LEFT OUTER JOIN mycrm_account ON mycrm_account.accountid = mycrm_quotes.accountid
				LEFT OUTER JOIN mycrm_potential ON mycrm_potential.potentialid = mycrm_quotes.potentialid
				LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
                LEFT JOIN mycrm_quotescf ON mycrm_quotescf.quoteid = mycrm_quotes.quoteid
				LEFT JOIN mycrm_quotesbillads ON mycrm_quotesbillads.quotebilladdressid = mycrm_quotes.quoteid
				LEFT JOIN mycrm_quotesshipads ON mycrm_quotesshipads.quoteshipaddressid = mycrm_quotes.quoteid
				LEFT JOIN mycrm_users ON mycrm_crmentity.smownerid = mycrm_users.id
				WHERE mycrm_crmentity.deleted = 0 AND (mycrm_account.accountid = $id";

		if(!empty ($entityIds)){
			$query .= " OR mycrm_quotes.contactid IN (".$entityIds."))";
		} else {
			$query .= ")";
		}

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_quotes method ...");
		return $return_value;
	}
	/**
	* Function to get Account related Invoices
	* @param  integer   $id      - accountid
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'account_id','readwrite') == '0') {
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

		$entityIds = $this->getRelatedContactsIds();
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');

		$query = "SELECT case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,
				mycrm_crmentity.*, mycrm_invoice.*, mycrm_account.accountname, mycrm_salesorder.subject AS salessubject
				FROM mycrm_invoice
				INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_invoice.invoiceid
				LEFT OUTER JOIN mycrm_account ON mycrm_account.accountid = mycrm_invoice.accountid
				LEFT OUTER JOIN mycrm_salesorder ON mycrm_salesorder.salesorderid = mycrm_invoice.salesorderid
                LEFT JOIN mycrm_invoicecf ON mycrm_invoicecf.invoiceid = mycrm_invoice.invoiceid
				LEFT JOIN mycrm_invoicebillads ON mycrm_invoicebillads.invoicebilladdressid = mycrm_invoice.invoiceid
				LEFT JOIN mycrm_invoiceshipads ON mycrm_invoiceshipads.invoiceshipaddressid = mycrm_invoice.invoiceid
				LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
				LEFT JOIN mycrm_users ON mycrm_crmentity.smownerid = mycrm_users.id
				WHERE mycrm_crmentity.deleted = 0 AND (mycrm_invoice.accountid = $id";

		if(!empty ($entityIds)){
			$query .= " OR mycrm_invoice.contactid IN (".$entityIds."))";
		} else {
			$query .= ")";
		}

        $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_invoices method ...");
		return $return_value;
	}

	/**
	* Function to get Account related SalesOrder
	* @param  integer   $id      - accountid
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'account_id','readwrite') == '0') {
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

		$entityIds = $this->getRelatedContactsIds();
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');

		$query = "SELECT mycrm_crmentity.*, mycrm_salesorder.*, mycrm_quotes.subject AS quotename, mycrm_account.accountname,
				case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name
				FROM mycrm_salesorder
				INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_salesorder.salesorderid
				LEFT OUTER JOIN mycrm_quotes ON mycrm_quotes.quoteid = mycrm_salesorder.quoteid
				LEFT OUTER JOIN mycrm_account ON mycrm_account.accountid = mycrm_salesorder.accountid
				LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
                LEFT JOIN mycrm_invoice_recurring_info ON mycrm_invoice_recurring_info.start_period = mycrm_salesorder.salesorderid
                LEFT JOIN mycrm_salesordercf ON mycrm_salesordercf.salesorderid = mycrm_salesorder.salesorderid
				LEFT JOIN mycrm_sobillads ON mycrm_sobillads.sobilladdressid = mycrm_salesorder.salesorderid
				LEFT JOIN mycrm_soshipads ON mycrm_soshipads.soshipaddressid = mycrm_salesorder.salesorderid
				LEFT JOIN mycrm_users ON mycrm_crmentity.smownerid = mycrm_users.id
				WHERE mycrm_crmentity.deleted = 0 AND (mycrm_salesorder.accountid = $id";

		if(!empty ($entityIds)){
			$query .= " OR mycrm_salesorder.contactid IN (".$entityIds."))";
		} else {
			$query .= ")";
		}

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_salesorder method ...");
		return $return_value;
	}
	/**
	* Function to get Account related Tickets
	* @param  integer   $id      - accountid
	* returns related Ticket record in array format
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'parent_id','readwrite') == '0') {
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

		$entityIds = $this->getRelatedContactsIds($id);
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');

		$query = "SELECT case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name, mycrm_users.id,
				mycrm_troubletickets.title, mycrm_troubletickets.ticketid AS crmid, mycrm_troubletickets.status, mycrm_troubletickets.priority,
				mycrm_troubletickets.parent_id, mycrm_troubletickets.contact_id, mycrm_troubletickets.ticket_no, mycrm_crmentity.smownerid, mycrm_crmentity.modifiedtime
				FROM mycrm_troubletickets
				INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_troubletickets.ticketid
				LEFT JOIN mycrm_ticketcf ON mycrm_troubletickets.ticketid = mycrm_ticketcf.ticketid
				LEFT JOIN mycrm_users ON mycrm_users.id=mycrm_crmentity.smownerid
				LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
				WHERE  mycrm_crmentity.deleted = 0 and (mycrm_troubletickets.parent_id = $id";

		if(!empty ($entityIds)){
			$query .= " OR mycrm_troubletickets.contact_id IN (".$entityIds."))";
		} else {
			$query .= ")";
		}
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_tickets method ...");
		return $return_value;
	}
	/**
	* Function to get Account related Products
	* @param  integer   $id      - accountid
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

		$entityIds = $this->getRelatedContactsIds();
		array_push($entityIds, $id);
		$entityIds = implode(',', $entityIds);

		$query = "SELECT mycrm_products.productid, mycrm_products.productname, mycrm_products.productcode, mycrm_products.commissionrate,
				mycrm_products.qty_per_unit, mycrm_products.unit_price, mycrm_crmentity.crmid, mycrm_crmentity.smownerid
				FROM mycrm_products
				INNER JOIN mycrm_seproductsrel ON mycrm_products.productid = mycrm_seproductsrel.productid
				and mycrm_seproductsrel.setype IN ('Accounts', 'Contacts')
				INNER JOIN mycrm_productcf ON mycrm_products.productid = mycrm_productcf.productid
				INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_products.productid
				LEFT JOIN mycrm_users ON mycrm_users.id=mycrm_crmentity.smownerid
				LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
				WHERE mycrm_crmentity.deleted = 0 AND mycrm_seproductsrel.crmid IN (".$entityIds.")";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	}

	/** Function to export the account records in CSV Format
	* @param reference variable - where condition is passed when the query is executed
	* Returns Export Accounts Query.
	*/
	function create_export_query($where)
	{
		global $log;
		global $current_user;
                $log->debug("Entering create_export_query(".$where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Accounts", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list,case when (mycrm_users.user_name not like '') then mycrm_users.user_name else mycrm_groups.groupname end as user_name
	       			FROM ".$this->entity_table."
				INNER JOIN mycrm_account
					ON mycrm_account.accountid = mycrm_crmentity.crmid
				LEFT JOIN mycrm_accountbillads
					ON mycrm_accountbillads.accountaddressid = mycrm_account.accountid
				LEFT JOIN mycrm_accountshipads
					ON mycrm_accountshipads.accountaddressid = mycrm_account.accountid
				LEFT JOIN mycrm_accountscf
					ON mycrm_accountscf.accountid = mycrm_account.accountid
	                        LEFT JOIN mycrm_groups
                        	        ON mycrm_groups.groupid = mycrm_crmentity.smownerid
				LEFT JOIN mycrm_users
					ON mycrm_users.id = mycrm_crmentity.smownerid and mycrm_users.status = 'Active'
				LEFT JOIN mycrm_account mycrm_account2
					ON mycrm_account2.accountid = mycrm_account.parentid
				";//mycrm_account2 is added to get the Member of account

		$query .= $this->getNonAdminAccessControlQuery('Accounts',$current_user);
		$where_auto = " mycrm_crmentity.deleted = 0 ";

		if($where != "")
			$query .= " WHERE ($where) AND ".$where_auto;
		else
			$query .= " WHERE ".$where_auto;

		$log->debug("Exiting create_export_query method ...");
		return $query;
	}

	/** Function to get the Columnnames of the Account Record
	* Used By mycrmCRM Word Plugin
	* Returns the Merge Fields for Word Plugin
	*/
	function getColumnNames_Acnt()
	{
		global $log,$current_user;
		$log->debug("Entering getColumnNames_Acnt() method ...");
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0)
		{
			$sql1 = "SELECT fieldlabel FROM mycrm_field WHERE tabid = 6 and mycrm_field.presence in (0,2)";
			$params1 = array();
		}else
		{
			$profileList = getCurrentUserProfileList();
			$sql1 = "select mycrm_field.fieldid,fieldlabel from mycrm_field INNER JOIN mycrm_profile2field on mycrm_profile2field.fieldid=mycrm_field.fieldid inner join mycrm_def_org_field on mycrm_def_org_field.fieldid=mycrm_field.fieldid where mycrm_field.tabid=6 and mycrm_field.displaytype in (1,2,4) and mycrm_profile2field.visible=0 and mycrm_def_org_field.visible=0 and mycrm_field.presence in (0,2)";
			$params1 = array();
			if (count($profileList) > 0) {
				$sql1 .= " and mycrm_profile2field.profileid in (". generateQuestionMarks($profileList) .")  group by fieldid";
			    array_push($params1,  $profileList);
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
		$log->debug("Exiting getColumnNames_Acnt method ...");
		return $mergeflds;
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

		$rel_table_arr = Array("Contacts"=>"mycrm_contactdetails","Potentials"=>"mycrm_potential","Quotes"=>"mycrm_quotes",
					"SalesOrder"=>"mycrm_salesorder","Invoice"=>"mycrm_invoice","Activities"=>"mycrm_seactivityrel",
					"Documents"=>"mycrm_senotesrel","Attachments"=>"mycrm_seattachmentsrel","HelpDesk"=>"mycrm_troubletickets",
					"Products"=>"mycrm_seproductsrel","ServiceContracts"=>"mycrm_servicecontracts","Campaigns"=>"mycrm_campaignaccountrel",
					"Assets"=>"mycrm_assets","Project"=>"mycrm_project");

		$tbl_field_arr = Array("mycrm_contactdetails"=>"contactid","mycrm_potential"=>"potentialid","mycrm_quotes"=>"quoteid",
					"mycrm_salesorder"=>"salesorderid","mycrm_invoice"=>"invoiceid","mycrm_seactivityrel"=>"activityid",
					"mycrm_senotesrel"=>"notesid","mycrm_seattachmentsrel"=>"attachmentsid","mycrm_troubletickets"=>"ticketid",
					"mycrm_seproductsrel"=>"productid","mycrm_servicecontracts"=>"servicecontractsid","mycrm_campaignaccountrel"=>"campaignid",
					"mycrm_assets"=>"assetsid","mycrm_project"=>"projectid","mycrm_payments"=>"paymentsid");

		$entity_tbl_field_arr = Array("mycrm_contactdetails"=>"accountid","mycrm_potential"=>"related_to","mycrm_quotes"=>"accountid",
					"mycrm_salesorder"=>"accountid","mycrm_invoice"=>"accountid","mycrm_seactivityrel"=>"crmid",
					"mycrm_senotesrel"=>"crmid","mycrm_seattachmentsrel"=>"crmid","mycrm_troubletickets"=>"parent_id",
					"mycrm_seproductsrel"=>"crmid","mycrm_servicecontracts"=>"sc_related_to","mycrm_campaignaccountrel"=>"accountid",
					"mycrm_assets"=>"account","mycrm_project"=>"linktoaccountscontacts","mycrm_payments"=>"relatedorganization");

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
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */
	function setRelationTables($secmodule){
		$rel_tables =  array (
			"Contacts" => array("mycrm_contactdetails"=>array("accountid","contactid"),"mycrm_account"=>"accountid"),
			"Potentials" => array("mycrm_potential"=>array("related_to","potentialid"),"mycrm_account"=>"accountid"),
			"Quotes" => array("mycrm_quotes"=>array("accountid","quoteid"),"mycrm_account"=>"accountid"),
			"SalesOrder" => array("mycrm_salesorder"=>array("accountid","salesorderid"),"mycrm_account"=>"accountid"),
			"Invoice" => array("mycrm_invoice"=>array("accountid","invoiceid"),"mycrm_account"=>"accountid"),
			"Calendar" => array("mycrm_seactivityrel"=>array("crmid","activityid"),"mycrm_account"=>"accountid"),
			"HelpDesk" => array("mycrm_troubletickets"=>array("parent_id","ticketid"),"mycrm_account"=>"accountid"),
			"Products" => array("mycrm_seproductsrel"=>array("crmid","productid"),"mycrm_account"=>"accountid"),
			"Documents" => array("mycrm_senotesrel"=>array("crmid","notesid"),"mycrm_account"=>"accountid"),
			"Campaigns" => array("mycrm_campaignaccountrel"=>array("accountid","campaignid"),"mycrm_account"=>"accountid"),
			"Emails" => array("mycrm_seactivityrel"=>array("crmid","activityid"),"mycrm_account"=>"accountid"),
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
		$matrix->setDependency('mycrm_crmentityAccounts', array('mycrm_groupsAccounts', 'mycrm_usersAccounts', 'mycrm_lastModifiedByAccounts'));
		$matrix->setDependency('mycrm_account', array('mycrm_crmentityAccounts',' mycrm_accountbillads', 'mycrm_accountshipads', 'mycrm_accountscf', 'mycrm_accountAccounts', 'mycrm_email_trackAccounts'));

		if (!$queryPlanner->requireTable('mycrm_account', $matrix)) {
			return '';
		}

         // Activities related to contact should linked to accounts if contact is related to that account
        if($module == "Calendar"){
            // query to get all the contacts related to Accounts
            $relContactsQuery = "SELECT contactid FROM mycrm_contactdetails as mycrm_tmpContactCalendar
                        INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_tmpContactCalendar.contactid
                        WHERE mycrm_tmpContactCalendar.accountid IS NOT NULL AND mycrm_tmpContactCalendar.accountid !=''
                        AND mycrm_crmentity.deleted=0";

            $query = " left join mycrm_cntactivityrel as mycrm_tmpcntactivityrel ON
                mycrm_activity.activityid = mycrm_tmpcntactivityrel.activityid AND
                mycrm_tmpcntactivityrel.contactid IN ($relContactsQuery)
                left join mycrm_contactdetails as mycrm_tmpcontactdetails on mycrm_tmpcntactivityrel.contactid = mycrm_tmpcontactdetails.contactid ";
        }else {
            $query = "";
        }

		$query .= $this->getRelationQuery($module,$secmodule,"mycrm_account","accountid", $queryPlanner);

        if($module == "Calendar"){
            $query .= " OR mycrm_account.accountid = mycrm_tmpcontactdetails.accountid " ;
        }
        // End

		if ($queryPlanner->requireTable('mycrm_crmentityAccounts', $matrix)) {
			$query .= " left join mycrm_crmentity as mycrm_crmentityAccounts on mycrm_crmentityAccounts.crmid=mycrm_account.accountid and mycrm_crmentityAccounts.deleted=0";
		}
		if ($queryPlanner->requireTable('mycrm_accountbillads')) {
			$query .= " left join mycrm_accountbillads on mycrm_account.accountid=mycrm_accountbillads.accountaddressid";
		}
		if ($queryPlanner->requireTable('mycrm_accountshipads')) {
			$query .= " left join mycrm_accountshipads on mycrm_account.accountid=mycrm_accountshipads.accountaddressid";
		}
		if ($queryPlanner->requireTable('mycrm_accountscf')) {
			$query .= " left join mycrm_accountscf on mycrm_account.accountid = mycrm_accountscf.accountid";
		}
		if ($queryPlanner->requireTable('mycrm_accountAccounts', $matrix)) {
			$query .= "	left join mycrm_account as mycrm_accountAccounts on mycrm_accountAccounts.accountid = mycrm_account.parentid";
		}
		if ($queryPlanner->requireTable('mycrm_email_track')) {
			$query .= " LEFT JOIN mycrm_email_track AS mycrm_email_trackAccounts ON mycrm_email_trackAccounts .crmid = mycrm_account.accountid";
		}
		if ($queryPlanner->requireTable('mycrm_groupsAccounts')) {
			$query .= "	left join mycrm_groups as mycrm_groupsAccounts on mycrm_groupsAccounts.groupid = mycrm_crmentityAccounts.smownerid";
		}
		if ($queryPlanner->requireTable('mycrm_usersAccounts')) {
			$query .= " left join mycrm_users as mycrm_usersAccounts on mycrm_usersAccounts.id = mycrm_crmentityAccounts.smownerid";
		}
		if ($queryPlanner->requireTable('mycrm_lastModifiedByAccounts')) {
            $query .= " left join mycrm_users as mycrm_lastModifiedByAccounts on mycrm_lastModifiedByAccounts.id = mycrm_crmentityAccounts.modifiedby ";
		}
        if ($queryPlanner->requireTable("mycrm_createdbyAccounts")){
			$query .= " left join mycrm_users as mycrm_createdbyAccounts on mycrm_createdbyAccounts.id = mycrm_crmentityAccounts.smcreatorid ";
		}

		return $query;
	}

	/**
	* Function to get Account hierarchy of the given Account
	* @param  integer   $id      - accountid
	* returns Account hierarchy in array format
	*/
	function getAccountHierarchy($id) {
		global $log, $adb, $current_user;
        $log->debug("Entering getAccountHierarchy(".$id.") method ...");
		require('user_privileges/user_privileges_'.$current_user->id.'.php');

		$tabname = getParentTab();
		$listview_header = Array();
		$listview_entries = array();

		foreach ($this->list_fields_name as $fieldname=>$colname) {
			if(getFieldVisibilityPermission('Accounts', $current_user->id, $colname) == '0') {
				$listview_header[] = getTranslatedString($fieldname);
			}
		}

		$accounts_list = Array();

		// Get the accounts hierarchy from the top most account in the hierarch of the current account, including the current account
		$encountered_accounts = array($id);
		$accounts_list = $this->__getParentAccounts($id, $accounts_list, $encountered_accounts);

		// Get the accounts hierarchy (list of child accounts) based on the current account
		$accounts_list = $this->__getChildAccounts($id, $accounts_list, $accounts_list[$id]['depth']);

		// Create array of all the accounts in the hierarchy
		foreach($accounts_list as $account_id => $account_info) {
			$account_info_data = array();

			$hasRecordViewAccess = (is_admin($current_user)) || (isPermitted('Accounts', 'DetailView', $account_id) == 'yes');

			foreach ($this->list_fields_name as $fieldname=>$colname) {
				// Permission to view account is restricted, avoid showing field values (except account name)
				if(!$hasRecordViewAccess && $colname != 'accountname') {
					$account_info_data[] = '';
				} else if(getFieldVisibilityPermission('Accounts', $current_user->id, $colname) == '0') {
					$data = $account_info[$colname];
					if ($colname == 'accountname') {
						if ($account_id != $id) {
							if($hasRecordViewAccess) {
								$data = '<a href="index.php?module=Accounts&action=DetailView&record='.$account_id.'&parenttab='.$tabname.'">'.$data.'</a>';
							} else {
								$data = '<i>'.$data.'</i>';
							}
						} else {
							$data = '<b>'.$data.'</b>';
						}
						// - to show the hierarchy of the Accounts
						$account_depth = str_repeat(" .. ", $account_info['depth'] * 2);
						$data = $account_depth . $data;
					} else if ($colname == 'website') {
						$data = '<a href="http://'. $data .'" target="_blank">'.$data.'</a>';
					}
					$account_info_data[] = $data;
				}
			}
			$listview_entries[$account_id] = $account_info_data;
		}

		$account_hierarchy = array('header'=>$listview_header,'entries'=>$listview_entries);
        $log->debug("Exiting getAccountHierarchy method ...");
		return $account_hierarchy;
	}

	/**
	* Function to Recursively get all the upper accounts of a given Account
	* @param  integer   $id      		- accountid
	* @param  array   $parent_accounts   - Array of all the parent accounts
	* returns All the parent accounts of the given accountid in array format
	*/
	function __getParentAccounts($id, &$parent_accounts, &$encountered_accounts) {
		global $log, $adb;
        $log->debug("Entering __getParentAccounts(".$id.",".$parent_accounts.") method ...");

		$query = "SELECT parentid FROM mycrm_account " .
				" INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_account.accountid" .
				" WHERE mycrm_crmentity.deleted = 0 and mycrm_account.accountid = ?";
		$params = array($id);

		$res = $adb->pquery($query, $params);

		if ($adb->num_rows($res) > 0 &&
			$adb->query_result($res, 0, 'parentid') != '' && $adb->query_result($res, 0, 'parentid') != 0 &&
			!in_array($adb->query_result($res, 0, 'parentid'),$encountered_accounts)) {

			$parentid = $adb->query_result($res, 0, 'parentid');
			$encountered_accounts[] = $parentid;
			$this->__getParentAccounts($parentid,$parent_accounts,$encountered_accounts);
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "SELECT mycrm_account.*, mycrm_accountbillads.*," .
				" CASE when (mycrm_users.user_name not like '') THEN $userNameSql ELSE mycrm_groups.groupname END as user_name " .
				" FROM mycrm_account" .
				" INNER JOIN mycrm_crmentity " .
				" ON mycrm_crmentity.crmid = mycrm_account.accountid" .
				" INNER JOIN mycrm_accountbillads" .
				" ON mycrm_account.accountid = mycrm_accountbillads.accountaddressid " .
				" LEFT JOIN mycrm_groups" .
				" ON mycrm_groups.groupid = mycrm_crmentity.smownerid" .
				" LEFT JOIN mycrm_users" .
				" ON mycrm_users.id = mycrm_crmentity.smownerid" .
				" WHERE mycrm_crmentity.deleted = 0 and mycrm_account.accountid = ?";
		$params = array($id);
		$res = $adb->pquery($query, $params);

		$parent_account_info = array();
		$depth = 0;
		$immediate_parentid = $adb->query_result($res, 0, 'parentid');
		if (isset($parent_accounts[$immediate_parentid])) {
			$depth = $parent_accounts[$immediate_parentid]['depth'] + 1;
		}
		$parent_account_info['depth'] = $depth;
		foreach($this->list_fields_name as $fieldname=>$columnname) {
			if ($columnname == 'assigned_user_id') {
				$parent_account_info[$columnname] = $adb->query_result($res, 0, 'user_name');
			} else {
				$parent_account_info[$columnname] = $adb->query_result($res, 0, $columnname);
			}
		}
		$parent_accounts[$id] = $parent_account_info;
        $log->debug("Exiting __getParentAccounts method ...");
		return $parent_accounts;
	}

	/**
	* Function to Recursively get all the child accounts of a given Account
	* @param  integer   $id      		- accountid
	* @param  array   $child_accounts   - Array of all the child accounts
	* @param  integer   $depth          - Depth at which the particular account has to be placed in the hierarchy
	* returns All the child accounts of the given accountid in array format
	*/
	function __getChildAccounts($id, &$child_accounts, $depth) {
		global $log, $adb;
        $log->debug("Entering __getChildAccounts(".$id.",".$child_accounts.",".$depth.") method ...");

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "SELECT mycrm_account.*, mycrm_accountbillads.*," .
				" CASE when (mycrm_users.user_name not like '') THEN $userNameSql ELSE mycrm_groups.groupname END as user_name " .
				" FROM mycrm_account" .
				" INNER JOIN mycrm_crmentity " .
				" ON mycrm_crmentity.crmid = mycrm_account.accountid" .
				" INNER JOIN mycrm_accountbillads" .
				" ON mycrm_account.accountid = mycrm_accountbillads.accountaddressid " .
				" LEFT JOIN mycrm_groups" .
				" ON mycrm_groups.groupid = mycrm_crmentity.smownerid" .
				" LEFT JOIN mycrm_users" .
				" ON mycrm_users.id = mycrm_crmentity.smownerid" .
				" WHERE mycrm_crmentity.deleted = 0 and parentid = ?";
		$params = array($id);
		$res = $adb->pquery($query, $params);

		$num_rows = $adb->num_rows($res);

		if ($num_rows > 0) {
			$depth = $depth + 1;
			for($i=0;$i<$num_rows;$i++) {
				$child_acc_id = $adb->query_result($res, $i, 'accountid');
				if(array_key_exists($child_acc_id,$child_accounts)) {
					continue;
				}
				$child_account_info = array();
				$child_account_info['depth'] = $depth;
				foreach($this->list_fields_name as $fieldname=>$columnname) {
					if ($columnname == 'assigned_user_id') {
						$child_account_info[$columnname] = $adb->query_result($res, $i, 'user_name');
					} else {
						$child_account_info[$columnname] = $adb->query_result($res, $i, $columnname);
					}
				}
				$child_accounts[$child_acc_id] = $child_account_info;
				$this->__getChildAccounts($child_acc_id, $child_accounts, $depth);
			}
		}
        $log->debug("Exiting __getChildAccounts method ...");
		return $child_accounts;
	}

	// Function to unlink the dependent records of the given record by id
	function unlinkDependencies($module, $id) {
		global $log;

		//Deleting Account related Potentials.
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
		//Backup deleted Account related Potentials.
		$params = array($id, RB_RECORD_UPDATED, 'mycrm_crmentity', 'deleted', 'crmid', implode(",", $pot_ids_list));
		$this->db->pquery('INSERT INTO mycrm_relatedlists_rb VALUES(?,?,?,?,?,?)', $params);

		//Deleting Account related Quotes.
		$quo_q = 'SELECT mycrm_crmentity.crmid FROM mycrm_crmentity
			INNER JOIN mycrm_quotes ON mycrm_crmentity.crmid=mycrm_quotes.quoteid
			INNER JOIN mycrm_account ON mycrm_account.accountid=mycrm_quotes.accountid
			WHERE mycrm_crmentity.deleted=0 AND mycrm_quotes.accountid=?';
		$quo_res = $this->db->pquery($quo_q, array($id));
		$quo_ids_list = array();
		for($k=0;$k < $this->db->num_rows($quo_res);$k++)
		{
			$quo_id = $this->db->query_result($quo_res,$k,"crmid");
			$quo_ids_list[] = $quo_id;
			$sql = 'UPDATE mycrm_crmentity SET deleted = 1 WHERE crmid = ?';
			$this->db->pquery($sql, array($quo_id));
		}
		//Backup deleted Account related Quotes.
		$params = array($id, RB_RECORD_UPDATED, 'mycrm_crmentity', 'deleted', 'crmid', implode(",", $quo_ids_list));
		$this->db->pquery('INSERT INTO mycrm_relatedlists_rb VALUES(?,?,?,?,?,?)', $params);

		//Backup Contact-Account Relation
		$con_q = 'SELECT contactid FROM mycrm_contactdetails WHERE accountid = ?';
		$con_res = $this->db->pquery($con_q, array($id));
		if ($this->db->num_rows($con_res) > 0) {
			$con_ids_list = array();
			for($k=0;$k < $this->db->num_rows($con_res);$k++)
			{
				$con_ids_list[] = $this->db->query_result($con_res,$k,"contactid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'mycrm_contactdetails', 'accountid', 'contactid', implode(",", $con_ids_list));
			$this->db->pquery('INSERT INTO mycrm_relatedlists_rb VALUES(?,?,?,?,?,?)', $params);
		}
		//Deleting Contact-Account Relation.
		$con_q = 'UPDATE mycrm_contactdetails SET accountid = 0 WHERE accountid = ?';
		$this->db->pquery($con_q, array($id));

		//Backup Trouble Tickets-Account Relation
		$tkt_q = 'SELECT ticketid FROM mycrm_troubletickets WHERE parent_id = ?';
		$tkt_res = $this->db->pquery($tkt_q, array($id));
		if ($this->db->num_rows($tkt_res) > 0) {
			$tkt_ids_list = array();
			for($k=0;$k < $this->db->num_rows($tkt_res);$k++)
			{
				$tkt_ids_list[] = $this->db->query_result($tkt_res,$k,"ticketid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'mycrm_troubletickets', 'parent_id', 'ticketid', implode(",", $tkt_ids_list));
			$this->db->pquery('INSERT INTO mycrm_relatedlists_rb VALUES(?,?,?,?,?,?)', $params);
		}
		//Deleting Trouble Tickets-Account Relation.
		$tt_q = 'UPDATE mycrm_troubletickets SET parent_id = 0 WHERE parent_id = ?';
		$this->db->pquery($tt_q, array($id));

		parent::unlinkDependencies($module, $id);
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Campaigns') {
			$sql = 'DELETE FROM mycrm_campaignaccountrel WHERE accountid=? AND campaignid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} else if($return_module == 'Products') {
			$sql = 'DELETE FROM mycrm_seproductsrel WHERE crmid=? AND productid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} else {
			$sql = 'DELETE FROM mycrm_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}

	function save_related_module($module, $crmid, $with_module, $with_crmids) {
		$adb = $this->db;

		if(!is_array($with_crmids)) $with_crmids = Array($with_crmids);
		foreach($with_crmids as $with_crmid) {
			if($with_module == 'Products')
				$adb->pquery("insert into mycrm_seproductsrel values(?,?,?)", array($crmid, $with_crmid, $module));
			elseif($with_module == 'Campaigns') {
				$checkResult = $adb->pquery('SELECT 1 FROM mycrm_campaignaccountrel WHERE campaignid = ? AND accountid = ?',
												array($with_crmid, $crmid));
				if($checkResult && $adb->num_rows($checkResult) > 0) {
					continue;
				}
				$adb->pquery("insert into mycrm_campaignaccountrel values(?,?,1)", array($with_crmid, $crmid));
			} else {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid);
			}
		}
	}

	function getListButtons($app_strings,$mod_strings = false) {
		$list_buttons = Array();

		if(isPermitted('Accounts','Delete','') == 'yes') {
			$list_buttons['del'] = $app_strings[LBL_MASS_DELETE];
		}
		if(isPermitted('Accounts','EditView','') == 'yes') {
			$list_buttons['mass_edit'] = $app_strings[LBL_MASS_EDIT];
			$list_buttons['c_owner'] = $app_strings[LBL_CHANGE_OWNER];
		}
		if(isPermitted('Emails','EditView','') == 'yes') {
			$list_buttons['s_mail'] = $app_strings[LBL_SEND_MAIL_BUTTON];
		}
		// mailer export
		if(isPermitted('Accounts','Export','') == 'yes') {
			$list_buttons['mailer_exp'] = $mod_strings[LBL_MAILER_EXPORT];
		}
		// end of mailer export
		return $list_buttons;
	}

	/* Function to get attachments in the related list of accounts module */
	function get_attachments($id, $cur_tab_id, $rel_tab_id, $actions = false) {

		global $currentModule, $app_strings, $singlepane_view;
		$this_module = $currentModule;
		$parenttab = getParentTab();

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);

		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars($related_module, $other);

		$singular_modname = vtlib_toSingular($related_module);
		$button = '';
		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />" .
						"<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "' class='crmbutton small create'" .
						" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
						" value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "'>&nbsp;";
			}
		}

		// To make the edit or del link actions to return back to same view.
		if ($singlepane_view == 'true'){
			$returnset = "&return_module=$this_module&return_action=DetailView&return_id=$id";
		} else {
			$returnset = "&return_module=$this_module&return_action=CallRelatedList&return_id=$id";
		}

		$entityIds = $this->getRelatedContactsIds();
		array_push($entityIds, $id);
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=> 'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');

		$query = "SELECT case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,
				'Documents' ActivityType,mycrm_attachments.type  FileType,crm2.modifiedtime lastmodified,mycrm_crmentity.modifiedtime,
				mycrm_seattachmentsrel.attachmentsid attachmentsid, mycrm_notes.notesid crmid, mycrm_notes.notecontent description,mycrm_notes.*
				from mycrm_notes
				INNER JOIN mycrm_senotesrel ON mycrm_senotesrel.notesid= mycrm_notes.notesid
				LEFT JOIN mycrm_notescf ON mycrm_notescf.notesid= mycrm_notes.notesid
				INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid= mycrm_notes.notesid and mycrm_crmentity.deleted=0
				INNER JOIN mycrm_crmentity crm2 ON crm2.crmid=mycrm_senotesrel.crmid
				LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
				LEFT JOIN mycrm_seattachmentsrel ON mycrm_seattachmentsrel.crmid =mycrm_notes.notesid
				LEFT JOIN mycrm_attachments ON mycrm_seattachmentsrel.attachmentsid = mycrm_attachments.attachmentsid
				LEFT JOIN mycrm_users ON mycrm_crmentity.smownerid= mycrm_users.id
				WHERE crm2.crmid IN (".$entityIds.")";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		return $return_value;
	}

	/**
	 * Function to handle the dependents list for the module.
	 * NOTE: UI type '10' is used to stored the references to other modules for a given record.
	 * These dependent records can be retrieved through this function.
	 * For eg: A trouble ticket can be related to an Account or a Contact.
	 * From a given Contact/Account if we need to fetch all such dependent trouble tickets, get_dependents_list function can be used.
	 */
	function get_dependents_list($id, $cur_tab_id, $rel_tab_id, $actions = false) {

		global $currentModule, $app_strings, $singlepane_view, $current_user;

		$parenttab = getParentTab();

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);

		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars($currentModule, $this);
		vtlib_setup_modulevars($related_module, $other);

		$singular_modname = 'SINGLE_' . $related_module;
		$button = '';

		// To make the edit or del link actions to return back to same view.
		if ($singlepane_view == 'true')
			$returnset = "&return_module=$currentModule&return_action=DetailView&return_id=$id";
		else
			$returnset = "&return_module=$currentModule&return_action=CallRelatedList&return_id=$id";

		$return_value = null;
		$dependentFieldSql = $this->db->pquery("SELECT tabid, fieldname, columnname FROM mycrm_field WHERE uitype='10' AND" .
				" fieldid IN (SELECT fieldid FROM mycrm_fieldmodulerel WHERE relmodule=? AND module=?)", array($currentModule, $related_module));
		$numOfFields = $this->db->num_rows($dependentFieldSql);

		if ($numOfFields > 0) {
			$dependentColumn = $this->db->query_result($dependentFieldSql, 0, 'columnname');
			$dependentField = $this->db->query_result($dependentFieldSql, 0, 'fieldname');

			$button .= '<input type="hidden" name="' . $dependentColumn . '" id="' . $dependentColumn . '" value="' . $id . '">';
			$button .= '<input type="hidden" name="' . $dependentColumn . '_type" id="' . $dependentColumn . '_type" value="' . $currentModule . '">';
			if ($actions) {
				if (is_string($actions))
					$actions = explode(',', strtoupper($actions));
				if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes'
						&& getFieldVisibilityPermission($related_module, $current_user->id, $dependentField, 'readwrite') == '0') {
					$button .= "<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname, $related_module) . "' class='crmbutton small create'" .
							" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
							" value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname, $related_module) . "'>&nbsp;";
				}
			}

			$entityIds = $this->getRelatedContactsIds();
			array_push($entityIds, $id);
			$entityIds = implode(',', $entityIds);

			$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'mycrm_users.first_name','last_name' => 'mycrm_users.last_name'), 'Users');

			$query = "SELECT mycrm_crmentity.*, $other->table_name.*";
			$query .= ", CASE WHEN (mycrm_users.user_name NOT LIKE '') THEN $userNameSql ELSE mycrm_groups.groupname END AS user_name";

			$more_relation = '';
			if (!empty($other->related_tables)) {
				foreach ($other->related_tables as $tname => $relmap) {
					$query .= ", $tname.*";

					// Setup the default JOIN conditions if not specified
					if (empty($relmap[1]))
						$relmap[1] = $other->table_name;
					if (empty($relmap[2]))
						$relmap[2] = $relmap[0];
					$more_relation .= " LEFT JOIN $tname ON $tname.$relmap[0] = $relmap[1].$relmap[2]";
				}
			}

			$query .= " FROM $other->table_name";
			$query .= " INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = $other->table_name.$other->table_index";
			$query .= $more_relation;
			$query .= " LEFT JOIN mycrm_users ON mycrm_users.id = mycrm_crmentity.smownerid";
			$query .= " LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid";
			$query .= " WHERE mycrm_crmentity.deleted = 0 AND $other->table_name.$dependentColumn IN (".$entityIds.")";

			$return_value = GetRelatedList($currentModule, $related_module, $other, $query, $button, $returnset);
		}
		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		return $return_value;
	}

	/**
	 * Function to handle the related list for the module.
	 * NOTE: Mycrm_Module::setRelatedList sets reference to this function in mycrm_relatedlists table
	 * if function name is not explicitly specified.
	 */
	function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions = false) {

		global $currentModule, $app_strings, $singlepane_view;

		$parenttab = getParentTab();

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);

		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars($currentModule, $this);
		vtlib_setup_modulevars($related_module, $other);

		$singular_modname = 'SINGLE_' . $related_module;

		$button = '';
		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "' class='crmbutton small edit' " .
						" type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\"" .
						" value='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module, $related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />" .
						"<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "' class='crmbutton small create'" .
						" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
						" value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname, $related_module) . "'>&nbsp;";
			}
		}

		// To make the edit or del link actions to return back to same view.
		if ($singlepane_view == 'true') {
			$returnset = "&return_module=$currentModule&return_action=DetailView&return_id=$id";
		} else {
			$returnset = "&return_module=$currentModule&return_action=CallRelatedList&return_id=$id";
		}

		$more_relation = '';
		if (!empty($other->related_tables)) {
			foreach ($other->related_tables as $tname => $relmap) {
				$query .= ", $tname.*";

				// Setup the default JOIN conditions if not specified
				if (empty($relmap[1]))
					$relmap[1] = $other->table_name;
				if (empty($relmap[2]))
					$relmap[2] = $relmap[0];
				$more_relation .= " LEFT JOIN $tname ON $tname.$relmap[0] = $relmap[1].$relmap[2]";
			}
		}

		$entityIds = $this->getRelatedContactsIds();
		array_push($entityIds, $id);
		$entityIds = implode(',', $entityIds);

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');

		$query = "SELECT mycrm_crmentity.*, $other->table_name.*,
				CASE WHEN (mycrm_users.user_name NOT LIKE '') THEN $userNameSql ELSE mycrm_groups.groupname END AS user_name FROM $other->table_name
				INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = $other->table_name.$other->table_index
				INNER JOIN mycrm_crmentityrel ON (mycrm_crmentityrel.relcrmid = mycrm_crmentity.crmid OR mycrm_crmentityrel.crmid = mycrm_crmentity.crmid)
				$more_relation
				LEFT  JOIN mycrm_users ON mycrm_users.id = mycrm_crmentity.smownerid
				LEFT  JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
				WHERE mycrm_crmentity.deleted = 0 AND (mycrm_crmentityrel.crmid IN (" .$entityIds. ") OR mycrm_crmentityrel.relcrmid IN (". $entityIds . "))";

		$return_value = GetRelatedList($currentModule, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		return $return_value;
	}

	/* Function to get related contact ids for an account record*/
	function getRelatedContactsIds($id = null) {
		global $adb;
		if($id ==null)
		$id = $this->id;
		$entityIds = array();
		$query = 'SELECT contactid FROM mycrm_contactdetails
				INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_contactdetails.contactid
				WHERE mycrm_contactdetails.accountid = ? AND mycrm_crmentity.deleted = 0';
		$accountContacts = $adb->pquery($query, array($id));
		$numOfContacts = $adb->num_rows($accountContacts);
		if($accountContacts && $numOfContacts > 0) {
			for($i=0; $i < $numOfContacts; ++$i) {
				array_push($entityIds, $adb->query_result($accountContacts, $i, 'contactid'));
			}
		}
		return $entityIds;
	}
}

?>
