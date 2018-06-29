<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Inventory_ServicesPopup_View extends Mycrm_Popup_View {

	/**
	 * Function returns module name for which Popup will be initialized
	 * @param type $request
	 */
	function getModule($request) {
		return 'Services';
	}

	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */
	public function initializeListViewContents(Mycrm_Request $request, Mycrm_Viewer $viewer) {
		//src_module value is added just to stop showing inactive services
		$request->set('src_module', $request->getModule());

		parent::initializeListViewContents($request, $viewer);
		$viewer->assign('MODULE', $request->getModule());
		$viewer->assign('GETURL', 'getTaxesURL');
		$viewer->assign('VIEW', 'ServicesPopup');
	}

}