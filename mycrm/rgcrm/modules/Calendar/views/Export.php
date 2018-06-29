<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_Export_View extends Mycrm_Export_View {

	public function preprocess(Mycrm_Request $request) {
	}

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('ACTION', 'ExportData');
		
		$viewer->view('Export.tpl', $moduleName);
	}

	public function postprocess(Mycrm_Request $request) {
	}
}