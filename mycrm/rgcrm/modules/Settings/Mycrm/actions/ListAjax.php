<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Mycrm_ListAjax_Action extends Settings_Mycrm_ListAjax_View{
    
    public function __construct() {
        parent::__construct();
        $this->exposeMethod('getPageCount');
    }
	/**
	 * Function returns the number of records for the current filter
	 * @param Mycrm_Request $request
	 */
	function getRecordsCount(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$cvId = $request->get('viewname');
		$count = $this->getListViewCount($request);

		$result = array();
		$result['module'] = $moduleName;
		$result['viewname'] = $cvId;
		$result['count'] = $count;

		$response = new Mycrm_Response();
		$response->setEmitType(Mycrm_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
    
    public function getListViewCount(Mycrm_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$sourceModule = $request->get('sourceModule');

		$listViewModel = Settings_Mycrm_ListView_Model::getInstance($qualifiedModuleName);
		
		if(!empty($sourceModule)) {
			$listViewModel->set('sourceModule', $sourceModule);
		}

		return $listViewModel->getListViewCount();
    }
    
    public function getPageCount(Mycrm_Request $request) {
        $numOfRecords = $this->getListViewCount($request);
        $pagingModel = new Mycrm_Paging_Model();
        $pageCount = ceil((int) $numOfRecords/(int)($pagingModel->getPageLimit()));
        
		if($pageCount == 0){
			$pageCount = 1;
		}
		$result = array();
		$result['page'] = $pageCount;
		$result['numberOfRecords'] = $numOfRecords;
		$response = new Mycrm_Response();
		$response->setResult($result);
		$response->emit();
    }
}