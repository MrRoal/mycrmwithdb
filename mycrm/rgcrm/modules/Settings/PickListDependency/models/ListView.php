<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_PickListDependency_ListView_Model extends Settings_Mycrm_ListView_Model {

	/**
	 * Function to get the list view header
	 * @return <Array> - List of Mycrm_Field_Model instances
	 */
	public function getListViewHeaders() {
		$field = new Mycrm_Base_Model();
		$field->set('name', 'sourceLabel');
		$field->set('label', 'Module');
		$field->set('sort',false);

		$field1 = new Mycrm_Base_Model();
		$field1->set('name', 'sourcefieldlabel');
		$field1->set('label', 'Source Field');
		$field1->set('sort',false);

		$field2 = new Mycrm_Base_Model();
		$field2->set('name', 'targetfieldlabel');
		$field2->set('label', 'Target Field');
		$field2->set('sort',false);

		return array($field, $field1, $field2);
	}

	/**
	 * Function to get the list view entries
	 * @param Mycrm_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Mycrm_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel) {
		$forModule = $this->get('formodule');

		$dependentPicklists = Mycrm_DependencyPicklist::getDependentPicklistFields($forModule);

		$noOfRecords = count($dependentPicklists);
		$recordModelClass = Mycrm_Loader::getComponentClassName('Model', 'Record', 'Settings:PickListDependency');

		$listViewRecordModels = array();
		for($i=0; $i<$noOfRecords; $i++) {
			$record = new $recordModelClass();
			$module = $dependentPicklists[$i]['module'];
			unset($dependentPicklists[$i]['module']);
			$record->setData($dependentPicklists[$i]);
			$record->set('sourceModule',$module);
			$record->set('sourceLabel', vtranslate($module, $module));
			$listViewRecordModels[] = $record;
		}
		$pagingModel->calculatePageRange($listViewRecordModels);
		return $listViewRecordModels;
	}
}