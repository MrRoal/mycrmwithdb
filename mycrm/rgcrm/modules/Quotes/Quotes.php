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
 * $Header$
 * Description:  Defines the Account SugarBean Account entity with the necessary
 * methods and variables.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
class Quotes extends CRMEntity {
	var $log;
	var $db;

	var $table_name = "mycrm_quotes";
	var $table_index= 'quoteid';
	var $tab_name = Array('mycrm_crmentity','mycrm_quotes','mycrm_quotesbillads','mycrm_quotesshipads','mycrm_quotescf','mycrm_inventoryproductrel');
	var $tab_name_index = Array('mycrm_crmentity'=>'crmid','mycrm_quotes'=>'quoteid','mycrm_quotesbillads'=>'quotebilladdressid','mycrm_quotesshipads'=>'quoteshipaddressid','mycrm_quotescf'=>'quoteid','mycrm_inventoryproductrel'=>'id');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('mycrm_quotescf', 'quoteid');
	var $entity_table = "mycrm_crmentity";

	var $billadr_table = "mycrm_quotesbillads";

	var $object_name = "Quote";

	var $new_schema = true;

	var $column_fields = Array();

	var $sortby_fields = Array('subject','crmid','smownerid','accountname','lastname');

	// This is used to retrieve related mycrm_fields from form posts.
	var $additional_column_fields = Array('assigned_user_name', 'smownerid', 'opportunity_id', 'case_id', 'contact_id', 'task_id', 'note_id', 'meeting_id', 'call_id', 'email_id', 'parent_name', 'member_id' );

	// This is the list of mycrm_fields that are in the lists.
	var $list_fields = Array(
				//'Quote No'=>Array('crmentity'=>'crmid'),
				// Module Sequence Numbering
				'Quote No'=>Array('quotes'=>'quote_no'),
				// END
				'Subject'=>Array('quotes'=>'subject'),
				'Quote Stage'=>Array('quotes'=>'quotestage'),
				'Potential Name'=>Array('quotes'=>'potentialid'),
				'Account Name'=>Array('account'=> 'accountid'),
				'Total'=>Array('quotes'=> 'total'),
				'Assigned To'=>Array('crmentity'=>'smownerid')
				);

	var $list_fields_name = Array(
				        'Quote No'=>'quote_no',
				        'Subject'=>'subject',
				        'Quote Stage'=>'quotestage',
				        'Potential Name'=>'potential_id',
					'Account Name'=>'account_id',
					'Total'=>'hdnGrandTotal',
				        'Assigned To'=>'assigned_user_id'
				      );
	var $list_link_field= 'subject';

	var $search_fields = Array(
				'Quote No'=>Array('quotes'=>'quote_no'),
				'Subject'=>Array('quotes'=>'subject'),
				'Account Name'=>Array('quotes'=>'accountid'),
				'Quote Stage'=>Array('quotes'=>'quotestage'),
				);

	var $search_fields_name = Array(
					'Quote No'=>'quote_no',
				        'Subject'=>'subject',
				        'Account Name'=>'account_id',
				        'Quote Stage'=>'quotestage',
				      );

	// This is the list of mycrm_fields that are required.
	var $required_fields =  array("accountname"=>1);

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'crmid';
	var $default_sort_order = 'ASC';
	//var $groupTable = Array('mycrm_quotegrouprelation','quoteid');

	var $mandatory_fields = Array('subject','createdtime' ,'modifiedtime', 'assigned_user_id');

	// For Alphabetical search
	var $def_basicsearch_col = 'subject';

	// For workflows update field tasks is deleted all the lineitems.
	var $isLineItemUpdate = true;

	/**	Constructor which will set the column_fields in this object
	 */
	function Quotes() {
		$this->log =LoggerManager::getLogger('quote');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Quotes');
	}

	function save_module()
	{
		global $adb;
		//in ajax save we should not call this function, because this will delete all the existing product values
		if($_REQUEST['action'] != 'QuotesAjax' && $_REQUEST['ajxaction'] != 'DETAILVIEW'
				&& $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates'
				&& $_REQUEST['action'] != 'SaveAjax' && $this->isLineItemUpdate != false) {
			//Based on the total Number of rows we will save the product relationship with this entity
			saveInventoryProductDetails($this, 'Quotes');
		}

		// Update the currency id and the conversion rate for the quotes
		$update_query = "update mycrm_quotes set currency_id=?, conversion_rate=? where quoteid=?";
		$update_params = array($this->column_fields['currency_id'], $this->column_fields['conversion_rate'], $this->id);
		$adb->pquery($update_query, $update_params);
	}

