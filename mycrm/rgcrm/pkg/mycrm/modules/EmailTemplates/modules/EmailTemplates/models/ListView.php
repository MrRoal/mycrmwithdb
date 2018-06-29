<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class EmailTemplates_ListView_Model extends Mycrm_ListView_Model {
	
	
	/**
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associative array of Link type to List of  Mycrm_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams) {
		$moduleModel = $this->getModule();
		$linkTypes = array('LISTVIEWMASSACTION');
		$links = array();

		$massActionLinks[] = array(
			'linktype' => 'LISTVIEWMASSACTION',
			'linklabel' => 'LBL_DELETE',
			'linkurl' => 'javascript:Mycrm_List_Js.massDeleteRecords("index.php?module='.$moduleModel->get('name').'&action=MassDelete");',
			'linkicon' => ''
		);

		foreach($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Mycrm_Link_Model::getInstanceFromValues($massActionLink);
		}

		return $links;
	}
	
	/**
	 * Static Function to get the Instance of Mycrm ListView model for a given module and custom view
	 * @param <String> $moduleName - Module Name
	 * @param <Number> $viewId - Custom View Id
	 * @return Mycrm_ListView_Model instance
	 */
	public static function getInstance($moduleName, $viewId = 0) {
		$db = PearDatabase::getInstance();
		$modelClassName = Mycrm_Loader::getComponentClassName('Model', 'ListView', $moduleName);
		$instance = new $modelClassName();
		
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);
		return $instance->set('module', $moduleModel);
	}
	
	/**
	 * Function to get the list view header
	 * @return <Array> - List of Mycrm_Field_Model instances
	 */
	public function getListViewHeaders() {
		$fieldObjects = array();
		$listViewHeaders = array('Template Name' => 'templatename', 'Subject' => 'subject');
		foreach ($listViewHeaders as $key => $fieldName) {
			$fieldModel = new EmailTemplates_Field_Model();
			$fieldModel->set('name', $fieldName);
			$fieldModel->set('label', $key);
			$fieldModel->set('column', $fieldName);
			$fieldObjects[] = $fieldModel;
		}
		return $fieldObjects;
	}
	
	/**
	 * Function to get the list view entries
	 * @param Mycrm_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Mycrm_Record_Model instance.
	 */
	
	public function getListViewEntries($pagingModel) {
		$db = PearDatabase::getInstance();
		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();
		$orderBy = $this->getForSql('orderby');
		$sortOrder = $this->getForSql('sortorder');

		$listQuery = $this->getQuery();
		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		
		if(!empty($searchKey) && !empty($searchValue)) {
			$listQuery .= " WHERE $searchKey LIKE '$searchValue%'";
		}
		if (!empty($orderBy) && $orderBy === 'smownerid') { 
			$fieldModel = Mycrm_Field_Model::getInstance('assigned_user_id', $moduleModel); 
			if ($fieldModel->getFieldDataType() == 'owner') { 
				$orderBy = 'COALESCE(CONCAT(mycrm_users.first_name,mycrm_users.last_name),mycrm_groups.groupname)'; 
			} 
		}	
		if ($orderBy) {
			$listQuery .= " ORDER BY $orderBy $sortOrder";
		}
		$listQuery .= " LIMIT $startIndex,".($pageLimit+1);

		$result = $db->pquery($listQuery, array());
		$num_rows = $db->num_rows($result);
		
		$listViewRecordModels = array();
		for ($i = 0; $i < $num_rows; $i++) {
			$recordModel = new EmailTemplates_Record_Model();
			$recordModel->setModule('EmailTemplates');
			$row = $db->query_result_rowdata($result, $i);
			$listViewRecordModels[$row['templateid']] = $recordModel->setData($row);
		}
		
		$pagingModel->calculatePageRange($listViewRecordModels);

		if($num_rows > $pageLimit){
			array_pop($listViewRecordModels);
			$pagingModel->set('nextPageExists', true);
		}else{
			$pagingModel->set('nextPageExists', false);
		}
		
        return $listViewRecordModels;
	}
	
	/**
	 * Function to get the list of listview links for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associate array of Link Type to List of Mycrm_Link_Model instances
	 */
	public function getListViewLinks($linkParams) {
		$moduleModel = $this->getModule();

		$linkTypes = array('LISTVIEWBASIC', 'LISTVIEW', 'LISTVIEWSETTING');
		$links = Mycrm_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);

		$basicLinks = array(
				array(
						'linktype' => 'LISTVIEWBASIC',
						'linklabel' => 'LBL_ADD_RECORD',
						'linkurl' => $moduleModel->getCreateRecordUrl(),
						'linkicon' => ''
				)
		);
		foreach($basicLinks as $basicLink) {
			$links['LISTVIEWBASIC'][] = Mycrm_Link_Model::getInstanceFromValues($basicLink);
		}

		return $links;
	}
	
	function getQuery() {
		$listQuery = 'SELECT templateid, templatename, foldername, subject FROM mycrm_emailtemplates';
		return $listQuery;
	}
	
	/**
	 * Function to get the list view entries
	 * @param Mycrm_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Mycrm_Record_Model instance.
	 */
	public function getListViewCount() {
		$db = PearDatabase::getInstance();

		$listQuery = $this->getQuery();
		
		$position = stripos($listQuery, 'from');
		if ($position) {
			$split = spliti('from', $listQuery);
			$splitCount = count($split);
			$listQuery = 'SELECT count(*) AS count ';
			for ($i=1; $i<$splitCount; $i++) {
				$listQuery = $listQuery. ' FROM ' .$split[$i];
			}
		}
		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		
		if(!empty($searchKey) && !empty($searchValue)) {
			$listQuery .= " WHERE $searchKey LIKE '$searchValue%'";
		}

		$listResult = $db->pquery($listQuery, array());
		return $db->query_result($listResult, 0, 'count');
	}
	
} 