<?php
/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */
require_once 'include/events/VTEventHandler.inc';

class Mycrm_RecordLabelUpdater_Handler extends VTEventHandler {

	function handleEvent($eventName, $data) {
		global $adb;

		if ($eventName == 'mycrm.entity.aftersave') {
			$labelInfo = getEntityName($data->getModuleName(), $data->getId(), true);

			if ($labelInfo) {
				$label = decode_html($labelInfo[$data->getId()]);
				$adb->pquery('UPDATE mycrm_crmentity SET label=? WHERE crmid=?', array($label, $data->getId()));
			}
		}
	}
}