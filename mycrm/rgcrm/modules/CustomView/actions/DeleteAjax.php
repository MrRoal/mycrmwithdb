<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class CustomView_DeleteAjax_Action extends Mycrm_Action_Controller {

	function preProcess(Mycrm_Request $request) {
		return true;
	}

	function postProcess(Mycrm_Request $request) {
		return true;
	}

	public function process(Mycrm_Request $request) {
		$customViewModel = CustomView_Record_Model::getInstanceById($request->get('record'));

		$customViewModel->delete();
	}
    
    public function validateRequest(Mycrm_Request $request) {
        $request->validateWriteAccess();
    }
}
