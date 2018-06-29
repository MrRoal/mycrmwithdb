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
class PurchaseOrder extends CRMEntity {
	var $log;
	var $db;

	var $table_name = "mycrm_purchaseorder";
	var $table_index= 'purchaseorderid';
	var $tab_name = Array('mycrm_crmentity','mycrm_purchaseorder','mycrm_pobillads','mycrm_poshipads','mycrm_purchaseordercf','mycrm_inventoryproductrel');
	var $tab_name_index = Array('mycrm_crmentity'=>'crmid','mycrm_purchaseorder'=>'purchaseorderid','mycrm_pobillads'=>'pobilladdressid','mycrm_poshipads'=>'poshipaddressid','mycrm_purchaseordercf'=>'purchaseorderid','mycrm_inventoryproductrel'=>'id');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('mycrm_purchaseordercf', 'purchaseorderid');
	var $entity_table = "mycrm_crmentity";

	var $billadr_table = "mycrm_pobillads";

	var $column_fields = Array();

	var $sortby_fields = Array('subject','tracking_no','smownerid','lastname');

	// This is used to retrieve related mycrm_fields from form posts.
	var $additional_column_fields = Array('assigned_user_name', 'smownerid', 'opportunity_id', 'case_id', 'contact_id', 'task_id', 'note_id', 'meeting_id', 'call_id', 'email_id', 'parent_name', 'member_id' );

	// This is the list of mycrm_fields that are in the lists.
	var $list_fields = Array(
				//  Module Sequence Numbering
				//'Order No'=>Array('crmentity'=>'crmid'),
				'Order No'=>Array('purchaseorder'=>'purchaseorder_no'),
				// END
				'Subject'=>Array('purchaseorder'=>'subject'),
				'Vendor Name'=>Array('purchaseorder'=>'vendorid'),
				'Tracking Number'=>Array('purchaseorder'=> 'tracking_no'),
				'Total'=>Array('purchaseorder'=>'total'),
				'Assigned To'=>Array('crmentity'=>'smownerid')
				);

	var $list_fields_name = Array(
				        'Order No'=>'purchaseorder_no',
				        'Subject'=>'subject',
				        'Vendor Name'=>'vendor_id',
					'Tracking Number'=>'tracking_no',
					'Total'=>'hdnGrandTotal',
				        'Assigned To'=>'assigned_user_id'
				      );
	var $list_link_field= 'subject';

	var $search_fields = Array(
				'Order No'=>Array('purchaseorder'=>'purchaseorder_no'),
				'Subject'=>Array('purchaseorder'=>'subject'),
				);

	var $search_fields_name = Array(
				        'Order No'=>'purchaseorder_no',
				        'Subject'=>'subject',
				      );
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to mycrm_field.fieldname values.
	var $mandatory_fields = Array('subject', 'vendor_id','createdtime' ,'modifiedtime', 'assigned_user_id');

	// This is the list of mycrm_fields that are required.
	var $required_fields =  array("accountname"=>1);

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'subject';
	var $default_sort_order = 'ASC';

	// For Alphabetical search
	var $def_basicsearch_col = 'subject';

	// For workflows update field tasks is deleted all the lineitems.
	var $isLineItemUpdate = true;

	//var $groupTable = Array('mycrm_pogrouprelation','purchaseorderid');
	/** Constructor Function for Order class
	 *  This function creates an instance of LoggerManager class using getLogger method
	 *  creates an instance for PearDatabase class and get values for column_fields array of Order class.
	 */
	function PurchaseOrder() {
		$this->log =LoggerManager::getLogger('PurchaseOrder');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('PurchaseOrder');
	}

