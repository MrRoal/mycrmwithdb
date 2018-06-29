<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Potentials_DetailView_Model extends Mycrm_DetailView_Model {
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
        $quoteModuleModel = Mycrm_Module_Model::getInstance('Quotes');

		if($currentUserModel->hasModuleActionPermission($invoiceModuleModel->getId(), 'EditView')) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => vtranslate('LBL_CREATE').' '.vtranslate($invoiceModuleModel->getSingularLabelKey(), 'Invoice'),
				'linkurl' => $recordModel->getCreateInvoiceUrl(),
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Mycrm_Link_Model::getInstanceFromValues($basicActionLink);
		}
        
        if($currentUserModel->hasModuleActionPermission($quoteModuleModel->getId(), 'EditView')) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => vtranslate('LBL_CREATE').' '.vtranslate($quoteModuleModel->getSingularLabelKey(), 'Quotes'),
				'linkurl' => $recordModel->getCreateQuoteUrl(),
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Mycrm_Link_Model::getInstanceFromValues($basicActionLink);
		}

		$CalendarActionLinks[] = array();
		$CalendarModuleModel = Mycrm_Module_Model::getInstance('Calendar');
		if($currentUserModel->hasModuleActionPermission($CalendarModuleModel->getId(), 'EditView')) {
			$CalendarActionLinks[] = array(
					'linktype' => 'DETAILVIEW',
					'linklabel' => 'LBL_ADD_EVENT',
					'linkurl' => $recordModel->getCreateEventUrl(),
					'linkicon' => ''
			);

			$CalendarActionLinks[] = array(
					'linktype' => 'DETAILVIEW',
					'linklabel' => 'LBL_ADD_TASK',
					'linkurl' => $recordModel->getCreateTaskUrl(),
					'linkicon' => ''
			);
		}
		
        foreach($CalendarActionLinks as $basicLink) {
			$linkModelList['DETAILVIEW'][] = Mycrm_Link_Model::getInstanceFromValues($basicLink);
		}

		return $linkModelList;
	}
	
	/**
	 * Function to get the detail view widgets
	 * @return <Array> - List of widgets , where each widget is an Mycrm_Link_Model
	 */
	public function getWidgets() {
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$widgetLinks = parent::getWidgets();
		$widgets = array();

		$documentsInstance = Mycrm_Module_Model::getInstance('Documents');
		if($userPrivilegesModel->hasModuleActionPermission($documentsInstance->getId(), 'DetailView')) {
			$createPermission = $userPrivilegesModel->hasModuleActionPermission($documentsInstance->getId(), 'EditView');
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'Documents',
					'linkName'	=> $documentsInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=Documents&mode=showRelatedRecords&page=1&limit=5',
					'action'	=>	($createPermission == true) ? array('Add') : array(),
					'actionURL' =>	$documentsInstance->getQuickCreateUrl()
			);
		}

		$contactsInstance = Mycrm_Module_Model::getInstance('Contacts');
		if($userPrivilegesModel->hasModuleActionPermission($contactsInstance->getId(), 'DetailView')) {
			$createPermission = $userPrivilegesModel->hasModuleActionPermission($contactsInstance->getId(), 'EditView');
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'LBL_RELATED_CONTACTS',
					'linkName'	=> $contactsInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=Contacts&mode=showRelatedRecords&page=1&limit=5',
					'action'	=>	($createPermission == true) ? array('Add') : array(),
					'actionURL' =>	$contactsInstance->getQuickCreateUrl()
			);
		}

		$productsInstance = Mycrm_Module_Model::getInstance('Products');
		if($userPrivilegesModel->hasModuleActionPermission($productsInstance->getId(), 'DetailView')) {
			$createPermission = $userPrivilegesModel->hasModuleActionPermission($productsInstance->getId(), 'EditView');
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'LBL_RELATED_PRODUCTS',
					'linkName'	=> $productsInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=Products&mode=showRelatedRecords&page=1&limit=5',
					'action'	=>	($createPermission == true) ? array('Add') : array(),
					'actionURL' =>	$productsInstance->getQuickCreateUrl()
			);
		}

		foreach ($widgets as $widgetDetails) {
			$widgetLinks[] = Mycrm_Link_Model::getInstanceFromValues($widgetDetails);
		}

		return $widgetLinks;
	}
}
