<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Events_Save_Action extends Calendar_Save_Action {

	/**
	 * Function to save record
	 * @param <Mycrm_Request> $request - values of the record
	 * @return <RecordModel> - record Model of saved record
	 */
	public function saveRecord($request) {
		$adb = PearDatabase::getInstance();
		$recordModel = $this->getRecordModelFromRequest($request);
		$recordModel->save();
		$originalRecordId = $recordModel->getId();
		if($request->get('relationOperation')) {
			$parentModuleName = $request->get('sourceModule');
			$parentModuleModel = Mycrm_Module_Model::getInstance($parentModuleName);
			$parentRecordId = $request->get('sourceRecord');
			$relatedModule = $recordModel->getModule();
			if($relatedModule->getName() == 'Events'){
				$relatedModule = Mycrm_Module_Model::getInstance('Calendar');
			}
			$relatedRecordId = $recordModel->getId();

			$relationModel = Mycrm_Relation_Model::getInstance($parentModuleModel, $relatedModule);
			$relationModel->addRelation($parentRecordId, $relatedRecordId);
		}

		// Handled to save follow up event
		$followupMode = $request->get('followup');

		//Start Date and Time values
		$startTime = Mycrm_Time_UIType::getTimeValueWithSeconds($request->get('followup_time_start'));
		$startDateTime = Mycrm_Datetime_UIType::getDBDateTimeValue($request->get('followup_date_start') . " " . $startTime);
		list($startDate, $startTime) = explode(' ', $startDateTime);

		$subject = $request->get('subject');
		if($followupMode == 'on' && $startTime != '' && $startDate != ''){
			$recordModel->set('eventstatus', 'Planned');
			$recordModel->set('subject','[Followup] '.$subject);
			$recordModel->set('date_start',$startDate);
			$recordModel->set('time_start',$startTime);

			$currentUser = Users_Record_Model::getCurrentUserModel();
			$activityType = $recordModel->get('activitytype');
			if($activityType == 'Call') {
				$minutes = $currentUser->get('callduration');
			} else {
				$minutes = $currentUser->get('othereventduration');
			}
			$dueDateTime = date('Y-m-d H:i:s', strtotime("$startDateTime+$minutes minutes"));
			list($startDate, $startTime) = explode(' ', $dueDateTime);

			$recordModel->set('due_date',$startDate);
			$recordModel->set('time_end',$startTime);
			$recordModel->set('recurringtype', '');
			$recordModel->set('mode', 'create');
			$recordModel->save();
			$heldevent = true;
		}

		//TODO: remove the dependency on $_REQUEST
		if($_REQUEST['recurringtype'] != '' && $_REQUEST['recurringtype'] != '--None--') {
			vimport('~~/modules/Calendar/RepeatEvents.php');
			$focus =  new Activity();

			//get all the stored data to this object
			$focus->column_fields = $recordModel->getData();

			Calendar_RepeatEvents::repeatFromRequest($focus);
		}
        return $recordModel;
    }


    /**
	 * Function to get the record model based on the request parameters
	 * @param Mycrm_Request $request
	 * @return Mycrm_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(Mycrm_Request $request) {
        $recordModel = parent::getRecordModelFromRequest($request);

        $recordModel->set('selectedusers', $request->get('selectedusers'));
        return $recordModel;
    }
}
