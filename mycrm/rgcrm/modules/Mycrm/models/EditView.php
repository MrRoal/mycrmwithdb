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
 * Mycrm EditView Model Class
 */
class Mycrm_EditView_Model extends Mycrm_Base_Model {

	/**
	 * Function to get the Module Model
	 * @return Mycrm_Module_Model instance
	 */
	public function getModule() {
		return $this->get('module');
	}

	/**
	 * Function to get the list of listview links for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associate array of Link Type to List of Mycrm_Link_Model instances
	 */
	public function getEditViewLinks($linkParams) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$linkTypes = array('LISTVIEWQUICK', 'LISTVIEWQUICKWIDGET', 'LISTVIEWBASIC', 'LISTVIEW', 'LISTVIEWSETTING');
		$links = Mycrm_Link_Model::getAllByType($this->getModule()->getId(), $linkTypes, $linkParams);

		$quickLinks = array(
			array(
				'linktype' => 'LISTVIEWQUICK',
				'linklabel' => 'Dashboard',
				'linkurl' => $this->getModule()->getDefaultUrl(),
				'linkicon' => ''
			),
			array(
				'linktype' => 'LISTVIEWQUICK',
				'linklabel' => $this->getModule()->get('label').' List',
				'linkurl' => $this->getModule()->getDefaultUrl(),
				'linkicon' => ''
			),
		);
		foreach($quickLinks as $quickLink) {
			$links['LISTVIEWQUICK'][] = Mycrm_Link_Model::getInstanceFromValues($quickLink);
		}

		$quickWidgets = array(
			array(
				'linktype' => 'LISTVIEWQUICKWIDGET',
				'linklabel' => 'Active '.$this->getModule()->get('label'),
				'linkurl' => 'module='.$this->getModule()->get('name').'&view=List&mode=showActiveRecords',
				'linkicon' => ''
			)
		);
		foreach($quickWidgets as $quickWidget) {
			$links['LISTVIEWQUICKWIDGET'][] = Mycrm_Link_Model::getInstanceFromValues($quickWidget);
		}

		$basicLinks = array(
			array(
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'Add '.$this->getModule()->get('name'),
				'linkurl' => $this->getModule()->getCreateRecordUrl(),
				'linkicon' => ''
			)
		);
		foreach($basicLinks as $basicLink) {
			$links['LISTVIEWBASIC'][] = Mycrm_Link_Model::getInstanceFromValues($basicLink);
		}

		$advancedLinks = array(
			array(
				'linktype' => 'LISTVIEW',
				'linklabel' => 'Import',
				'linkurl' => $this->getModule()->getImportUrl(),
				'linkicon' => ''
			),
			array(
				'linktype' => 'LISTVIEW',
				'linklabel' => 'Export',
				'linkurl' => $this->getModule()->getExportUrl(),
				'linkicon' => ''
			),
			array(
				'linktype' => 'LISTVIEW',
				'linklabel' => 'Find Duplicates',
				'linkurl' => $this->getModule()->getFindDuplicatesUrl(),
				'linkicon' => ''
			)
		);
		foreach($advancedLinks as $advancedLink) {
			$links['LISTVIEW'][] = Mycrm_Link_Model::getInstanceFromValues($advancedLink);
		}

		if($currentUserModel->isAdminUser()) {

			$settingsLinks = array(
				array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'Edit Fields',
					'linkurl' => $this->getModule()->getSettingsUrl('LayoutEditor'),
					'linkicon' => ''
				),
				array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'Edit Workflows',
					'linkurl' => $this->getModule()->getSettingsUrl('EditWorkflows'),
					'linkicon' => ''
				),
				array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'Edit Picklist Values',
					'linkurl' => $this->getModule()->getSettingsUrl('PicklistEditor'),
					'linkicon' => ''
				)
			);
			foreach($settingsLinks as $settingsLink) {
				$links['LISTVIEWSETTING'][] = Mycrm_Link_Model::getInstanceFromValues($settingsLink);
			}
		}

		return $links;
	}
}
