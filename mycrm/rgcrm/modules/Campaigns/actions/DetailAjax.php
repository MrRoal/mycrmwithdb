<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Campaigns_DetailAjax_Action extends Mycrm_BasicAjax_Action {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('getRecordsCount');
	}

	public function process(Mycrm_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Function to get related Records count from this relation
	 * @param <Mycrm_Request> $request
	 * @return <Number> Number of record from this relation
	 */
	public function getRecordsCount(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		$label = $request->get('tab_label');

		$parentRecordModel = Mycrm_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Mycrm_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);
		$count =  $relationListView->getRelatedEntriesCount();
		$result = array();
		$result['module'] = $moduleName;
		$result['viewname'] = $cvId;
		$result['count'] = $count;

		$response = new Mycrm_Response();
		$response->setEmitType(Mycrm_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
}