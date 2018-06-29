<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class PriceBooks_Save_Action extends Mycrm_Save_Action {

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

			//To store the relationship between Products/Services and PriceBooks
			if ($parentRecordId && ($parentModuleName === 'Products' || $parentModuleName === 'Services')) {
				$parentRecordModel = Mycrm_Record_Model::getInstanceById($parentRecordId, $parentModuleName);
				$recordModel->updateListPrice($parentRecordId, $parentRecordModel->get('unit_price'));
			}
		}
		return $recordModel;
	}

}