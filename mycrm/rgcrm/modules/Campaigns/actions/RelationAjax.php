<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Campaigns_RelationAjax_Action extends Mycrm_RelationAjax_Action {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('addRelationsFromRelatedModuleViewId');
		$this->exposeMethod('updateStatus');
	}

	/**
	 * Function to add relations using related module viewid
	 * @param Mycrm_Request $request
	 */
	public function addRelationsFromRelatedModuleViewId(Mycrm_Request $request) {
		$sourceRecordId = $request->get('sourceRecord');
		$relatedModuleName = $request->get('relatedModule');

		$viewId = $request->get('viewId');
		if ($viewId) {
			$sourceModuleModel = Mycrm_Module_Model::getInstance($request->getModule());
			$relatedModuleModel = Mycrm_Module_Model::getInstance($relatedModuleName);

			$relationModel = Mycrm_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
			$emailEnabledModulesInfo = $relationModel->getEmailEnabledModulesInfoForDetailView();

			if (array_key_exists($relatedModuleName, $emailEnabledModulesInfo)) {
				$fieldName = $emailEnabledModulesInfo[$relatedModuleName]['fieldName'];

				$db = PearDatabase::getInstance();
				$currentUserModel = Users_Record_Model::getCurrentUserModel();

				$queryGenerator = new QueryGenerator($relatedModuleName, $currentUserModel);
				$queryGenerator->initForCustomViewById($viewId);

				$query = $queryGenerator->getQuery();
				$result = $db->pquery($query, array());

				$numOfRows = $db->num_rows($result);
				for ($i=0; $i<$numOfRows; $i++) {
					$relatedRecordIdsList[] = $db->query_result($result, $i, $fieldName);
				}
				if(empty($relatedRecordIdsList)){
					$response = new Mycrm_Response();
					$response->setResult(array(false));
					$response->emit();
				} else{
					foreach($relatedRecordIdsList as $relatedRecordId) {
						$relationModel->addRelation($sourceRecordId, $relatedRecordId);
					}
				}
			}
		}
	}

	/**
	 * Function to update Relation status
	 * @param Mycrm_Request $request
	 */
	public function updateStatus(Mycrm_Request $request) {
		$relatedModuleName = $request->get('relatedModule');
		$relatedRecordId = $request->get('relatedRecord');
		$status = $request->get('status');
		$response = new Mycrm_Response();

		if ($relatedRecordId && $status && $status < 5) {
			$sourceModuleModel = Mycrm_Module_Model::getInstance($request->getModule());
			$relatedModuleModel = Mycrm_Module_Model::getInstance($relatedModuleName);

			$relationModel = Mycrm_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
			$relationModel->updateStatus($request->get('sourceRecord'), array($relatedRecordId => $status));

			$response->setResult(array(true));
		} else {
			$response->setError($code);
		}
		$response->emit();
	}
}
