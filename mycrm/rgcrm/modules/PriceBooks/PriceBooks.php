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

class PriceBooks extends CRMEntity {
	var $log;
	var $db;
	var $table_name = "mycrm_pricebook";
	var $table_index= 'pricebookid';
	var $tab_name = Array('mycrm_crmentity','mycrm_pricebook','mycrm_pricebookcf');
	var $tab_name_index = Array('mycrm_crmentity'=>'crmid','mycrm_pricebook'=>'pricebookid','mycrm_pricebookcf'=>'pricebookid');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('mycrm_pricebookcf', 'pricebookid');
	var $column_fields = Array();

	var $sortby_fields = Array('bookname');

        // This is the list of fields that are in the lists.
	var $list_fields = Array(
                                'Price Book Name'=>Array('pricebook'=>'bookname'),
                                'Active'=>Array('pricebook'=>'active')
                                );

	var $list_fields_name = Array(
                                        'Price Book Name'=>'bookname',
                                        'Active'=>'active'
                                     );
	var $list_link_field= 'bookname';

	var $search_fields = Array(
                                'Price Book Name'=>Array('pricebook'=>'bookname')
                                );
	var $search_fields_name = Array(
                                        'Price Book Name'=>'bookname'
                                     );

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'bookname';
	var $default_sort_order = 'ASC';

	var $mandatory_fields = Array('bookname','currency_id','pricebook_no','createdtime' ,'modifiedtime');

	// For Alphabetical search
	var $def_basicsearch_col = 'bookname';

	/**	Constructor which will set the column_fields in this object
	 */
	function PriceBooks() {
		$this->log =LoggerManager::getLogger('pricebook');
		$this->log->debug("Entering PriceBooks() method ...");
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('PriceBooks');
		$this->log->debug("Exiting PriceBook method ...");
	}

	function save_module($module)
	{
		// Update the list prices in the price book with the unit price, if the Currency has been changed
		$this->updateListPrices();
	}

	/* Function to Update the List prices for all the products of a current price book
	   with its Unit price, if the Currency for Price book has changed. */
	function updateListPrices() {
		global $log, $adb;
		$log->debug("Entering function updateListPrices...");
		$pricebook_currency = $this->column_fields['currency_id'];
		$prod_res = $adb->pquery("select * from mycrm_pricebookproductrel where pricebookid=? AND usedcurrency != ?",
							array($this->id, $pricebook_currency));
		$numRows = $adb->num_rows($prod_res);

		for($i=0;$i<$numRows;$i++) {
			$product_id = $adb->query_result($prod_res,$i,'productid');
			$list_price = $adb->query_result($prod_res,$i,'listprice');
			$used_currency = $adb->query_result($prod_res,$i,'usedcurrency');
			$product_currency_info = getCurrencySymbolandCRate($used_currency);
			$product_conv_rate = $product_currency_info['rate'];
			$pricebook_currency_info = getCurrencySymbolandCRate($pricebook_currency);
			$pb_conv_rate = $pricebook_currency_info['rate'];
			$conversion_rate = $pb_conv_rate / $product_conv_rate;
			$computed_list_price = $list_price * $conversion_rate;

			$query = "update mycrm_pricebookproductrel set listprice=?, usedcurrency=? where pricebookid=? and productid=?";
			$params = array($computed_list_price, $pricebook_currency, $this->id, $product_id);
			$adb->pquery($query, $params);
		}
		$log->debug("Exiting function updateListPrices...");
	}

	/**	function used to get the products which are related to the pricebook
	 *	@param int $id - pricebook id
         *      @return array - return an array which will be returned from the function getPriceBookRelatedProducts
        **/
	function get_pricebook_products($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_pricebook_products(".$id.") method ...");
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
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='submit' name='button' onclick=\"this.form.action.value='AddProductsToPriceBook';this.form.module.value='$related_module';this.form.return_module.value='$currentModule';this.form.return_action.value='PriceBookDetailView'\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
		}

