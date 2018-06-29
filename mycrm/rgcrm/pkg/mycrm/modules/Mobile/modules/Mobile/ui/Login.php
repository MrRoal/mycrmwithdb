<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

include_once dirname(__FILE__) . '/../api/ws/Login.php';

class Mobile_UI_Login  extends Mobile_WS_Login {
	
	function process(Mobile_API_Request $request) {
		$viewer = new Mobile_UI_Viewer();
		return $viewer->process('generic/Login.tpl');
	}

}