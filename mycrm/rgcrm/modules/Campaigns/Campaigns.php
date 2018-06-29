<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of txhe License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
class Campaigns extends CRMEntity {
	var $log;
	var $db;
	var $table_name = "mycrm_campaign";
	var $table_index= 'campaignid';

	var $tab_name = Array('mycrm_crmentity','mycrm_campaign','mycrm_campaignscf');
	var $tab_name_index = Array('mycrm_crmentity'=>'crmid','mycrm_campaign'=>'campaignid','mycrm_campaignscf'=>'campaignid');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('mycrm_campaignscf', 'campaignid');
	var $column_fields = Array();

	var $sortby_fields = Array('campaignname','smownerid','campaigntype','productname','expectedrevenue','closingdate','campaignstatus','expectedresponse','targetaudience','expectedcost');

	var $list_fields = Array(
					'Campaign Name'=>Array('campaign'=>'campaignname'),
					'Campaign Type'=>Array('campaign'=>'campaigntype'),
					'Campaign Status'=>Array('campaign'=>'campaignstatus'),
					'Expected Revenue'=>Array('campaign'=>'expectedrevenue'),
					'Expected Close Date'=>Array('campaign'=>'closingdate'),
					'Assigned To' => Array('crmentity'=>'smownerid')
				);

	var $list_fields_name = Array(
					'Campaign Name'=>'campaignname',
					'Campaign Type'=>'campaigntype',
					'Campaign Status'=>'campaignstatus',
					'Expected Revenue'=>'expectedrevenue',
					'Expected Close Date'=>'closingdate',
					'Assigned To'=>'assigned_user_id'
				     );

	var $list_link_field= 'campaignname';
	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'crmid';
	var $default_sort_order = 'DESC';

	//var $groupTable = Array('mycrm_campaigngrouprelation','campaignid');

	var $search_fields = Array(
			'Campaign Name'=>Array('mycrm_campaign'=>'campaignname'),
			'Campaign Type'=>Array('mycrm_campaign'=>'campaigntype'),
			);

	var $search_fields_name = Array(
			'Campaign Name'=>'campaignname',
			'Campaign Type'=>'campaigntype',
			);
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to mycrm_field.fieldname values.
	var $mandatory_fields = Array('campaignname','createdtime' ,'modifiedtime','assigned_user_id');

	// For Alphabetical search
	var $def_basicsearch_col = 'campaignname';

	function Campaigns()
	{
		$this->log =LoggerManager::getLogger('campaign');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Campaigns');
	}

	/** Function to handle module specific operations when saving a entity
	*/
	function save_module($module)
	{
	}

