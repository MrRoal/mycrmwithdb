<?php

/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */

class Google_SaveSettings_Action extends Mycrm_BasicAjax_Action {

    public function process(Mycrm_Request $request) {
        $sourceModule = $request->get('sourcemodule');
        $fieldMapping = $request->get('fieldmapping');
        Google_Utils_Helper::saveSettings($request);
        Google_Utils_Helper::saveFieldMappings($sourceModule, $fieldMapping);
        $response = new Mycrm_Response;
        $result = array('settingssaved' => true);
        $response->setResult($result);
        $response->emit();
    }
    
}

?>