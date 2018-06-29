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
 * Calendar Module Model Class
 */
class Events_Module_Model extends Calendar_Module_Model {

    /**
	 * Function to get the url for list view of the module
	 * @return <string> - url
	 */
	public function getListViewUrl() {
		return 'index.php?module=Calendar&view='.$this->getListViewName();
	}

   /**
	 * Function to save a given record model of the current module
	 * @param Mycrm_Record_Model $recordModel
	 */
	public function saveRecord($recordModel) {
        $recordModel = parent::saveRecord($recordModel);
        
        //code added to send mail to the mycrm_invitees
        $selectUsers = $recordModel->get('selectedusers');
        if(!empty($selectUsers))
        {
            $invities = implode(';',$selectUsers);
            $mail_contents = $recordModel->getInviteUserMailData();
            $activityMode = ($recordModel->getModuleName()=='Calendar') ? 'Task' : 'Events';
            sendInvitation($invities,$activityMode,$recordModel->get('subject'),$mail_contents);
        }
    }

	/**
	 * Function to retrieve name fields of a module
	 * @return <array> - array which contains fields which together construct name fields
	 */
	public function getNameFields(){
        $nameFieldObject = Mycrm_Cache::get('EntityField',$this->getName());
        $moduleName = $this->getName();
		if($nameFieldObject && $nameFieldObject->fieldname) {
			$this->nameFields = explode(',', $nameFieldObject->fieldname);
		} else {
			$adb = PearDatabase::getInstance();

			$query = "SELECT fieldname, tablename, entityidfield FROM mycrm_entityname WHERE tabid = ?";
			$result = $adb->pquery($query, array(getTabid('Calendar')));
			$this->nameFields = array();
			if($result){
				$rowCount = $adb->num_rows($result);
				if($rowCount > 0){
					$fieldNames = $adb->query_result($result,0,'fieldname');
					$this->nameFields = explode(',', $fieldNames);
				}
			}
			
			$entiyObj = new stdClass();
			$entiyObj->basetable = $adb->query_result($result, 0, 'tablename');
			$entiyObj->basetableid =  $adb->query_result($result, 0, 'entityidfield');
			$entiyObj->fieldname =  $fieldNames;
			Mycrm_Cache::set('EntityField',$this->getName(), $entiyObj);
		}
        return $this->nameFields;
	}
	
}
