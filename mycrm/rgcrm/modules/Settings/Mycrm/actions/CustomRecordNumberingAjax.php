<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Mycrm_CustomRecordNumberingAjax_Action extends Settings_Mycrm_Index_Action {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('getModuleCustomNumberingData');
		$this->exposeMethod('saveModuleCustomNumberingData');
		$this->exposeMethod('updateRecordsWithSequenceNumber');
	}

	public function checkPermission(Mycrm_Request $request) {
		parent::checkPermission($request);
		$qualifiedModuleName = $request->getModule(false);
		$sourceModule = $request->get('sourceModule');

		if(!$sourceModule) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $qualifiedModuleName));
		}
	}

	public function process(Mycrm_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Function to get Module custom numbering data
	 * @param Mycrm_Request $request
	 */
	public function getModuleCustomNumberingData(Mycrm_Request $request) {
		$sourceModule = $request->get('sourceModule');

		$moduleModel = Settings_Mycrm_CustomRecordNumberingModule_Model::getInstance($sourceModule);
		$moduleData = $moduleModel->getModuleCustomNumberingData();
		
		$response = new Mycrm_Response();
		$response->setEmitType(Mycrm_Response::$EMIT_JSON);
		$response->setResult($moduleData);
		$response->emit();
	}

	/**
	 * Function save module custom numbering data
	 * @param Mycrm_Request $request
	 */
	public function saveModuleCustomNumberingData(Mycrm_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$sourceModule = $request->get('sourceModule');

		$moduleModel = Settings_Mycrm_CustomRecordNumberingModule_Model::getInstance($sourceModule);
		$moduleModel->set('prefix', $request->get('prefix'));
		$moduleModel->set('sequenceNumber', $request->get('sequenceNumber'));

		$result = $moduleModel->setModuleSequence();

		$response = new Mycrm_Response();
		if ($result['success']) {
			$response->setResult(vtranslate('LBL_SUCCESSFULLY_UPDATED', $qualifiedModuleName));
		} else {
			$message = vtranslate('LBL_PREFIX_IN_USE', $qualifiedModuleName);
			$response->setError($message);
		}
		$response->emit();
	}

	/**
	 * Function to update record with sequence number
	 * @param Mycrm_Request $request
	 */
	public function updateRecordsWithSequenceNumber(Mycrm_Request $request) {
		$sourceModule = $request->get('sourceModule');

		$moduleModel = Settings_Mycrm_CustomRecordNumberingModule_Model::getInstance($sourceModule);
		$result = $moduleModel->updateRecordsWithSequence();

		$response = new Mycrm_Response();
		$response->setResult($result);
		$response->emit();
	}
        
        public function validateRequest(Mycrm_Request $request) { 
            $request->validateWriteAccess(); 
        }
}