<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_FileLocationType_UIType extends Mycrm_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/FileLocationType.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <String> value of field
	 * @return <String> Converted value
	 */
	public function getDisplayValue($value) {
		if ($value === 'I') {
			$value = 'LBL_INTERNAL';
		} else {
			$value = 'LBL_EXTERNAL';
		}
		return vtranslate($value, 'Documents');
	}

}