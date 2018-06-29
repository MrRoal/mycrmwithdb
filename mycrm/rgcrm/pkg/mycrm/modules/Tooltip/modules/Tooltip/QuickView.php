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
require_once 'include/utils/utils.php';
require_once 'modules/Tooltip/TooltipUtils.php';

global $mod_strings,$app_strings,$theme,$currentModule;
$smarty=new mycrmCRM_Smarty;
$smarty->assign("MOD",$mod_strings);
$smarty->assign("APP",$app_strings);
$smarty->assign("THEME", $theme);

$module_array=moduleList();
$smarty->assign("MODULES",$module_array);

if(!empty($_REQUEST['formodule'])){
	$fld_module = vtlib_purify($_REQUEST['formodule']);
}
else{
	echo "NO MODULES SELECTED";
	exit;
}
$smarty->assign("MODULE",$fld_module);

$fieldsDropDown = QuickViewFieldList($fld_module);
$smarty->assign("FIELDNAMES",$fieldsDropDown);

if($_REQUEST['mode'] != ''){
	$mode = $_REQUEST['mode'];
}
$smarty->assign("MODE", $mode);
$smarty->assign("FORMODULE", $fld_module);
$smarty->assign("MOD",$mod_strings);

$smarty->display(vtlib_getModuleTemplate($currentModule,'Quickview.tpl'));
?>
