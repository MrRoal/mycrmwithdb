<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Leads_MappingDelete_Action extends Settings_Mycrm_Index_Action {

	public function process(Mycrm_Request $request) {
		$recordId = $request->get('mappingId');
		$qualifiedModuleName = $request->getModule(false);

		$response = new Mycrm_Response();
		if ($recordId) {
			Settings_Leads_Mapping_Model::deleteMapping(array($recordId));
			$response->setResult(array(vtranslate('LBL_DELETED_SUCCESSFULLY', $qualifiedModuleName)));
		} else {
			$response->setError(vtranslate('LBL_INVALID_MAPPING', $qualifiedModuleName));
		}
		$response->emit();
	}
}