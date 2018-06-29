<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Contacts_RelationListView_Model extends Mycrm_RelationListView_Model {
    
    public function getCreateViewUrl(){
        $createViewUrl = parent::getCreateViewUrl();
		$relationModuleModel = $this->getRelationModel()->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();

        //if parent module has account id it should be related to Potentials
        if($parentRecordModule->get('account_id') && $relationModuleModel->getName() == 'Potentials') {
            $createViewUrl .= '&related_to='.$parentRecordModule->get('account_id');
        }
		return $createViewUrl;
	}
}