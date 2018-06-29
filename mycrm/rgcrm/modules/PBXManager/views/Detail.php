<?php
/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */

class PBXManager_Detail_View extends Mycrm_Detail_View{
    
    /**
     * Overrided to disable Ajax Edit option in Detail View of
     * PBXManager Record
     */
    function isAjaxEnabled($recordModel) {
		return false;
	}
 
    /*
     * Overided to convert totalduration to minutes
     */
    function preProcess(Mycrm_Request $request, $display=true) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		if(!$this->record){
			$this->record = Mycrm_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();
        
       // To show recording link only if callstatus is 'completed' 
        if($recordModel->get('callstatus') != 'completed') { 
            $recordModel->set('recordingurl', ''); 
        }
        return parent::preProcess($request, true);
	}
}
