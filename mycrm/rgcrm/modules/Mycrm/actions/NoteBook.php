<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_NoteBook_Action extends Mycrm_Action_Controller {
	
	function __construct() {
		$this->exposeMethod('NoteBookCreate');
	}
	
	function process(Mycrm_Request $request) {
		$mode = $request->getMode();
		
		if($mode){
			$this->invokeExposedMethod($mode,$request);
		}
	}
	
	function NoteBookCreate(Mycrm_Request $request){
		$adb = PearDatabase::getInstance();
		
		$userModel = Users_Record_Model::getCurrentUserModel();
		$linkId = $request->get('linkId');
		$noteBookName = $request->get('notePadName');
		$noteBookContent = $request->get('notePadContent');
		
		$date_var = date("Y-m-d H:i:s");
		$date = $adb->formatDate($date_var, true);
		
		$dataValue = array();
		$dataValue['contents'] = $noteBookContent;
		$dataValue['lastSavedOn'] = $date;
		
		$data = Zend_Json::encode((object) $dataValue);

		$query="INSERT INTO mycrm_module_dashboard_widgets(linkid, userid, filterid, title, data) VALUES(?,?,?,?,?)";
		$params= array($linkId,$userModel->getId(),0,$noteBookName,$data);
		$adb->pquery($query, $params);
		$id = $adb->getLastInsertID();
		
		$result = array();
		$result['success'] = TRUE;
		$result['widgetId'] = $id;
		$response = new Mycrm_Response();
		$response->setResult($result);
		$response->emit();
		
	}
        
        public function validateRequest(Mycrm_Request $request) { 
            $request->validateWriteAccess(); 
        }
}