	/**	function used to get the list of sales orders which are related to the Quotes
	 *	@param int $id - quote id
	 *	@return array - return an array which will be returned from the function GetRelatedList
	 */
	function get_salesorder($id)
	{
		global $log,$singlepane_view;
		$log->debug("Entering get_salesorder(".$id.") method ...");
		require_once('modules/SalesOrder/SalesOrder.php');
	        $focus = new SalesOrder();

		$button = '';

		if($singlepane_view == 'true')
			$returnset = '&return_module=Quotes&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module=Quotes&return_action=CallRelatedList&return_id='.$id;

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "select mycrm_crmentity.*, mycrm_salesorder.*, mycrm_quotes.subject as quotename
			, mycrm_account.accountname,case when (mycrm_users.user_name not like '') then
			$userNameSql else mycrm_groups.groupname end as user_name
		from mycrm_salesorder
		inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_salesorder.salesorderid
		left outer join mycrm_quotes on mycrm_quotes.quoteid=mycrm_salesorder.quoteid
		left outer join mycrm_account on mycrm_account.accountid=mycrm_salesorder.accountid
		left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid
        LEFT JOIN mycrm_salesordercf ON mycrm_salesordercf.salesorderid = mycrm_salesorder.salesorderid
        LEFT JOIN mycrm_invoice_recurring_info ON mycrm_invoice_recurring_info.start_period = mycrm_salesorder.salesorderid
		LEFT JOIN mycrm_sobillads ON mycrm_sobillads.sobilladdressid = mycrm_salesorder.salesorderid
		LEFT JOIN mycrm_soshipads ON mycrm_soshipads.soshipaddressid = mycrm_salesorder.salesorderid
		left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid
		where mycrm_crmentity.deleted=0 and mycrm_salesorder.quoteid = ".$id;
		$log->debug("Exiting get_salesorder method ...");
		return GetRelatedList('Quotes','SalesOrder',$focus,$query,$button,$returnset);
	}

	/**	function used to get the list of activities which are related to the Quotes
	 *	@param int $id - quote id
	 *	@return array - return an array which will be returned from the function GetRelatedList
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
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "SELECT case when (mycrm_users.user_name not like '') then $userNameSql else
		mycrm_groups.groupname end as user_name, mycrm_contactdetails.contactid,
		mycrm_contactdetails.lastname, mycrm_contactdetails.firstname, mycrm_activity.*,
		mycrm_seactivityrel.crmid as parent_id,mycrm_crmentity.crmid, mycrm_crmentity.smownerid,
		mycrm_crmentity.modifiedtime,mycrm_recurringevents.recurringtype
		from mycrm_activity
		inner join mycrm_seactivityrel on mycrm_seactivityrel.activityid=
		mycrm_activity.activityid
		inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_activity.activityid
		left join mycrm_cntactivityrel on mycrm_cntactivityrel.activityid=
		mycrm_activity.activityid
		left join mycrm_contactdetails on mycrm_contactdetails.contactid =
		mycrm_cntactivityrel.contactid
		left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid
		left outer join mycrm_recurringevents on mycrm_recurringevents.activityid=
		mycrm_activity.activityid
		left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid
		where mycrm_seactivityrel.crmid=".$id." and mycrm_crmentity.deleted=0 and
			activitytype='Task' and (mycrm_activity.status is not NULL and
			mycrm_activity.status != 'Completed') and (mycrm_activity.status is not NULL and
			mycrm_activity.status != 'Deferred')";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_activities method ...");
		return $return_value;
	}

	/**	function used to get the the activity history related to the quote
	 *	@param int $id - quote id
	 *	@return array - return an array which will be returned from the function GetHistory
	 */
	function get_history($id)
	{
		global $log;
		$log->debug("Entering get_history(".$id.") method ...");
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "SELECT mycrm_activity.activityid, mycrm_activity.subject, mycrm_activity.status,
			mycrm_activity.eventstatus, mycrm_activity.activitytype,mycrm_activity.date_start,
			mycrm_activity.due_date,mycrm_activity.time_start, mycrm_activity.time_end,
			mycrm_contactdetails.contactid,
			mycrm_contactdetails.firstname,mycrm_contactdetails.lastname, mycrm_crmentity.modifiedtime,
			mycrm_crmentity.createdtime, mycrm_crmentity.description, case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name
			from mycrm_activity
				inner join mycrm_seactivityrel on mycrm_seactivityrel.activityid=mycrm_activity.activityid
				inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_activity.activityid
				left join mycrm_cntactivityrel on mycrm_cntactivityrel.activityid= mycrm_activity.activityid
				left join mycrm_contactdetails on mycrm_contactdetails.contactid= mycrm_cntactivityrel.contactid
                                left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid
				left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid
				where mycrm_activity.activitytype='Task'
  				and (mycrm_activity.status = 'Completed' or mycrm_activity.status = 'Deferred')
	 	        	and mycrm_seactivityrel.crmid=".$id."
                                and mycrm_crmentity.deleted = 0";
		//Don't add order by, because, for security, one more condition will be added with this query in include/RelatedListView.php

		$log->debug("Exiting get_history method ...");
		return getHistory('Quotes',$query,$id);
	}





