<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Mycrm_TaxAjax_View extends Settings_Mycrm_Index_View {
    
    public function process(Mycrm_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$taxId = $request->get('taxid');
		$type = $request->get('type');
		
		if(empty($taxId)) {
            $taxRecordModel = new Settings_Mycrm_TaxRecord_Model();
        }else{
            $taxRecordModel = Settings_Mycrm_TaxRecord_Model::getInstanceById($taxId,$type);
        }
		
		$viewer->assign('TAX_TYPE', $type);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('TAX_RECORD_MODEL', $taxRecordModel);

		echo $viewer->view('EditTax.tpl', $qualifiedModuleName, true);
    }
	
}