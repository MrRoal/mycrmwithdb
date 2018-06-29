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
 * PurchaseOrder Record Model Class
 */
class PurchaseOrder_Record_Model extends Inventory_Record_Model {
	
	/**
	 * This Function adds the specified product quantity to the Product Quantity in Stock
	 * @param type $recordId
	 */
	function addStockToProducts($recordId) {
		$db = PearDatabase::getInstance();

		$recordModel = Inventory_Record_Model::getInstanceById($recordId);
		$relatedProducts = $recordModel->getProducts();

		foreach ($relatedProducts as $key => $relatedProduct) {
			if($relatedProduct['qty'.$key]){
				$productId = $relatedProduct['hdnProductId'.$key];
				$result = $db->pquery("SELECT qtyinstock FROM mycrm_products WHERE productid=?", array($productId));
				$qty = $db->query_result($result,0,"qtyinstock");
				$stock = $qty + $relatedProduct['qty'.$key];
				$db->pquery("UPDATE mycrm_products SET qtyinstock=? WHERE productid=?", array($stock, $productId));
			}
		}
	}
	
	/**
	 * This Function returns the current status of the specified Purchase Order.
	 * @param type $purchaseOrderId
	 * @return <String> PurchaseOrderStatus
	 */
	function getPurchaseOrderStatus($purchaseOrderId){
			$db = PearDatabase::getInstance();
			$sql = "SELECT postatus FROM mycrm_purchaseorder WHERE purchaseorderid=?";
			$result = $db->pquery($sql, array($purchaseOrderId));
			$purchaseOrderStatus = $db->query_result($result,0,"postatus");
			return $purchaseOrderStatus;
	}
}