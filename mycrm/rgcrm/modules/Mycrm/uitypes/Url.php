<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_Url_UIType extends Mycrm_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/Url.tpl';
	}

	public function getDisplayValue($value) {
		$matchPattern = "^[\w]+:\/\/^";
		preg_match($matchPattern, $value, $matches);
		if(!empty ($matches[0])) {
			$value = '<a class="urlField cursorPointer" href="'.$value.'" target="_blank">'.textlength_check($value).'</a>';
		} else {
			$value = '<a class="urlField cursorPointer" href="http://'.$value.'" target="_blank">'.textlength_check($value).'</a>';
		}
		return $value;
	}
}