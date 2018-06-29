<?php

/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_LayoutEditor_Relation_Action extends Settings_Mycrm_Index_Action {
    
    public function process(Mycrm_Request $request) {
        $relationInfo = $request->get('related_info');
        $updatedRelatedList = $relationInfo['updated'];
        $deletedRelatedList = $relationInfo['deleted'];
		if(empty($updatedRelatedList)) {
			$updatedRelatedList = array();
		}
		if(empty($deletedRelatedList)) {
			$deletedRelatedList = array();
		}
        $sourceModule = $request->get('sourceModule');
        $moduleModel = Mycrm_Module_Model::getInstance($sourceModule, false);
        $relationModulesList = Mycrm_Relation_Model::getAllRelations($moduleModel, false);
        $sequenceList = array();
        foreach($relationModulesList as $relationModuleModel) {
            $sequenceList[] = $relationModuleModel->get('sequence');
        }
        //To sort sequence in ascending order
        sort($sequenceList);
        $relationUpdateDetail = array();
        $index = 0;
        foreach($updatedRelatedList as $relatedId) {
            $relationUpdateDetail[] = array('relation_id' => $relatedId, 'sequence' => $sequenceList[$index++] , 'presence' => 0);
        }
        foreach($deletedRelatedList as $relatedId) {
            $relationUpdateDetail[] = array('relation_id'=> $relatedId, 'sequence' => $sequenceList[$index++], 'presence' => 1);
        }
        $response = new Mycrm_Response();
        try{
            $response->setResult(array('success'=> true));
            Mycrm_Relation_Model::updateRelationSequenceAndPresence($relationUpdateDetail, $moduleModel->getId());
        }
        catch(Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
    
    public function validateRequest(Mycrm_Request $request) { 
        $request->validateWriteAccess(); 
    } 
}