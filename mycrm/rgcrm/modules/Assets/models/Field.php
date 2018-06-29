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
 * Assets Field Model Class
 */
class Assets_Field_Model extends Mycrm_Field_Model {

	/**
	 * Function returns special validator for fields
	 * @return <Array>
	 */
	function getValidator() {
		$validator = array();
		$fieldName = $this->getName();

		switch($fieldName) {
            case 'datesold' : $funcName = array('name'=>'lessThanOrEqualToToday'); 
                              array_push($validator, $funcName); 
                              break; 
			default : $validator = parent::getValidator();
						break;
		}
		return $validator;
	}
}
