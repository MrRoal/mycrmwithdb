<?php

/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_PickListDependency_Index_Action extends Settings_Mycrm_Basic_Action {
    
    function __construct() {
        parent::__construct();
        $this->exposeMethod('checkCyclicDependency');
    }
   
    public function checkCyclicDependency(Mycrm_Request $request) {
        $module = $request->get('sourceModule');
        $sourceField = $request->get('sourcefield');
        $targetField = $request->get('targetfield');
        $result = Mycrm_DependencyPicklist::checkCyclicDependency($module, $sourceField, $targetField);
        $response = new Mycrm_Response();
        $response->setResult(array('result'=>$result));
        $response->emit();
    }
}