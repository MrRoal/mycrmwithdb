<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Products_RelationListView_Model extends Mycrm_RelationListView_Model {

	/**
	 * Function to get the links for related list
	 * @return <Array> List of action models <Mycrm_Link_Model>
	 */
	public function getLinks() {
		$relationModel = $this->getRelationModel();
		$parentModel = $this->getParentRecordModel();
		
		$isSubProduct = false;
		if($parentModel->getModule()->getName() == $relationModel->getRelationModuleModel()->getName()) {
			$isSubProduct = $relationModel->isSubProduct($parentModel->getId());
		}
		
		if(!$isSubProduct){
			return parent::getLinks();
		}
	}
	
	public function getHeaders() {
		$headerFields = parent::getHeaders();
		if($this->getRelationModel()->getRelationModuleModel()->getName() == 'PriceBooks') {
			//Added to support Unit Price
			$unitPriceField = new Mycrm_Field_Model();
			$unitPriceField->set('name', 'unit_price');
			$unitPriceField->set('column', 'unit_price');
			$unitPriceField->set('label', 'Unit Price');
			
			$headerFields['unit_price'] = $unitPriceField;
			
			//Added to support List Price
			$field = new Mycrm_Field_Model();
			$field->set('name', 'listprice');
			$field->set('column', 'listprice');
			$field->set('label', 'List Price');
			
			$headerFields['listprice'] = $field;
		}
		
		return $headerFields;
	}
	
}
