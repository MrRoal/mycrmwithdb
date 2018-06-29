<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Mobile_UI_Logout extends Mobile_WS_Controller {
	
	function process(Mobile_API_Request $request) {
		HTTP_Session2::destroy(HTTP_Session2::detectId());
		header('Location: index.php');
		exit;
	}

}