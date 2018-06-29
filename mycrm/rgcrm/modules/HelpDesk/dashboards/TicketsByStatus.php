<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class HelpDesk_TicketsByStatus_Dashboard extends Mycrm_IndexAjax_View {
 
    function getSearchParams($value,$assignedto = '',$dates) {
        $listSearchParams = array();
        $conditions = array(array('ticketstatus','e',$value));
        if($assignedto != '') array_push($conditions,array('assigned_user_id','e',getUserFullName($assignedto)));
        if(!empty($dates)){
            array_push($conditions,array('createdtime','bw',$dates['start'].' 00:00:00,'.$dates['end'].' 23:59:59'));
        }
        $listSearchParams[] = $conditions;
        return '&search_params='. json_encode($listSearchParams);
    }

	public function process(Mycrm_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$linkId = $request->get('linkid');
		$data = $request->get('data');
		
		$createdTime = $request->get('createdtime');
		
		//Date conversion from user to database format
		if(!empty($createdTime)) {
			$dates['start'] = Mycrm_Date_UIType::getDBInsertedValue($createdTime['start']);
			$dates['end'] = Mycrm_Date_UIType::getDBInsertedValue($createdTime['end']);
		}

		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);
		$data = $moduleModel->getTicketsByStatus($request->get('owner'), $dates);

        $listViewUrl = $moduleModel->getListViewUrl();
        for($i = 0;$i<count($data);$i++){
            $data[$i]["links"] = $listViewUrl.$this->getSearchParams($data[$i][2],$request->get('owner'),$dates);
        }
		$widget = Mycrm_Widget_Model::getInstance($linkId, $currentUser->getId());

		//Include special script and css needed for this widget

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('CURRENTUSER', $currentUser);

		$accessibleUsers = $currentUser->getAccessibleUsersForModule($moduleName);
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);

		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/TicketsByStatus.tpl', $moduleName);
		}
	}
}
