<?php

/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */

class ModComments_Save_Action extends Mycrm_Save_Action {

	public function process(Mycrm_Request $request) {
		$recordId = $request->get('record');
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$request->set('assigned_user_id', $currentUserModel->getId());
		$request->set('userid', $currentUserModel->getId());
		
		$recordModel = $this->saveRecord($request);
		$responseFieldsToSent = array('reasontoedit','commentcontent');
		$fieldModelList = $recordModel->getModule()->getFields();
		foreach ($responseFieldsToSent as $fieldName) {
            $fieldModel = $fieldModelList[$fieldName];
            $fieldValue = $recordModel->get($fieldName);
			$result[$fieldName] = $fieldModel->getDisplayValue(Mycrm_Util_Helper::toSafeHTML($fieldValue));
		}

		$result['success'] = true;
		$result['modifiedtime'] = Mycrm_Util_Helper::formatDateDiffInStrings($recordModel->get('modifiedtime'));
		$result['modifiedtimetitle'] = Mycrm_Util_Helper::formatDateTimeIntoDayString($recordModel->get('modifiedtime'));

		$response = new Mycrm_Response();
		$response->setEmitType(Mycrm_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
	
	/**
	 * Function to save record
	 * @param <Mycrm_Request> $request - values of the record
	 * @return <RecordModel> - record Model of saved record
	 */
	public function saveRecord($request) {
		$recordModel = $this->getRecordModelFromRequest($request);
		$recordModel->save();
		if($request->get('relationOperation')) {
			$parentModuleName = $request->get('sourceModule');
			$parentModuleModel = Mycrm_Module_Model::getInstance($parentModuleName);
			$parentRecordId = $request->get('sourceRecord');
			$relatedModule = $recordModel->getModule();
			$relatedRecordId = $recordModel->getId();

			$relationModel = Mycrm_Relation_Model::getInstance($parentModuleModel, $relatedModule);
			$relationModel->addRelation($parentRecordId, $relatedRecordId);
		}
		return $recordModel;
	}
	
	/**
	 * Function to get the record model based on the request parameters
	 * @param Mycrm_Request $request
	 * @return Mycrm_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(Mycrm_Request $request) {
		$recordModel = parent::getRecordModelFromRequest($request);
		
		$recordModel->set('commentcontent', $request->getRaw('commentcontent'));
		$recordModel->set('reasontoedit', $request->getRaw('reasontoedit'));

		return $recordModel;
	}
	
}
