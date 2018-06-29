<?php
/*********************************************************************************
** The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
*
 ********************************************************************************/
class Vendors extends CRMEntity {
	var $log;
	var $db;
	var $table_name = "mycrm_vendor";
	var $table_index= 'vendorid';
	var $tab_name = Array('mycrm_crmentity','mycrm_vendor','mycrm_vendorcf');
	var $tab_name_index = Array('mycrm_crmentity'=>'crmid','mycrm_vendor'=>'vendorid','mycrm_vendorcf'=>'vendorid');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('mycrm_vendorcf', 'vendorid');
	var $column_fields = Array();

        //Pavani: Assign value to entity_table
        var $entity_table = "mycrm_crmentity";
        var $sortby_fields = Array('vendorname','category');

        // This is the list of mycrm_fields that are in the lists.
	var $list_fields = Array(
                                'Vendor Name'=>Array('vendor'=>'vendorname'),
                                'Phone'=>Array('vendor'=>'phone'),
                                'Email'=>Array('vendor'=>'email'),
                                'Category'=>Array('vendor'=>'category')
                                );
        var $list_fields_name = Array(
                                        'Vendor Name'=>'vendorname',
                                        'Phone'=>'phone',
                                        'Email'=>'email',
                                        'Category'=>'category'
                                     );
        var $list_link_field= 'vendorname';

	var $search_fields = Array(
                                'Vendor Name'=>Array('vendor'=>'vendorname'),
                                'Phone'=>Array('vendor'=>'phone')
                                );
        var $search_fields_name = Array(
                                        'Vendor Name'=>'vendorname',
                                        'Phone'=>'phone'
                                     );
	//Specifying required fields for vendors
        var $required_fields =  array();

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to mycrm_field.fieldname values.
	var $mandatory_fields = Array('createdtime', 'modifiedtime', 'vendorname', 'assigned_user_id');

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'vendorname';
	var $default_sort_order = 'ASC';

	// For Alphabetical search
	var $def_basicsearch_col = 'vendorname';

	/**	Constructor which will set the column_fields in this object
	 */
	function Vendors() {
		$this->log =LoggerManager::getLogger('vendor');
		$this->log->debug("Entering Vendors() method ...");
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Vendors');
		$this->log->debug("Exiting Vendor method ...");
	}

	function save_module($module)
	{
	}

	/**	function used to get the list of products which are related to the vendor
	 *	@param int $id - vendor id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_products($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_products(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		checkFileAccessForInclusion("modules/$related_module/$related_module.php");
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
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.parent_id.value=\"\";' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>";
			}
		}

		$query = "SELECT mycrm_products.productid, mycrm_products.productname, mycrm_products.productcode,
					mycrm_products.commissionrate, mycrm_products.qty_per_unit, mycrm_products.unit_price,
					mycrm_crmentity.crmid, mycrm_crmentity.smownerid,mycrm_vendor.vendorname
			  		FROM mycrm_products
			  		INNER JOIN mycrm_vendor ON mycrm_vendor.vendorid = mycrm_products.vendor_id
			  		INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_products.productid INNER JOIN mycrm_productcf
				    ON mycrm_products.productid = mycrm_productcf.productid
					LEFT JOIN mycrm_users
						ON mycrm_users.id=mycrm_crmentity.smownerid
					LEFT JOIN mycrm_groups
						ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			  		WHERE mycrm_crmentity.deleted = 0 AND mycrm_vendor.vendorid = $id";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	}

	/**	function used to get the list of purchase orders which are related to the vendor
	 *	@param int $id - vendor id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_purchase_orders($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_purchase_orders(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		checkFileAccessForInclusion("modules/$related_module/$related_module.php");
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
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "select case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,mycrm_crmentity.*, mycrm_purchaseorder.*,mycrm_vendor.vendorname from mycrm_purchaseorder inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_purchaseorder.purchaseorderid left outer join mycrm_vendor on mycrm_purchaseorder.vendorid=mycrm_vendor.vendorid LEFT JOIN mycrm_purchaseordercf ON mycrm_purchaseordercf.purchaseorderid = mycrm_purchaseorder.purchaseorderid LEFT JOIN mycrm_pobillads ON mycrm_pobillads.pobilladdressid = mycrm_purchaseorder.purchaseorderid LEFT JOIN mycrm_poshipads ON mycrm_poshipads.poshipaddressid = mycrm_purchaseorder.purchaseorderid  left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid where mycrm_crmentity.deleted=0 and mycrm_purchaseorder.vendorid=".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_purchase_orders method ...");
		return $return_value;
	}
	//Pavani: Function to create, export query for vendors module
        /** Function to export the vendors in CSV Format
        * @param reference variable - where condition is passed when the query is executed
        * Returns Export Vendors Query.
        */
        function create_export_query($where)
        {
                global $log;
                global $current_user;
                $log->debug("Entering create_export_query(".$where.") method ...");

                include("include/utils/ExportUtils.php");

                //To get the Permitted fields query and the permitted fields list
                $sql = getPermittedFieldsQuery("Vendors", "detail_view");
                $fields_list = getFieldsListFromQuery($sql);

                $query = "SELECT $fields_list FROM ".$this->entity_table."
                                INNER JOIN mycrm_vendor
                                        ON mycrm_crmentity.crmid = mycrm_vendor.vendorid
                                LEFT JOIN mycrm_vendorcf
                                        ON mycrm_vendorcf.vendorid=mycrm_vendor.vendorid
                                LEFT JOIN mycrm_seattachmentsrel
                                        ON mycrm_vendor.vendorid=mycrm_seattachmentsrel.crmid
                                LEFT JOIN mycrm_attachments
                                ON mycrm_seattachmentsrel.attachmentsid = mycrm_attachments.attachmentsid
                                LEFT JOIN mycrm_users
                                        ON mycrm_crmentity.smownerid = mycrm_users.id and mycrm_users.status='Active'
                                ";
                $where_auto = " mycrm_crmentity.deleted = 0 ";

                 if($where != "")
                   $query .= "  WHERE ($where) AND ".$where_auto;
                else
                   $query .= "  WHERE ".$where_auto;

                $log->debug("Exiting create_export_query method ...");
                return $query;
        }

