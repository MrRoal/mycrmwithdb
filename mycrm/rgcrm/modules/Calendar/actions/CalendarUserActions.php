<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_CalendarUserActions_Action extends Mycrm_Action_Controller{
	
	function __construct() {
		$this->exposeMethod('deleteUserCalendar');
		$this->exposeMethod('addUserCalendar');
		$this->exposeMethod('deleteCalendarView');
		$this->exposeMethod('addCalendarView');
	}
	
	public function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');

		if(!Users_Privileges_Model::isPermitted($moduleName, 'Save', $record)) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}
	
	public function process(Mycrm_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
	
	/**
	 * Function to delete the user calendar from shared calendar
	 * @param Mycrm_Request $request
	 * @return Mycrm_Response $response
	 */
	function deleteUserCalendar(Mycrm_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$sharedUserId = $request->get('userid');
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT 1 FROM mycrm_shareduserinfo WHERE userid=? AND shareduserid=?', array($userId, $sharedUserId));
		if($db->num_rows($result) > 0) {
			$db->pquery('UPDATE mycrm_shareduserinfo SET visible=? WHERE userid=? AND shareduserid=?', array('0', $userId, $sharedUserId));
		} else {
			$db->pquery('INSERT INTO mycrm_shareduserinfo (userid, shareduserid, visible) VALUES(?, ?, ?)', array($userId, $sharedUserId, '0'));
		}
		
		$result = array('userid' => $userId, 'sharedid' => $sharedUserId, 'username' => getUserFullName($sharedUserId));
		$response = new Mycrm_Response();
		$response->setResult($result);
		$response->emit();
	}
	
	/**
	 * Function to add other user calendar to shared calendar
	 * @param Mycrm_Request $request
	 * @return Mycrm_Response $response
	 */
	function addUserCalendar(Mycrm_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$sharedUserId = $request->get('selectedUser');
		$color = $request->get('selectedColor');
		
		$db = PearDatabase::getInstance();
		
		$queryResult = $db->pquery('SELECT 1 FROM mycrm_shareduserinfo WHERE userid=? AND shareduserid=?', array($userId, $sharedUserId));
		
		if($db->num_rows($queryResult) > 0) {
			$db->pquery('UPDATE mycrm_shareduserinfo SET color=?, visible=? WHERE userid=? AND shareduserid=?', array($color, '1', $userId, $sharedUserId));
		} else {
			$db->pquery('INSERT INTO mycrm_shareduserinfo (userid, shareduserid, color, visible) VALUES(?, ?, ?, ?)', array($userId, $sharedUserId, $color, '1'));
		}
		
		$response = new Mycrm_Response();
		$response->setResult(array('success' => true));
		$response->emit();
	}
	
	/**
	 * Function to delete the calendar view from My Calendar
	 * @param Mycrm_Request $request
	 * @return Mycrm_Response $response
	 */
	function deleteCalendarView(Mycrm_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$viewmodule = $request->get('viewmodule');
		$viewfieldname = $request->get('viewfieldname');
		
		
		$db = PearDatabase::getInstance();
		$db->pquery('UPDATE mycrm_calendar_user_activitytypes 
			INNER JOIN mycrm_calendar_default_activitytypes ON mycrm_calendar_default_activitytypes.id = mycrm_calendar_user_activitytypes.defaultid
			SET mycrm_calendar_user_activitytypes.visible=? WHERE mycrm_calendar_user_activitytypes.userid=? AND mycrm_calendar_default_activitytypes.module=? AND mycrm_calendar_default_activitytypes.fieldname=?', 
				array('0', $userId, $viewmodule, $viewfieldname));
		
		$result = array('viewmodule' => $viewmodule, 'viewfieldname' => $viewfieldname, 'viewfieldlabel' => $request->get('viewfieldlabel'));
		$response = new Mycrm_Response();
		$response->setResult($result);
		$response->emit();
	}
	
	/**
	 * Function to add calendar views to My calendar
	 * @param Mycrm_Request $request
	 * @return Mycrm_Response $response
	 */
	function addCalendarView(Mycrm_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$viewmodule = $request->get('viewmodule');
		$viewfieldname = $request->get('viewfieldname');
		$viewcolor = $request->get('viewColor');
		
		$db = PearDatabase::getInstance();
		
		$db->pquery('UPDATE mycrm_calendar_user_activitytypes 
					INNER JOIN mycrm_calendar_default_activitytypes ON mycrm_calendar_default_activitytypes.id = mycrm_calendar_user_activitytypes.defaultid
					SET mycrm_calendar_user_activitytypes.color=?, mycrm_calendar_user_activitytypes.visible=? 
					WHERE mycrm_calendar_user_activitytypes.userid=? AND mycrm_calendar_default_activitytypes.module=? AND mycrm_calendar_default_activitytypes.fieldname=?',
						array($viewcolor, '1', $userId, $viewmodule, $viewfieldname));
		
		$response = new Mycrm_Response();
		$response->setResult(array('success' => true));
		$response->emit();
	}
	

}