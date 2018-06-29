<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/
class Products extends CRMEntity {
	var $db, $log; // Used in class functions of CRMEntity

	var $table_name = 'mycrm_products';
	var $table_index= 'productid';
    var $column_fields = Array();

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('mycrm_productcf','productid');

	var $tab_name = Array('mycrm_crmentity','mycrm_products','mycrm_productcf');

	var $tab_name_index = Array('mycrm_crmentity'=>'crmid','mycrm_products'=>'productid','mycrm_productcf'=>'productid','mycrm_seproductsrel'=>'productid','mycrm_producttaxrel'=>'productid');



	// This is the list of mycrm_fields that are in the lists.
	var $list_fields = Array(
		'Product Name'=>Array('products'=>'productname'),
		'Part Number'=>Array('products'=>'productcode'),
		'Commission Rate'=>Array('products'=>'commissionrate'),
		'Qty/Unit'=>Array('products'=>'qty_per_unit'),
		'Unit Price'=>Array('products'=>'unit_price')
	);
	var $list_fields_name = Array(
		'Product Name'=>'productname',
		'Part Number'=>'productcode',
		'Commission Rate'=>'commissionrate',
		'Qty/Unit'=>'qty_per_unit',
		'Unit Price'=>'unit_price'
	);

	var $list_link_field= 'productname';

	var $search_fields = Array(
		'Product Name'=>Array('products'=>'productname'),
		'Part Number'=>Array('products'=>'productcode'),
		'Unit Price'=>Array('products'=>'unit_price')
	);
	var $search_fields_name = Array(
		'Product Name'=>'productname',
		'Part Number'=>'productcode',
		'Unit Price'=>'unit_price'
	);

    var $required_fields = Array(
            'productname'=>1
    );

	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	var $sortby_fields = Array();
	var $def_basicsearch_col = 'productname';

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'productname';
	var $default_sort_order = 'ASC';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to mycrm_field.fieldname values.
	var $mandatory_fields = Array('createdtime', 'modifiedtime', 'productname', 'assigned_user_id');
	 // Josh added for importing and exporting -added in patch2
    var $unit_price;

	/**	Constructor which will set the column_fields in this object
	 */
	function Products() {
		$this->log =LoggerManager::getLogger('product');
		$this->log->debug("Entering Products() method ...");
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Products');
		$this->log->debug("Exiting Product method ...");
	}

