<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

/*
 * Settings List View Model Class
 */
class Settings_Workflows_ListView_Model extends Settings_Mycrm_ListView_Model {

	/**
	 * Function to get the list view entries
	 * @param Mycrm_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Mycrm_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel) {
		$db = PearDatabase::getInstance();

		$module = $this->getModule();
		$moduleName = $module->getName();
		$parentModuleName = $module->getParentName();
		$qualifiedModuleName = $moduleName;
		if(!empty($parentModuleName)) {
			$qualifiedModuleName = $parentModuleName.':'.$qualifiedModuleName;
		}
		$recordModelClass = Mycrm_Loader::getComponentClassName('Model', 'Record', $qualifiedModuleName);

		$listFields = $module->listFields;
		$listQuery = "SELECT ";
		foreach ($listFields as $fieldName => $fieldLabel) {
			$listQuery .= "$fieldName, ";
		}
		$listQuery .= $module->baseIndex . " FROM ". $module->baseTable;

		$params = array();
		$sourceModule = $this->get('sourceModule');
		if(!empty($sourceModule)) {
			$listQuery .= ' WHERE module_name = ?';
			$params[] = $sourceModule;
		}

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$orderBy = $this->getForSql('orderby');
		if (!empty($orderBy) && $orderBy === 'smownerid') { 
			$fieldModel = Mycrm_Field_Model::getInstance('assigned_user_id', $moduleModel); 
			if ($fieldModel->getFieldDataType() == 'owner') { 
				$orderBy = 'COALESCE(CONCAT(mycrm_users.first_name,mycrm_users.last_name),mycrm_groups.groupname)'; 
			} 
		}
		if(!empty($orderBy)) {
			$listQuery .= ' ORDER BY '. $orderBy . ' ' .$this->getForSql('sortorder');
		}
        $nextListQuery = $listQuery.' LIMIT '.($startIndex+$pageLimit).',1';
		$listQuery .= " LIMIT $startIndex,".($pageLimit+1);

		$listResult = $db->pquery($listQuery, $params);
		$noOfRecords = $db->num_rows($listResult);

		$listViewRecordModels = array();
		for($i=0; $i<$noOfRecords; ++$i) {
			$row = $db->query_result_rowdata($listResult, $i);
			$record = new $recordModelClass();
			$module_name = $row['module_name'];
			
			//To handle translation of calendar to To Do
			if($module_name == 'Calendar'){
				$module_name = vtranslate('LBL_TASK', $module_name);
			}else{
				$module_name = vtranslate($module_name, $module_name);
			}
			
			$row['module_name'] = $module_name;
			$row['execution_condition'] = vtranslate($record->executionConditionAsLabel($row['execution_condition']), 'Settings:Workflows');
			$record->setData($row);
			$listViewRecordModels[$record->getId()] = $record;
		}
		$pagingModel->calculatePageRange($listViewRecordModels);
		
		if($db->num_rows($listResult) > $pageLimit){
			array_pop($listViewRecordModels);
			$pagingModel->set('nextPageExists', true);
		}else{
			$pagingModel->set('nextPageExists', false);
		}
		
        $nextPageResult = $db->pquery($nextListQuery, $params);
        $nextPageNumRows = $db->num_rows($nextPageResult);
        if($nextPageNumRows <= 0) {
            $pagingModel->set('nextPageExists', false);
        }
		return $listViewRecordModels;
	}

	/*	 * *
	 * Function which will get the list view count
	 * @return - number of records
	 */

	public function getListViewCount() {
		$db = PearDatabase::getInstance();

		$module = $this->getModule();
		$listQuery = 'SELECT count(*) AS count FROM ' . $module->baseTable;

		$sourceModule = $this->get('sourceModule');
		if($sourceModule) {
			$listQuery .= " WHERE module_name = '$sourceModule'";
		}

		$listResult = $db->pquery($listQuery, array());
		return $db->query_result($listResult, 0, 'count');
	}
}