<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_SaveFollowupAjax_Action extends Calendar_SaveAjax_Action {
    
    function __construct() {
        $this->exposeMethod('createFollowupEvent');
        $this->exposeMethod('markAsHeldCompleted');
    }
    
    public function process(Mycrm_Request $request) {  
		$mode = $request->getMode();
		if(!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

	}

	public function createFollowupEvent(Mycrm_Request $request) {
        
        $recordId = $request->get('record');
        
        $recordModel = Mycrm_Record_Model::getInstanceById($recordId);
        $subject = $recordModel->get('subject');
        $followupSubject = "[Followup] ".$subject;
        $recordModel->set('subject',$followupSubject);
        //followup event is Planned
        $recordModel->set('eventstatus',"Planned");
        
        $activityType = $recordModel->get('activitytype');
        if($activityType == "Call")
            $eventDuration = $request->get('defaultCallDuration');
        else
            $eventDuration = $request->get('defaultOtherEventDuration');
        
        $followupStartTime = Mycrm_Time_UIType::getTimeValueWithSeconds($request->get('followup_time_start'));
		$followupStartDateTime = Mycrm_Datetime_UIType::getDBDateTimeValue($request->get('followup_date_start')." ".$followupStartTime);
		list($followupStartDate, $followupStartTime) = explode(' ', $followupStartDateTime);
        //Duration of followup event based on activitytype
        $durationMS = $eventDuration*60;
        $followupStartDateTimeMS = strtotime($followupStartDateTime);
        $followupEndDateTimeMS = $followupStartDateTimeMS+$durationMS;
        $followupEndDateTime = date("Y-m-d H:i:s", $followupEndDateTimeMS);
        list($followupEndDate, $followupEndTime) = explode(' ', $followupEndDateTime);
        
		$recordModel->set('date_start', $followupStartDate);
		$recordModel->set('time_start', $followupStartTime);
        
        $recordModel->set('due_date', $followupEndDate);
		$recordModel->set('time_end', $followupEndTime);
        
        $recordModel->save();
        
        $response = new Mycrm_Response();
        $result = array('created'=>true);
        $response->setResult($result);
        $response->emit();
	}
    
    public function markAsHeldCompleted(Mycrm_Request $request) {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');
        $recordModel = Mycrm_Record_Model::getInstanceById($recordId,$moduleName);
        $recordModel->set('mode','edit');
        $activityType = $recordModel->get('activitytype');
        $response = new Mycrm_Response();
        
        if($activityType == 'Task'){
            $status = 'Completed';
            $recordModel->set('taskstatus',$status);
            $result = array("valid"=>TRUE,"markedascompleted"=>TRUE,"activitytype"=>"Task");
        }
        else{
            //checking if the event can be marked as Held (status validation)
            $startDateTime[] = $recordModel->get('date_start');
            $startDateTime[] = $recordModel->get('time_start');
            $startDateTime = implode(' ',$startDateTime);
            $startDateTime = new DateTime($startDateTime);
            $currentDateTime = date("Y-m-d H:i:s");
            $currentDateTime = new DateTime($currentDateTime);
            if($startDateTime > $currentDateTime){
                $result = array("valid"=>FALSE,"markedascompleted"=>FALSE);
                $response->setResult($result);
                $response->emit();
                return;
            }
            $status = 'Held';
            $recordModel->set('eventstatus',$status);
            $result = array("valid"=>TRUE,"markedascompleted"=>TRUE,"activitytype"=>"Event");
        }
        $recordModel->save();
        $response->setResult($result);
        $response->emit();
    }
}