	function save_module($module)
	{
		//Inserting into product_taxrel table
		if($_REQUEST['ajxaction'] != 'DETAILVIEW' && $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates')
		{
			$this->insertTaxInformation('mycrm_producttaxrel', 'Products');
			$this->insertPriceInformation('mycrm_productcurrencyrel', 'Products');
		}

		// Update unit price value in mycrm_productcurrencyrel
		$this->updateUnitPrice();
		//Inserting into attachments
		$this->insertIntoAttachment($this->id,'Products');

	}

	/**	function to save the product tax information in mycrm_producttaxrel table
	 *	@param string $tablename - mycrm_tablename to save the product tax relationship (producttaxrel)
	 *	@param string $module	 - current module name
	 *	$return void
	*/
	function insertTaxInformation($tablename, $module)
	{
		global $adb, $log;
		$log->debug("Entering into insertTaxInformation($tablename, $module) method ...");
		$tax_details = getAllTaxes();

		$tax_per = '';
		//Save the Product - tax relationship if corresponding tax check box is enabled
		//Delete the existing tax if any
		if($this->mode == 'edit')
		{
			for($i=0;$i<count($tax_details);$i++)
			{
				$taxid = getTaxId($tax_details[$i]['taxname']);
				$sql = "delete from mycrm_producttaxrel where productid=? and taxid=?";
				$adb->pquery($sql, array($this->id,$taxid));
			}
		}
		for($i=0;$i<count($tax_details);$i++)
		{
			$tax_name = $tax_details[$i]['taxname'];
			$tax_checkname = $tax_details[$i]['taxname']."_check";
			if($_REQUEST[$tax_checkname] == 'on' || $_REQUEST[$tax_checkname] == 1)
			{
				$taxid = getTaxId($tax_name);
				$tax_per = $_REQUEST[$tax_name];
				if($tax_per == '')
				{
					$log->debug("Tax selected but value not given so default value will be saved.");
					$tax_per = getTaxPercentage($tax_name);
				}

				$log->debug("Going to save the Product - $tax_name tax relationship");

				$query = "insert into mycrm_producttaxrel values(?,?,?)";
				$adb->pquery($query, array($this->id,$taxid,$tax_per));
			}
		}

		$log->debug("Exiting from insertTaxInformation($tablename, $module) method ...");
	}

	/**	function to save the product price information in mycrm_productcurrencyrel table
	 *	@param string $tablename - mycrm_tablename to save the product currency relationship (productcurrencyrel)
	 *	@param string $module	 - current module name
	 *	$return void
	*/
	function insertPriceInformation($tablename, $module)
	{
		global $adb, $log, $current_user;
		$log->debug("Entering into insertPriceInformation($tablename, $module) method ...");
		//removed the update of currency_id based on the logged in user's preference : fix 6490

		$currency_details = getAllCurrencies('all');

		//Delete the existing currency relationship if any
		if($this->mode == 'edit' && $_REQUEST['action'] !== 'MassEditSave')
		{
			for($i=0;$i<count($currency_details);$i++)
			{
				$curid = $currency_details[$i]['curid'];
				$sql = "delete from mycrm_productcurrencyrel where productid=? and currencyid=?";
				$adb->pquery($sql, array($this->id,$curid));
			}
		}

		$product_base_conv_rate = getBaseConversionRateForProduct($this->id, $this->mode);
		$currencySet = 0;
		//Save the Product - Currency relationship if corresponding currency check box is enabled
		for($i=0;$i<count($currency_details);$i++)
		{
			$curid = $currency_details[$i]['curid'];
			$curname = $currency_details[$i]['currencylabel'];
			$cur_checkname = 'cur_' . $curid . '_check';
			$cur_valuename = 'curname' . $curid;

			$requestPrice = CurrencyField::convertToDBFormat($_REQUEST['unit_price'], null, true);
			$actualPrice = CurrencyField::convertToDBFormat($_REQUEST[$cur_valuename], null, true);
			if($_REQUEST[$cur_checkname] == 'on' || $_REQUEST[$cur_checkname] == 1)
			{
				$conversion_rate = $currency_details[$i]['conversionrate'];
				$actual_conversion_rate = $product_base_conv_rate * $conversion_rate;
				$converted_price = $actual_conversion_rate * $requestPrice;

				$log->debug("Going to save the Product - $curname currency relationship");

				$query = "insert into mycrm_productcurrencyrel values(?,?,?,?)";
				$adb->pquery($query, array($this->id,$curid,$converted_price,$actualPrice));

				// Update the Product information with Base Currency choosen by the User.
				if ($_REQUEST['base_currency'] == $cur_valuename) {
					$currencySet = 1;
					$adb->pquery("update mycrm_products set currency_id=?, unit_price=? where productid=?", array($curid, $actualPrice, $this->id));
				}
			}
			if(!$currencySet){
				$curid = fetchCurrency($current_user->id);
				$adb->pquery("update mycrm_products set currency_id=? where productid=?", array($curid, $this->id));
			}
		}

		$log->debug("Exiting from insertPriceInformation($tablename, $module) method ...");
	}

	function updateUnitPrice() {
		$prod_res = $this->db->pquery("select unit_price, currency_id from mycrm_products where productid=?", array($this->id));
		$prod_unit_price = $this->db->query_result($prod_res, 0, 'unit_price');
		$prod_base_currency = $this->db->query_result($prod_res, 0, 'currency_id');

		$query = "update mycrm_productcurrencyrel set actual_price=? where productid=? and currencyid=?";
		$params = array($prod_unit_price, $this->id, $prod_base_currency);
		$this->db->pquery($query, $params);
	}

	function insertIntoAttachment($id,$module)
	{
		global  $log,$adb;
		$log->debug("Entering into insertIntoAttachment($id,$module) method.");

		$file_saved = false;
		foreach($_FILES as $fileindex => $files)
		{
			if($files['name'] != '' && $files['size'] > 0)
			{
			      if($_REQUEST[$fileindex.'_hidden'] != '')
				      $files['original_name'] = vtlib_purify($_REQUEST[$fileindex.'_hidden']);
			      else
				      $files['original_name'] = stripslashes($files['name']);
			      $files['original_name'] = str_replace('"','',$files['original_name']);
				$file_saved = $this->uploadAndSaveFile($id,$module,$files);
			}
		}

		//Updating image information in main table of products
		$existingImageSql = 'SELECT name FROM mycrm_seattachmentsrel INNER JOIN mycrm_attachments ON
								mycrm_seattachmentsrel.attachmentsid = mycrm_attachments.attachmentsid LEFT JOIN mycrm_products ON
								mycrm_products.productid = mycrm_seattachmentsrel.crmid WHERE mycrm_seattachmentsrel.crmid = ?';
		$existingImages = $adb->pquery($existingImageSql,array($id));
		$numOfRows = $adb->num_rows($existingImages);
		$productImageMap = array();

		for ($i = 0; $i < $numOfRows; $i++) {
			$imageName = $adb->query_result($existingImages, $i, "name");
			array_push($productImageMap, decode_html($imageName));
		}
		$commaSeperatedFileNames = implode(",", $productImageMap);

		$adb->pquery('UPDATE mycrm_products SET imagename = ? WHERE productid = ?',array($commaSeperatedFileNames,$id));

		//Remove the deleted mycrm_attachments from db - Products
		if($module == 'Products' && $_REQUEST['del_file_list'] != '')
		{
			$del_file_list = explode("###",trim($_REQUEST['del_file_list'],"###"));
			foreach($del_file_list as $del_file_name)
			{
				$attach_res = $adb->pquery("select mycrm_attachments.attachmentsid from mycrm_attachments inner join mycrm_seattachmentsrel on mycrm_attachments.attachmentsid=mycrm_seattachmentsrel.attachmentsid where crmid=? and name=?", array($id,$del_file_name));
				$attachments_id = $adb->query_result($attach_res,0,'attachmentsid');

				$del_res1 = $adb->pquery("delete from mycrm_attachments where attachmentsid=?", array($attachments_id));
				$del_res2 = $adb->pquery("delete from mycrm_seattachmentsrel where attachmentsid=?", array($attachments_id));
			}
		}

		$log->debug("Exiting from insertIntoAttachment($id,$module) method.");
	}



	/**	function used to get the list of leads which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_leads($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_leads(".$id.") method ...");
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

		$query = "SELECT mycrm_leaddetails.leadid, mycrm_crmentity.crmid, mycrm_leaddetails.firstname, mycrm_leaddetails.lastname, mycrm_leaddetails.company, mycrm_leadaddress.phone, mycrm_leadsubdetails.website, mycrm_leaddetails.email, case when (mycrm_users.user_name not like \"\") then mycrm_users.user_name else mycrm_groups.groupname end as user_name, mycrm_crmentity.smownerid, mycrm_products.productname, mycrm_products.qty_per_unit, mycrm_products.unit_price, mycrm_products.expiry_date
			FROM mycrm_leaddetails
			INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_leaddetails.leadid
			INNER JOIN mycrm_leadaddress ON mycrm_leadaddress.leadaddressid = mycrm_leaddetails.leadid
			INNER JOIN mycrm_leadsubdetails ON mycrm_leadsubdetails.leadsubscriptionid = mycrm_leaddetails.leadid
			INNER JOIN mycrm_seproductsrel ON mycrm_seproductsrel.crmid=mycrm_leaddetails.leadid
			INNER JOIN mycrm_products ON mycrm_seproductsrel.productid = mycrm_products.productid
			INNER JOIN mycrm_leadscf ON mycrm_leaddetails.leadid = mycrm_leadscf.leadid
			LEFT JOIN mycrm_users ON mycrm_users.id = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			WHERE mycrm_crmentity.deleted = 0 AND mycrm_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_leads method ...");
		return $return_value;
	}

	/**	function used to get the list of accounts which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_accounts($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_accounts(".$id.") method ...");
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

		$query = "SELECT mycrm_account.accountid, mycrm_crmentity.crmid, mycrm_account.accountname, mycrm_accountbillads.bill_city, mycrm_account.website, mycrm_account.phone, case when (mycrm_users.user_name not like \"\") then mycrm_users.user_name else mycrm_groups.groupname end as user_name, mycrm_crmentity.smownerid, mycrm_products.productname, mycrm_products.qty_per_unit, mycrm_products.unit_price, mycrm_products.expiry_date
			FROM mycrm_account
			INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_account.accountid
			INNER JOIN mycrm_accountbillads ON mycrm_accountbillads.accountaddressid = mycrm_account.accountid
            LEFT JOIN mycrm_accountshipads ON mycrm_accountshipads.accountaddressid = mycrm_account.accountid
			INNER JOIN mycrm_seproductsrel ON mycrm_seproductsrel.crmid=mycrm_account.accountid
			INNER JOIN mycrm_products ON mycrm_seproductsrel.productid = mycrm_products.productid
			INNER JOIN mycrm_accountscf ON mycrm_account.accountid = mycrm_accountscf.accountid
			LEFT JOIN mycrm_users ON mycrm_users.id = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			WHERE mycrm_crmentity.deleted = 0 AND mycrm_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_accounts method ...");
		return $return_value;
	}

	/**	function used to get the list of contacts which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
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

		$query = "SELECT mycrm_contactdetails.firstname, mycrm_contactdetails.lastname, mycrm_contactdetails.title, mycrm_contactdetails.accountid, mycrm_contactdetails.email, mycrm_contactdetails.phone, mycrm_crmentity.crmid, case when (mycrm_users.user_name not like \"\") then mycrm_users.user_name else mycrm_groups.groupname end as user_name, mycrm_crmentity.smownerid, mycrm_products.productname, mycrm_products.qty_per_unit, mycrm_products.unit_price, mycrm_products.expiry_date,mycrm_account.accountname
			FROM mycrm_contactdetails
			INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_contactdetails.contactid
			INNER JOIN mycrm_seproductsrel ON mycrm_seproductsrel.crmid=mycrm_contactdetails.contactid
			INNER JOIN mycrm_contactaddress ON mycrm_contactdetails.contactid = mycrm_contactaddress.contactaddressid
			INNER JOIN mycrm_contactsubdetails ON mycrm_contactdetails.contactid = mycrm_contactsubdetails.contactsubscriptionid
			INNER JOIN mycrm_customerdetails ON mycrm_contactdetails.contactid = mycrm_customerdetails.customerid
			INNER JOIN mycrm_contactscf ON mycrm_contactdetails.contactid = mycrm_contactscf.contactid
			INNER JOIN mycrm_products ON mycrm_seproductsrel.productid = mycrm_products.productid
			LEFT JOIN mycrm_users ON mycrm_users.id = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_account ON mycrm_account.accountid = mycrm_contactdetails.accountid
			WHERE mycrm_crmentity.deleted = 0 AND mycrm_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
	}


	/**	function used to get the list of potentials which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
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

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "SELECT mycrm_potential.potentialid, mycrm_crmentity.crmid,
			mycrm_potential.potentialname, mycrm_account.accountname, mycrm_potential.related_to, mycrm_potential.contact_id,
			mycrm_potential.sales_stage, mycrm_potential.amount, mycrm_potential.closingdate,
			case when (mycrm_users.user_name not like '') then $userNameSql else
			mycrm_groups.groupname end as user_name, mycrm_crmentity.smownerid,
			mycrm_products.productname, mycrm_products.qty_per_unit, mycrm_products.unit_price,
			mycrm_products.expiry_date FROM mycrm_potential
			INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_potential.potentialid
			INNER JOIN mycrm_seproductsrel ON mycrm_seproductsrel.crmid = mycrm_potential.potentialid
			INNER JOIN mycrm_products ON mycrm_seproductsrel.productid = mycrm_products.productid
			INNER JOIN mycrm_potentialscf ON mycrm_potential.potentialid = mycrm_potentialscf.potentialid
			LEFT JOIN mycrm_account ON mycrm_potential.related_to = mycrm_account.accountid
			LEFT JOIN mycrm_contactdetails ON mycrm_potential.contact_id = mycrm_contactdetails.contactid
			LEFT JOIN mycrm_users ON mycrm_users.id = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			WHERE mycrm_crmentity.deleted = 0 AND mycrm_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_opportunities method ...");
		return $return_value;
	}

	/**	function used to get the list of tickets which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
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

		if($actions && getFieldVisibilityPermission($related_module, $current_user->id, 'product_id','readwrite') == '0') {
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
		$query = "SELECT  case when (mycrm_users.user_name not like \"\") then $userNameSql else mycrm_groups.groupname end as user_name, mycrm_users.id,
			mycrm_products.productid, mycrm_products.productname,
			mycrm_troubletickets.ticketid,
			mycrm_troubletickets.parent_id, mycrm_troubletickets.title,
			mycrm_troubletickets.status, mycrm_troubletickets.priority,
			mycrm_crmentity.crmid, mycrm_crmentity.smownerid,
			mycrm_crmentity.modifiedtime, mycrm_troubletickets.ticket_no
			FROM mycrm_troubletickets
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_troubletickets.ticketid
			LEFT JOIN mycrm_products
				ON mycrm_products.productid = mycrm_troubletickets.product_id
			LEFT JOIN mycrm_ticketcf ON mycrm_troubletickets.ticketid = mycrm_ticketcf.ticketid
			LEFT JOIN mycrm_users
				ON mycrm_users.id = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			WHERE mycrm_crmentity.deleted = 0
			AND mycrm_products.productid = ".$id;

		$log->debug("Exiting get_tickets method ...");

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_tickets method ...");
		return $return_value;
	}

	/**	function used to get the list of activities which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_activities($id)
	{
		global $log, $singlepane_view;
		$log->debug("Entering get_activities(".$id.") method ...");
		global $app_strings;

		require_once('modules/Calendar/Activity.php');

        	//if($this->column_fields['contact_id']!=0 && $this->column_fields['contact_id']!='')
        	$focus = new Activity();

		$button = '';

		if($singlepane_view == 'true')
			$returnset = '&return_module=Products&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module=Products&return_action=CallRelatedList&return_id='.$id;


		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'mycrm_users.first_name', 'last_name' => 'mycrm_users.last_name'), 'Users');
		$query = "SELECT mycrm_contactdetails.lastname,
			mycrm_contactdetails.firstname,
			mycrm_contactdetails.contactid,
			mycrm_activity.*,
			mycrm_seactivityrel.crmid as parent_id,
			mycrm_crmentity.crmid, mycrm_crmentity.smownerid,
			mycrm_crmentity.modifiedtime,
			$userNameSql,
			mycrm_recurringevents.recurringtype
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
			AND (activitytype != 'Emails')";
		$log->debug("Exiting get_activities method ...");
		return GetRelatedList('Products','Calendar',$focus,$query,$button,$returnset);
	}

	/**	function used to get the list of quotes which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
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
		$query = "SELECT mycrm_crmentity.*,
			mycrm_quotes.*,
			mycrm_potential.potentialname,
			mycrm_account.accountname,
			mycrm_inventoryproductrel.productid,
			case when (mycrm_users.user_name not like '') then $userNameSql
				else mycrm_groups.groupname end as user_name
			FROM mycrm_quotes
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_quotes.quoteid
			INNER JOIN mycrm_inventoryproductrel
				ON mycrm_inventoryproductrel.id = mycrm_quotes.quoteid
			LEFT OUTER JOIN mycrm_account
				ON mycrm_account.accountid = mycrm_quotes.accountid
			LEFT OUTER JOIN mycrm_potential
				ON mycrm_potential.potentialid = mycrm_quotes.potentialid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
            LEFT JOIN mycrm_quotescf
                ON mycrm_quotescf.quoteid = mycrm_quotes.quoteid
			LEFT JOIN mycrm_quotesbillads
				ON mycrm_quotesbillads.quotebilladdressid = mycrm_quotes.quoteid
			LEFT JOIN mycrm_quotesshipads
				ON mycrm_quotesshipads.quoteshipaddressid = mycrm_quotes.quoteid
			LEFT JOIN mycrm_users
				ON mycrm_users.id = mycrm_crmentity.smownerid
			WHERE mycrm_crmentity.deleted = 0
			AND mycrm_inventoryproductrel.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_quotes method ...");
		return $return_value;
	}

	/**	function used to get the list of purchase orders which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
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
		$query = "SELECT mycrm_crmentity.*,
			mycrm_purchaseorder.*,
			mycrm_products.productname,
			mycrm_inventoryproductrel.productid,
			case when (mycrm_users.user_name not like '') then $userNameSql
				else mycrm_groups.groupname end as user_name
			FROM mycrm_purchaseorder
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_purchaseorder.purchaseorderid
			INNER JOIN mycrm_inventoryproductrel
				ON mycrm_inventoryproductrel.id = mycrm_purchaseorder.purchaseorderid
			INNER JOIN mycrm_products
				ON mycrm_products.productid = mycrm_inventoryproductrel.productid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
            LEFT JOIN mycrm_purchaseordercf
                ON mycrm_purchaseordercf.purchaseorderid = mycrm_purchaseorder.purchaseorderid
			LEFT JOIN mycrm_pobillads
				ON mycrm_pobillads.pobilladdressid = mycrm_purchaseorder.purchaseorderid
			LEFT JOIN mycrm_poshipads
				ON mycrm_poshipads.poshipaddressid = mycrm_purchaseorder.purchaseorderid
			LEFT JOIN mycrm_users
				ON mycrm_users.id = mycrm_crmentity.smownerid
			WHERE mycrm_crmentity.deleted = 0
			AND mycrm_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_purchase_orders method ...");
		return $return_value;
	}

	/**	function used to get the list of sales orders which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
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
		$query = "SELECT mycrm_crmentity.*,
			mycrm_salesorder.*,
			mycrm_products.productname AS productname,
			mycrm_account.accountname,
			case when (mycrm_users.user_name not like '') then $userNameSql
				else mycrm_groups.groupname end as user_name
			FROM mycrm_salesorder
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_salesorder.salesorderid
			INNER JOIN mycrm_inventoryproductrel
				ON mycrm_inventoryproductrel.id = mycrm_salesorder.salesorderid
			INNER JOIN mycrm_products
				ON mycrm_products.productid = mycrm_inventoryproductrel.productid
			LEFT OUTER JOIN mycrm_account
				ON mycrm_account.accountid = mycrm_salesorder.accountid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
            LEFT JOIN mycrm_salesordercf
                ON mycrm_salesordercf.salesorderid = mycrm_salesorder.salesorderid
            LEFT JOIN mycrm_invoice_recurring_info
                ON mycrm_invoice_recurring_info.start_period = mycrm_salesorder.salesorderid
			LEFT JOIN mycrm_sobillads
				ON mycrm_sobillads.sobilladdressid = mycrm_salesorder.salesorderid
			LEFT JOIN mycrm_soshipads
				ON mycrm_soshipads.soshipaddressid = mycrm_salesorder.salesorderid
			LEFT JOIN mycrm_users
				ON mycrm_users.id = mycrm_crmentity.smownerid
			WHERE mycrm_crmentity.deleted = 0
			AND mycrm_products.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_salesorder method ...");
		return $return_value;
	}

	/**	function used to get the list of invoices which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
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
		$query = "SELECT mycrm_crmentity.*,
			mycrm_invoice.*,
			mycrm_inventoryproductrel.quantity,
			mycrm_account.accountname,
			case when (mycrm_users.user_name not like '') then $userNameSql
				else mycrm_groups.groupname end as user_name
			FROM mycrm_invoice
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_invoice.invoiceid
			LEFT OUTER JOIN mycrm_account
				ON mycrm_account.accountid = mycrm_invoice.accountid
			INNER JOIN mycrm_inventoryproductrel
				ON mycrm_inventoryproductrel.id = mycrm_invoice.invoiceid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
            LEFT JOIN mycrm_invoicecf
                ON mycrm_invoicecf.invoiceid = mycrm_invoice.invoiceid
			LEFT JOIN mycrm_invoicebillads
				ON mycrm_invoicebillads.invoicebilladdressid = mycrm_invoice.invoiceid
			LEFT JOIN mycrm_invoiceshipads
				ON mycrm_invoiceshipads.invoiceshipaddressid = mycrm_invoice.invoiceid
			LEFT JOIN mycrm_users
				ON  mycrm_users.id=mycrm_crmentity.smownerid
			WHERE mycrm_crmentity.deleted = 0
			AND mycrm_inventoryproductrel.productid = ".$id;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_invoices method ...");
		return $return_value;
	}

	/**	function used to get the list of pricebooks which are related to the product
	 *	@param int $id - product id
	 *	@return array - array which will be returned from the function GetRelatedList
	 */
	function get_product_pricebooks($id, $cur_tab_id, $rel_tab_id, $actions=false)
	{
		global $log,$singlepane_view,$currentModule;
		$log->debug("Entering get_product_pricebooks(".$id.") method ...");

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		checkFileAccessForInclusion("modules/$related_module/$related_module.php");
		require_once("modules/$related_module/$related_module.php");
		$focus = new $related_module();
		$singular_modname = vtlib_toSingular($related_module);

		$button = '';
		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes' && isPermitted($currentModule,'EditView',$id) == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_ADD_TO'). " ". getTranslatedString($related_module) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"AddProductToPriceBooks\";this.form.module.value=\"$currentModule\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_TO'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
		}

