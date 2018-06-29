<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

require_once('modules/com_mycrm_workflow/VTEntityCache.inc');
require_once('modules/com_mycrm_workflow/VTWorkflowUtils.php');
require_once('modules/com_mycrm_workflow/VTSimpleTemplate.inc');

require_once('modules/SMSNotifier/SMSNotifier.php');

class VTSMSTask extends VTTask {
	public $executeImmediately = true; 
	
	public function getFieldNames(){
		return array('content', 'sms_recepient');
	}
	
	public function doTask($entity){
		
		if(SMSNotifier::checkServer()) {
			
			global $adb, $current_user,$log;
			
			$util = new VTWorkflowUtils();
			$admin = $util->adminUser();
			$ws_id = $entity->getId();
			$entityCache = new VTEntityCache($admin);
			
			$et = new VTSimpleTemplate($this->sms_recepient);
			$recepient = $et->render($entityCache, $ws_id);
			$recepients = explode(',',$recepient);
			
			$ct = new VTSimpleTemplate($this->content);
			$content = $ct->render($entityCache, $ws_id);
			$relatedCRMid = substr($ws_id, stripos($ws_id, 'x')+1);
			
			$relatedModule = $entity->getModuleName();
			
			/** Pickup only non-empty numbers */
			$tonumbers = array();
			foreach($recepients as $tonumber) {
				if(!empty($tonumber)) $tonumbers[] = $tonumber;
			}
			
			SMSNotifier::sendsms($content, $tonumbers, $current_user->id, $relatedCRMid, $relatedModule);
		}
		
	}
}
?>