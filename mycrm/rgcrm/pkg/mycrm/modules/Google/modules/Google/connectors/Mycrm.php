<?php

/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */
vimport('~~/modules/WSAPP/synclib/connectors/MycrmConnector.php');
vimport('~~/modules/WSAPP/SyncServer.php');
include_once 'include/Webservices/Query.php';
include_once 'include/Webservices/Create.php';
include_once 'include/Webservices/Retrieve.php';

class Google_Mycrm_Connector extends WSAPP_MycrmConnector {

	/**
	 * function to push data to mycrm
	 * @param type $recordList
	 * @param type $syncStateModel
	 * @return type
	 */
	public function push($recordList, $syncStateModel) {
		return parent::push($recordList, $syncStateModel);
	}

	/**
	 * function to get data from mycrm
	 * @param type $syncStateModel
	 * @return type
	 */
	public function pull($syncStateModel) {
		$records = parent::pull($syncStateModel);
		return $records;
	}

	/**
	 * function that returns syncTrackerhandler name
	 * @return string
	 */
	public function getSyncTrackerHandlerName() {
		return 'Google_mycrmSyncHandler';
	}
	
}
