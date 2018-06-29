<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_SaveAjax_Action extends Mycrm_SaveAjax_Action {

	public function process(Mycrm_Request $request) {
        $user = Users_Record_Model::getCurrentUserModel();

		$recordModel = $this->saveRecord($request);

		$fieldModelList = $recordModel->getModule()->getFields();
		$result = array();
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			$fieldValue =  Mycrm_Util_Helper::toSafeHTML($recordModel->get($fieldName));
            $result[$fieldName] = array();
			if($fieldName == 'date_start') {
				$timeStart = $recordModel->get('time_start');
                $dateTimeFieldInstance = new DateTimeField($fieldValue . ' ' . $timeStart);

				$fieldValue = $fieldValue.' '.$timeStart;

                $userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue();
                $dateTimeComponents = explode(' ',$userDateTimeString);
                $dateComponent = $dateTimeComponents[0];
                //Conveting the date format in to Y-m-d . since full calendar expects in the same format
                $dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $user->get('date_format'));
                $result[$fieldName]['calendar_display_value'] = $dataBaseDateFormatedString.' '. $dateTimeComponents[1];
			} else if($fieldName == 'due_date') {
				$timeEnd = $recordModel->get('time_end');
                $dateTimeFieldInstance = new DateTimeField($fieldValue . ' ' . $timeEnd);

				$fieldValue = $fieldValue.' '.$timeEnd;

                $userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue();
                $dateTimeComponents = explode(' ',$userDateTimeString);
                $dateComponent = $dateTimeComponents[0];
                //Conveting the date format in to Y-m-d . since full calendar expects in the same format
                $dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $user->get('date_format'));
                $result[$fieldName]['calendar_display_value']   =  $dataBaseDateFormatedString.' '. $dateTimeComponents[1];
			}
			$result[$fieldName]['value'] = $fieldValue;
            $result[$fieldName]['display_value'] = decode_html($fieldModel->getDisplayValue($fieldValue));
		}

		$result['_recordLabel'] = $recordModel->getName();
		$result['_recordId'] = $recordModel->getId();

		// Handled to save follow up event
		$followupMode = $request->get('followup');

        if($followupMode == 'on') {
            //Start Date and Time values
            $startTime = Mycrm_Time_UIType::getTimeValueWithSeconds($request->get('followup_time_start'));
            $startDateTime = Mycrm_Datetime_UIType::getDBDateTimeValue($request->get('followup_date_start') . " " . $startTime);
            list($startDate, $startTime) = explode(' ', $startDateTime);

            $subject = $request->get('subject');
            if($startTime != '' && $startDate != ''){
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
				list($endDate, $endTime) = explode(' ', $dueDateTime);

				$recordModel->set('due_date',$endDate);
                $recordModel->set('time_end',$endTime);
                $recordModel->set('mode', 'create');
                $recordModel->save();
            }
        }
		$response = new Mycrm_Response();
		$response->setEmitType(Mycrm_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Mycrm_Request $request
	 * @return Mycrm_Record_Model or Module specific Record Model instance
	 */
	public function getRecordModelFromRequest(Mycrm_Request $request) {
		$recordModel = parent::getRecordModelFromRequest($request);

		$startDate = $request->get('date_start');
		if(!empty($startDate)) {
			//Start Date and Time values
			$startTime = Mycrm_Time_UIType::getTimeValueWithSeconds($request->get('time_start'));
			$startDateTime = Mycrm_Datetime_UIType::getDBDateTimeValue($request->get('date_start')." ".$startTime);
			list($startDate, $startTime) = explode(' ', $startDateTime);

			$recordModel->set('date_start', $startDate);
			$recordModel->set('time_start', $startTime);
		}

		$endDate = $request->get('due_date');
		if(!empty($endDate)) {
			//End Date and Time values
			$endTime = $request->get('time_end');
			$endDate = Mycrm_Date_UIType::getDBInsertedValue($request->get('due_date'));

			if ($endTime) {
				$endTime = Mycrm_Time_UIType::getTimeValueWithSeconds($endTime);
				$endDateTime = Mycrm_Datetime_UIType::getDBDateTimeValue($request->get('due_date')." ".$endTime);
				list($endDate, $endTime) = explode(' ', $endDateTime);
			}

			$recordModel->set('time_end', $endTime);
			$recordModel->set('due_date', $endDate);
		}

		$activityType = $request->get('activitytype');
		$visibility = $request->get('visibility');
		if(empty($activityType)) {
			$recordModel->set('activitytype', 'Task');
			$visibility = 'Private';
			$recordModel->set('visibility', $visibility);
		}

		if(empty($visibility)) {
			$assignedUserId = $recordModel->get('assigned_user_id');
			$sharedType = Calendar_Module_Model::getSharedType($assignedUserId);
			if($sharedType == 'selectedusers') {
				$sharedType = 'public';
			}
			$recordModel->set('visibility', ucfirst($sharedType));
		}

        $time = (strtotime($endTime))- (strtotime($startTime));
        $diffinSec=  (strtotime($endDate))- (strtotime($startDate));
        $diff_days=floor($diffinSec/(60*60*24));
          
        $hours=((float)$time/3600)+($diff_days*24);
        $minutes = ((float)$hours-(int)$hours)*60;  
        
        $recordModel->set('duration_hours', (int)$hours);
		$recordModel->set('duration_minutes', round($minutes,0)); 
        
		return $recordModel;
	}
}
