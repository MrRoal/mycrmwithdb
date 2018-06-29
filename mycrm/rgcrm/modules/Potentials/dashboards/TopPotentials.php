<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Potentials_TopPotentials_Dashboard extends Mycrm_IndexAjax_View {

	public function process(Mycrm_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$linkId = $request->get('linkid');
		$page = $request->get('page');
		if(empty($page)) {
			$page = 1;
		}
		$pagingModel = new Mycrm_Paging_Model();
		$pagingModel->set('page', $page);

		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);
		$models = $moduleModel->getTopPotentials($pagingModel);
        $moduleHeader = $moduleModel->getTopPotentialsHeader();

		$widget = Mycrm_Widget_Model::getInstance($linkId, $currentUser->getId());

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('MODULE_HEADER', $moduleHeader);
		$viewer->assign('MODELS', $models);

		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/TopPotentialsContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/TopPotentials.tpl', $moduleName);
		}
	}
}
