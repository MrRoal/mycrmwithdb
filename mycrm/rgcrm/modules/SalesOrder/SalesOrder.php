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
class SalesOrder extends CRMEntity {
	var $log;
	var $db;

	var $table_name = "mycrm_salesorder";
	var $table_index= 'salesorderid';
	var $tab_name = Array('mycrm_crmentity','mycrm_salesorder','mycrm_sobillads','mycrm_soshipads','mycrm_salesordercf','mycrm_invoice_recurring_info','mycrm_inventoryproductrel');
	var $tab_name_index = Array('mycrm_crmentity'=>'crmid','mycrm_salesorder'=>'salesorderid','mycrm_sobillads'=>'sobilladdressid','mycrm_soshipads'=>'soshipaddressid','mycrm_salesordercf'=>'salesorderid','mycrm_invoice_recurring_info'=>'salesorderid','mycrm_inventoryproductrel'=>'id');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('mycrm_salesordercf', 'salesorderid');
	var $entity_table = "mycrm_crmentity";

	var $billadr_table = "mycrm_sobillads";

	var $object_name = "SalesOrder";

	var $new_schema = true;

	var $update_product_array = Array();

	var $column_fields = Array();

	var $sortby_fields = Array('subject','smownerid','accountname','lastname');

	// This is used to retrieve related mycrm_fields from form posts.
	var $additional_column_fields = Array('assigned_user_name', 'smownerid', 'opportunity_id', 'case_id', 'contact_id', 'task_id', 'note_id', 'meeting_id', 'call_id', 'email_id', 'parent_name', 'member_id' );

	// This is the list of mycrm_fields that are in the lists.
	var $list_fields = Array(
				// Module Sequence Numbering
				//'Order No'=>Array('crmentity'=>'crmid'),
				'Order No'=>Array('salesorder','salesorder_no'),
				// END
				'Subject'=>Array('salesorder'=>'subject'),
				'Account Name'=>Array('account'=>'accountid'),
				'Quote Name'=>Array('quotes'=>'quoteid'),
				'Total'=>Array('salesorder'=>'total'),
				'Assigned To'=>Array('crmentity'=>'smownerid')
				);

	var $list_fields_name = Array(
				        'Order No'=>'salesorder_no',
				        'Subject'=>'subject',
				        'Account Name'=>'account_id',
				        'Quote Name'=>'quote_id',
					'Total'=>'hdnGrandTotal',
				        'Assigned To'=>'assigned_user_id'
				      );
	var $list_link_field= 'subject';

	var $search_fields = Array(
				'Order No'=>Array('salesorder'=>'salesorder_no'),
				'Subject'=>Array('salesorder'=>'subject'),
				'Account Name'=>Array('account'=>'accountid'),
				'Quote Name'=>Array('salesorder'=>'quoteid')
				);

	var $search_fields_name = Array(
					'Order No'=>'salesorder_no',
				        'Subject'=>'subject',
				        'Account Name'=>'account_id',
				        'Quote Name'=>'quote_id'
				      );

	// This is the list of mycrm_fields that are required.
	var $required_fields =  array("accountname"=>1);

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'subject';
	var $default_sort_order = 'ASC';
	//var $groupTable = Array('mycrm_sogrouprelation','salesorderid');

	var $mandatory_fields = Array('subject','createdtime' ,'modifiedtime', 'assigned_user_id');

	// For Alphabetical search
	var $def_basicsearch_col = 'subject';

	// For workflows update field tasks is deleted all the lineitems.
	var $isLineItemUpdate = true;

	/** Constructor Function for SalesOrder class
	 *  This function creates an instance of LoggerManager class using getLogger method
	 *  creates an instance for PearDatabase class and get values for column_fields array of SalesOrder class.
	 */
	function SalesOrder() {
		$this->log =LoggerManager::getLogger('SalesOrder');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('SalesOrder');
	}

