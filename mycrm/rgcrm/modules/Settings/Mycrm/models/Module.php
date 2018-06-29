<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

/*
 * Settings Module Model Class
 */
class Settings_Mycrm_Module_Model extends Mycrm_Base_Model {

	var $baseTable = 'mycrm_settings_field';
	var $baseIndex = 'fieldid';
	var $listFields = array('name' => 'Name', 'description' => 'Description');
	var $nameFields = array('name');
	var $name = 'Mycrm';

	public function getName($includeParentIfExists = false) {
		if($includeParentIfExists) {
			return  $this->getParentName() .':'. $this->name;
		}
		return $this->name;
	}

	public function getParentName() {
		return 'Settings';
	}

	public function getBaseTable() {
		return $this->baseTable;
	}

	public function getBaseIndex() {
		return $this->baseIndex;
	}

	public function setListFields($fieldNames) {
		$this->listFields = $fieldNames;
		return $this;
	}

	public function getListFields() {
		if(!$this->listFieldModels) {
			$fields = $this->listFields;
			$fieldObjects = array();
			foreach($fields as $fieldName => $fieldLabel) {
				$fieldObjects[$fieldName] = new Mycrm_Base_Model(array('name' => $fieldName, 'label' => $fieldLabel));
			}
			$this->listFieldModels = $fieldObjects;
		}
		return $this->listFieldModels;
	}

	/**
	 * Function to get name fields of this module
	 * @return <Array> list field names
	 */
	public function getNameFields() {
		return $this->nameFields;
	}

	/**
	 * Function to get field using field name
	 * @param <String> $fieldName
	 * @return <Field_Model>
	 */
	public function getField($fieldName) {
		return new Mycrm_Base_Model(array('name' => $fieldName, 'label' => $fieldName));
	}

	public function hasCreatePermissions() {
		return true;
	}

	/**
	 * Function to get all the Settings menus
	 * @return <Array> - List of Settings_Mycrm_Menu_Model instances
	 */
	public function getMenus() {
		return Settings_Mycrm_Menu_Model::getAll();
	}

	/**
	 * Function to get all the Settings menu items for the given menu
	 * @return <Array> - List of Settings_Mycrm_MenuItem_Model instances
	 */
	public function getMenuItems($menu=false) {
		$menuModel = false;
		if($menu) {
			$menuModel = Settings_Mycrm_Menu_Model::getInstance($menu);
		}
		return Settings_Mycrm_MenuItem_Model::getAll($menuModel);
	}
    
    public function isPagingSupported(){
        return true;
    }

	/**
	 * Function to get the instance of Settings module model
	 * @return Settings_Mycrm_Module_Model instance
	 */
	public static function getInstance($name='Settings:Mycrm') {
		$modelClassName = Mycrm_Loader::getComponentClassName('Model', 'Module', $name);
		return new $modelClassName();
	}

	/**
	 * Function to get Index view Url
	 * @return <String> URL
	 */
	public function getIndexViewUrl() {
		return 'index.php?module='.$this->getName().'&parent='.$this->getParentName().'&view=Index';
	}
}
