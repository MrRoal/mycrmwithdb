<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Home_Index_View extends Mycrm_Index_View {

	function process (Mycrm_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		//$viewer->assign('HOME_PAGES', Home_Page_Model::getAll());
		//$viewer->assign('HOME_PAGE_WIDGETS', Home_Widget_Model::getAll());

		$viewer->view('Index.tpl', $moduleName);
	}
}