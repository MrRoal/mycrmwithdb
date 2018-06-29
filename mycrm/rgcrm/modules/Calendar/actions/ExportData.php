<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

vimport('modules.Calendar.iCal.iCalendar_rfc2445');
vimport('modules.Calendar.iCal.iCalendar_components');
vimport('modules.Calendar.iCal.iCalendar_properties');
vimport('modules.Calendar.iCal.iCalendar_parameters');

class Calendar_ExportData_Action extends Mycrm_ExportData_Action {

	/**
	 * Function that generates Export Query based on the mode
	 * @param Mycrm_Request $request
	 * @return <String> export query
	 */
	public function getExportQuery(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);

		return $moduleModel->getExportQuery('','');
	}

	/**
	 * Function returns the export type - This can be extended to support different file exports
	 * @param Mycrm_Request $request
	 * @return <String>
	 */
	public function getExportContentType(Mycrm_Request $request) {
		return 'text/calendar';
	}

	/**
	 * Function exports the data based on the mode
	 * @param Mycrm_Request $request
	 */
	public function ExportData(Mycrm_Request $request) {
		$db = PearDatabase::getInstance();
		$moduleModel = Mycrm_Module_Model::getInstance($request->getModule());

		$moduleModel->setEventFieldsForExport();
		$moduleModel->setTodoFieldsForExport();

		$query = $this->getExportQuery($request);
		$result = $db->pquery($query, array());

		$this->output($request, $result, $moduleModel);
	}

	/**
	 * Function that create the exported file
	 * @param Mycrm_Request $request
	 * @param <Array> $result
	 * @param Mycrm_Module_Model $moduleModel
	 */
	public function output($request, $result, $moduleModel) {
		$fileName = $request->get('filename');
		$exportType = $this->getExportContentType($request);

		// Send the right content type and filename
		header("Content-type: $exportType");
		header("Content-Disposition: attachment; filename={$fileName}.ics");

		$timeZone = new iCalendar_timezone;
		$timeZoneId = split('/', date_default_timezone_get());

		if(!empty($timeZoneId[1])) {
			$zoneId = $timeZoneId[1];
		} else {
			$zoneId = $timeZoneId[0];
		}

		$timeZone->add_property('TZID', $zoneId);
		$timeZone->add_property('TZOFFSETTO', date('O'));

		if(date('I') == 1) {
			$timeZone->add_property('DAYLIGHTC', date('I'));
		} else {
			$timeZone->add_property('STANDARDC', date('I'));
		}

		$myiCal = new iCalendar;
		$myiCal->add_component($timeZone);

		while (!$result->EOF) {
			$eventFields = $result->fields;
			$id = $eventFields['activityid'];
			$type = $eventFields['activitytype'];
			if($type != 'Task') {
				$temp = $moduleModel->get('eventFields');
				foreach($temp as $fieldName => $access) {
                    /* Priority property of ical is Integer
                     * http://kigkonsult.se/iCalcreator/docs/using.html#PRIORITY
                     */
                    if($fieldName == 'priority'){
                        $priorityMap = array('High'=>'1','Medium'=>'2','Low'=>'3');
                        $priorityval = $eventFields[$fieldName];
                        $icalZeroPriority = 0;
                        if(array_key_exists($priorityval, $priorityMap))
                            $temp[$fieldName] = $priorityMap[$priorityval];
                        else 
                            $temp[$fieldName] = $icalZeroPriority;
                    }
                    else
                        $temp[$fieldName] = $eventFields[$fieldName];
				}
				$temp['id'] = $id;

				$iCalTask = new iCalendar_event;
				$iCalTask->assign_values($temp);

				$iCalAlarm = new iCalendar_alarm;
				$iCalAlarm->assign_values($temp);
				$iCalTask->add_component($iCalAlarm);
			} else {
				$temp = $moduleModel->get('todoFields');
				foreach($temp as $fieldName => $access) {
                    if($fieldName == 'priority'){
                        $priorityMap = array('High'=>'1','Medium'=>'2','Low'=>'3');
                        $priorityval = $eventFields[$fieldName];
                        $icalZeroPriority = 0;
                        if(array_key_exists($priorityval, $priorityMap))
                            $temp[$fieldName] = $priorityMap[$priorityval];
                        else 
                            $temp[$fieldName] = $icalZeroPriority;
                    }
                    else
                        $temp[$fieldName] = $eventFields[$fieldName];
				}
				$iCalTask = new iCalendar_todo;
				$iCalTask->assign_values($temp);
			}

			$myiCal->add_component($iCalTask);
			$result->MoveNext();
		}
		echo $myiCal->serialize();
	}
}
