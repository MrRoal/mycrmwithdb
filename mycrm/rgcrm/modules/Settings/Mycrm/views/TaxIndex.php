<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Mycrm_TaxIndex_View extends Settings_Mycrm_Index_View {
    
    public function process(Mycrm_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
		
		$taxRecordModel = new Settings_Mycrm_TaxRecord_Model();
        $productAndServicesTaxList = Settings_Mycrm_TaxRecord_Model::getProductTaxes();
        $shippingAndHandlingTaxList = Settings_Mycrm_TaxRecord_Model::getShippingTaxes();
        
        $qualifiedModuleName = $request->getModule(false);
        
        $viewer = $this->getViewer($request);
		$viewer->assign('TAX_RECORD_MODEL', $taxRecordModel);
        $viewer->assign('PRODUCT_AND_SERVICES_TAXES',$productAndServicesTaxList);
        $viewer->assign('SHIPPING_AND_HANDLING_TAXES',$shippingAndHandlingTaxList);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->view('TaxIndex.tpl',$qualifiedModuleName);
    }
	
	
		
	
	function getPageTitle(Mycrm_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		return vtranslate('LBL_TAX_CALCULATIONS',$qualifiedModuleName);
	}
	
	/**
	 * Function to get the list of Script models to be included
	 * @param Mycrm_Request $request
	 * @return <Array> - List of Mycrm_JsScript_Model instances
	 */
	function getHeaderScripts(Mycrm_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.Tax"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}