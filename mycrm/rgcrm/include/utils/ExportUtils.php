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


/**	function used to get the permitted blocks
 *	@param string $module - module name
 *	@param string $disp_view - view name, this may be create_view, edit_view or detail_view
 *	@return string $blockid_list - list of block ids within the paranthesis with comma seperated
 */
function getPermittedBlocks($module, $disp_view)
{
	global $adb, $log;
	$log->debug("Entering into the function getPermittedBlocks($module, $disp_view)");

        $tabid = getTabid($module);
        $block_detail = Array();
        $query="select blockid,blocklabel,show_title from mycrm_blocks where tabid=? and $disp_view=0 and visible = 0 order by sequence";
        $result = $adb->pquery($query, array($tabid));
        $noofrows = $adb->num_rows($result);
	$blockid_list ='(';
	for($i=0; $i<$noofrows; $i++)
	{
		$blockid = $adb->query_result($result,$i,"blockid");
		if($i != 0)
			$blockid_list .= ', ';
		$blockid_list .= $blockid;
		$block_label[$blockid] = $adb->query_result($result,$i,"blocklabel");
	}
	$blockid_list .= ')';

	$log->debug("Exit from the function getPermittedBlocks($module, $disp_view). Return value = $blockid_list");
	return $blockid_list;
}

/**	function used to get the query which will list the permitted fields
 *	@param string $module - module name
 *	@param string $disp_view - view name, this may be create_view, edit_view or detail_view
 *	@return string $sql - query to get the list of fields which are permitted to the current user
 */
function getPermittedFieldsQuery($module, $disp_view)
{
	global $adb, $log;
	$log->debug("Entering into the function getPermittedFieldsQuery($module, $disp_view)");

	global $current_user;
	require('user_privileges/user_privileges_'.$current_user->id.'.php');

	//To get the permitted blocks
	$blockid_list = getPermittedBlocks($module, $disp_view);

        $tabid = getTabid($module);
	if($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0 || $module == "Users")
	{
 		$sql = "SELECT mycrm_field.columnname, mycrm_field.fieldlabel, mycrm_field.tablename FROM mycrm_field WHERE mycrm_field.tabid=".$tabid." AND mycrm_field.block IN $blockid_list AND mycrm_field.displaytype IN (1,2,4,5) and mycrm_field.presence in (0,2) ORDER BY block,sequence";
  	}
  	else
  	{
		$profileList = getCurrentUserProfileList();
		$sql = "SELECT mycrm_field.columnname, mycrm_field.fieldlabel, mycrm_field.tablename FROM mycrm_field INNER JOIN mycrm_profile2field ON mycrm_profile2field.fieldid=mycrm_field.fieldid INNER JOIN mycrm_def_org_field ON mycrm_def_org_field.fieldid=mycrm_field.fieldid WHERE mycrm_field.tabid=".$tabid." AND mycrm_field.block IN ".$blockid_list." AND mycrm_field.displaytype IN (1,2,4,5) AND mycrm_profile2field.visible=0 AND mycrm_def_org_field.visible=0 AND mycrm_profile2field.profileid IN (". implode(",", $profileList) .") and mycrm_field.presence in (0,2) GROUP BY mycrm_field.fieldid ORDER BY block,sequence";
	}

	$log->debug("Exit from the function getPermittedFieldsQuery($module, $disp_view). Return value = $sql");
	return $sql;
}

/**	function used to get the list of fields from the input query as a comma seperated string
 *	@param string $query - field table query which contains the list of fields
 *	@return string $fields - list of fields as a comma seperated string
 */
