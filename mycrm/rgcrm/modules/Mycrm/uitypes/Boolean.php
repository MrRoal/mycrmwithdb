<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_Boolean_UIType extends Mycrm_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/Boolean.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false) {
		if($value == 1 || $value == '1' || strtolower($value) == 'on') {
			return Mycrm_Language_Handler::getTranslatedString('LBL_YES', $this->get('field')->getModuleName());
		}
		return Mycrm_Language_Handler::getTranslatedString('LBL_NO', $this->get('field')->getModuleName());
	}
    
     public function getListSearchTemplateName() {
        return 'uitypes/BooleanFieldSearchView.tpl';
    }

}