<?php
/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_PickListDependency_AddDependency_View extends Settings_Mycrm_IndexAjax_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('GetPickListFields');
	}

	function process(Mycrm_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode) && method_exists($this, $mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

		$qualifiedModule = $request->getModule(true);
		$viewer = $this->getViewer($request);
		$moduleModels = Mycrm_Module_Model::getEntityModules();

		$viewer->assign('MODULES', $moduleModels);
		echo $viewer->view('AddDependency.tpl', $qualifiedModule);
	}

	/**
	 * Function returns the picklist field for a module
	 * @param Mycrm_Request $request
	 */
	function GetPickListFields(Mycrm_Request $request) {
		$module = $request->get('sourceModule');

		$fieldList = Settings_PickListDependency_Module_Model::getAvailablePicklists($module);

		$response = new Mycrm_Response();
		$response->setResult($fieldList);
		$response->emit();
	}

	function CheckCyclicDependency() {

	}
}