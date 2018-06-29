<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

	function vtws_extendSession(){
		global $adb,$API_VERSION,$application_unique_key;
		if(isset($_SESSION["authenticated_user_id"]) && $_SESSION["app_unique_key"] == $application_unique_key){
			$userId = $_SESSION["authenticated_user_id"];
			$sessionManager = new SessionManager();
			$sessionManager->set("authenticatedUserId", $userId);
			$crmObject = MycrmWebserviceObject::fromName($adb,"Users");
			$userId = vtws_getId($crmObject->getEntityId(),$userId);
			$mycrmVersion = vtws_getMycrmVersion();
			$resp = array("sessionName"=>$sessionManager->getSessionId(),"userId"=>$userId,"version"=>$API_VERSION,"mycrmVersion"=>$mycrmVersion);
			return $resp;
		}else{
			throw new WebServiceException(WebServiceErrorCode::$AUTHFAILURE,"Authencation Failed");
		}
	}
?>