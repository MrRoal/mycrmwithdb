<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_PickListDependency_IndexAjax_View extends Settings_PickListDependency_Edit_View {

    public function __construct() {
        parent::__construct();
        $this->exposeMethod('getDependencyGraph');
    }
    
    public function preProcess(Mycrm_Request $request) {
        return true;
    }
    
    public function postProcess(Mycrm_Request $request) {
        return true;
    }
    
    public function process(Mycrm_Request $request) {
        $mode = $request->getMode();

		if($mode){
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
    }
    
}