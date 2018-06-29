<?php
/*+********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *********************************************************************************/

if(defined('MYCRM_UPGRADE')) {
    
global $adb;

$query = 'SELECT DISTINCT profileid FROM mycrm_profile2utility';
$result = $adb->pquery($query, array());

$profileId = $adb->query_result($result,0,'profileid');
Migration_Index_View::ExecuteQuery('INSERT INTO mycrm_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',40,5,0)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO mycrm_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',40,6,0)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO mycrm_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',40,10,0)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO mycrm_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',19,5,0)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO mycrm_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',19,6,0)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO mycrm_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',19,10,0)',array());

for($i=1; $i< $adb->num_rows($result); $i++){

$profileId = $adb->query_result($result,$i,'profileid');

Migration_Index_View::ExecuteQuery('INSERT INTO mycrm_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',40,5,1)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO mycrm_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',40,6,1)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO mycrm_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',40,10,0)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO mycrm_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',19,5,1)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO mycrm_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',19,6,1)',array());
Migration_Index_View::ExecuteQuery('INSERT INTO mycrm_profile2utility(profileid,tabid,activityid,permission) VALUES ('.$profileId.',19,10,0)',array());

}
}
chdir(dirname(__FILE__) . '/../../../');
require_once 'includes/main/WebUI.php';

$pickListFieldName = 'no_of_currency_decimals'; 
$moduleModel = Settings_Picklist_Module_Model::getInstance('Users'); 
$fieldModel = Mycrm_Field_Model::getInstance($pickListFieldName, $moduleModel); 

if ($fieldModel) { 
    $moduleModel->addPickListValues($fieldModel, 0); 
    $moduleModel->addPickListValues($fieldModel, 1); 
    
    $pickListValues = Mycrm_Util_Helper::getPickListValues($pickListFieldName); 
    $moduleModel->updateSequence($pickListFieldName, $pickListValues); 
} 
?>
