<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Project_Relation_Model extends Mycrm_Relation_Model{

	/**
	 * Function that deletes Project related records information
	 * @param <Integer> $sourceRecordId - Project Id
	 * @param <Integer> $relatedRecordId - Related Record Id
	 */
	public function deleteRelation($sourceRecordId, $relatedRecordId){
		$sourceModule = $this->getParentModuleModel();
		$sourceModuleName = $sourceModule->get('name');
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		$sourceModuleFocus = CRMEntity::getInstance($sourceModuleName);
        	$sourceModuleFocus->delete_related_module($sourceModuleName, $sourceRecordId, $destinationModuleName, $relatedRecordId);
		return true;
	}
}