	/**	Function used to get the Quote Stage history of the Quotes
	 *	@param $id - quote id
	 *	@return $return_data - array with header and the entries in format Array('header'=>$header,'entries'=>$entries_list) where as $header and $entries_list are arrays which contains header values and all column values of all entries
	 */
	function get_quotestagehistory($id)
	{
		global $log;
		$log->debug("Entering get_quotestagehistory(".$id.") method ...");

		global $adb;
		global $mod_strings;
		global $app_strings;

		$query = 'select mycrm_quotestagehistory.*, mycrm_quotes.quote_no from mycrm_quotestagehistory inner join mycrm_quotes on mycrm_quotes.quoteid = mycrm_quotestagehistory.quoteid inner join mycrm_crmentity on mycrm_crmentity.crmid = mycrm_quotes.quoteid where mycrm_crmentity.deleted = 0 and mycrm_quotes.quoteid = ?';
		$result=$adb->pquery($query, array($id));
		$noofrows = $adb->num_rows($result);

		$header[] = $app_strings['Quote No'];
		$header[] = $app_strings['LBL_ACCOUNT_NAME'];
		$header[] = $app_strings['LBL_AMOUNT'];
		$header[] = $app_strings['Quote Stage'];
		$header[] = $app_strings['LBL_LAST_MODIFIED'];

		//Getting the field permission for the current user. 1 - Not Accessible, 0 - Accessible
		//Account Name , Total are mandatory fields. So no need to do security check to these fields.
		global $current_user;

		//If field is accessible then getFieldVisibilityPermission function will return 0 else return 1
		$quotestage_access = (getFieldVisibilityPermission('Quotes', $current_user->id, 'quotestage') != '0')? 1 : 0;
		$picklistarray = getAccessPickListValues('Quotes');

		$quotestage_array = ($quotestage_access != 1)? $picklistarray['quotestage']: array();
		//- ==> picklist field is not permitted in profile
		//Not Accessible - picklist is permitted in profile but picklist value is not permitted
		$error_msg = ($quotestage_access != 1)? 'Not Accessible': '-';

		while($row = $adb->fetch_array($result))
		{
			$entries = Array();

			// Module Sequence Numbering
			//$entries[] = $row['quoteid'];
			$entries[] = $row['quote_no'];
			// END
			$entries[] = $row['accountname'];
			$entries[] = $row['total'];
			$entries[] = (in_array($row['quotestage'], $quotestage_array))? $row['quotestage']: $error_msg;
			$date = new DateTimeField($row['lastmodified']);
			$entries[] = $date->getDisplayDateTimeValue();

			$entries_list[] = $entries;
		}

		$return_data = Array('header'=>$header,'entries'=>$entries_list);

	 	$log->debug("Exiting get_quotestagehistory method ...");

		return $return_data;
	}

