<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_Base_UIType extends Mycrm_Base_Model {

	/**
	 * Function to get the Template name for the current UI Type Object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/String.tpl';
	}

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDBInsertValue($value) {
		return $value;
	}

	/**
	 * Function to get the Value of the field in the format, the user provides it on Save
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getUserRequestValue($value) {
		return $value;
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value, $record=false, $recordInstance=false) {
		return $value;
	}

	/**
	 * Static function to get the UIType object from Mycrm Field Model
	 * @param Mycrm_Field_Model $fieldModel
	 * @return Mycrm_Base_UIType or UIType specific object instance
	 */
	public static function getInstanceFromField($fieldModel) {
		$fieldDataType = $fieldModel->getFieldDataType();
		$uiTypeClassSuffix = ucfirst($fieldDataType);
		$moduleName = $fieldModel->getModuleName();
		$moduleSpecificUiTypeClassName = $moduleName.'_'.$uiTypeClassSuffix.'_UIType';
		$uiTypeClassName = 'Mycrm_'.$uiTypeClassSuffix.'_UIType';
		$fallBackClassName = 'Mycrm_Base_UIType';

		$moduleSpecificFileName = 'modules.'. $moduleName .'.uitypes.'.$uiTypeClassSuffix;
		$uiTypeClassFileName = 'modules.Mycrm.uitypes.'.$uiTypeClassSuffix;

		$moduleSpecificFilePath = Mycrm_Loader::resolveNameToPath($moduleSpecificFileName);
		$completeFilePath = Mycrm_Loader::resolveNameToPath($uiTypeClassFileName);

		if(file_exists($moduleSpecificFilePath)) {
			$instance = new $moduleSpecificUiTypeClassName();
		}
		else if(file_exists($completeFilePath)) {
			$instance = new $uiTypeClassName();
		} else {
			$instance = new $fallBackClassName();
		}
		$instance->set('field', $fieldModel);
		return $instance;
	}

	/**
	 * Function to get the display value in edit view
	 * @param reference record id
	 * @return link
	 */
	public function getEditViewDisplayValue($value) {
		return $value;
	}

    /**
	 * Function to get the Detailview template name for the current UI Type Object
	 * @return <String> - Template Name
	 */
	public function getDetailViewTemplateName() {
		return 'uitypes/StringDetailView.tpl';
	}

	/**
	 * Function to get Display value for RelatedList
	 * @param <String> $value
	 * @return <String>
	 */
	public function getRelatedListDisplayValue($value) {
		return $this->getDisplayValue($value);
	}
    
    public function getListSearchTemplateName() {
        return 'uitypes/FieldSearchView.tpl';
    }
}