		if($singlepane_view == 'true')
			$returnset = '&return_module=Products&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module=Products&return_action=CallRelatedList&return_id='.$id;


		$query = "SELECT mycrm_crmentity.crmid,
			mycrm_pricebook.*,
			mycrm_pricebookproductrel.productid as prodid
			FROM mycrm_pricebook
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_pricebook.pricebookid
			INNER JOIN mycrm_pricebookproductrel
				ON mycrm_pricebookproductrel.pricebookid = mycrm_pricebook.pricebookid
			INNER JOIN mycrm_pricebookcf
				ON mycrm_pricebookcf.pricebookid = mycrm_pricebook.pricebookid
			WHERE mycrm_crmentity.deleted = 0
			AND mycrm_pricebookproductrel.productid = ".$id;
		$log->debug("Exiting get_product_pricebooks method ...");

		$return_value = GetRelatedList($currentModule, $related_module, $focus, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		return $return_value;
	}

	/**	function used to get the number of vendors which are related to the product
	 *	@param int $id - product id
	 *	@return int number of rows - return the number of products which do not have relationship with vendor
	 */
	function product_novendor()
	{
		global $log;
		$log->debug("Entering product_novendor() method ...");
		$query = "SELECT mycrm_products.productname, mycrm_crmentity.deleted
			FROM mycrm_products
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_products.productid
			WHERE mycrm_crmentity.deleted = 0
			AND mycrm_products.vendor_id is NULL";
		$result=$this->db->pquery($query, array());
		$log->debug("Exiting product_novendor method ...");
		return $this->db->num_rows($result);
	}

