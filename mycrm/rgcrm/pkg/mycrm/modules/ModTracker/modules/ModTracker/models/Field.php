<?php
/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */

class ModTracker_Field_Model extends Mycrm_Record_Model {

	/**
	 * Function to set parent to this model
	 * @param Mycrm_Record_Model
	 */
	public function setParent($parent) {
		$this->parent = $parent;
		return $this;
	}

	/**
	 * Function to get parent
	 * @return Mycrm_Record_Model
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Function to set Field instance
	 * @param Mycrm_Field_Model
	 */
	public function setFieldInstance($fieldModel) {
		$this->fieldInstance = $fieldModel;
		return $this;
	}

	/**
	 * Function to get Field instance
	 * @return Mycrm_Field_Model
	 */
	public function getFieldInstance() {
		return $this->fieldInstance;
	}

	/**
	 * Function to get Old value of this Field
	 * @return <String>
	 */
	public function getOldValue() {
		return $this->getDisplayValue($this->get('prevalue'));
	}

	/**
	 * Function to get new(updated) value of this Field
	 * @return <String>
	 */
	public function getNewValue() {
		return $this->getDisplayValue($this->get('postvalue'));
	}

	/**
	 * Function to get name
	 * @return <type>
	 */
	public function getName() {
		return $this->getFieldInstance()->get('label');
	}

	/**
	 * Function to get Display Value
	 * @param <type> $value
	 * @return <String>
	 */
	public function getDisplayValue($value) {
		return $this->getFieldInstance()->getDisplayValue($value);
	}

	/**
	 * Function returns the module name of the field
	 * @return <String>
	 */
	public function getModuleName() {
		return $this->getParent()->getParent()->getModule()->getName();
	}
}