	function save_module($module)
	{
		global $adb, $updateInventoryProductRel_deduct_stock;
		$updateInventoryProductRel_deduct_stock = false;
		//in ajax save we should not call this function, because this will delete all the existing product values
		if($_REQUEST['action'] != 'PurchaseOrderAjax' && $_REQUEST['ajxaction'] != 'DETAILVIEW'
				&& $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates'
				&& $_REQUEST['action'] != 'SaveAjax' && $this->isLineItemUpdate != false && $_REQUEST['action'] != 'FROM_WS') {

			$requestProductIdsList = $requestQuantitiesList = array();
			$totalNoOfProducts = $_REQUEST['totalProductCount'];
			for($i=1; $i<=$totalNoOfProducts; $i++) {
				$productId = $_REQUEST['hdnProductId'.$i];
				$requestProductIdsList[$productId] = $productId;
                if(array_key_exists($productId, $requestQuantitiesList)){
                    $requestQuantitiesList[$productId] = $requestQuantitiesList[$productId] + $_REQUEST['qty'.$i];
                    continue;
                }
				$requestQuantitiesList[$productId] =  $_REQUEST['qty'.$i];
			}

			if($this->mode == '' && $this->column_fields['postatus'] === 'Received Shipment') {																			//Updating Product stock quantity during create mode
				foreach ($requestProductIdsList as $productId) {
					addToProductStock($productId, $requestQuantitiesList[$productId]);
				}
			} else if ($this->column_fields['postatus'] === 'Received Shipment' && $this->mode != '') {		//Updating Product stock quantity during edit mode
				$recordId = $this->id;
				$result = $adb->pquery("SELECT productid, quantity FROM mycrm_inventoryproductrel WHERE id = ?", array($recordId));
				$numOfRows = $adb->num_rows($result);
				for ($i=0; $i<$numOfRows; $i++) {
					$productId = $adb->query_result($result, $i, 'productid');
					$productIdsList[$productId] = $productId;
					$quantitiesList[$productId] = $adb->query_result($result, $i, 'quantity');
				}

				$newProductIds = array_diff($requestProductIdsList, $productIdsList);
				if ($newProductIds) {
					foreach ($newProductIds as $productId) {
						addToProductStock($productId, $requestQuantitiesList[$productId]);
					}
				}

				$deletedProductIds = array_diff($productIdsList, $requestProductIdsList);
				if ($deletedProductIds) {
					foreach ($deletedProductIds as $productId) {
						$productStock= getPrdQtyInStck($productId);
						$quantity = $productStock - $quantitiesList[$productId];
						updateProductQty($productId, $quantity);
					}
				}

				$updatedProductIds = array_intersect($productIdsList, $requestProductIdsList);
				if ($updatedProductIds) {
					foreach ($updatedProductIds as $productId) {
						$quantityDiff = $quantitiesList[$productId] - $requestQuantitiesList[$productId];
						if ($quantityDiff < 0) {
							$quantityDiff = -($quantityDiff);
							addToProductStock($productId, $quantityDiff);
						} elseif ($quantityDiff > 0) {
							$productStock= getPrdQtyInStck($productId);
							$quantity = $productStock - $quantityDiff;
							updateProductQty($productId, $quantity);
						}
					}
				}
			}

			//Based on the total Number of rows we will save the product relationship with this entity
			saveInventoryProductDetails($this, 'PurchaseOrder', $this->update_prod_stock);

			if ($this->mode != '') {
				$updateInventoryProductRel_deduct_stock = true;
			}
		}

		// Update the currency id and the conversion rate for the purchase order
		$update_query = "update mycrm_purchaseorder set currency_id=?, conversion_rate=? where purchaseorderid=?";
		$update_params = array($this->column_fields['currency_id'], $this->column_fields['conversion_rate'], $this->id);
		$adb->pquery($update_query, $update_params);
	}

