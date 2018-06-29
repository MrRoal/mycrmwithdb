<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Project_Detail_View extends Mycrm_Detail_View {
	
	function __construct() {
		parent::__construct();
		$this->exposeMethod('showRelatedRecords');
	}

	public function showModuleSummaryView($request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$recordModel = Mycrm_Record_Model::getInstanceById($recordId);
		$recordStrucure = Mycrm_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Mycrm_RecordStructure_Model::RECORD_STRUCTURE_MODE_SUMMARY);
		
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
        $viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		$viewer->assign('SUMMARY_INFORMATION', $recordModel->getSummaryInfo());
		$viewer->assign('SUMMARY_RECORD_STRUCTURE', $recordStrucure->getStructure());
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE_NAME', $moduleName);

		return $viewer->view('ModuleSummaryView.tpl', $moduleName, true);
	}
	
	/**
	 * Function returns related records based on related moduleName
	 * @param Mycrm_Request $request
	 * @return <type>
	 */
	function showRelatedRecords(Mycrm_Request $request) {
		$parentId = $request->get('record');
		$pageNumber = $request->get('page');
		$limit = $request->get('limit');
		$relatedModuleName = $request->get('relatedModule');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		$whereCondition = $request->get('whereCondition');
		$moduleName = $request->getModule();
		
		if($sortOrder == "ASC") {
			$nextSortOrder = "DESC";
			$sortImage = "icon-chevron-down";
		} else {
			$nextSortOrder = "ASC";
			$sortImage = "icon-chevron-up";
		}
		
		$parentRecordModel = Mycrm_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Mycrm_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);
		
		if(!empty($orderBy)) {
			$relationListView->set('orderby', $orderBy);
			$relationListView->set('sortorder', $sortOrder);
		}

		if(empty($pageNumber)) {
			$pageNumber = 1;
		}

		$pagingModel = new Mycrm_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if(!empty($limit)) {
			$pagingModel->set('limit', $limit);
		}

		if ($whereCondition) {
			$relationListView->set('whereCondition', $whereCondition);
		}

		$models = $relationListView->getEntries($pagingModel);
		$header = $relationListView->getHeaders();
		
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE' , $moduleName);
		$viewer->assign('RELATED_RECORDS' , $models);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE' , $relatedModuleName);
		$viewer->assign('RELATED_MODULE_MODEL', Mycrm_Module_Model::getInstance($relatedModuleName));
		$viewer->assign('PAGING_MODEL', $pagingModel);

		return $viewer->view('SummaryWidgets.tpl', $moduleName, 'true');
	}
}
?>