		$query = 'SELECT mycrm_products.productid, mycrm_products.productname, mycrm_products.productcode, mycrm_products.commissionrate,
						mycrm_products.qty_per_unit, mycrm_products.unit_price, mycrm_crmentity.crmid, mycrm_crmentity.smownerid,
						mycrm_pricebookproductrel.listprice
				FROM mycrm_products
				INNER JOIN mycrm_pricebookproductrel ON mycrm_products.productid = mycrm_pricebookproductrel.productid
				INNER JOIN mycrm_crmentity on mycrm_crmentity.crmid = mycrm_products.productid
				INNER JOIN mycrm_pricebook on mycrm_pricebook.pricebookid = mycrm_pricebookproductrel.pricebookid
				LEFT JOIN mycrm_users ON mycrm_users.id=mycrm_crmentity.smownerid
				LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid '
				. getNonAdminAccessControlQuery($related_module, $current_user) .'
				WHERE mycrm_pricebook.pricebookid = '.$id.' and mycrm_crmentity.deleted = 0';

		$this->retrieve_entity_info($id,$this_module);
		$return_value = getPriceBookRelatedProducts($query,$this,$returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_pricebook_products method ...");
		return $return_value;
	}

	/**	function used to get the services which are related to the pricebook
	 *	@param int $id - pricebook id
         *      @return array - return an array which will be returned from the function getPriceBookRelatedServices
        **/
	function get_pricebook_services($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_pricebook_services(".$id.") method ...");
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
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='submit' name='button' onclick=\"this.form.action.value='AddServicesToPriceBook';this.form.module.value='$related_module';this.form.return_module.value='$currentModule';this.form.return_action.value='PriceBookDetailView'\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
		}

		$query = 'SELECT mycrm_service.serviceid, mycrm_service.servicename, mycrm_service.commissionrate,
					mycrm_service.qty_per_unit, mycrm_service.unit_price, mycrm_crmentity.crmid, mycrm_crmentity.smownerid,
					mycrm_pricebookproductrel.listprice
			FROM mycrm_service
			INNER JOIN mycrm_pricebookproductrel on mycrm_service.serviceid = mycrm_pricebookproductrel.productid
			INNER JOIN mycrm_crmentity on mycrm_crmentity.crmid = mycrm_service.serviceid
			INNER JOIN mycrm_pricebook on mycrm_pricebook.pricebookid = mycrm_pricebookproductrel.pricebookid
			LEFT JOIN mycrm_users ON mycrm_users.id=mycrm_crmentity.smownerid
			LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid '
			. getNonAdminAccessControlQuery($related_module, $current_user) .'
			WHERE mycrm_pricebook.pricebookid = '.$id.' and mycrm_crmentity.deleted = 0';

		$this->retrieve_entity_info($id,$this_module);
		$return_value = $other->getPriceBookRelatedServices($query,$this,$returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_pricebook_services method ...");
		return $return_value;
	}

	/**	function used to get whether the pricebook has related with a product or not
	 *	@param int $id - product id
	 *	@return true or false - if there are no pricebooks available or associated pricebooks for the product is equal to total number of pricebooks then return false, else return true
	 */
	function get_pricebook_noproduct($id)
	{
		global $log;
		$log->debug("Entering get_pricebook_noproduct(".$id.") method ...");

		$query = "select mycrm_crmentity.crmid, mycrm_pricebook.* from mycrm_pricebook inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_pricebook.pricebookid where mycrm_crmentity.deleted=0";
		$result = $this->db->pquery($query, array());
		$no_count = $this->db->num_rows($result);
		if($no_count !=0)
		{
       	 	$pb_query = 'select mycrm_crmentity.crmid, mycrm_pricebook.pricebookid,mycrm_pricebookproductrel.productid from mycrm_pricebook inner join mycrm_crmentity on mycrm_crmentity.crmid=mycrm_pricebook.pricebookid inner join mycrm_pricebookproductrel on mycrm_pricebookproductrel.pricebookid=mycrm_pricebook.pricebookid where mycrm_crmentity.deleted=0 and mycrm_pricebookproductrel.productid=?';
			$result_pb = $this->db->pquery($pb_query, array($id));
			if($no_count == $this->db->num_rows($result_pb))
			{
				$log->debug("Exiting get_pricebook_noproduct method ...");
				return false;
			}
			elseif($this->db->num_rows($result_pb) == 0)
			{
				$log->debug("Exiting get_pricebook_noproduct method ...");
				return true;
			}
			elseif($this->db->num_rows($result_pb) < $no_count)
			{
				$log->debug("Exiting get_pricebook_noproduct method ...");
				return true;
			}
		}
		else
		{
			$log->debug("Exiting get_pricebook_noproduct method ...");
			return false;
		}
	}

