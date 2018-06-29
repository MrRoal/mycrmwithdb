<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/
	
	function vtws_getchallenge($username){
		
		global $adb;
		
		$user = new Users();
		$userid = $user->retrieve_user_id($username);
		$authToken = uniqid();
		
		$servertime = time();
		$expireTime = time()+(60*5);
		
		$sql = "delete from mycrm_ws_userauthtoken where userid=?";
		$adb->pquery($sql,array($userid));
		
		$sql = "insert into mycrm_ws_userauthtoken(userid,token,expireTime) values (?,?,?)";
		$adb->pquery($sql,array($userid,$authToken,$expireTime));
		
		return array("token"=>$authToken,"serverTime"=>$servertime,"expireTime"=>$expireTime);
	}

?>