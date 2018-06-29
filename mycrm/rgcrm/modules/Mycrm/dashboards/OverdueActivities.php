<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

Class Mycrm_OverdueActivities_Dashboard extends Mycrm_IndexAjax_View {

	public function process(Mycrm_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$moduleName = $request->getModule();
		$page = $request->get('page');
		$linkId = $request->get('linkid');

		$pagingModel = new Mycrm_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', 10);

		$user = $request->get('type');
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);
		$overDueActivities = $moduleModel->getCalendarActivities('overdue', $pagingModel, $user);

		$widget = Mycrm_Widget_Model::getInstance($linkId, $currentUser->getId());
		$viewer = $this->getViewer($request);

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('ACTIVITIES', $overDueActivities);
		$viewer->assign('PAGING', $pagingModel);
		$viewer->assign('CURRENTUSER', $currentUser);
		
		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/CalendarActivitiesContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/CalendarActivities.tpl', $moduleName);
		}
	}
}