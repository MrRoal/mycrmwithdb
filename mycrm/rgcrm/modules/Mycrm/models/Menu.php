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
 * Mycrm Menu Model Class
 */
class Mycrm_Menu_Model extends Mycrm_Module_Model {

    /**
     * Static Function to get all the accessible menu models with/without ordering them by sequence
     * @param <Boolean> $sequenced - true/false
     * @return <Array> - List of Mycrm_Menu_Model instances
     */
    public static function getAll($sequenced = false, $restrictedModulesList = array()) {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $restrictedModulesList = array('Emails', 'ProjectMilestone', 'ProjectTask', 'ModComments', 'ExtensionStore', 'ExtensionStorePro',
										'Integration', 'Dashboard', 'Home', 'vtmessages', 'vttwitter');

		
        $allModules = parent::getAll(array('0','2'));
		$menuModels = array();
        $moduleSeqs = Array();
        $moduleNonSeqs = Array();
        foreach($allModules as $module){
            if($module->get('tabsequence') != -1){
                $moduleSeqs[$module->get('tabsequence')] = $module;
            }else {
                $moduleNonSeqs[] = $module;
            }
        }
        ksort($moduleSeqs);
        $modules = array_merge($moduleSeqs, $moduleNonSeqs);

		foreach($modules as $module) {
            if (($userPrivModel->isAdminUser() ||
                    $userPrivModel->hasGlobalReadPermission() ||
                    $userPrivModel->hasModulePermission($module->getId()))& !in_array($module->getName(), $restrictedModulesList) && $module->get('parent') != '') {
                    $menuModels[$module->getName()] = $module;

            }
        }

        return $menuModels;
    }

}
