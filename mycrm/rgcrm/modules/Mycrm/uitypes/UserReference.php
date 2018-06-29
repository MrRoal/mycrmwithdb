<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_UserReference_UIType extends Mycrm_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/Reference.tpl';
	}

	/**
	 * Function to get the display value in detail view
	 * @param <Integer> crmid of record
	 * @return <String>
	 */
	public function getEditViewDisplayValue($value) {
		if($value) {
			$userName = getOwnerName($value);
			return $userName;
		}
	}

	/**
	 * Function to get display value
	 * @param <String> $value
	 * @param <Number> $recordId
	 * @return <String> display value
	 */
	public function getDisplayValue($value, $recordId) {
		$displayValue = $this->getEditViewDisplayValue($value);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if ($currentUserModel->isAdminUser()) {
			$recordModel = Users_Record_Model::getCleanInstance('Users');
			$recordModel->set('id', $value);
			return '<a href="'. $recordModel->getDetailViewUrl() .'">'. textlength_check($displayValue) .'</a>';
		}
		return $displayValue;
	}

}