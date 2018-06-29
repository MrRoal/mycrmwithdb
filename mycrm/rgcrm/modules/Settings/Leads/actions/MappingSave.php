<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Leads_MappingSave_Action extends Settings_Mycrm_Index_Action {

	public function process(Mycrm_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$mapping = $request->get('mapping');
		$csrfKey = $GLOBALS['csrf']['input-name'];
		if(array_key_exists($csrfKey,$mapping)){
			unset($mapping[$csrfKey]);
		}
		$mappingModel = Settings_Leads_Mapping_Model::getCleanInstance();

		$response = new Mycrm_Response();
		if ($mapping) {
			$mappingModel->save($mapping);
            $result = array('status' => true);
		} else {
            $result['status'] = false;
		}
        $response->setResult($result);
		return $response->emit();
	}

	public function validateRequest(Mycrm_Request $request){
		$request->validateWriteAccess();
	}
}