	/**
	* Function to get Product's related Products
	* @param  integer   $id      - productid
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

		if($actions && $this->ismember_check() === 0) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />".
					"<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$query = "SELECT mycrm_products.productid, mycrm_products.productname,
			mycrm_products.productcode, mycrm_products.commissionrate,
			mycrm_products.qty_per_unit, mycrm_products.unit_price,
			mycrm_crmentity.crmid, mycrm_crmentity.smownerid
			FROM mycrm_products
			INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_products.productid
			INNER JOIN mycrm_productcf
				ON mycrm_products.productid = mycrm_productcf.productid
			LEFT JOIN mycrm_seproductsrel ON mycrm_seproductsrel.crmid = mycrm_products.productid AND mycrm_seproductsrel.setype='Products'
			LEFT JOIN mycrm_users
				ON mycrm_users.id=mycrm_crmentity.smownerid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			WHERE mycrm_crmentity.deleted = 0 AND mycrm_seproductsrel.productid = $id ";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_products method ...");
		return $return_value;
	}

	/**
	* Function to get Product's related Products
	* @param  integer   $id      - productid
	* returns related Products record in array format
	*/
	function get_parent_products($id)
	{
		global $log, $singlepane_view;
                $log->debug("Entering get_products(".$id.") method ...");

		global $app_strings;

		$focus = new Products();

		$button = '';

		if(isPermitted("Products",1,"") == 'yes')
		{
			$button .= '<input title="'.$app_strings['LBL_NEW_PRODUCT'].'" accessyKey="F" class="button" onclick="this.form.action.value=\'EditView\';this.form.module.value=\'Products\';this.form.return_module.value=\'Products\';this.form.return_action.value=\'DetailView\'" type="submit" name="button" value="'.$app_strings['LBL_NEW_PRODUCT'].'">&nbsp;';
		}
		if($singlepane_view == 'true')
			$returnset = '&return_module=Products&return_action=DetailView&is_parent=1&return_id='.$id;
		else
			$returnset = '&return_module=Products&return_action=CallRelatedList&is_parent=1&return_id='.$id;

		$query = "SELECT mycrm_products.productid, mycrm_products.productname,
			mycrm_products.productcode, mycrm_products.commissionrate,
			mycrm_products.qty_per_unit, mycrm_products.unit_price,
			mycrm_crmentity.crmid, mycrm_crmentity.smownerid
			FROM mycrm_products
			INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = mycrm_products.productid
			INNER JOIN mycrm_seproductsrel ON mycrm_seproductsrel.productid = mycrm_products.productid AND mycrm_seproductsrel.setype='Products'
			INNER JOIN mycrm_productcf ON mycrm_products.productid = mycrm_productcf.productid

			WHERE mycrm_crmentity.deleted = 0 AND mycrm_seproductsrel.crmid = $id ";

		$log->debug("Exiting get_products method ...");
		return GetRelatedList('Products','Products',$focus,$query,$button,$returnset);
	}

