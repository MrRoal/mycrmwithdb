<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/
class Mobile_WS_FetchModuleFilters extends Mobile_WS_Controller {
	
	function process(Mobile_API_Request $request) {
		$response = new Mobile_API_Response();

		$module = $request->get('module');
		$current_user = $this->getActiveUser();
		
		$result = array();
		
		$filters = $this->getModuleFilters($module, $current_user);
		$yours = array();
		$others= array();
		if(!empty($filters)) {
			foreach($filters as $filter) {
				if($filter['userName'] == $current_user->column_fields['user_name']) {
					$yours[] = $filter;
				} else {
					$others[]= $filter;
				}
			}
		}
		
		$result['filters'] = array('yours' => $yours, 'others' => $others);
		$response->setResult($result);

		return $response;
	}

	protected function getModuleFilters($moduleName, $user) {
		
		$filters = array();
		
		global $adb;
		$sql = "SELECT mycrm_customview.*, mycrm_users.user_name FROM mycrm_customview 
			INNER JOIN mycrm_users ON mycrm_customview.userid = mycrm_users.id WHERE mycrm_customview.entitytype=?";
		$parameters = array($moduleName);

		if(!is_admin($user)) {
			require('user_privileges/user_privileges_'.$user->id.'.php');
			
			$sql .= " AND (mycrm_customview.status=0 or mycrm_customview.userid = ? or mycrm_customview.status = 3 or mycrm_customview.userid IN
			(SELECT mycrm_user2role.userid FROM mycrm_user2role INNER JOIN mycrm_users on mycrm_users.id=mycrm_user2role.userid 
			INNER JOIN mycrm_role on mycrm_role.roleid=mycrm_user2role.roleid WHERE mycrm_role.parentrole LIKE '".$current_user_parent_role_seq."::%'))";
			
			array_push($parameters, $current_user->id);
		}
		
		$result = $adb->pquery($sql, $parameters);
		if($result && $adb->num_rows($result)) {
			while($resultrow = $adb->fetch_array($result)) {
				$filters[] = $this->prepareFilterDetailUsingResultRow($resultrow);
			}
		}
		
		return $filters;
	}
	
	protected function prepareFilterDetailUsingResultRow($resultrow) {
		$filter = array();
		$filter['cvid'] = $resultrow['cvid'];
		$filter['viewname'] = decode_html($resultrow['viewname']);
		$filter['setdefault'] = $resultrow['setdefault'];
		$filter['setmetrics'] = $resultrow['setmetrics'];
		$filter['moduleName'] = decode_html($resultrow['entitytype']);
		$filter['status']     = decode_html($resultrow['status']);
		$filter['userName']   = decode_html($resultrow['user_name']);
		return $filter;
	}
}