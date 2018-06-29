<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

include_once dirname(__FILE__) . '/../api/ws/FetchRecordWithGrouping.php';

class Mobile_UI_FetchRecordWithGrouping extends Mobile_WS_FetchRecordWithGrouping {
	
	function cachedModuleLookupWithRecordId($recordId) {
		$recordIdComponents = explode('x', $recordId);
		$modules = $this->sessionGet('_MODULES'); // Should be available post login
		foreach($modules as $module) {
			if ($module->id() == $recordIdComponents[0]) { return $module; };
		}
		return false;
	}
	
	function process(Mobile_API_Request $request) {
		$wsResponse = parent::process($request);
		
		$response = false;
		if($wsResponse->hasError()) {
			$response = $wsResponse;
		} else {
			$wsResponseResult = $wsResponse->getResult();

			$module = $this->cachedModuleLookupWithRecordId($wsResponseResult['record']['id']);
			$record = Mobile_UI_ModuleRecordModel::buildModelFromResponse($wsResponseResult['record']);
			$record->setId($wsResponseResult['record']['id']);
			
			$viewer = new Mobile_UI_Viewer();
			$viewer->assign('_MODULE', $module);
			$viewer->assign('_RECORD', $record);

			$response = $viewer->process('generic/Detail.tpl');
		}
		return $response;
	}

}