<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/
include_once 'include/InventoryPDFController.php';
include_once dirname(__FILE__). '/SalesOrderPDFHeaderViewer.php';
class Mycrm_SalesOrderPDFController extends Mycrm_InventoryPDFController{
	function buildHeaderModelTitle() {
		$singularModuleNameKey = 'SINGLE_'.$this->moduleName;
		$translatedSingularModuleLabel = getTranslatedString($singularModuleNameKey, $this->moduleName);
		if($translatedSingularModuleLabel == $singularModuleNameKey) {
			$translatedSingularModuleLabel = getTranslatedString($this->moduleName, $this->moduleName);
		}
		return sprintf("%s: %s", $translatedSingularModuleLabel, $this->focusColumnValue('salesorder_no'));
	}

	function getHeaderViewer() {
		$headerViewer = new SalesOrderPDFHeaderViewer();
		$headerViewer->setModel($this->buildHeaderModel());
		return $headerViewer;
	}
	
	function buildHeaderModelColumnLeft() {
		$modelColumnLeft = parent::buildHeaderModelColumnLeft();
		return $modelColumnLeft;
	}
	
	function buildHeaderModelColumnCenter() {
		$subject = $this->focusColumnValue('subject');
		$customerName = $this->resolveReferenceLabel($this->focusColumnValue('account_id'), 'Accounts');
		$contactName = $this->resolveReferenceLabel($this->focusColumnValue('contact_id'), 'Contacts');
		$purchaseOrder = $this->focusColumnValue('mycrm_purchaseorder');
		$quoteName = $this->resolveReferenceLabel($this->focusColumnValue('quote_id'), 'Quotes');
		
		$subjectLabel = getTranslatedString('Subject', $this->moduleName);
        $quoteNameLabel = getTranslatedString('Quote Name', $this->moduleName);
		$customerNameLabel = getTranslatedString('Customer Name', $this->moduleName);
		$contactNameLabel = getTranslatedString('Contact Name', $this->moduleName);
		$purchaseOrderLabel = getTranslatedString('Purchase Order', $this->moduleName);

		$modelColumn1 = array(
				$subjectLabel		=>	$subject,
				$customerNameLabel	=>	$customerName,
				$contactNameLabel	=>	$contactName,
				$purchaseOrderLabel =>  $purchaseOrder,
                $quoteNameLabel => $quoteName
			);
		return $modelColumn1;
	}

	function buildHeaderModelColumnRight() {
		$issueDateLabel = getTranslatedString('Issued Date', $this->moduleName);
		$validDateLabel = getTranslatedString('Due Date', $this->moduleName);
		$billingAddressLabel = getTranslatedString('Billing Address', $this->moduleName);
		$shippingAddressLabel = getTranslatedString('Shipping Address', $this->moduleName);


		$modelColumn2 = array(
				'dates' => array(
					$issueDateLabel  => $this->formatDate(date("Y-m-d")),
					$validDateLabel => $this->formatDate($this->focusColumnValue('duedate')),
				),
				$billingAddressLabel  => $this->buildHeaderBillingAddress(),
				$shippingAddressLabel => $this->buildHeaderShippingAddress()
			);
		return $modelColumn2;
	}

	function getWatermarkContent() {
		return $this->focusColumnValue('sostatus');
	}
}
?>