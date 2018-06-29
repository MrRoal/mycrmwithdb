<?php

/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */

class Google_MapAjax_Action extends Mycrm_BasicAjax_Action {

    public function process(Mycrm_Request $request) {
        switch ($request->get("mode")) {
            case 'getLocation':$result = $this->getLocation($request);
                break;
        }
        echo json_encode($result);
    }

    /**
     * get address for the record, based on the module type.
     * @param Mycrm_Request $request
     * @return type 
     */
    function getLocation(Mycrm_Request $request) {
        $address = Google_Map_Helper::getLocation($request);
        return empty($address) ? "" : array("address" => join(",", $address));
    }
    
    public function validateRequest(Mycrm_Request $request) { 
        $request->validateReadAccess(); 
    } 

}

?>
