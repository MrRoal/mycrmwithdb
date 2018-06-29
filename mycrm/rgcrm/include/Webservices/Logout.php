<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

function vtws_logout($sessionId,$user){
        global $adb;
        $sql = "select type from mycrm_ws_operation where name=?";
        $result = $adb->pquery($sql,array("logout"));
        $row = $adb->query_result_rowdata($result,0);
        $requestType = $row['type'];
        if($_SERVER['REQUEST_METHOD'] != $requestType){
            throw new WebServiceException(WebServiceErrorCode::$OPERATIONNOTSUPPORTED, "Permission to perform the operation is denied");
        }
	$sessionManager = new SessionManager();
	$sid = $sessionManager->startSession($sessionId);
	
	if(!isset($sessionId) || !$sessionManager->isValid()){
		return $sessionManager->getError();
	}

	$sessionManager->destroy();
//	$sessionManager->setExpire(1);
	return array("message"=>"successfull");

}
?>
