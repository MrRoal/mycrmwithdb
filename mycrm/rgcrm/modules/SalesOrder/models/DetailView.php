<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class SalesOrder_DetailView_Model extends Inventory_DetailView_Model {

	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$linkModelList = parent::getDetailViewLinks($linkParams);
		$recordModel = $this->getRecord();

		$invoiceModuleModel = Mycrm_Module_Model::getInstance('Invoice');
		if($currentUserModel->hasModuleActionPermission($invoiceModuleModel->getId(), 'EditView')) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => vtranslate('LBL_CREATE').' '.vtranslate($invoiceModuleModel->getSingularLabelKey(), 'Invoice'),
				'linkurl' => $recordModel->getCreateInvoiceUrl(),
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Mycrm_Link_Model::getInstanceFromValues($basicActionLink);
		}
                
                $purchaseOrderModuleModel = Mycrm_Module_Model::getInstance('PurchaseOrder');
		if($currentUserModel->hasModuleActionPermission($purchaseOrderModuleModel->getId(), 'EditView')) {
		    $basicActionLink = array(
		            'linktype' => 'DETAILVIEW',
		            'linklabel' => vtranslate('LBL_CREATE').' '.vtranslate($purchaseOrderModuleModel->getSingularLabelKey(), 'PurchaseOrder'),
		            'linkurl' => $recordModel->getCreatePurchaseOrderUrl(),
		            'linkicon' => ''
		    );
		    $linkModelList['DETAILVIEW'][] = Mycrm_Link_Model::getInstanceFromValues($basicActionLink);
		}
		
		return $linkModelList;
	}
		
}