function getFieldsListFromQuery($query)
{
	global $adb, $log;
	$log->debug("Entering into the function getFieldsListFromQuery($query)");

	$result = $adb->query($query);
	$num_rows = $adb->num_rows($result);

	for($i=0; $i < $num_rows;$i++)
	{
		$columnName = $adb->query_result($result,$i,"columnname");
		$fieldlabel = $adb->query_result($result,$i,"fieldlabel");
		$tablename = $adb->query_result($result,$i,"tablename");

		//HANDLE HERE - Mismatch fieldname-tablename in field table, in future we have to avoid these if elses
		if($columnName == 'smownerid')//for all assigned to user name
		{
			$fields .= "case when (mycrm_users.user_name not like '') then mycrm_users.user_name else mycrm_groups.groupname end as '".$fieldlabel."',";
		}
		elseif($tablename == 'mycrm_account' && $columnName == 'parentid')//Account - Member Of
		{
			 $fields .= "mycrm_account2.accountname as '".$fieldlabel."',";
		}
		elseif($tablename == 'mycrm_contactdetails' && $columnName == 'accountid')//Contact - Account Name
		{
			$fields .= "mycrm_account.accountname as '".$fieldlabel."',";
		}
		elseif($tablename == 'mycrm_contactdetails' && $columnName == 'reportsto')//Contact - Reports To
		{
			$fields .= " concat(mycrm_contactdetails2.lastname,' ',mycrm_contactdetails2.firstname) as 'Reports To Contact',";
		}
		elseif($tablename == 'mycrm_potential' && $columnName == 'related_to')//Potential - Related to (changed for B2C model support)
		{
			$fields .= "mycrm_potential.related_to as '".$fieldlabel."',";
		}
		elseif($tablename == 'mycrm_potential' && $columnName == 'campaignid')//Potential - Campaign Source
		{
			$fields .= "mycrm_campaign.campaignname as '".$fieldlabel."',";
		}
		elseif($tablename == 'mycrm_seproductsrel' && $columnName == 'crmid')//Product - Related To
		{
			$fields .= "case mycrm_crmentityRelatedTo.setype
					when 'Leads' then concat('Leads :::: ',mycrm_ProductRelatedToLead.lastname,' ',mycrm_ProductRelatedToLead.firstname)
					when 'Accounts' then concat('Accounts :::: ',mycrm_ProductRelatedToAccount.accountname)
					when 'Potentials' then concat('Potentials :::: ',mycrm_ProductRelatedToPotential.potentialname)
				    End as 'Related To',";
		}
		elseif($tablename == 'mycrm_products' && $columnName == 'contactid')//Product - Contact
		{
			$fields .= " concat(mycrm_contactdetails.lastname,' ',mycrm_contactdetails.firstname) as 'Contact Name',";
		}
		elseif($tablename == 'mycrm_products' && $columnName == 'vendor_id')//Product - Vendor Name
		{
			$fields .= "mycrm_vendor.vendorname as '".$fieldlabel."',";
		}
		elseif($tablename == 'mycrm_producttaxrel' && $columnName == 'taxclass')//avoid product - taxclass
		{
			$fields .= "";
		}
		elseif($tablename == 'mycrm_attachments' && $columnName == 'name')//Emails filename
		{
			$fields .= $tablename.".name as '".$fieldlabel."',";
		}
		//By Pavani...Handling mismatch field and table name for trouble tickets
      	elseif($tablename == 'mycrm_troubletickets' && $columnName == 'product_id')//Ticket - Product
        {
			$fields .= "mycrm_products.productname as '".$fieldlabel."',";
        }
        elseif($tablename == 'mycrm_notes' && ($columnName == 'filename' || $columnName == 'filetype' || $columnName == 'filesize' || $columnName == 'filelocationtype' || $columnName == 'filestatus' || $columnName == 'filedownloadcount' ||$columnName == 'folderid')){
			continue;
		}
		elseif(($tablename == 'mycrm_invoice' || $tablename == 'mycrm_quotes' || $tablename == 'mycrm_salesorder')&& $columnName == 'accountid') {
			$fields .= 'concat("Accounts::::",mycrm_account.accountname) as "'.$fieldlabel.'",';
		}
		elseif(($tablename == 'mycrm_invoice' || $tablename == 'mycrm_quotes' || $tablename == 'mycrm_salesorder' || $tablename == 'mycrm_purchaseorder') && $columnName == 'contactid') {
			$fields .= 'concat("Contacts::::",mycrm_contactdetails.lastname," ",mycrm_contactdetails.firstname) as "'.$fieldlabel.'",';
		}
		elseif($tablename == 'mycrm_invoice' && $columnName == 'salesorderid') {
			$fields .= 'concat("SalesOrder::::",mycrm_salesorder.subject) as "'.$fieldlabel.'",';
		}
		elseif(($tablename == 'mycrm_quotes' || $tablename == 'mycrm_salesorder') && $columnName == 'potentialid') {
			$fields .= 'concat("Potentials::::",mycrm_potential.potentialname) as "'.$fieldlabel.'",';
		}
		elseif($tablename == 'mycrm_quotes' && $columnName == 'inventorymanager') {
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'mycrm_inventoryManager.first_name', 'last_name' => 'mycrm_inventoryManager.last_name'), 'Users');
			$fields .= $userNameSql. ' as "'.$fieldlabel.'",';
		}
		elseif($tablename == 'mycrm_salesorder' && $columnName == 'quoteid') {
			$fields .= 'concat("Quotes::::",mycrm_quotes.subject) as "'.$fieldlabel.'",';
		}
		elseif($tablename == 'mycrm_purchaseorder' && $columnName == 'vendorid') {
			$fields .= 'concat("Vendors::::",mycrm_vendor.vendorname) as "'.$fieldlabel.'",';
		}
		else
		{
			$fields .= $tablename.".".$columnName. " as '" .$fieldlabel."',";
		}
	}
	$fields = trim($fields,",");

	$log->debug("Exit from the function getFieldsListFromQuery($query). Return value = $fields");
	return $fields;
}



?>
