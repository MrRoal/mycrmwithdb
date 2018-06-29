<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Campaigns_RelationListView_Model extends Mycrm_RelationListView_Model {

	/**
	 * Function to get the links for related list
	 * @return <Array> List of action models <Mycrm_Link_Model>
	 */
	public function getLinks() {
		$relatedLinks = parent::getLinks();
		$relationModel = $this->getRelationModel();
		$relatedModuleName = $relationModel->getRelationModuleModel()->getName();

		if (array_key_exists($relatedModuleName, $relationModel->getEmailEnabledModulesInfoForDetailView())) {
			$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			if ($currentUserPriviligesModel->hasModulePermission(getTabid('Emails'))) {
				$emailLink = Mycrm_Link_Model::getInstanceFromValues(array(
						'linktype' => 'LISTVIEWBASIC',
						'linklabel' => vtranslate('LBL_SEND_EMAIL', $relatedModuleName),
						'linkurl' => "javascript:Campaigns_RelatedList_Js.triggerSendEmail('index.php?module=$relatedModuleName&view=MassActionAjax&mode=showComposeEmailForm&step=step1','Emails');",
						'linkicon' => ''
				));
				$emailLink->set('_sendEmail',true);
				$relatedLinks['LISTVIEWBASIC'][] = $emailLink;
			}
		}
		return $relatedLinks;
	}

	/**
	 * Function to get list of record models in this relation
	 * @param <Mycrm_Paging_Model> $pagingModel
	 * @return <array> List of record models <Mycrm_Record_Model>
	 */
	public function getEntries($pagingModel) {
		$relationModel = $this->getRelationModel();
		$parentRecordModel = $this->getParentRecordModel();
		$relatedModuleName = $relationModel->getRelationModuleModel()->getName();

		$relatedRecordModelsList = parent::getEntries($pagingModel);
		$emailEnabledModulesInfo = $relationModel->getEmailEnabledModulesInfoForDetailView();

		if (array_key_exists($relatedModuleName, $emailEnabledModulesInfo) && $relatedRecordModelsList) {
			$fieldName = $emailEnabledModulesInfo[$relatedModuleName]['fieldName'];
			$tableName = $emailEnabledModulesInfo[$relatedModuleName]['tableName'];

			$db = PearDatabase::getInstance();
			$relatedRecordIdsList = array_keys($relatedRecordModelsList);

			$query = "SELECT campaignrelstatus, $fieldName FROM $tableName
						INNER JOIN mycrm_campaignrelstatus ON mycrm_campaignrelstatus.campaignrelstatusid = $tableName.campaignrelstatusid
						WHERE $fieldName IN (". generateQuestionMarks($relatedRecordIdsList).") AND campaignid = ?";
			array_push($relatedRecordIdsList, $parentRecordModel->getId());

			$result = $db->pquery($query, $relatedRecordIdsList);
			$numOfrows = $db->num_rows($result);

			for($i=0; $i<$numOfrows; $i++) {
				$recordId = $db->query_result($result, $i, $fieldName);
				$relatedRecordModel = $relatedRecordModelsList[$recordId];

				$relatedRecordModel->set('status', $db->query_result($result, $i, 'campaignrelstatus'));
				$relatedRecordModelsList[$recordId] = $relatedRecordModel;
			}
		}
		return $relatedRecordModelsList;
	}
}
