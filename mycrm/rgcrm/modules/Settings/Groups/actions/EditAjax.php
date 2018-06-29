<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

Class Settings_Groups_EditAjax_Action extends Settings_Mycrm_IndexAjax_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('checkDuplicate');
	}

	public function process(Mycrm_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function checkDuplicate(Mycrm_Request $request) {
		$groupName = $request->get('groupname');
		$recordId = $request->get('record');
		
		$recordModel = Settings_Groups_Record_Model::getInstanceByName(decode_html($groupName), array($recordId));

		$response = new Mycrm_Response();
		if(!empty($recordModel)) {
			$response->setResult(array('success' => true,'message'=>  vtranslate('LBL_DUPLICATES_EXIST',$request->getModule(false))));
			
		}else{
			$response->setResult(array('success' => false));
		}
		$response->emit();
	}

}