	/*
	 * Function to get the primary query part of a report
	 * @param - $module Primary module name
	 * returns the query string formed on fetching the related data for report for primary module
	 */
	function generateReportsQuery($module,$queryplanner){
	 			$moduletable = $this->table_name;
	 			$moduleindex = $this->table_index;
				$modulecftable = $this->customFieldTable[0];
				$modulecfindex = $this->customFieldTable[1];

				$cfquery = '';
				if(isset($modulecftable) && $queryplanner->requireTable($modulecftable) ){
					$cfquery = "inner join $modulecftable as $modulecftable on $modulecftable.$modulecfindex=$moduletable.$moduleindex";
				}

	 			$query = "from $moduletable $cfquery
					inner join mycrm_crmentity on mycrm_crmentity.crmid=$moduletable.$moduleindex";
				if ($queryplanner->requireTable("mycrm_currency_info$module")){
				    $query .= "  left join mycrm_currency_info as mycrm_currency_info$module on mycrm_currency_info$module.id = $moduletable.currency_id";
				}
				if ($queryplanner->requireTable("mycrm_groups$module")){
				    $query .= " left join mycrm_groups as mycrm_groups$module on mycrm_groups$module.groupid = mycrm_crmentity.smownerid";
				}
				if ($queryplanner->requireTable("mycrm_users$module")){
				    $query .= " left join mycrm_users as mycrm_users$module on mycrm_users$module.id = mycrm_crmentity.smownerid";
				}
				$query .= " left join mycrm_groups on mycrm_groups.groupid = mycrm_crmentity.smownerid";
				$query .= " left join mycrm_users on mycrm_users.id = mycrm_crmentity.smownerid";

				if ($queryplanner->requireTable("mycrm_lastModifiedByPriceBooks")){
				    $query .= " left join mycrm_users as mycrm_lastModifiedByPriceBooks on mycrm_lastModifiedByPriceBooks.id = mycrm_crmentity.modifiedby ";
				}
				return $query;
	}

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule,$queryplanner) {

		$matrix = $queryplanner->newDependencyMatrix();

		$matrix->setDependency("mycrm_crmentityPriceBooks",array("mycrm_usersPriceBooks","mycrm_groupsPriceBooks"));
		$matrix->setDependency("mycrm_pricebook",array("mycrm_crmentityPriceBooks","mycrm_currency_infoPriceBooks"));
		if (!$queryplanner->requireTable('mycrm_pricebook', $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module,$secmodule,"mycrm_pricebook","pricebookid", $queryplanner);
		// TODO Support query planner
		if ($queryplanner->requireTable("mycrm_crmentityPriceBooks",$matrix)){
		$query .=" left join mycrm_crmentity as mycrm_crmentityPriceBooks on mycrm_crmentityPriceBooks.crmid=mycrm_pricebook.pricebookid and mycrm_crmentityPriceBooks.deleted=0";
		}
		if ($queryplanner->requireTable("mycrm_currency_infoPriceBooks")){
		$query .=" left join mycrm_currency_info as mycrm_currency_infoPriceBooks on mycrm_currency_infoPriceBooks.id = mycrm_pricebook.currency_id";
		}
		if ($queryplanner->requireTable("mycrm_usersPriceBooks")){
		    $query .=" left join mycrm_users as mycrm_usersPriceBooks on mycrm_usersPriceBooks.id = mycrm_crmentityPriceBooks.smownerid";
		}
		if ($queryplanner->requireTable("mycrm_groupsPriceBooks")){
		    $query .=" left join mycrm_groups as mycrm_groupsPriceBooks on mycrm_groupsPriceBooks.groupid = mycrm_crmentityPriceBooks.smownerid";
		}
		if ($queryplanner->requireTable("mycrm_lastModifiedByPriceBooks")){
		    $query .=" left join mycrm_users as mycrm_lastModifiedByPriceBooks on mycrm_lastModifiedByPriceBooks.id = mycrm_crmentityPriceBooks.smownerid";
		}
        if ($queryplanner->requireTable("mycrm_createdbyPriceBooks")){
			$query .= " left join mycrm_users as mycrm_createdbyPriceBooks on mycrm_createdbyPriceBooks.id = mycrm_crmentityPriceBooks.smcreatorid ";
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
			"Products" => array("mycrm_pricebookproductrel"=>array("pricebookid","productid"),"mycrm_pricebook"=>"pricebookid"),
			"Services" => array("mycrm_pricebookproductrel"=>array("pricebookid","productid"),"mycrm_pricebook"=>"pricebookid"),
		);
		return $rel_tables[$secmodule];
	}

}
?>
