<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/
require_once dirname(__FILE__) .'/ModTracker.php';
require_once 'data/VTEntityDelta.php';

class ModTrackerHandler extends VTEventHandler {

	function handleEvent($eventName, $data) {
		global $log, $current_module, $adb, $current_user;
		$moduleName = $data->getModuleName();

		$flag = ModTracker::isTrackingEnabledForModule($moduleName);
        
		if($flag) {
			if($eventName == 'mycrm.entity.aftersave.final') {
                $recordId = $data->getId();
                $columnFields = $data->getData();
                $vtEntityDelta = new VTEntityDelta();
                $delta = $vtEntityDelta->getEntityDelta($moduleName, $recordId, true);
                
                $newerEntity = $vtEntityDelta->getNewEntity($moduleName, $recordId);
                $newerColumnFields = $newerEntity->getData();
                $newerColumnFields=array_change_key_case($newerColumnFields,CASE_LOWER);
                $delta=  array_change_key_case($delta,CASE_LOWER);
                if(is_array($delta)) {
                    $inserted = false;
                    foreach($delta as $fieldName => $values) {
                        if($fieldName != 'modifiedtime') {
                            if(!$inserted) {
                                $checkRecordPresentResult = $adb->pquery('SELECT * FROM mycrm_modtracker_basic WHERE 
                                    crmid = ?', array($recordId));
                                if(!$adb->num_rows($checkRecordPresentResult) && $data->isNew()) {
                                    $status = ModTracker::$CREATED;
                                } else {
                                    $status = ModTracker::$UPDATED;
                                }
                                $this->id = $adb->getUniqueId('mycrm_modtracker_basic');
                                $adb->pquery('INSERT INTO mycrm_modtracker_basic(id, crmid, module, whodid, changedon, status)
                                            VALUES(?,?,?,?,?,?)', Array($this->id, $recordId, $moduleName,
                                            $current_user->id, $newerColumnFields['modifiedtime'], $status));
                                $inserted = true;
                            }
                            $adb->pquery('INSERT INTO mycrm_modtracker_detail(id,fieldname,prevalue,postvalue) VALUES(?,?,?,?)',
                                Array($this->id, $fieldName, $values['oldValue'], $values['currentValue']));
                        }
                    }
                }
			}
            
            if($eventName == 'mycrm.entity.beforedelete') {
                $recordId = $data->getId();
                $columnFields = $data->getData();
                $id = $adb->getUniqueId('mycrm_modtracker_basic');
                $adb->pquery('INSERT INTO mycrm_modtracker_basic(id, crmid, module, whodid, changedon, status)
                    VALUES(?,?,?,?,?,?)', Array($id, $recordId, $moduleName, $current_user->id, date('Y-m-d H:i:s',time()), ModTracker::$DELETED));
            }

            if($eventName == 'mycrm.entity.afterrestore') {
                $recordId = $data->getId();
                $columnFields = $data->getData();
                $id = $adb->getUniqueId('mycrm_modtracker_basic');
                $adb->pquery('INSERT INTO mycrm_modtracker_basic(id, crmid, module, whodid, changedon, status)
                    VALUES(?,?,?,?,?,?)', Array($id, $recordId, $moduleName, $current_user->id, date('Y-m-d H:i:s',time()), ModTracker::$RESTORED));
            }
		}
	}
}
?>