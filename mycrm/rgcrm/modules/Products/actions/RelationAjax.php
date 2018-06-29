<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Products_RelationAjax_Action extends Mycrm_RelationAjax_Action {
	
	function __construct() {
		parent::__construct();
		$this->exposeMethod('addListPrice');
	}
	
	/*
	 * Function to add relation for specified source record id and related record id list
	 * @param <array> $request
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
			$relationModel->addRelation($sourceRecordId,$relatedRecordId,$listPrice);
			if($relatedModule == 'PriceBooks'){
				$recordModel = Mycrm_Record_Model::getInstanceById($relatedRecordId);
				if ($sourceRecordId && ($sourceModule === 'Products' || $sourceModule === 'Services')) {
					$parentRecordModel = Mycrm_Record_Model::getInstanceById($sourceRecordId, $sourceModule);
					$recordModel->updateListPrice($sourceRecordId, $parentRecordModel->get('unit_price'));
				}
			}
		}		
	}
	
	/**
	 * Function adds Products/Services-PriceBooks Relation
	 * @param type $request
	 */
	function addListPrice($request) {
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');
		$relatedModule =  $request->get('related_module');
		$relInfos = $request->get('relinfo');

		$sourceModuleModel = Mycrm_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Mycrm_Module_Model::getInstance($relatedModule);
		$relationModel = Mycrm_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		foreach($relInfos as $relInfo) {
			$price = CurrencyField::convertToDBFormat($relInfo['price'], null, true);
			$relationModel->addListPrice($sourceRecordId, $relInfo['id'], $price);
		}
	}
	
}
?>
