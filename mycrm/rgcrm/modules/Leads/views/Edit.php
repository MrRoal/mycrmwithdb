<?php

/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */

class Leads_Edit_View extends Mycrm_Edit_View {

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
        $recordModel = $this->record;
        if(!$recordModel){
            if (!empty($recordId)) {
                $recordModel = Mycrm_Record_Model::getInstanceById($recordId, $moduleName);
            } else {
                $recordModel = Mycrm_Record_Model::getCleanInstance($moduleName);
            }
        }

		$viewer = $this->getViewer($request);

		$salutationFieldModel = Mycrm_Field_Model::getInstance('salutationtype', $recordModel->getModule());
		// Fix for http://trac.mycrm.com/cgi-bin/trac.cgi/ticket/7851
		$salutationType = $request->get('salutationtype');
		if(!empty($salutationType)){ 
                    $salutationFieldModel->set('fieldvalue', $request->get('salutationtype')); 
                } 
                else{ 
                    $salutationFieldModel->set('fieldvalue', $recordModel->get('salutationtype')); 
                } 
		$viewer->assign('SALUTATION_FIELD_MODEL', $salutationFieldModel);

		parent::process($request);
	}

}
