<?php
/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */

class ModTracker_Relation_Model extends Mycrm_Record_Model {

	function setParent($parent) {
		$this->parent = $parent;
	}

	function getParent() {
		return $this->parent;
	}

	function getLinkedRecord() {
        $db = PearDatabase::getInstance();
        
		$targetId = $this->get('targetid');
		$targetModule = $this->get('targetmodule');
        
        $query = 'SELECT * FROM mycrm_crmentity WHERE crmid = ?';
		$params = array($targetId);
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);
		$moduleModels = array();
		if($noOfRows) {
			if(!array_key_exists($targetModule, $moduleModels)) {
				$moduleModel = Mycrm_Module_Model::getInstance($targetModule);
			}
			$row = $db->query_result_rowdata($result, 0);
			$modelClassName = Mycrm_Loader::getComponentClassName('Model', 'Record', $targetModule);
			$recordInstance = new $modelClassName();
			$recordInstance->setData($row)->setModuleFromInstance($moduleModel);
			$recordInstance->set('id', $row['crmid']);
			return $recordInstance;
		}
		return false;
	}
}