	/**	function used to get the export query for product
	 *	@param reference $where - reference of the where variable which will be added with the query
	 *	@return string $query - return the query which will give the list of products to export
	 */
	function create_export_query($where)
	{
		global $log, $current_user;
		$log->debug("Entering create_export_query(".$where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("Products", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list FROM ".$this->table_name ."
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_products.productid
			LEFT JOIN mycrm_productcf
				ON mycrm_products.productid = mycrm_productcf.productid
			LEFT JOIN mycrm_vendor
				ON mycrm_vendor.vendorid = mycrm_products.vendor_id";

		$query .= " LEFT JOIN mycrm_groups ON mycrm_groups.groupid = mycrm_crmentity.smownerid";
		$query .= " LEFT JOIN mycrm_users ON mycrm_crmentity.smownerid = mycrm_users.id AND mycrm_users.status='Active'";
		$query .= $this->getNonAdminAccessControlQuery('Products',$current_user);
		$where_auto = " mycrm_crmentity.deleted=0";

		if($where != '') $query .= " WHERE ($where) AND $where_auto";
		else $query .= " WHERE $where_auto";

		$log->debug("Exiting create_export_query method ...");
		return $query;
	}

	/** Function to check if the product is parent of any other product
	*/
	function isparent_check(){
		global $adb;
		$isparent_query = $adb->pquery(getListQuery("Products")." AND (mycrm_products.productid IN (SELECT productid from mycrm_seproductsrel WHERE mycrm_seproductsrel.productid = ? AND mycrm_seproductsrel.setype='Products'))",array($this->id));
		$isparent = $adb->num_rows($isparent_query);
		return $isparent;
	}

	/** Function to check if the product is member of other product
	*/
	function ismember_check(){
		global $adb;
		$ismember_query = $adb->pquery(getListQuery("Products")." AND (mycrm_products.productid IN (SELECT crmid from mycrm_seproductsrel WHERE mycrm_seproductsrel.crmid = ? AND mycrm_seproductsrel.setype='Products'))",array($this->id));
		$ismember = $adb->num_rows($ismember_query);
		return $ismember;
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

		$rel_table_arr = Array("HelpDesk"=>"mycrm_troubletickets","Products"=>"mycrm_seproductsrel","Attachments"=>"mycrm_seattachmentsrel",
				"Quotes"=>"mycrm_inventoryproductrel","PurchaseOrder"=>"mycrm_inventoryproductrel","SalesOrder"=>"mycrm_inventoryproductrel",
				"Invoice"=>"mycrm_inventoryproductrel","PriceBooks"=>"mycrm_pricebookproductrel","Leads"=>"mycrm_seproductsrel",
				"Accounts"=>"mycrm_seproductsrel","Potentials"=>"mycrm_seproductsrel","Contacts"=>"mycrm_seproductsrel",
				"Documents"=>"mycrm_senotesrel",'Assets'=>'mycrm_assets',);

		$tbl_field_arr = Array("mycrm_troubletickets"=>"ticketid","mycrm_seproductsrel"=>"crmid","mycrm_seattachmentsrel"=>"attachmentsid",
				"mycrm_inventoryproductrel"=>"id","mycrm_pricebookproductrel"=>"pricebookid","mycrm_seproductsrel"=>"crmid",
				"mycrm_senotesrel"=>"notesid",'mycrm_assets'=>'assetsid');

		$entity_tbl_field_arr = Array("mycrm_troubletickets"=>"product_id","mycrm_seproductsrel"=>"crmid","mycrm_seattachmentsrel"=>"crmid",
				"mycrm_inventoryproductrel"=>"productid","mycrm_pricebookproductrel"=>"productid","mycrm_seproductsrel"=>"productid",
				"mycrm_senotesrel"=>"crmid",'mycrm_assets'=>'product');

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

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule,$queryplanner) {
		global $current_user;
		$matrix = $queryplanner->newDependencyMatrix();

		$matrix->setDependency("mycrm_crmentityProducts",array("mycrm_groupsProducts","mycrm_usersProducts","mycrm_lastModifiedByProducts"));
		$matrix->setDependency("mycrm_products",array("innerProduct","mycrm_crmentityProducts","mycrm_productcf","mycrm_vendorRelProducts"));
		//query planner Support  added
		if (!$queryplanner->requireTable('mycrm_products', $matrix)) {
			return '';
		}
		$query = $this->getRelationQuery($module,$secmodule,"mycrm_products","productid", $queryplanner);
		if ($queryplanner->requireTable("innerProduct")){
		    $query .= " LEFT JOIN (
				    SELECT mycrm_products.productid,
						    (CASE WHEN (mycrm_products.currency_id = 1 ) THEN mycrm_products.unit_price
							    ELSE (mycrm_products.unit_price / mycrm_currency_info.conversion_rate) END
						    ) AS actual_unit_price
				    FROM mycrm_products
				    LEFT JOIN mycrm_currency_info ON mycrm_products.currency_id = mycrm_currency_info.id
				    LEFT JOIN mycrm_productcurrencyrel ON mycrm_products.productid = mycrm_productcurrencyrel.productid
				    AND mycrm_productcurrencyrel.currencyid = ". $current_user->currency_id . "
			    ) AS innerProduct ON innerProduct.productid = mycrm_products.productid";
		}
		if ($queryplanner->requireTable("mycrm_crmentityProducts")){
		    $query .= " left join mycrm_crmentity as mycrm_crmentityProducts on mycrm_crmentityProducts.crmid=mycrm_products.productid and mycrm_crmentityProducts.deleted=0";
		}
		if ($queryplanner->requireTable("mycrm_productcf")){
		    $query .= " left join mycrm_productcf on mycrm_products.productid = mycrm_productcf.productid";
		}
    		if ($queryplanner->requireTable("mycrm_groupsProducts")){
		    $query .= " left join mycrm_groups as mycrm_groupsProducts on mycrm_groupsProducts.groupid = mycrm_crmentityProducts.smownerid";
		}
		if ($queryplanner->requireTable("mycrm_usersProducts")){
		    $query .= " left join mycrm_users as mycrm_usersProducts on mycrm_usersProducts.id = mycrm_crmentityProducts.smownerid";
		}
		if ($queryplanner->requireTable("mycrm_vendorRelProducts")){
		    $query .= " left join mycrm_vendor as mycrm_vendorRelProducts on mycrm_vendorRelProducts.vendorid = mycrm_products.vendor_id";
		}
		if ($queryplanner->requireTable("mycrm_lastModifiedByProducts")){
		    $query .= " left join mycrm_users as mycrm_lastModifiedByProducts on mycrm_lastModifiedByProducts.id = mycrm_crmentityProducts.modifiedby ";
		}
        if ($queryplanner->requireTable("mycrm_createdbyProducts")){
			$query .= " left join mycrm_users as mycrm_createdbyProducts on mycrm_createdbyProducts.id = mycrm_crmentityProducts.smcreatorid ";
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
			"HelpDesk" => array("mycrm_troubletickets"=>array("product_id","ticketid"),"mycrm_products"=>"productid"),
			"Quotes" => array("mycrm_inventoryproductrel"=>array("productid","id"),"mycrm_products"=>"productid"),
			"PurchaseOrder" => array("mycrm_inventoryproductrel"=>array("productid","id"),"mycrm_products"=>"productid"),
			"SalesOrder" => array("mycrm_inventoryproductrel"=>array("productid","id"),"mycrm_products"=>"productid"),
			"Invoice" => array("mycrm_inventoryproductrel"=>array("productid","id"),"mycrm_products"=>"productid"),
			"Leads" => array("mycrm_seproductsrel"=>array("productid","crmid"),"mycrm_products"=>"productid"),
			"Accounts" => array("mycrm_seproductsrel"=>array("productid","crmid"),"mycrm_products"=>"productid"),
			"Contacts" => array("mycrm_seproductsrel"=>array("productid","crmid"),"mycrm_products"=>"productid"),
			"Potentials" => array("mycrm_seproductsrel"=>array("productid","crmid"),"mycrm_products"=>"productid"),
			"Products" => array("mycrm_products"=>array("productid","product_id"),"mycrm_products"=>"productid"),
			"PriceBooks" => array("mycrm_pricebookproductrel"=>array("productid","pricebookid"),"mycrm_products"=>"productid"),
			"Documents" => array("mycrm_senotesrel"=>array("crmid","notesid"),"mycrm_products"=>"productid"),
		);
		return $rel_tables[$secmodule];
	}

	function deleteProduct2ProductRelation($record,$return_id,$is_parent){
		global $adb;
		if($is_parent==0){
			$sql = "delete from mycrm_seproductsrel WHERE crmid = ? AND productid = ?";
			$adb->pquery($sql, array($record,$return_id));
		} else {
			$sql = "delete from mycrm_seproductsrel WHERE crmid = ? AND productid = ?";
			$adb->pquery($sql, array($return_id,$record));
		}
	}

	// Function to unlink all the dependent entities of the given Entity by Id
	function unlinkDependencies($module, $id) {
		global $log;
		//Backup Campaigns-Product Relation
		$cmp_q = 'SELECT campaignid FROM mycrm_campaign WHERE product_id = ?';
		$cmp_res = $this->db->pquery($cmp_q, array($id));
		if ($this->db->num_rows($cmp_res) > 0) {
			$cmp_ids_list = array();
			for($k=0;$k < $this->db->num_rows($cmp_res);$k++)
			{
				$cmp_ids_list[] = $this->db->query_result($cmp_res,$k,"campaignid");
			}
			$params = array($id, RB_RECORD_UPDATED, 'mycrm_campaign', 'product_id', 'campaignid', implode(",", $cmp_ids_list));
			$this->db->pquery('INSERT INTO mycrm_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
		}
		//we have to update the product_id as null for the campaigns which are related to this product
		$this->db->pquery('UPDATE mycrm_campaign SET product_id=0 WHERE product_id = ?', array($id));

		$this->db->pquery('DELETE from mycrm_seproductsrel WHERE productid=? or crmid=?',array($id,$id));

		parent::unlinkDependencies($module, $id);
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Calendar') {
			$sql = 'DELETE FROM mycrm_seactivityrel WHERE crmid = ? AND activityid = ?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif($return_module == 'Leads' || $return_module == 'Contacts' || $return_module == 'Potentials') {
			$sql = 'DELETE FROM mycrm_seproductsrel WHERE productid = ? AND crmid = ?';
			$this->db->pquery($sql, array($id, $return_id));
		} elseif($return_module == 'Vendors') {
			$sql = 'UPDATE mycrm_products SET vendor_id = ? WHERE productid = ?';
			$this->db->pquery($sql, array(null, $id));
		} elseif($return_module == 'Accounts') {
			$sql = 'DELETE FROM mycrm_seproductsrel WHERE productid = ? AND (crmid = ? OR crmid IN (SELECT contactid FROM mycrm_contactdetails WHERE accountid=?))';
			$param = array($id, $return_id,$return_id);
			$this->db->pquery($sql, $param);
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
			if($with_module == 'Leads' || $with_module == 'Accounts' ||
					$with_module == 'Contacts' || $with_module == 'Potentials' || $with_module == 'Products'){
				$query = $adb->pquery("SELECT * from mycrm_seproductsrel WHERE crmid=? and productid=?",array($crmid, $with_crmid));
				if($adb->num_rows($query)==0){
					$adb->pquery("insert into mycrm_seproductsrel values (?,?,?)", array($with_crmid, $crmid, $with_module));
				}
			}
			else {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid);
			}
		}
	}

}
?>