	function save_module($module)
	{

		//Checking if quote_id is present and updating the quote status
		if($this->column_fields["quote_id"] != '')
		{
        		$qt_id = $this->column_fields["quote_id"];
        		$query1 = "update mycrm_quotes set quotestage='Accepted' where quoteid=?";
        		$this->db->pquery($query1, array($qt_id));
		}

		//in ajax save we should not call this function, because this will delete all the existing product values
		if($_REQUEST['action'] != 'SalesOrderAjax' && $_REQUEST['ajxaction'] != 'DETAILVIEW'
				&& $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates'
				&& $_REQUEST['action'] != 'SaveAjax' && $this->isLineItemUpdate != false) {
			//Based on the total Number of rows we will save the product relationship with this entity
			saveInventoryProductDetails($this, 'SalesOrder');
		}

		// Update the currency id and the conversion rate for the sales order
		$update_query = "update mycrm_salesorder set currency_id=?, conversion_rate=? where salesorderid=?";
		$update_params = array($this->column_fields['currency_id'], $this->column_fields['conversion_rate'], $this->id);
		$this->db->pquery($update_query, $update_params);
	}

	/** Function to get activities associated with the Sales Order
	 *  This function accepts the id as arguments and execute the MySQL query using the id
	 *  and sends the query and the id as arguments to renderRelatedActivities() method
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
		$query = "SELECT case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,mycrm_contactdetails.lastname, mycrm_contactdetails.firstname, mycrm_contactdetails.contactid, mycrm_activity.*,mycrm_seactivityrel.crmid as parent_id,mycrm_crmentity.crmid, mycrm_crmentity.smownerid, mycrm_crmentity.modifiedtime from mycrm_activity inner join mycrm_seactivityrel on mycrm_seactivityrel.activityid=mycrm_activity.activityid inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_activity.activityid left join mycrm_cntactivityrel on mycrm_cntactivityrel.activityid= mycrm_activity.activityid left join mycrm_contactdetails on mycrm_contactdetails.contactid = mycrm_cntactivityrel.contactid left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid where mycrm_seactivityrel.crmid=".$id." and activitytype='Task' and mycrm_crmentity.deleted=0 and (mycrm_activity.status is not NULL and mycrm_activity.status != 'Completed') and (mycrm_activity.status is not NULL and mycrm_activity.status !='Deferred')";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_activities method ...");
		return $return_value;
	}

	/** Function to get the activities history associated with the Sales Order
	 *  This function accepts the id as arguments and execute the MySQL query using the id
	 *  and sends the query and the id as arguments to renderRelatedHistory() method
	 */
	function get_history($id)
	{
		global $log;
		$log->debug("Entering get_history(".$id.") method ...");
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "SELECT mycrm_contactdetails.lastname, mycrm_contactdetails.firstname,
			mycrm_contactdetails.contactid,mycrm_activity.*, mycrm_seactivityrel.*,
			mycrm_crmentity.crmid, mycrm_crmentity.smownerid, mycrm_crmentity.modifiedtime,
			mycrm_crmentity.createdtime, mycrm_crmentity.description, case when
			(mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname
			end as user_name from mycrm_activity
				inner join mycrm_seactivityrel on mycrm_seactivityrel.activityid=mycrm_activity.activityid
				inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_activity.activityid
				left join mycrm_cntactivityrel on mycrm_cntactivityrel.activityid= mycrm_activity.activityid
				left join mycrm_contactdetails on mycrm_contactdetails.contactid = mycrm_cntactivityrel.contactid
                                left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid
				left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid
			where activitytype='Task'
				and (mycrm_activity.status = 'Completed' or mycrm_activity.status = 'Deferred')
				and mycrm_seactivityrel.crmid=".$id."
                                and mycrm_crmentity.deleted = 0";
		//Don't add order by, because, for security, one more condition will be added with this query in include/RelatedListView.php

		$log->debug("Exiting get_history method ...");
		return getHistory('SalesOrder',$query,$id);
	}



	/** Function to get the invoices associated with the Sales Order
	 *  This function accepts the id as arguments and execute the MySQL query using the id
	 *  and sends the query and the id as arguments to renderRelatedInvoices() method.
	 */
	function get_invoices($id)
	{
		global $log,$singlepane_view;
		$log->debug("Entering get_invoices(".$id.") method ...");
		require_once('modules/Invoice/Invoice.php');

		$focus = new Invoice();

		$button = '';
		if($singlepane_view == 'true')
			$returnset = '&return_module=SalesOrder&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module=SalesOrder&return_action=CallRelatedList&return_id='.$id;

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "select mycrm_crmentity.*, mycrm_invoice.*, mycrm_account.accountname,
			mycrm_salesorder.subject as salessubject, case when
			(mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname
			end as user_name from mycrm_invoice
			inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_invoice.invoiceid
			left outer join mycrm_account on mycrm_account.accountid=mycrm_invoice.accountid
			inner join mycrm_salesorder on mycrm_salesorder.salesorderid=mycrm_invoice.salesorderid
            LEFT JOIN mycrm_invoicecf ON mycrm_invoicecf.invoiceid = mycrm_invoice.invoiceid
			LEFT JOIN mycrm_invoicebillads ON mycrm_invoicebillads.invoicebilladdressid = mycrm_invoice.invoiceid
			LEFT JOIN mycrm_invoiceshipads ON mycrm_invoiceshipads.invoiceshipaddressid = mycrm_invoice.invoiceid
			left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid
			left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid
			where mycrm_crmentity.deleted=0 and mycrm_salesorder.salesorderid=".$id;

		$log->debug("Exiting get_invoices method ...");
		return GetRelatedList('SalesOrder','Invoice',$focus,$query,$button,$returnset);

	}

	/**	Function used to get the Status history of the Sales Order
	 *	@param $id - salesorder id
	 *	@return $return_data - array with header and the entries in format Array('header'=>$header,'entries'=>$entries_list) where as $header and $entries_list are arrays which contains header values and all column values of all entries
	 */
	function get_sostatushistory($id)
	{
		global $log;
		$log->debug("Entering get_sostatushistory(".$id.") method ...");

		global $adb;
		global $mod_strings;
		global $app_strings;

		$query = 'select mycrm_sostatushistory.*, mycrm_salesorder.salesorder_no from mycrm_sostatushistory inner join mycrm_salesorder on mycrm_salesorder.salesorderid = mycrm_sostatushistory.salesorderid inner join mycrm_crmentity on mycrm_crmentity.crmid = mycrm_salesorder.salesorderid where mycrm_crmentity.deleted = 0 and mycrm_salesorder.salesorderid = ?';
		$result=$adb->pquery($query, array($id));
		$noofrows = $adb->num_rows($result);

		$header[] = $app_strings['Order No'];
		$header[] = $app_strings['LBL_ACCOUNT_NAME'];
		$header[] = $app_strings['LBL_AMOUNT'];
		$header[] = $app_strings['LBL_SO_STATUS'];
		$header[] = $app_strings['LBL_LAST_MODIFIED'];

		//Getting the field permission for the current user. 1 - Not Accessible, 0 - Accessible
		//Account Name , Total are mandatory fields. So no need to do security check to these fields.
		global $current_user;

		//If field is accessible then getFieldVisibilityPermission function will return 0 else return 1
		$sostatus_access = (getFieldVisibilityPermission('SalesOrder', $current_user->id, 'sostatus') != '0')? 1 : 0;
		$picklistarray = getAccessPickListValues('SalesOrder');

		$sostatus_array = ($sostatus_access != 1)? $picklistarray['sostatus']: array();
		//- ==> picklist field is not permitted in profile
		//Not Accessible - picklist is permitted in profile but picklist value is not permitted
		$error_msg = ($sostatus_access != 1)? 'Not Accessible': '-';

		while($row = $adb->fetch_array($result))
		{
			$entries = Array();

			// Module Sequence Numbering
			//$entries[] = $row['salesorderid'];
			$entries[] = $row['salesorder_no'];
			// END
			$entries[] = $row['accountname'];
			$entries[] = $row['total'];
			$entries[] = (in_array($row['sostatus'], $sostatus_array))? $row['sostatus']: $error_msg;
			$date = new DateTimeField($row['lastmodified']);
			$entries[] = $date->getDisplayDateTimeValue();

			$entries_list[] = $entries;
		}

		$return_data = Array('header'=>$header,'entries'=>$entries_list);

	 	$log->debug("Exiting get_sostatushistory method ...");

		return $return_data;
	}

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule,$queryPlanner){
		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('mycrm_crmentitySalesOrder', array('mycrm_usersSalesOrder', 'mycrm_groupsSalesOrder', 'mycrm_lastModifiedBySalesOrder'));
		$matrix->setDependency('mycrm_inventoryproductrelSalesOrder', array('mycrm_productsSalesOrder', 'mycrm_serviceSalesOrder'));
		$matrix->setDependency('mycrm_salesorder',array('mycrm_crmentitySalesOrder', "mycrm_currency_info$secmodule",
				'mycrm_salesordercf', 'mycrm_potentialRelSalesOrder', 'mycrm_sobillads','mycrm_soshipads',
				'mycrm_inventoryproductrelSalesOrder', 'mycrm_contactdetailsSalesOrder', 'mycrm_accountSalesOrder',
				'mycrm_invoice_recurring_info','mycrm_quotesSalesOrder'));

		if (!$queryPlanner->requireTable('mycrm_salesorder', $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module,$secmodule,"mycrm_salesorder","salesorderid", $queryPlanner);
		if ($queryPlanner->requireTable("mycrm_crmentitySalesOrder",$matrix)){
			$query .= " left join mycrm_crmentity as mycrm_crmentitySalesOrder on mycrm_crmentitySalesOrder.crmid=mycrm_salesorder.salesorderid and mycrm_crmentitySalesOrder.deleted=0";
		}
		if ($queryPlanner->requireTable("mycrm_salesordercf")){
			$query .= " left join mycrm_salesordercf on mycrm_salesorder.salesorderid = mycrm_salesordercf.salesorderid";
		}
		if ($queryPlanner->requireTable("mycrm_sobillads")){
			$query .= " left join mycrm_sobillads on mycrm_salesorder.salesorderid=mycrm_sobillads.sobilladdressid";
		}
		if ($queryPlanner->requireTable("mycrm_soshipads")){
			$query .= " left join mycrm_soshipads on mycrm_salesorder.salesorderid=mycrm_soshipads.soshipaddressid";
		}
		if ($queryPlanner->requireTable("mycrm_currency_info$secmodule")){
			$query .= " left join mycrm_currency_info as mycrm_currency_info$secmodule on mycrm_currency_info$secmodule.id = mycrm_salesorder.currency_id";
		}
		if ($queryPlanner->requireTable("mycrm_inventoryproductrelSalesOrder", $matrix)){
			$query .= " left join mycrm_inventoryproductrel as mycrm_inventoryproductrelSalesOrder on mycrm_salesorder.salesorderid = mycrm_inventoryproductrelSalesOrder.id";
            // To Eliminate duplicates in reports
            if(($module == 'Products' || $module == 'Services') && $secmodule == "SalesOrder"){
                if($module == 'Products'){
                    $query .= " and mycrm_inventoryproductrelSalesOrder.productid = mycrm_products.productid ";    
                }else if($module == 'Services'){
                    $query .= " and mycrm_inventoryproductrelSalesOrder.productid = mycrm_service.serviceid "; 
                }
            }
		}
		if ($queryPlanner->requireTable("mycrm_productsSalesOrder")){
			$query .= " left join mycrm_products as mycrm_productsSalesOrder on mycrm_productsSalesOrder.productid = mycrm_inventoryproductrelSalesOrder.productid";
		}
		if ($queryPlanner->requireTable("mycrm_serviceSalesOrder")){
			$query .= " left join mycrm_service as mycrm_serviceSalesOrder on mycrm_serviceSalesOrder.serviceid = mycrm_inventoryproductrelSalesOrder.productid";
		}
		if ($queryPlanner->requireTable("mycrm_groupsSalesOrder")){
			$query .= " left join mycrm_groups as mycrm_groupsSalesOrder on mycrm_groupsSalesOrder.groupid = mycrm_crmentitySalesOrder.smownerid";
		}
		if ($queryPlanner->requireTable("mycrm_usersSalesOrder")){
			$query .= " left join mycrm_users as mycrm_usersSalesOrder on mycrm_usersSalesOrder.id = mycrm_crmentitySalesOrder.smownerid";
		}
		if ($queryPlanner->requireTable("mycrm_potentialRelSalesOrder")){
			$query .= " left join mycrm_potential as mycrm_potentialRelSalesOrder on mycrm_potentialRelSalesOrder.potentialid = mycrm_salesorder.potentialid";
		}
		if ($queryPlanner->requireTable("mycrm_contactdetailsSalesOrder")){
			$query .= " left join mycrm_contactdetails as mycrm_contactdetailsSalesOrder on mycrm_salesorder.contactid = mycrm_contactdetailsSalesOrder.contactid";
		}
		if ($queryPlanner->requireTable("mycrm_invoice_recurring_info")){
			$query .= " left join mycrm_invoice_recurring_info on mycrm_salesorder.salesorderid = mycrm_invoice_recurring_info.salesorderid";
		}
		if ($queryPlanner->requireTable("mycrm_quotesSalesOrder")){
			$query .= " left join mycrm_quotes as mycrm_quotesSalesOrder on mycrm_salesorder.quoteid = mycrm_quotesSalesOrder.quoteid";
		}
		if ($queryPlanner->requireTable("mycrm_accountSalesOrder")){
			$query .= " left join mycrm_account as mycrm_accountSalesOrder on mycrm_accountSalesOrder.accountid = mycrm_salesorder.accountid";
		}
		if ($queryPlanner->requireTable("mycrm_lastModifiedBySalesOrder")){
			$query .= " left join mycrm_users as mycrm_lastModifiedBySalesOrder on mycrm_lastModifiedBySalesOrder.id = mycrm_crmentitySalesOrder.modifiedby ";
		}
        if ($queryPlanner->requireTable("mycrm_createdbySalesOrder")){
			$query .= " left join mycrm_users as mycrm_createdbySalesOrder on mycrm_createdbySalesOrder.id = mycrm_crmentitySalesOrder.smcreatorid ";
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
			"Calendar" =>array("mycrm_seactivityrel"=>array("crmid","activityid"),"mycrm_salesorder"=>"salesorderid"),
			"Invoice" =>array("mycrm_invoice"=>array("salesorderid","invoiceid"),"mycrm_salesorder"=>"salesorderid"),
			"Documents" => array("mycrm_senotesrel"=>array("crmid","notesid"),"mycrm_salesorder"=>"salesorderid"),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Accounts') {
			$this->trash('SalesOrder',$id);
		}
		elseif($return_module == 'Quotes') {
			$relation_query = 'UPDATE mycrm_salesorder SET quoteid=? WHERE salesorderid=?';
			$this->db->pquery($relation_query, array(null, $id));
		}
		elseif($return_module == 'Potentials') {
			$relation_query = 'UPDATE mycrm_salesorder SET potentialid=? WHERE salesorderid=?';
			$this->db->pquery($relation_query, array(null, $id));
		}
		elseif($return_module == 'Contacts') {
			$relation_query = 'UPDATE mycrm_salesorder SET contactid=? WHERE salesorderid=?';
			$this->db->pquery($relation_query, array(null, $id));
		} else {
			$sql = 'DELETE FROM mycrm_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}

	public function getJoinClause($tableName) {
		if ($tableName == 'mycrm_invoice_recurring_info') {
			return 'LEFT JOIN';
		}
		return parent::getJoinClause($tableName);
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
	* Returns Export SalesOrder Query.
	*/
	function create_export_query($where)
	{
		global $log;
		global $current_user;
		$log->debug("Entering create_export_query(".$where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("SalesOrder", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);
		$fields_list .= getInventoryFieldsForExport($this->table_name);
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');

		$query = "SELECT $fields_list FROM ".$this->entity_table."
				INNER JOIN mycrm_salesorder ON mycrm_salesorder.salesorderid = mycrm_crmentity.crmid
				LEFT JOIN mycrm_salesordercf ON mycrm_salesordercf.salesorderid = mycrm_salesorder.salesorderid
				LEFT JOIN mycrm_sobillads ON mycrm_sobillads.sobilladdressid = mycrm_salesorder.salesorderid
				LEFT JOIN mycrm_soshipads ON mycrm_soshipads.soshipaddressid = mycrm_salesorder.salesorderid
				LEFT JOIN mycrm_inventoryproductrel ON mycrm_inventoryproductrel.id = mycrm_salesorder.salesorderid
				LEFT JOIN mycrm_products ON mycrm_products.productid = mycrm_inventoryproductrel.productid
				LEFT JOIN mycrm_service ON mycrm_service.serviceid = mycrm_inventoryproductrel.productid
				LEFT JOIN mycrm_contactdetails ON mycrm_contactdetails.contactid = mycrm_salesorder.contactid
				LEFT JOIN mycrm_invoice_recurring_info ON mycrm_invoice_recurring_info.salesorderid = mycrm_salesorder.salesorderid
				LEFT JOIN mycrm_potential ON mycrm_potential.potentialid = mycrm_salesorder.potentialid
				LEFT JOIN mycrm_account ON mycrm_account.accountid = mycrm_salesorder.accountid
				LEFT JOIN mycrm_currency_info ON mycrm_currency_info.id = mycrm_salesorder.currency_id
				LEFT JOIN mycrm_quotes ON mycrm_quotes.quoteid = mycrm_salesorder.quoteid
				LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
				LEFT JOIN mycrm_users ON mycrm_users.id = mycrm_crmentity.smownerid";

		$query .= $this->getNonAdminAccessControlQuery('SalesOrder',$current_user);
		$where_auto = " mycrm_crmentity.deleted=0";

		if($where != "") {
			$query .= " where ($where) AND ".$where_auto;
		} else {
			$query .= " where ".$where_auto;
		}

		$log->debug("Exiting create_export_query method ...");
		return $query;
	}

    /**
	 * Function which will give the basic query to find duplicates
	 * @param <String> $module
	 * @param <String> $tableColumns
	 * @param <String> $selectedColumns
	 * @param <Boolean> $ignoreEmpty
	 * @return string
	 */
	// Note : remove getDuplicatesQuery API once mycrm5 code is removed
    function getQueryForDuplicates($module, $tableColumns, $selectedColumns = '', $ignoreEmpty = false) {
		if(is_array($tableColumns)) {
			$tableColumnsString = implode(',', $tableColumns);
		}
        $selectClause = "SELECT " . $this->table_name . "." . $this->table_index . " AS recordid," . $tableColumnsString;

        // Select Custom Field Table Columns if present
        if (isset($this->customFieldTable))
            $query .= ", " . $this->customFieldTable[0] . ".* ";

        $fromClause = " FROM $this->table_name";

        $fromClause .= " INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = $this->table_name.$this->table_index";

		if($this->tab_name) {
			foreach($this->tab_name as $tableName) {
				if($tableName != 'mycrm_crmentity' && $tableName != $this->table_name && $tableName != 'mycrm_inventoryproductrel') {
                    if($tableName == 'mycrm_invoice_recurring_info') {
						$fromClause .= " LEFT JOIN " . $tableName . " ON " . $tableName . '.' . $this->tab_name_index[$tableName] .
							" = $this->table_name.$this->table_index";
					}elseif($this->tab_name_index[$tableName]) {
						$fromClause .= " INNER JOIN " . $tableName . " ON " . $tableName . '.' . $this->tab_name_index[$tableName] .
							" = $this->table_name.$this->table_index";
					}
				}
			}
		}
        $fromClause .= " LEFT JOIN mycrm_users ON mycrm_users.id = mycrm_crmentity.smownerid
						LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid";

        $whereClause = " WHERE mycrm_crmentity.deleted = 0";
        $whereClause .= $this->getListViewSecurityParameter($module);

		if($ignoreEmpty) {
			foreach($tableColumns as $tableColumn){
				$whereClause .= " AND ($tableColumn IS NOT NULL AND $tableColumn != '') ";
			}
		}

        if (isset($selectedColumns) && trim($selectedColumns) != '') {
            $sub_query = "SELECT $selectedColumns FROM $this->table_name AS t " .
                    " INNER JOIN mycrm_crmentity AS crm ON crm.crmid = t." . $this->table_index;
            // Consider custom table join as well.
            if (isset($this->customFieldTable)) {
                $sub_query .= " LEFT JOIN " . $this->customFieldTable[0] . " tcf ON tcf." . $this->customFieldTable[1] . " = t.$this->table_index";
            }
            $sub_query .= " WHERE crm.deleted=0 GROUP BY $selectedColumns HAVING COUNT(*)>1";
        } else {
            $sub_query = "SELECT $tableColumnsString $fromClause $whereClause GROUP BY $tableColumnsString HAVING COUNT(*)>1";
        }

		$i = 1;
		foreach($tableColumns as $tableColumn){
			$tableInfo = explode('.', $tableColumn);
			$duplicateCheckClause .= " ifnull($tableColumn,'null') = ifnull(temp.$tableInfo[1],'null')";
			if (count($tableColumns) != $i++) $duplicateCheckClause .= " AND ";
		}

        $query = $selectClause . $fromClause .
                " LEFT JOIN mycrm_users_last_import ON mycrm_users_last_import.bean_id=" . $this->table_name . "." . $this->table_index .
                " INNER JOIN (" . $sub_query . ") AS temp ON " . $duplicateCheckClause .
                $whereClause .
                " ORDER BY $tableColumnsString," . $this->table_name . "." . $this->table_index . " ASC";
        return $query;
    }

}

?>
