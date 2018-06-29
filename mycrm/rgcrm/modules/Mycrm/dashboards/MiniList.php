<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_MiniList_Dashboard extends Mycrm_IndexAjax_View {

	public function process(Mycrm_Request $request, $widget=NULL) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		// Initialize Widget to the right-state of information
		if ($widget && !$request->has('widgetid')) {
			$widgetId = $widget->get('id');
		} else {
			$widgetId = $request->get('widgetid');
		}

		$widget = Mycrm_Widget_Model::getInstanceWithWidgetId($widgetId, $currentUser->getId());

		$minilistWidgetModel = new Mycrm_MiniList_Model();
		$minilistWidgetModel->setWidgetModel($widget);

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MINILIST_WIDGET_MODEL', $minilistWidgetModel);
		$viewer->assign('BASE_MODULE', $minilistWidgetModel->getTargetModule());
                $viewer->assign('SCRIPTS', $this->getHeaderScripts());

		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/MiniListContents.tpl', $moduleName);
		} else {
			$widget->set('title', $minilistWidgetModel->getTitle());

			$viewer->view('dashboards/MiniList.tpl', $moduleName);
		}

	}
        
        function getHeaderScripts() {
        return $this->checkAndConvertJsScripts(array('modules.Emails.resources.MassEdit'));
	}
}