	// Function to get column name - Overriding function of base class
	function get_column_value($columname, $fldvalue, $fieldname, $uitype, $datatype='') {
		if ($columname == 'potentialid' || $columname == 'contactid') {
			if ($fldvalue == '') return null;
		}
		return parent::get_column_value($columname, $fldvalue, $fieldname, $uitype, $datatype);
	}

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule,$queryPlanner){
		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('mycrm_crmentityQuotes', array('mycrm_usersQuotes', 'mycrm_groupsQuotes', 'mycrm_lastModifiedByQuotes'));
		$matrix->setDependency('mycrm_inventoryproductrelQuotes', array('mycrm_productsQuotes', 'mycrm_serviceQuotes'));
		$matrix->setDependency('mycrm_quotes',array('mycrm_crmentityQuotes', "mycrm_currency_info$secmodule",
				'mycrm_quotescf', 'mycrm_potentialRelQuotes', 'mycrm_quotesbillads','mycrm_quotesshipads',
				'mycrm_inventoryproductrelQuotes', 'mycrm_contactdetailsQuotes', 'mycrm_accountQuotes',
				'mycrm_invoice_recurring_info','mycrm_quotesQuotes','mycrm_usersRel1'));

		if (!$queryPlanner->requireTable('mycrm_quotes', $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module,$secmodule,"mycrm_quotes","quoteid", $queryPlanner);
		if ($queryPlanner->requireTable("mycrm_crmentityQuotes", $matrix)){
			$query .= " left join mycrm_crmentity as mycrm_crmentityQuotes on mycrm_crmentityQuotes.crmid=mycrm_quotes.quoteid and mycrm_crmentityQuotes.deleted=0";
		}
		if ($queryPlanner->requireTable("mycrm_quotescf")){
			$query .= " left join mycrm_quotescf on mycrm_quotes.quoteid = mycrm_quotescf.quoteid";
		}
		if ($queryPlanner->requireTable("mycrm_quotesbillads")){
			$query .= " left join mycrm_quotesbillads on mycrm_quotes.quoteid=mycrm_quotesbillads.quotebilladdressid";
		}
		if ($queryPlanner->requireTable("mycrm_quotesshipads")){
			$query .= " left join mycrm_quotesshipads on mycrm_quotes.quoteid=mycrm_quotesshipads.quoteshipaddressid";
		}
		if ($queryPlanner->requireTable("mycrm_currency_info$secmodule")){
			$query .= " left join mycrm_currency_info as mycrm_currency_info$secmodule on mycrm_currency_info$secmodule.id = mycrm_quotes.currency_id";
		}
		if ($queryPlanner->requireTable("mycrm_inventoryproductrelQuotes",$matrix)){
			$query .= " left join mycrm_inventoryproductrel as mycrm_inventoryproductrelQuotes on mycrm_quotes.quoteid = mycrm_inventoryproductrelQuotes.id";
            // To Eliminate duplicates in reports
            if(($module == 'Products' || $module == 'Services') && $secmodule == "Quotes"){
                if($module == 'Products'){
                    $query .= " and mycrm_inventoryproductrelQuotes.productid = mycrm_products.productid ";    
                }else if($module== 'Services'){
                    $query .= " and mycrm_inventoryproductrelQuotes.productid = mycrm_service.serviceid ";
                }
            }
		}
		if ($queryPlanner->requireTable("mycrm_productsQuotes")){
			$query .= " left join mycrm_products as mycrm_productsQuotes on mycrm_productsQuotes.productid = mycrm_inventoryproductrelQuotes.productid";
		}
		if ($queryPlanner->requireTable("mycrm_serviceQuotes")){
			$query .= " left join mycrm_service as mycrm_serviceQuotes on mycrm_serviceQuotes.serviceid = mycrm_inventoryproductrelQuotes.productid";
		}
		if ($queryPlanner->requireTable("mycrm_groupsQuotes")){
			$query .= " left join mycrm_groups as mycrm_groupsQuotes on mycrm_groupsQuotes.groupid = mycrm_crmentityQuotes.smownerid";
		}
		if ($queryPlanner->requireTable("mycrm_usersQuotes")){
			$query .= " left join mycrm_users as mycrm_usersQuotes on mycrm_usersQuotes.id = mycrm_crmentityQuotes.smownerid";
		}
		if ($queryPlanner->requireTable("mycrm_usersRel1")){
			$query .= " left join mycrm_users as mycrm_usersRel1 on mycrm_usersRel1.id = mycrm_quotes.inventorymanager";
		}
		if ($queryPlanner->requireTable("mycrm_potentialRelQuotes")){
			$query .= " left join mycrm_potential as mycrm_potentialRelQuotes on mycrm_potentialRelQuotes.potentialid = mycrm_quotes.potentialid";
		}
		if ($queryPlanner->requireTable("mycrm_contactdetailsQuotes")){
			$query .= " left join mycrm_contactdetails as mycrm_contactdetailsQuotes on mycrm_contactdetailsQuotes.contactid = mycrm_quotes.contactid";
		}
		if ($queryPlanner->requireTable("mycrm_accountQuotes")){
			$query .= " left join mycrm_account as mycrm_accountQuotes on mycrm_accountQuotes.accountid = mycrm_quotes.accountid";
		}
		if ($queryPlanner->requireTable("mycrm_lastModifiedByQuotes")){
			$query .= " left join mycrm_users as mycrm_lastModifiedByQuotes on mycrm_lastModifiedByQuotes.id = mycrm_crmentityQuotes.modifiedby ";
		}
        if ($queryPlanner->requireTable("mycrm_createdbyQuotes")){
			$query .= " left join mycrm_users as mycrm_createdbyQuotes on mycrm_createdbyQuotes.id = mycrm_crmentityQuotes.smcreatorid ";
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
			"SalesOrder" =>array("mycrm_salesorder"=>array("quoteid","salesorderid"),"mycrm_quotes"=>"quoteid"),
			"Calendar" =>array("mycrm_seactivityrel"=>array("crmid","activityid"),"mycrm_quotes"=>"quoteid"),
			"Documents" => array("mycrm_senotesrel"=>array("crmid","notesid"),"mycrm_quotes"=>"quoteid"),
			"Accounts" => array("mycrm_quotes"=>array("quoteid","accountid")),
			"Contacts" => array("mycrm_quotes"=>array("quoteid","contactid")),
			"Potentials" => array("mycrm_quotes"=>array("quoteid","potentialid")),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Accounts' ) {
			$this->trash('Quotes',$id);
		} elseif($return_module == 'Potentials') {
			$relation_query = 'UPDATE mycrm_quotes SET potentialid=? WHERE quoteid=?';
			$this->db->pquery($relation_query, array(null, $id));
		} elseif($return_module == 'Contacts') {
			$relation_query = 'UPDATE mycrm_quotes SET contactid=? WHERE quoteid=?';
			$this->db->pquery($relation_query, array(null, $id));
		} else {
			$sql = 'DELETE FROM mycrm_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}

	function insertIntoEntityTable($table_name, $module, $fileid = '')  {
		//Ignore relation table insertions while saving of the record
		if($table_name == 'mycrm_inventoryproductrel') {
			return;
		}
		parent::insertIntoEntityTable($table_name, $module, $fileid);
	}

	/*Function to create records in current module.
	**This function called while importing records to this module*/
	function createRecords($obj) {
		$createRecords = createRecords($obj);
		return $createRecords;
	}

	/*Function returns the record information which means whether the record is imported or not
	**This function called while importing records to this module*/
	function importRecord($obj, $inventoryFieldData, $lineItemDetails) {
		$entityInfo = importRecord($obj, $inventoryFieldData, $lineItemDetails);
		return $entityInfo;
	}

	/*Function to return the status count of imported records in current module.
	**This function called while importing records to this module*/
	function getImportStatusCount($obj) {
		$statusCount = getImportStatusCount($obj);
		return $statusCount;
	}

	function undoLastImport($obj, $user) {
		$undoLastImport = undoLastImport($obj, $user);
	}

	/** Function to export the lead records in CSV Format
	* @param reference variable - where condition is passed when the query is executed
	* Returns Export Quotes Query.
	*/
	function create_export_query($where)
	{
		global $log;
		global $current_user;
		$log->debug("Entering create_export_query(".$where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Quotes", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);
		$fields_list .= getInventoryFieldsForExport($this->table_name);
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');

		$query = "SELECT $fields_list FROM ".$this->entity_table."
				INNER JOIN mycrm_quotes ON mycrm_quotes.quoteid = mycrm_crmentity.crmid
				LEFT JOIN mycrm_quotescf ON mycrm_quotescf.quoteid = mycrm_quotes.quoteid
				LEFT JOIN mycrm_quotesbillads ON mycrm_quotesbillads.quotebilladdressid = mycrm_quotes.quoteid
				LEFT JOIN mycrm_quotesshipads ON mycrm_quotesshipads.quoteshipaddressid = mycrm_quotes.quoteid
				LEFT JOIN mycrm_inventoryproductrel ON mycrm_inventoryproductrel.id = mycrm_quotes.quoteid
				LEFT JOIN mycrm_products ON mycrm_products.productid = mycrm_inventoryproductrel.productid
				LEFT JOIN mycrm_service ON mycrm_service.serviceid = mycrm_inventoryproductrel.productid
				LEFT JOIN mycrm_contactdetails ON mycrm_contactdetails.contactid = mycrm_quotes.contactid
				LEFT JOIN mycrm_potential ON mycrm_potential.potentialid = mycrm_quotes.potentialid
				LEFT JOIN mycrm_account ON mycrm_account.accountid = mycrm_quotes.accountid
				LEFT JOIN mycrm_currency_info ON mycrm_currency_info.id = mycrm_quotes.currency_id
				LEFT JOIN mycrm_users AS mycrm_inventoryManager ON mycrm_inventoryManager.id = mycrm_quotes.inventorymanager
				LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
				LEFT JOIN mycrm_users ON mycrm_users.id = mycrm_crmentity.smownerid";

		$query .= $this->getNonAdminAccessControlQuery('Quotes',$current_user);
		$where_auto = " mycrm_crmentity.deleted=0";

		if($where != "") {
			$query .= " where ($where) AND ".$where_auto;
		} else {
			$query .= " where ".$where_auto;
		}

		$log->debug("Exiting create_export_query method ...");
		return $query;
	}

}

?>
