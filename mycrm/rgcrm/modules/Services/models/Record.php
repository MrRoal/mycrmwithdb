<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Services_Record_Model extends Products_Record_Model {

	function getCreateQuoteUrl() {
		$quotesModuleModel = Mycrm_Module_Model::getInstance('Quotes');

		return "index.php?module=".$quotesModuleModel->getName()."&view=".$quotesModuleModel->getEditViewName()."&service_id=".$this->getId().
				"&sourceModule=".$this->getModuleName()."&sourceRecord=".$this->getId()."&relationOperation=true";
	}

	function getCreateInvoiceUrl() {
		$invoiceModuleModel = Mycrm_Module_Model::getInstance('Invoice');

		return "index.php?module=".$invoiceModuleModel->getName()."&view=".$invoiceModuleModel->getEditViewName()."&service_id=".$this->getId().
				"&sourceModule=".$this->getModuleName()."&sourceRecord=".$this->getId()."&relationOperation=true";
	}

	function getCreatePurchaseOrderUrl() {
		$purchaseOrderModuleModel = Mycrm_Module_Model::getInstance('PurchaseOrder');

		return "index.php?module=".$purchaseOrderModuleModel->getName()."&view=".$purchaseOrderModuleModel->getEditViewName()."&service_id=".$this->getId().
				"&sourceModule=".$this->getModuleName()."&sourceRecord=".$this->getId()."&relationOperation=true";
	}

	function getCreateSalesOrderUrl() {
		$salesOrderModuleModel = Mycrm_Module_Model::getInstance('SalesOrder');

		return "index.php?module=".$salesOrderModuleModel->getName()."&view=".$salesOrderModuleModel->getEditViewName()."&service_id=".$this->getId().
				"&sourceModule=".$this->getModuleName()."&sourceRecord=".$this->getId()."&relationOperation=true";
	}
	
	/**
	 * Function to get acive status of record
	 */
	public function getActiveStatusOfRecord(){
		$activeStatus = $this->get('discontinued');
		if($activeStatus){
			return $activeStatus;
		}
		$recordId = $this->getId();
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT discontinued FROM mycrm_service WHERE serviceid = ?',array($recordId));
		$activeStatus = $db->query_result($result, 'discontinued');
		return $activeStatus;
	}
	
}
