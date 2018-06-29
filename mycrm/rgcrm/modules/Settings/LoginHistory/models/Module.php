<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

/*
 * Settings Module Model Class
 */
class Settings_LoginHistory_Module_Model extends Settings_Mycrm_Module_Model {

	var $baseTable = 'mycrm_loginhistory';
	var $baseIndex = 'login_id';
	var $listFields = Array(
			'user_name'=> 'LBL_USER_NAME',
			'user_ip'=> 'LBL_USER_IP_ADDRESS', 
			'login_time' => 'LBL_LOGIN_TIME',
		    'logout_time' => 'LBL_LOGGED_OUT_TIME', 
			'status' => 'LBL_STATUS'
		);

	var $name = 'LoginHistory';
	/**
	 * Function to get the url for default view of the module
	 * @return <string> - url
	 */
	public function getDefaultUrl() {
		return 'index.php?module=LoginHistory&parent=Settings&view=List';
	}
}
