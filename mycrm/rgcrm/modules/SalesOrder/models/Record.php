<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Inventory Record Model Class
 */
class SalesOrder_Record_Model extends Inventory_Record_Model {

	function getCreateInvoiceUrl() {
		$invoiceModuleModel = Mycrm_Module_Model::getInstance('Invoice');

		return "index.php?module=".$invoiceModuleModel->getName()."&view=".$invoiceModuleModel->getEditViewName()."&salesorder_id=".$this->getId();
	}
        
        function getCreatePurchaseOrderUrl() {
	    $purchaseOrderModuleModel = Mycrm_Module_Model::getInstance('PurchaseOrder');
	    return "index.php?module=".$purchaseOrderModuleModel->getName()."&view=".$purchaseOrderModuleModel->getEditViewName()."&salesorder_id=".$this->getId();
	}

}