	// Mike Crowe Mod --------------------------------------------------------Default ordering for us
	/**
	 * Function to get Campaign related Accouts
	 * @param  integer   $id      - campaignid
	 * returns related Accounts record in array format
	 */
	function get_accounts($id, $cur_tab_id, $rel_tab_id, $actions = false) {
		global $log, $singlepane_view,$currentModule;
		$log->debug("Entering get_accounts(".$id.") method ...");
		$this_module = $currentModule;

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();

		$is_CampaignStatusAllowed = false;
		global $current_user;
		if(getFieldVisibilityPermission('Accounts', $current_user->id, 'campaignrelstatus') == '0') {
			$other->list_fields['Status'] = array('mycrm_campaignrelstatus'=>'campaignrelstatus');
			$other->list_fields_name['Status'] = 'campaignrelstatus';
			$other->sortby_fields[] = 'campaignrelstatus';
			$is_CampaignStatusAllowed = (getFieldVisibilityPermission('Accounts', $current_user->id, 'campaignrelstatus','readwrite') == '0')? true : false;
		}

		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		// Send mail button for selected Accounts
		$button .= "<input title='".getTranslatedString('LBL_SEND_MAIL_BUTTON')."' class='crmbutton small edit' value='".getTranslatedString('LBL_SEND_MAIL_BUTTON')."' type='button' name='button' onclick='rel_eMail(\"$this_module\",this,\"$related_module\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';
		/* To get Accounts CustomView -START */
		require_once('modules/CustomView/CustomView.php');
		$ahtml = "<select id='".$related_module."_cv_list' class='small'><option value='None'>-- ".getTranslatedString('Select One')." --</option>";
		$oCustomView = new CustomView($related_module);
		$viewid = $oCustomView->getViewId($related_module);
		$customviewcombo_html = $oCustomView->getCustomViewCombo($viewid, false);
		$ahtml .= $customviewcombo_html;
		$ahtml .= "</select>";
		/* To get Accounts CustomView -END */

		$button .= $ahtml."<input title='".getTranslatedString('LBL_LOAD_LIST',$this_module)."' class='crmbutton small edit' value='".getTranslatedString('LBL_LOAD_LIST',$this_module)."' type='button' name='button' onclick='loadCvList(\"$related_module\",\"$id\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';

		if($actions)
		{
			if(is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes')
			{
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes')
			{
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />".
					"<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "SELECT mycrm_account.*,
				CASE when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,
				mycrm_crmentity.*, mycrm_crmentity.modifiedtime, mycrm_campaignrelstatus.*, mycrm_accountbillads.*
				FROM mycrm_account
				INNER JOIN mycrm_campaignaccountrel ON mycrm_campaignaccountrel.accountid = mycrm_account.accountid
				INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_account.accountid
				INNER JOIN mycrm_accountshipads ON mycrm_accountshipads.accountaddressid = mycrm_account.accountid
				LEFT JOIN mycrm_groups ON mycrm_groups.groupid=mycrm_crmentity.smownerid
				LEFT JOIN mycrm_users ON mycrm_crmentity.smownerid=mycrm_users.id
				LEFT JOIN mycrm_accountbillads ON mycrm_accountbillads.accountaddressid = mycrm_account.accountid
				LEFT JOIN mycrm_accountscf ON mycrm_account.accountid = mycrm_accountscf.accountid
				LEFT JOIN mycrm_campaignrelstatus ON mycrm_campaignrelstatus.campaignrelstatusid = mycrm_campaignaccountrel.campaignrelstatusid
				WHERE mycrm_campaignaccountrel.campaignid = ".$id." AND mycrm_crmentity.deleted=0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null)
			$return_value = Array();
		else if($is_CampaignStatusAllowed) {
			$statusPos = count($return_value['header']) - 2; // Last column is for Actions, exclude that. Also the index starts from 0, so reduce one more count.
			$return_value = $this->add_status_popup($return_value, $statusPos, 'Accounts');
		}

		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_accounts method ...");
		return $return_value;
	}

	/**
	 * Function to get Campaign related Contacts
	 * @param  integer   $id      - campaignid
	 * returns related Contacts record in array format
	 */
	function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule;
		$log->debug("Entering get_contacts(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();

		$is_CampaignStatusAllowed = false;
		global $current_user;
		if(getFieldVisibilityPermission('Contacts', $current_user->id, 'campaignrelstatus') == '0') {
			$other->list_fields['Status'] = array('mycrm_campaignrelstatus'=>'campaignrelstatus');
			$other->list_fields_name['Status'] = 'campaignrelstatus';
			$other->sortby_fields[] = 'campaignrelstatus';
			$is_CampaignStatusAllowed = (getFieldVisibilityPermission('Contacts', $current_user->id, 'campaignrelstatus','readwrite') == '0')? true : false;
		}

		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		// Send mail button for selected Leads
		$button .= "<input title='".getTranslatedString('LBL_SEND_MAIL_BUTTON')."' class='crmbutton small edit' value='".getTranslatedString('LBL_SEND_MAIL_BUTTON')."' type='button' name='button' onclick='rel_eMail(\"$this_module\",this,\"$related_module\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';

		/* To get Leads CustomView -START */
		require_once('modules/CustomView/CustomView.php');
		$lhtml = "<select id='".$related_module."_cv_list' class='small'><option value='None'>-- ".getTranslatedString('Select One')." --</option>";
		$oCustomView = new CustomView($related_module);
		$viewid = $oCustomView->getViewId($related_module);
		$customviewcombo_html = $oCustomView->getCustomViewCombo($viewid, false);
		$lhtml .= $customviewcombo_html;
		$lhtml .= "</select>";
		/* To get Leads CustomView -END */

		$button .= $lhtml."<input title='".getTranslatedString('LBL_LOAD_LIST',$this_module)."' class='crmbutton small edit' value='".getTranslatedString('LBL_LOAD_LIST',$this_module)."' type='button' name='button' onclick='loadCvList(\"$related_module\",\"$id\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />".
					"<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "SELECT mycrm_contactdetails.accountid, mycrm_account.accountname,
				CASE when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name ,
				mycrm_contactdetails.contactid, mycrm_contactdetails.lastname, mycrm_contactdetails.firstname, mycrm_contactdetails.title,
				mycrm_contactdetails.department, mycrm_contactdetails.email, mycrm_contactdetails.phone, mycrm_crmentity.crmid,
				mycrm_crmentity.smownerid, mycrm_crmentity.modifiedtime, mycrm_campaignrelstatus.*
				FROM mycrm_contactdetails
				INNER JOIN mycrm_campaigncontrel ON mycrm_campaigncontrel.contactid = mycrm_contactdetails.contactid
				INNER JOIN mycrm_contactaddress ON mycrm_contactdetails.contactid = mycrm_contactaddress.contactaddressid
				INNER JOIN mycrm_contactsubdetails ON mycrm_contactdetails.contactid = mycrm_contactsubdetails.contactsubscriptionid
				INNER JOIN mycrm_customerdetails ON mycrm_contactdetails.contactid = mycrm_customerdetails.customerid
				INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_contactdetails.contactid
				LEFT JOIN mycrm_contactscf ON mycrm_contactdetails.contactid = mycrm_contactscf.contactid
				LEFT JOIN mycrm_groups ON mycrm_groups.groupid=mycrm_crmentity.smownerid
				LEFT JOIN mycrm_users ON mycrm_crmentity.smownerid=mycrm_users.id
				LEFT JOIN mycrm_account ON mycrm_account.accountid = mycrm_contactdetails.accountid
				LEFT JOIN mycrm_campaignrelstatus ON mycrm_campaignrelstatus.campaignrelstatusid = mycrm_campaigncontrel.campaignrelstatusid
				WHERE mycrm_campaigncontrel.campaignid = ".$id." AND mycrm_crmentity.deleted=0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null)
			$return_value = Array();
		else if($is_CampaignStatusAllowed) {
			$statusPos = count($return_value['header']) - 2; // Last column is for Actions, exclude that. Also the index starts from 0, so reduce one more count.
			$return_value = $this->add_status_popup($return_value, $statusPos, 'Contacts');
		}

		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
	}

	/**
	 * Function to get Campaign related Leads
	 * @param  integer   $id      - campaignid
	 * returns related Leads record in array format
	 */
	function get_leads($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view, $currentModule;
        $log->debug("Entering get_leads(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();

		$is_CampaignStatusAllowed = false;
		global $current_user;
		if(getFieldVisibilityPermission('Leads', $current_user->id, 'campaignrelstatus') == '0') {
			$other->list_fields['Status'] = array('mycrm_campaignrelstatus'=>'campaignrelstatus');
			$other->list_fields_name['Status'] = 'campaignrelstatus';
			$other->sortby_fields[] = 'campaignrelstatus';
			$is_CampaignStatusAllowed  = (getFieldVisibilityPermission('Leads', $current_user->id, 'campaignrelstatus','readwrite') == '0')? true : false;
		}

		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		// Send mail button for selected Leads
		$button .= "<input title='".getTranslatedString('LBL_SEND_MAIL_BUTTON')."' class='crmbutton small edit' value='".getTranslatedString('LBL_SEND_MAIL_BUTTON')."' type='button' name='button' onclick='rel_eMail(\"$this_module\",this,\"$related_module\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';

		/* To get Leads CustomView -START */
		require_once('modules/CustomView/CustomView.php');
		$lhtml = "<select id='".$related_module."_cv_list' class='small'><option value='None'>-- ".getTranslatedString('Select One')." --</option>";
		$oCustomView = new CustomView($related_module);
		$viewid = $oCustomView->getViewId($related_module);
		$customviewcombo_html = $oCustomView->getCustomViewCombo($viewid, false);
		$lhtml .= $customviewcombo_html;
		$lhtml .= "</select>";
		/* To get Leads CustomView -END */

		$button .= $lhtml."<input title='".getTranslatedString('LBL_LOAD_LIST',$this_module)."' class='crmbutton small edit' value='".getTranslatedString('LBL_LOAD_LIST',$this_module)."' type='button' name='button' onclick='loadCvList(\"$related_module\",\"$id\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />".
					"<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "SELECT mycrm_leaddetails.*, mycrm_crmentity.crmid,mycrm_leadaddress.phone,mycrm_leadsubdetails.website,
				CASE when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,
				mycrm_crmentity.smownerid, mycrm_campaignrelstatus.*
				FROM mycrm_leaddetails
				INNER JOIN mycrm_campaignleadrel ON mycrm_campaignleadrel.leadid=mycrm_leaddetails.leadid
				INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_leaddetails.leadid
				INNER JOIN mycrm_leadsubdetails  ON mycrm_leadsubdetails.leadsubscriptionid = mycrm_leaddetails.leadid
				INNER JOIN mycrm_leadaddress ON mycrm_leadaddress.leadaddressid = mycrm_leadsubdetails.leadsubscriptionid
				INNER JOIN mycrm_leadscf ON mycrm_leaddetails.leadid = mycrm_leadscf.leadid
				LEFT JOIN mycrm_users ON mycrm_crmentity.smownerid = mycrm_users.id
				LEFT JOIN mycrm_groups ON mycrm_groups.groupid=mycrm_crmentity.smownerid
				LEFT JOIN mycrm_campaignrelstatus ON mycrm_campaignrelstatus.campaignrelstatusid = mycrm_campaignleadrel.campaignrelstatusid
				WHERE mycrm_crmentity.deleted=0 AND mycrm_campaignleadrel.campaignid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null)
			$return_value = Array();
		else if($is_CampaignStatusAllowed) {
			$statusPos = count($return_value['header']) - 2; // Last column is for Actions, exclude that. Also the index starts from 0, so reduce one more count.
			$return_value = $this->add_status_popup($return_value, $statusPos, 'Leads');
		}

		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_leads method ...");
		return $return_value;
	}

	/**
	 * Function to get Campaign related Potentials
	 * @param  integer   $id      - campaignid
	 * returns related potentials record in array format
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

		if($actions && getFieldVisibilityPermission($related_module,$current_user->id,'campaignid', 'readwrite') == '0') {
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
		$query = "SELECT CASE when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,
					mycrm_potential.related_to, mycrm_potential.contact_id, mycrm_account.accountname, mycrm_potential.potentialid, mycrm_potential.potentialname,
					mycrm_potential.potentialtype, mycrm_potential.sales_stage, mycrm_potential.amount, mycrm_potential.closingdate,
					mycrm_crmentity.crmid, mycrm_crmentity.smownerid FROM mycrm_campaign
					INNER JOIN mycrm_potential ON mycrm_campaign.campaignid = mycrm_potential.campaignid
					INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_potential.potentialid
					INNER JOIN mycrm_potentialscf ON mycrm_potential.potentialid = mycrm_potentialscf.potentialid
					LEFT JOIN mycrm_groups ON mycrm_groups.groupid=mycrm_crmentity.smownerid
					LEFT JOIN mycrm_users ON mycrm_users.id=mycrm_crmentity.smownerid
					LEFT JOIN mycrm_account ON mycrm_account.accountid = mycrm_potential.related_to
					LEFT JOIN mycrm_contactdetails ON mycrm_contactdetails.contactid = mycrm_potential.contact_id
					WHERE mycrm_campaign.campaignid = ".$id." AND mycrm_crmentity.deleted=0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_opportunities method ...");
		return $return_value;
	}

	/**
	 * Function to get Campaign related Activities
	 * @param  integer   $id      - campaignid
	 * returns related activities record in array format
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
		$query = "SELECT mycrm_contactdetails.lastname,
			mycrm_contactdetails.firstname,
			mycrm_contactdetails.contactid,
			mycrm_activity.*,
			mycrm_seactivityrel.crmid as parent_id,
			mycrm_crmentity.crmid, mycrm_crmentity.smownerid,
			mycrm_crmentity.modifiedtime,
			CASE when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,
			mycrm_recurringevents.recurringtype,
			CASE WHEN (mycrm_activity.activitytype = 'Task') THEN mycrm_activity.status ELSE mycrm_activity.eventstatus END AS status
			FROM mycrm_activity
			INNER JOIN mycrm_seactivityrel
				ON mycrm_seactivityrel.activityid = mycrm_activity.activityid
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid=mycrm_activity.activityid
			LEFT JOIN mycrm_cntactivityrel
				ON mycrm_cntactivityrel.activityid = mycrm_activity.activityid
			LEFT JOIN mycrm_contactdetails
				ON mycrm_contactdetails.contactid = mycrm_cntactivityrel.contactid
			LEFT JOIN mycrm_users
				ON mycrm_users.id = mycrm_crmentity.smownerid
			LEFT OUTER JOIN mycrm_recurringevents
				ON mycrm_recurringevents.activityid = mycrm_activity.activityid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			WHERE mycrm_seactivityrel.crmid=".$id."
			AND mycrm_crmentity.deleted = 0
			AND (activitytype = 'Task'
				OR activitytype !='Emails')";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_activities method ...");
		return $return_value;

	}
	/*
	 * Function populate the status columns' HTML
	 * @param - $related_list return value from GetRelatedList
	 * @param - $status_column index of the status column in the list.
	 * returns true on success
	 */
	function add_status_popup($related_list, $status_column = 7, $related_module)
	{
		global $adb;

		if(!$this->campaignrelstatus)
		{
			$result = $adb->query('SELECT * FROM mycrm_campaignrelstatus;');
			while($row = $adb->fetchByAssoc($result))
			{
				$this->campaignrelstatus[$row['campaignrelstatus']] = $row;
			}
		}
		foreach($related_list['entries'] as $key => &$entry)
		{
			$popupitemshtml = '';
			foreach($this->campaignrelstatus as $campaingrelstatus)
			{
				$camprelstatus = getTranslatedString($campaingrelstatus[campaignrelstatus],'Campaigns');
				$popupitemshtml .= "<a onmouseover=\"javascript: showBlock('campaignstatus_popup_$key')\" href=\"javascript:updateCampaignRelationStatus('$related_module', '".$this->id."', '$key', '$campaingrelstatus[campaignrelstatusid]', '".addslashes($camprelstatus)."');\">$camprelstatus</a><br />";
			}
			$popuphtml = '<div onmouseover="javascript:clearTimeout(statusPopupTimer);" onmouseout="javascript:closeStatusPopup(\'campaignstatus_popup_'.$key.'\');" style="margin-top: -14px; width: 200px;" id="campaignstatus_popup_'.$key.'" class="calAction"><div style="background-color: #FFFFFF; padding: 8px;">'.$popupitemshtml.'</div></div>';

			$entry[$status_column] = "<a href=\"javascript: showBlock('campaignstatus_popup_$key');\">[+]</a> <span id='campaignstatus_$key'>".$entry[$status_column]."</span>".$popuphtml;
		}

		return $related_list;
	}

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule,$queryplanner){
		$matrix = $queryplanner->newDependencyMatrix();
        $matrix->setDependency('mycrm_crmentityCampaigns',array('mycrm_groupsCampaigns','mycrm_usersCampaignss','mycrm_lastModifiedByCampaigns','mycrm_campaignscf'));
        $matrix->setDependency('mycrm_campaign', array('mycrm_crmentityCampaigns','mycrm_productsCampaigns'));

		if (!$queryplanner->requireTable("mycrm_campaign",$matrix)){
			return '';
		}

		$query = $this->getRelationQuery($module,$secmodule,"mycrm_campaign","campaignid", $queryplanner);

		if ($queryplanner->requireTable("mycrm_crmentityCampaigns",$matrix)){
			$query .=" left join mycrm_crmentity as mycrm_crmentityCampaigns on mycrm_crmentityCampaigns.crmid=mycrm_campaign.campaignid and mycrm_crmentityCampaigns.deleted=0";
		}
		if ($queryplanner->requireTable("mycrm_productsCampaigns")){
			$query .=" 	left join mycrm_products as mycrm_productsCampaigns on mycrm_campaign.product_id = mycrm_productsCampaigns.productid";
		}
		if ($queryplanner->requireTable("mycrm_campaignscf")){
			$query .=" 	left join mycrm_campaignscf on mycrm_campaignscf.campaignid = mycrm_crmentityCampaigns.crmid";
		}
		if ($queryplanner->requireTable("mycrm_groupsCampaigns")){
			$query .=" left join mycrm_groups as mycrm_groupsCampaigns on mycrm_groupsCampaigns.groupid = mycrm_crmentityCampaigns.smownerid";
		}
		if ($queryplanner->requireTable("mycrm_usersCampaigns")){
			$query .=" left join mycrm_users as mycrm_usersCampaigns on mycrm_usersCampaigns.id = mycrm_crmentityCampaigns.smownerid";
		}
		if ($queryplanner->requireTable("mycrm_lastModifiedByCampaigns")){
			$query .=" left join mycrm_users as mycrm_lastModifiedByCampaigns on mycrm_lastModifiedByCampaigns.id = mycrm_crmentityCampaigns.modifiedby ";
		}
        if ($queryplanner->requireTable("mycrm_createdbyCampaigns")){
			$query .= " left join mycrm_users as mycrm_createdbyCampaigns on mycrm_createdbyCampaigns.id = mycrm_crmentityCampaigns.smcreatorid ";
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
			"Contacts" => array("mycrm_campaigncontrel"=>array("campaignid","contactid"),"mycrm_campaign"=>"campaignid"),
			"Leads" => array("mycrm_campaignleadrel"=>array("campaignid","leadid"),"mycrm_campaign"=>"campaignid"),
			"Accounts" => array("mycrm_campaignaccountrel"=>array("campaignid","accountid"),"mycrm_campaign"=>"campaignid"),
			"Potentials" => array("mycrm_potential"=>array("campaignid","potentialid"),"mycrm_campaign"=>"campaignid"),
			"Calendar" => array("mycrm_seactivityrel"=>array("crmid","activityid"),"mycrm_campaign"=>"campaignid"),
			"Products" => array("mycrm_campaign"=>array("campaignid","product_id")),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Leads') {
			$sql = 'DELETE FROM mycrm_campaignleadrel WHERE campaignid=? AND leadid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif($return_module == 'Contacts') {
			$sql = 'DELETE FROM mycrm_campaigncontrel WHERE campaignid=? AND contactid=?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif($return_module == 'Accounts') {
			$sql = 'DELETE FROM mycrm_campaignaccountrel WHERE campaignid=? AND accountid=?';
			$this->db->pquery($sql, array($id, $return_id));
			$sql = 'DELETE FROM mycrm_campaigncontrel WHERE campaignid=? AND contactid IN (SELECT contactid FROM mycrm_contactdetails WHERE accountid=?)';
			$this->db->pquery($sql, array($id, $return_id));
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
			if ($with_module == 'Leads') {
				$checkResult = $adb->pquery('SELECT 1 FROM mycrm_campaignleadrel WHERE campaignid = ? AND leadid = ?',
												array($crmid, $with_crmid));
				if($checkResult && $adb->num_rows($checkResult) > 0) {
					continue;
				}
				$sql = 'INSERT INTO mycrm_campaignleadrel VALUES(?,?,1)';
				$adb->pquery($sql, array($crmid, $with_crmid));

			} elseif($with_module == 'Contacts') {
				$checkResult = $adb->pquery('SELECT 1 FROM mycrm_campaigncontrel WHERE campaignid = ? AND contactid = ?',
												array($crmid, $with_crmid));
				if($checkResult && $adb->num_rows($checkResult) > 0) {
					continue;
				}
				$sql = 'INSERT INTO mycrm_campaigncontrel VALUES(?,?,1)';
				$adb->pquery($sql, array($crmid, $with_crmid));

			} elseif($with_module == 'Accounts') {
				$checkResult = $adb->pquery('SELECT 1 FROM mycrm_campaignaccountrel WHERE campaignid = ? AND accountid = ?',
												array($crmid, $with_crmid));
				if($checkResult && $adb->num_rows($checkResult) > 0) {
					continue;
				}
				$sql = 'INSERT INTO mycrm_campaignaccountrel VALUES(?,?,1)';
				$adb->pquery($sql, array($crmid, $with_crmid));

			} else {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid);
			}
		}
	}

}
?>
