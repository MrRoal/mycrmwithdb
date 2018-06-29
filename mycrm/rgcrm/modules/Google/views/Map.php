<?php

/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */

class Google_Map_View extends Mycrm_Detail_View {

    /**
     * must be overriden
     * @param Mycrm_Request $request
     * @return boolean 
     */
    function preProcess(Mycrm_Request $request) {
        return true;
    }

    /**
     * must be overriden
     * @param Mycrm_Request $request
     * @return boolean 
     */
    function postProcess(Mycrm_Request $request) {
        return true;
    }

    /**
     * called when the request is recieved.
     * if viewtype : detail then show location
     * TODO : if viewtype : list then show the optimal route.    
     * @param Mycrm_Request $request 
     */
    function process(Mycrm_Request $request) {
        switch ($request->get('viewtype')) {
            case 'detail':$this->showLocation($request);
                break;
            default:break;
        }
    }

    /**
     * display the template.
     * @param Mycrm_Request $request 
     */
    function showLocation(Mycrm_Request $request) {
        $viewer = $this->getViewer($request);
        // record and source_module values to be passed to populate the values in the template,
        // required to get the respective records address based on the module type.
        $viewer->assign('RECORD', $request->get('record'));
        $viewer->assign('SOURCE_MODULE', $request->get('source_module'));
        $viewer->view('map.tpl', $request->getModule());
    }

}

?>