	/**	function used to get the list of contacts which are related to the vendor
	 *	@param int $id - vendor id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_contacts(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		checkFileAccessForInclusion("modules/$related_module/$related_module.php");
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

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "SELECT case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,mycrm_contactdetails.*, mycrm_crmentity.crmid, mycrm_crmentity.smownerid,mycrm_vendorcontactrel.vendorid,mycrm_account.accountname from mycrm_contactdetails
				inner join mycrm_crmentity on mycrm_crmentity.crmid = mycrm_contactdetails.contactid
				inner join mycrm_vendorcontactrel on mycrm_vendorcontactrel.contactid=mycrm_contactdetails.contactid
				INNER JOIN mycrm_contactaddress ON mycrm_contactdetails.contactid = mycrm_contactaddress.contactaddressid
				INNER JOIN mycrm_contactsubdetails ON mycrm_contactdetails.contactid = mycrm_contactsubdetails.contactsubscriptionid
				INNER JOIN mycrm_customerdetails ON mycrm_contactdetails.contactid = mycrm_customerdetails.customerid
				INNER JOIN mycrm_contactscf ON mycrm_contactdetails.contactid = mycrm_contactscf.contactid
				left join mycrm_groups on mycrm_groups.groupid=mycrm_crmentity.smownerid
				left join mycrm_account on mycrm_account.accountid = mycrm_contactdetails.accountid
				left join mycrm_users on mycrm_users.id=mycrm_crmentity.smownerid
				where mycrm_crmentity.deleted=0 and mycrm_vendorcontactrel.vendorid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
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

		$rel_table_arr = Array("Products"=>"mycrm_products","PurchaseOrder"=>"mycrm_purchaseorder","Contacts"=>"mycrm_vendorcontactrel");

		$tbl_field_arr = Array("mycrm_products"=>"productid","mycrm_vendorcontactrel"=>"contactid","mycrm_purchaseorder"=>"purchaseorderid");

		$entity_tbl_field_arr = Array("mycrm_products"=>"vendor_id","mycrm_vendorcontactrel"=>"vendorid","mycrm_purchaseorder"=>"vendorid");

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
		$log->debug("Exiting transferRelatedRecords...");
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
		checkFileAccessForInclusion("modules/$related_module/$related_module.php");
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
		$query = "SELECT case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,
			mycrm_activity.activityid, mycrm_activity.subject,
			mycrm_activity.activitytype, mycrm_crmentity.modifiedtime,
			mycrm_crmentity.crmid, mycrm_crmentity.smownerid, mycrm_activity.date_start,mycrm_activity.time_start, mycrm_seactivityrel.crmid as parent_id
			FROM mycrm_activity, mycrm_seactivityrel, mycrm_vendor, mycrm_users, mycrm_crmentity
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid=mycrm_crmentity.smownerid
			WHERE mycrm_seactivityrel.activityid = mycrm_activity.activityid
				AND mycrm_vendor.vendorid = mycrm_seactivityrel.crmid
				AND mycrm_users.id=mycrm_crmentity.smownerid
				AND mycrm_crmentity.crmid = mycrm_activity.activityid
				AND mycrm_vendor.vendorid = ".$id."
				AND mycrm_activity.activitytype='Emails'
				AND mycrm_crmentity.deleted = 0";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_emails method ...");
		return $return_value;
	}

	/*
	 * Function to get the primary query part of a report
	 * @param - $module Primary module name
	 * returns the query string formed on fetching the related data for report for primary module
	 */
	function generateReportsQuery($module, $queryPlanner) {
		$moduletable = $this->table_name;
		$moduleindex = $this->table_index;
		$modulecftable = $this->tab_name[2];
		$modulecfindex = $this->tab_name_index[$modulecftable];

		$query = "from $moduletable
			inner join $modulecftable as $modulecftable on $modulecftable.$modulecfindex=$moduletable.$moduleindex
			inner join mycrm_crmentity on mycrm_crmentity.crmid=$moduletable.$moduleindex
			left join mycrm_groups as mycrm_groups$module on mycrm_groups$module.groupid = mycrm_crmentity.smownerid
			left join mycrm_users as mycrm_users".$module." on mycrm_users".$module.".id = mycrm_crmentity.smownerid
			left join mycrm_groups on mycrm_groups.groupid = mycrm_crmentity.smownerid
			left join mycrm_users on mycrm_users.id = mycrm_crmentity.smownerid
			left join mycrm_users as mycrm_lastModifiedByVendors on mycrm_lastModifiedByVendors.id = mycrm_crmentity.modifiedby ";
		return $query;
	}

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule, $queryplanner) {

		$matrix = $queryplanner->newDependencyMatrix();

		$matrix->setDependency("mycrm_crmentityVendors",array("mycrm_usersVendors","mycrm_lastModifiedByVendors"));
		$matrix->setDependency("mycrm_vendor",array("mycrm_crmentityVendors","mycrm_vendorcf","mycrm_email_trackVendors"));
		if (!$queryplanner->requireTable('mycrm_vendor', $matrix)) {
			return '';
		}
		$query = $this->getRelationQuery($module,$secmodule,"mycrm_vendor","vendorid", $queryplanner);
		// TODO Support query planner
		if ($queryplanner->requireTable("mycrm_crmentityVendors",$matrix)){
		    $query .=" left join mycrm_crmentity as mycrm_crmentityVendors on mycrm_crmentityVendors.crmid=mycrm_vendor.vendorid and mycrm_crmentityVendors.deleted=0";
		}
		if ($queryplanner->requireTable("mycrm_vendorcf")){
		    $query .=" left join mycrm_vendorcf on mycrm_vendorcf.vendorid = mycrm_crmentityVendors.crmid";
		}
		if ($queryplanner->requireTable("mycrm_email_trackVendors")){
		    $query .=" LEFT JOIN mycrm_email_track AS mycrm_email_trackVendors ON mycrm_email_trackVendors.crmid = mycrm_vendor.vendorid";
		}
		if ($queryplanner->requireTable("mycrm_usersVendors")){
		    $query .=" left join mycrm_users as mycrm_usersVendors on mycrm_usersVendors.id = mycrm_crmentityVendors.smownerid";
		}
		if ($queryplanner->requireTable("mycrm_lastModifiedByVendors")){
		    $query .=" left join mycrm_users as mycrm_lastModifiedByVendors on mycrm_lastModifiedByVendors.id = mycrm_crmentityVendors.modifiedby ";
		}
        if ($queryplanner->requireTable("mycrm_createdbyVendors")){
			$query .= " left join mycrm_users as mycrm_createdbyVendors on mycrm_createdbyVendors.id = mycrm_crmentityVendors.smcreatorid ";
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
			"Products" =>array("mycrm_products"=>array("vendor_id","productid"),"mycrm_vendor"=>"vendorid"),
			"PurchaseOrder" =>array("mycrm_purchaseorder"=>array("vendorid","purchaseorderid"),"mycrm_vendor"=>"vendorid"),
			"Contacts" =>array("mycrm_vendorcontactrel"=>array("vendorid","contactid"),"mycrm_vendor"=>"vendorid"),
			"Emails" => array("mycrm_seactivityrel"=>array("crmid","activityid"),"mycrm_vendor"=>"vendorid"),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink all the dependent entities of the given Entity by Id
	function unlinkDependencies($module, $id) {
		global $log;
		//Deleting Vendor related PO.
		$po_q = 'SELECT mycrm_crmentity.crmid FROM mycrm_crmentity
			INNER JOIN mycrm_purchaseorder ON mycrm_crmentity.crmid=mycrm_purchaseorder.purchaseorderid
			INNER JOIN mycrm_vendor ON mycrm_vendor.vendorid=mycrm_purchaseorder.vendorid
			WHERE mycrm_crmentity.deleted=0 AND mycrm_purchaseorder.vendorid=?';
		$po_res = $this->db->pquery($po_q, array($id));
		$po_ids_list = array();
		for($k=0;$k < $this->db->num_rows($po_res);$k++)
		{
			$po_id = $this->db->query_result($po_res,$k,"crmid");
			$po_ids_list[] = $po_id;
			$sql = 'UPDATE mycrm_crmentity SET deleted = 1 WHERE crmid = ?';
			$this->db->pquery($sql, array($po_id));
		}
		//Backup deleted Vendors related Potentials.
		$params = array($id, RB_RECORD_UPDATED, 'mycrm_crmentity', 'deleted', 'crmid', implode(",", $po_ids_list));
		$this->db->pquery('INSERT INTO mycrm_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);

		//Backup Product-Vendor Relation
		$pro_q = 'SELECT productid FROM mycrm_products WHERE vendor_id=?';
		$pro_res = $this->db->pquery($pro_q, array($id));
		if ($this->db->num_rows($pro_res) > 0) {
			$pro_ids_list = array();
			for($k=0;$k < $this->db->num_rows($pro_res);$k++)
			{
				$pro_ids_list[] = $this->db->query_result($pro_res,$k,"productid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'mycrm_products', 'vendor_id', 'productid', implode(",", $pro_ids_list));
			$this->db->pquery('INSERT INTO mycrm_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
		}
		//Deleting Product-Vendor Relation.
		$pro_q = 'UPDATE mycrm_products SET vendor_id = 0 WHERE vendor_id = ?';
		$this->db->pquery($pro_q, array($id));

		/*//Backup Contact-Vendor Relaton
		$con_q = 'SELECT contactid FROM mycrm_vendorcontactrel WHERE vendorid = ?';
		$con_res = $this->db->pquery($con_q, array($id));
		if ($this->db->num_rows($con_res) > 0) {
			for($k=0;$k < $this->db->num_rows($con_res);$k++)
			{
				$con_id = $this->db->query_result($con_res,$k,"contactid");
				$params = array($id, RB_RECORD_DELETED, 'mycrm_vendorcontactrel', 'vendorid', 'contactid', $con_id);
				$this->db->pquery('INSERT INTO mycrm_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
			}
		}
		//Deleting Contact-Vendor Relaton
		$vc_sql = 'DELETE FROM mycrm_vendorcontactrel WHERE vendorid=?';
		$this->db->pquery($vc_sql, array($id));*/

		parent::unlinkDependencies($module, $id);
	}

	function save_related_module($module, $crmid, $with_module, $with_crmids) {
		$adb = PearDatabase::getInstance();

		if(!is_array($with_crmids)) $with_crmids = Array($with_crmids);
		foreach($with_crmids as $with_crmid) {
			if($with_module == 'Contacts')
				$adb->pquery("insert into mycrm_vendorcontactrel values (?,?)", array($crmid, $with_crmid));
			elseif($with_module == 'Products')
				$adb->pquery("update mycrm_products set vendor_id=? where productid=?", array($crmid, $with_crmid));
			else {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid);
			}
		}
	}

    // Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;
        if($return_module == 'Contacts') {
			$sql = 'DELETE FROM mycrm_vendorcontactrel WHERE vendorid=? AND contactid=?';
			$this->db->pquery($sql, array($id,$return_id));
		} else {
			parent::unlinkRelationship($id, $return_module, $return_id);
		}
	}

}
?>
