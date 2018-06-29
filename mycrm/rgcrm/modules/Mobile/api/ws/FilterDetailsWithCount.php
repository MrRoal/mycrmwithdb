<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/FetchModuleFilters.php';

include_once 'modules/CustomView/CustomView.php';

class Mobile_WS_FilterDetailsWithCount extends Mobile_WS_FetchModuleFilters {
	
	function process(Mobile_API_Request $request) {
		global $current_user;
		
		$response = new Mobile_API_Response();

		$filterid = $request->get('filterid');
		$current_user = $this->getActiveUser();
		
		$result = array();
		$result['filter'] = $this->getModuleFilterDetails($filterid);
		$response->setResult($result);

		return $response;
	}
	
	protected function getModuleFilterDetails($filterid) {
		global $adb;
		$result = $adb->pquery("SELECT * FROM mycrm_customview WHERE cvid=?", array($filterid));
		if ($result && $adb->num_rows($result)) {
			$resultrow = $adb->fetch_array($result);
				
			$module = $resultrow['entitytype'];
				
			$view = new CustomView($module);
			$viewid = $resultrow['cvid'];
			$view->getCustomViewByCvid($viewid);
			$viewQuery = $view->getModifiedCvListQuery($viewid, getListQuery($module), $module);
				
			$countResult = $adb->pquery(Mycrm_Functions::mkCountQuery($viewQuery), array()); 
			$count = 0;
			if($countResult && $adb->num_rows($countResult)) {
				$count = $adb->query_result($countResult, 0, 'count');
			}
				
			$filter = $this->prepareFilterDetailUsingResultRow($resultrow);
			$filter['userName'] = getUserName($resultrow['userid']);
			$filter['count'] = $count;
				
			return $filter;
		}
	}
}