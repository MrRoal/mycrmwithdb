<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/
vimport('~~modules/PickList/DependentPickListUtils.php');

class Settings_PickListDependency_Module_Model extends Settings_Mycrm_Module_Model {

	var $baseTable = 'mycrm_picklist_dependency';
	var $baseIndex = 'id';
	var $name = 'PickListDependency';

	/**
	 * Function to get the url for default view of the module
	 * @return <string> - url
	 */
	public function getDefaultUrl() {
		return 'index.php?module=PickListDependency&parent=Settings&view=List';
	}

	/**
	 * Function to get the url for Adding Dependency
	 * @return <string> - url
	 */
	public function getCreateRecordUrl() {
		return "javascript:Settings_PickListDependency_Js.triggerAdd(event)";
	}
    
    public function isPagingSupported() {
        return false;
    }

	public static function getAvailablePicklists($module) {
		return Mycrm_DependencyPicklist::getAvailablePicklists($module);
	}
	
	public static function getPicklistSupportedModules() {
		$adb = PearDatabase::getInstance();

		$query = "SELECT distinct mycrm_field.tabid, mycrm_tab.tablabel, mycrm_tab.name as tabname FROM mycrm_field
						INNER JOIN mycrm_tab ON mycrm_tab.tabid = mycrm_field.tabid
						WHERE uitype IN ('15','16')
						AND mycrm_field.tabid != 29
						AND mycrm_field.displaytype = 1
						AND mycrm_field.presence in ('0','2')
						AND mycrm_field.block != 'NULL'
					GROUP BY mycrm_field.tabid HAVING count(*) > 1";
		// END
		$result = $adb->pquery($query, array());
		while($row = $adb->fetch_array($result)) {
			$modules[$row['tablabel']] = $row['tabname'];
		}
		ksort($modules);
		
        $modulesModelsList = array();
        foreach($modules as $moduleLabel => $moduleName) {
            $instance = new Mycrm_Module_Model();
            $instance->name = $moduleName;
            $instance->label = $moduleLabel;
            $modulesModelsList[] = $instance;
        }
        return $modulesModelsList;
    }
}
