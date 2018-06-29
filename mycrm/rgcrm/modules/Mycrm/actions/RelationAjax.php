<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_RelationAjax_Action extends Mycrm_Action_Controller {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('addRelation');
		$this->exposeMethod('deleteRelation');
		$this->exposeMethod('getRelatedListPageCount');
	}

	function checkPermission(Mycrm_Request $request) { }

	function preProcess(Mycrm_Request $request) {
		return true;
	}

	function postProcess(Mycrm_Request $request) {
		return true;
	}

	function process(Mycrm_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/*
	 * Function to add relation for specified source record id and related record id list
	 * @param <array> $request
	 *		keys					Content
	 *		src_module				source module name
	 *		src_record				source record id
	 *		related_module			related module name
	 *		related_record_list		json encoded of list of related record ids
	 */
	function addRelation($request) {
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');

		$relatedModule = $request->get('related_module');
		$relatedRecordIdList = $request->get('related_record_list');

		$sourceModuleModel = Mycrm_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Mycrm_Module_Model::getInstance($relatedModule);
		$relationModel = Mycrm_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		foreach($relatedRecordIdList as $relatedRecordId) {
			$relationModel->addRelation($sourceRecordId,$relatedRecordId);
		}
	}

	/**
	 * Function to delete the relation for specified source record id and related record id list
	 * @param <array> $request
	 *		keys					Content
	 *		src_module				source module name
	 *		src_record				source record id
	 *		related_module			related module name
	 *		related_record_list		json encoded of list of related record ids
	 */
	function deleteRelation($request) {
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');

		$relatedModule = $request->get('related_module');
		$relatedRecordIdList = $request->get('related_record_list');

		//Setting related module as current module to delete the relation
		vglobal('currentModule', $relatedModule);

		$sourceModuleModel = Mycrm_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Mycrm_Module_Model::getInstance($relatedModule);
		$relationModel = Mycrm_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		foreach($relatedRecordIdList as $relatedRecordId) {
			$response = $relationModel->deleteRelation($sourceRecordId,$relatedRecordId);
		}
		echo $response;
	}
	
	/**
	 * Function to get the page count for reltedlist
	 * @return total number of pages
	 */
	function getRelatedListPageCount(Mycrm_Request $request){
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		$label = $request->get('tab_label');
		$pagingModel = new Mycrm_Paging_Model();
		$parentRecordModel = Mycrm_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Mycrm_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);
		$totalCount = $relationListView->getRelatedEntriesCount();
		$pageLimit = $pagingModel->getPageLimit();
		$pageCount = ceil((int) $totalCount / (int) $pageLimit);

		if($pageCount == 0){
			$pageCount = 1;
		}
		$result = array();
		$result['numberOfRecords'] = $totalCount;
		$result['page'] = $pageCount;
		$response = new Mycrm_Response();
		$response->setResult($result);
		$response->emit();
	}
        
        public function validateRequest(Mycrm_Request $request) { 
            $request->validateWriteAccess(); 
        } 
}
