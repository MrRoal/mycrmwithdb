<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Mycrm_FindDuplicatesAjax_View extends Mycrm_FindDuplicates_View {

	function process (Mycrm_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode) && method_exists($this, $mode)) {
			$this->$mode($request);
		}
	}
	/**
	 * Function to get listView count
	 * @param Mycrm_Request $request
	 */
	/*function getListViewCount(Mycrm_Request $request){
		$moduleName = $request->getModule();
		$cvId = $request->get('viewname');
		if(empty($cvId)) {
			$cvId = '0';
		}

		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');

		$listViewModel = Mycrm_ListView_Model::getInstance($moduleName, $cvId);
		$listViewModel->set('search_key', $searchKey);
		$listViewModel->set('search_value', $searchValue);
		$listViewModel->set('operator', $request->get('operator'));

		$count = $listViewModel->getListViewCount();

		return $count;
	}



	/**
	 * Function to get the page count for list
	 * @return total number of pages
	 */
	/*function getPageCount(Mycrm_Request $request){
		$listViewCount = $this->getListViewCount($request);
		$pagingModel = new Mycrm_Paging_Model();
		$pageLimit = $pagingModel->getPageLimit();
		$pageCount = ceil((int) $listViewCount / (int) $pageLimit);

		$result = array();
		$result['page'] = $pageCount;
		$response = new Mycrm_Response();
		$response->setResult($result);
		$response->emit();
	}*/
}