	/** Function to get activities associated with the Purchase Order
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
		$query = "SELECT case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,mycrm_contactdetails.lastname, mycrm_contactdetails.firstname, mycrm_contactdetails.contactid,mycrm_activity.*,mycrm_seactivityrel.crmid as parent_id,mycrm_crmentity.crmid, mycrm_crmentity.smownerid, mycrm_crmentity.modifiedtime from mycrm_activity inner join mycrm_seactivityrel on mycrm_seactivityrel.activityid=mycrm_activity.activityid inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_activity.activityid left join mycrm_cntactivityrel on mycrm_cntactivityrel.activityid= mycrm_activity.activityid left join mycrm_contactdetails on mycrm_contactdetails.contactid = mycrm_cntactivityrel.contactid left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid where mycrm_seactivityrel.crmid=".$id." and activitytype='Task' and mycrm_crmentity.deleted=0 and (mycrm_activity.status is not NULL && mycrm_activity.status != 'Completed') and (mycrm_activity.status is not NULL and mycrm_activity.status != 'Deferred') ";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_activities method ...");
		return $return_value;
	}

	/** Function to get the activities history associated with the Purchase Order
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
			mycrm_contactdetails.contactid,mycrm_activity.* ,mycrm_seactivityrel.*,
			mycrm_crmentity.crmid, mycrm_crmentity.smownerid, mycrm_crmentity.modifiedtime,
			mycrm_crmentity.createdtime, mycrm_crmentity.description,case when
			(mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end
			as user_name from mycrm_activity
				inner join mycrm_seactivityrel on mycrm_seactivityrel.activityid=mycrm_activity.activityid
				inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_activity.activityid
				left join mycrm_cntactivityrel on mycrm_cntactivityrel.activityid= mycrm_activity.activityid
				left join mycrm_contactdetails on mycrm_contactdetails.contactid = mycrm_cntactivityrel.contactid
                                left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid
				left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid
			where mycrm_activity.activitytype='Task'
				and (mycrm_activity.status = 'Completed' or mycrm_activity.status = 'Deferred')
				and mycrm_seactivityrel.crmid=".$id."
                                and mycrm_crmentity.deleted = 0";
		//Don't add order by, because, for security, one more condition will be added with this query in include/RelatedListView.php

        $returnValue = getHistory('PurchaseOrder',$query,$id);
		$log->debug("Exiting get_history method ...");
		return $returnValue;
	}


	/**	Function used to get the Status history of the Purchase Order
	 *	@param $id - purchaseorder id
	 *	@return $return_data - array with header and the entries in format Array('header'=>$header,'entries'=>$entries_list) where as $header and $entries_list are arrays which contains header values and all column values of all entries
	 */
	function get_postatushistory($id)
	{
		global $log;
		$log->debug("Entering get_postatushistory(".$id.") method ...");

		global $adb;
		global $mod_strings;
		global $app_strings;

		$query = 'select mycrm_postatushistory.*, mycrm_purchaseorder.purchaseorder_no from mycrm_postatushistory inner join mycrm_purchaseorder on mycrm_purchaseorder.purchaseorderid = mycrm_postatushistory.purchaseorderid inner join mycrm_crmentity on mycrm_crmentity.crmid = mycrm_purchaseorder.purchaseorderid where mycrm_crmentity.deleted = 0 and mycrm_purchaseorder.purchaseorderid = ?';
		$result=$adb->pquery($query, array($id));
		$noofrows = $adb->num_rows($result);

		$header[] = $app_strings['Order No'];
		$header[] = $app_strings['Vendor Name'];
		$header[] = $app_strings['LBL_AMOUNT'];
		$header[] = $app_strings['LBL_PO_STATUS'];
		$header[] = $app_strings['LBL_LAST_MODIFIED'];

		//Getting the field permission for the current user. 1 - Not Accessible, 0 - Accessible
		//Vendor, Total are mandatory fields. So no need to do security check to these fields.
		global $current_user;

		//If field is accessible then getFieldVisibilityPermission function will return 0 else return 1
		$postatus_access = (getFieldVisibilityPermission('PurchaseOrder', $current_user->id, 'postatus') != '0')? 1 : 0;
		$picklistarray = getAccessPickListValues('PurchaseOrder');

		$postatus_array = ($postatus_access != 1)? $picklistarray['postatus']: array();
		//- ==> picklist field is not permitted in profile
		//Not Accessible - picklist is permitted in profile but picklist value is not permitted
		$error_msg = ($postatus_access != 1)? 'Not Accessible': '-';

		while($row = $adb->fetch_array($result))
		{
			$entries = Array();

			//Module Sequence Numbering
			//$entries[] = $row['purchaseorderid'];
			$entries[] = $row['purchaseorder_no'];
			// END
			$entries[] = $row['vendorname'];
			$entries[] = $row['total'];
			$entries[] = (in_array($row['postatus'], $postatus_array))? $row['postatus']: $error_msg;
			$date = new DateTimeField($row['lastmodified']);
			$entries[] = $date->getDisplayDateTimeValue();

			$entries_list[] = $entries;
		}

		$return_data = Array('header'=>$header,'entries'=>$entries_list);

	 	$log->debug("Exiting get_postatushistory method ...");

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
		$matrix->setDependency('mycrm_crmentityPurchaseOrder', array('mycrm_usersPurchaseOrder', 'mycrm_groupsPurchaseOrder', 'mycrm_lastModifiedByPurchaseOrder'));
		$matrix->setDependency('mycrm_inventoryproductrelPurchaseOrder', array('mycrm_productsPurchaseOrder', 'mycrm_servicePurchaseOrder'));
		$matrix->setDependency('mycrm_purchaseorder',array('mycrm_crmentityPurchaseOrder', "mycrm_currency_info$secmodule",
				'mycrm_purchaseordercf', 'mycrm_vendorRelPurchaseOrder', 'mycrm_pobillads',
				'mycrm_poshipads', 'mycrm_inventoryproductrelPurchaseOrder', 'mycrm_contactdetailsPurchaseOrder'));

		if (!$queryPlanner->requireTable('mycrm_purchaseorder', $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module,$secmodule,"mycrm_purchaseorder","purchaseorderid",$queryPlanner);
		if ($queryPlanner->requireTable("mycrm_crmentityPurchaseOrder", $matrix)){
			$query .= " left join mycrm_crmentity as mycrm_crmentityPurchaseOrder on mycrm_crmentityPurchaseOrder.crmid=mycrm_purchaseorder.purchaseorderid and mycrm_crmentityPurchaseOrder.deleted=0";
		}
		if ($queryPlanner->requireTable("mycrm_purchaseordercf")){
			$query .= " left join mycrm_purchaseordercf on mycrm_purchaseorder.purchaseorderid = mycrm_purchaseordercf.purchaseorderid";
		}
		if ($queryPlanner->requireTable("mycrm_pobillads")){
			$query .= " left join mycrm_pobillads on mycrm_purchaseorder.purchaseorderid=mycrm_pobillads.pobilladdressid";
		}
		if ($queryPlanner->requireTable("mycrm_poshipads")){
			$query .= " left join mycrm_poshipads on mycrm_purchaseorder.purchaseorderid=mycrm_poshipads.poshipaddressid";
		}
		if ($queryPlanner->requireTable("mycrm_currency_info$secmodule")){
			$query .= " left join mycrm_currency_info as mycrm_currency_info$secmodule on mycrm_currency_info$secmodule.id = mycrm_purchaseorder.currency_id";
		}
		if ($queryPlanner->requireTable("mycrm_inventoryproductrelPurchaseOrder", $matrix)){
			$query .= " left join mycrm_inventoryproductrel as mycrm_inventoryproductrelPurchaseOrder on mycrm_purchaseorder.purchaseorderid = mycrm_inventoryproductrelPurchaseOrder.id";
            // To Eliminate duplicates in reports
            if(($module == 'Products' || $module == 'Services') && $secmodule == "PurchaseOrder"){
                if($module == 'Products'){
                    $query .= " and mycrm_inventoryproductrelPurchaseOrder.productid = mycrm_products.productid ";    
                }else if($module == 'Services'){
                    $query .= " and mycrm_inventoryproductrelPurchaseOrder.productid = mycrm_service.serviceid ";
                }
            }
		}
		if ($queryPlanner->requireTable("mycrm_productsPurchaseOrder")){
			$query .= " left join mycrm_products as mycrm_productsPurchaseOrder on mycrm_productsPurchaseOrder.productid = mycrm_inventoryproductrelPurchaseOrder.productid";
		}
		if ($queryPlanner->requireTable("mycrm_servicePurchaseOrder")){
			$query .= " left join mycrm_service as mycrm_servicePurchaseOrder on mycrm_servicePurchaseOrder.serviceid = mycrm_inventoryproductrelPurchaseOrder.productid";
		}
		if ($queryPlanner->requireTable("mycrm_usersPurchaseOrder")){
			$query .= " left join mycrm_users as mycrm_usersPurchaseOrder on mycrm_usersPurchaseOrder.id = mycrm_crmentityPurchaseOrder.smownerid";
		}
		if ($queryPlanner->requireTable("mycrm_groupsPurchaseOrder")){
			$query .= " left join mycrm_groups as mycrm_groupsPurchaseOrder on mycrm_groupsPurchaseOrder.groupid = mycrm_crmentityPurchaseOrder.smownerid";
		}
		if ($queryPlanner->requireTable("mycrm_vendorRelPurchaseOrder")){
			$query .= " left join mycrm_vendor as mycrm_vendorRelPurchaseOrder on mycrm_vendorRelPurchaseOrder.vendorid = mycrm_purchaseorder.vendorid";
		}
		if ($queryPlanner->requireTable("mycrm_contactdetailsPurchaseOrder")){
			$query .= " left join mycrm_contactdetails as mycrm_contactdetailsPurchaseOrder on mycrm_contactdetailsPurchaseOrder.contactid = mycrm_purchaseorder.contactid";
		}
		if ($queryPlanner->requireTable("mycrm_lastModifiedByPurchaseOrder")){
			$query .= " left join mycrm_users as mycrm_lastModifiedByPurchaseOrder on mycrm_lastModifiedByPurchaseOrder.id = mycrm_crmentityPurchaseOrder.modifiedby ";
		}
        if ($queryPlanner->requireTable("mycrm_createdbyPurchaseOrder")){
			$query .= " left join mycrm_users as mycrm_createdbyPurchaseOrder on mycrm_createdbyPurchaseOrder.id = mycrm_crmentityPurchaseOrder.smcreatorid ";
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
			"Calendar" =>array("mycrm_seactivityrel"=>array("crmid","activityid"),"mycrm_purchaseorder"=>"purchaseorderid"),
			"Documents" => array("mycrm_senotesrel"=>array("crmid","notesid"),"mycrm_purchaseorder"=>"purchaseorderid"),
			"Contacts" => array("mycrm_purchaseorder"=>array("purchaseorderid","contactid")),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Vendors') {
			$sql_req ='UPDATE mycrm_crmentity SET deleted = 1 WHERE crmid= ?';
			$this->db->pquery($sql_req, array($id));
		} elseif($return_module == 'Contacts') {
			$sql_req ='UPDATE mycrm_purchaseorder SET contactid=? WHERE purchaseorderid = ?';
			$this->db->pquery($sql_req, array(null, $id));
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
	* Returns Export PurchaseOrder Query.
	*/
	function create_export_query($where)
	{
		global $log;
		global $current_user;
		$log->debug("Entering create_export_query(".$where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("PurchaseOrder", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);
		$fields_list .= getInventoryFieldsForExport($this->table_name);
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');

		$query = "SELECT $fields_list FROM ".$this->entity_table."
				INNER JOIN mycrm_purchaseorder ON mycrm_purchaseorder.purchaseorderid = mycrm_crmentity.crmid
				LEFT JOIN mycrm_purchaseordercf ON mycrm_purchaseordercf.purchaseorderid = mycrm_purchaseorder.purchaseorderid
				LEFT JOIN mycrm_pobillads ON mycrm_pobillads.pobilladdressid = mycrm_purchaseorder.purchaseorderid
				LEFT JOIN mycrm_poshipads ON mycrm_poshipads.poshipaddressid = mycrm_purchaseorder.purchaseorderid
				LEFT JOIN mycrm_inventoryproductrel ON mycrm_inventoryproductrel.id = mycrm_purchaseorder.purchaseorderid
				LEFT JOIN mycrm_products ON mycrm_products.productid = mycrm_inventoryproductrel.productid
				LEFT JOIN mycrm_service ON mycrm_service.serviceid = mycrm_inventoryproductrel.productid
				LEFT JOIN mycrm_contactdetails ON mycrm_contactdetails.contactid = mycrm_purchaseorder.contactid
				LEFT JOIN mycrm_vendor ON mycrm_vendor.vendorid = mycrm_purchaseorder.vendorid
				LEFT JOIN mycrm_currency_info ON mycrm_currency_info.id = mycrm_purchaseorder.currency_id
				LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
				LEFT JOIN mycrm_users ON mycrm_users.id = mycrm_crmentity.smownerid";

		$query .= $this->getNonAdminAccessControlQuery('PurchaseOrder',$current_user);
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