<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/
require_once('modules/com_mycrm_workflow/VTEntityMethodManager.inc');
class VTEntityMethodTask extends VTTask{
	
	public $executeImmediately = true;
	
	public function getFieldNames(){return array('methodName');}
	public function doTask($entityData){
		global $adb;
		$emm = new VTEntityMethodManager($adb);
		$emm->executeMethod($entityData, $this->methodName);
	}
}
?>