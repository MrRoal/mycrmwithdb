<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Campaigns_ListView_Model extends Mycrm_ListView_Model {

	/**
	 * Function to get the list of listview links for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associate array of Link Type to List of Mycrm_Link_Model instances
	 */
	public function getListViewLinks($linkParams) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$moduleModel = $this->getModule();

		$linkTypes = array('LISTVIEWBASIC', 'LISTVIEW', 'LISTVIEWSETTING');
		$links = Mycrm_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);

		$basicLinks = array();

		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
		if($createPermission) {
			$basicLinks[] = array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => 'LBL_ADD_RECORD',
					'linkurl' => $moduleModel->getCreateRecordUrl(),
					'linkicon' => ''
			);
		}

		foreach($basicLinks as $basicLink) {
			$links['LISTVIEWBASIC'][] = Mycrm_Link_Model::getInstanceFromValues($basicLink);
		}

		$advancedLinks = array();

		foreach($advancedLinks as $advancedLink) {
			$links['LISTVIEW'][] = Mycrm_Link_Model::getInstanceFromValues($advancedLink);
		}
        
        if($currentUserModel->isAdminUser()) {

			$settingsLinks = $this->getSettingLinks();
			foreach($settingsLinks as $settingsLink) {
				$links['LISTVIEWSETTING'][] = Mycrm_Link_Model::getInstanceFromValues($settingsLink);
			}
		}

		return $links;
	}
}