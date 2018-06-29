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
 * Mycrm Record Structure Model
 */
class Mycrm_RecordStructure_Model extends Mycrm_Base_Model {

	protected $record = false;
	protected $module = false;
	protected $structuredValues = false;

	const RECORD_STRUCTURE_MODE_DEFAULT = '';
	const RECORD_STRUCTURE_MODE_DETAIL = 'Detail';
	const RECORD_STRUCTURE_MODE_EDIT = 'Edit';
	const RECORD_STRUCTURE_MODE_QUICKCREATE = 'QuickCreate';
	const RECORD_STRUCTURE_MODE_MASSEDIT = 'MassEdit';
	const RECORD_STRUCTURE_MODE_SUMMARY = 'Summary';

	/**
	 * Function to set the record Model
	 * @param <type> $record - record instance
	 * @return Mycrm_RecordStructure_Model
	 */
	public function setRecord($record) {
		$this->record = $record;
		return $this;
	}

	/**
	 * Function to get the record
	 * @return <Mycrm_Record_Model>
	 */
	public function getRecord() {
		return $this->record;
	}

	public function getRecordName() {
		return $this->record->getName();
	}

	/**
	 * Function to get the module
	 * @return <Mycrm_Module_Model>
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Function to set the module
	 * @param <type> $module - module model
	 * @return Mycrm_RecordStructure_Model
	 */
	public function setModule($module) {
		$this->module = $module;
		return $this;
	}

	/**
	 * Function to get the values in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure() {
		if(!empty($this->structuredValues)) {
			return $this->structuredValues;
		}

		$values = array();
		$recordModel = $this->getRecord();
		$recordExists = !empty($recordModel);
		$moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();
		foreach($blockModelList as $blockLabel=>$blockModel) {
			$fieldModelList = $blockModel->getFields();
			if (!empty ($fieldModelList)) {
				$values[$blockLabel] = array();
				foreach($fieldModelList as $fieldName=>$fieldModel) {
					if($fieldModel->isViewable()) {
						if($recordExists) {
							$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
						}
						$values[$blockLabel][$fieldName] = $fieldModel;
					}
				}
			}
		}
		$this->structuredValues = $values;
		return $values;
	}

	/**
	 * Function to retieve the instance from record model
	 * @param <Mycrm_Record_Model> $recordModel - record instance
	 * @return Mycrm_RecordStructure_Model
	 */
	public static function getInstanceFromRecordModel($recordModel, $mode=self::RECORD_STRUCTURE_MODE_DEFAULT) {
		$moduleModel = $recordModel->getModule();
		$className = Mycrm_Loader::getComponentClassName('Model', $mode.'RecordStructure', $moduleModel->getName(true));
		$instance = new $className();
		$instance->setModule($moduleModel)->setRecord($recordModel);
		return $instance;
	}

	/**
	 * Function to retieve the instance from module model
	 * @param <Mycrm_Module_Model> $moduleModel - module instance
	 * @return Mycrm_RecordStructure_Model
	 */
	public static function getInstanceForModule($moduleModel, $mode=self::RECORD_STRUCTURE_MODE_DEFAULT) {
		$className = Mycrm_Loader::getComponentClassName('Model', $mode.'RecordStructure', $moduleModel->get('name'));
		$instance = new $className();
		$instance->setModule($moduleModel);
		return $instance;
	}
}