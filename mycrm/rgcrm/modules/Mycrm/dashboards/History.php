<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_History_Dashboard extends Mycrm_IndexAjax_View {

	public function process(Mycrm_Request $request) {
		$LIMIT = 10;
		
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);

		$moduleName = $request->getModule();
		$type = $request->get('type');
		$page = $request->get('page');
		$linkId = $request->get('linkid');
                if( empty($page)) { $page=1; }
		$pagingModel = new Mycrm_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', $LIMIT);

		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);
		$history = $moduleModel->getHistory($pagingModel, $type);
		$widget = Mycrm_Widget_Model::getInstance($linkId, $currentUser->getId());
		$modCommentsModel = Mycrm_Module_Model::getInstance('ModComments'); 

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('HISTORIES', $history);
		$viewer->assign('PAGE', $page);
		$viewer->assign('NEXTPAGE', (count($history) < $LIMIT)? 0 : $page+1);
		$viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel); 
		
		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/HistoryContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/History.tpl', $moduleName);
		}
	}
}
