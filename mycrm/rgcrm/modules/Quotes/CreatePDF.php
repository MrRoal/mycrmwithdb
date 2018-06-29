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
include_once 'modules/Quotes/QuotePDFController.php';
$controller = new Mycrm_QuotePDFController($currentModule);
$controller->loadRecord(vtlib_purify($_REQUEST['record']));
$quote_no = getModuleSequenceNumber($currentModule,vtlib_purify($_REQUEST['record']));
if(isset($_REQUEST['savemode']) && $_REQUEST['savemode'] == 'file') {
	$quote_id = vtlib_purify($_REQUEST['record']);
	$filepath='test/product/'.$quote_id.'_Quotes_'.$quote_no.'.pdf';
	//added file name to make it work in IE, also forces the download giving the user the option to save
	$controller->Output($filepath,'F');
} else {
	//added file name to make it work in IE, also forces the download giving the user the option to save
	$controller->Output('Quotes_'.$quote_no.'.pdf', 'D');
	exit();
}

?>
