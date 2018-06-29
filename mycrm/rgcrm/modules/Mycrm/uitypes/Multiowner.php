<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_Multiowner_UIType extends Mycrm_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/MultiOwner.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($values) {
        if($values == NULL && !is_array($values)) return;
        foreach($values as $value){
            if (self::getOwnerType($value) === 'User') {
                $userModel = Users_Record_Model::getCleanInstance('Users');
                $userModel->set('id', $value);
                $detailViewUrl = $userModel->getDetailViewUrl();
                $currentUser = Users_Record_Model::getCurrentUserModel();
                if(!$currentUser->isAdminUser()){
                    return getOwnerName($value);
                }
            } else {
                $currentUser = Users_Record_Model::getCurrentUserModel();
                if(!$currentUser->isAdminUser()){
                    return getOwnerName($value);
                }
                $recordModel = new Settings_Groups_Record_Model();
                $recordModel->set('groupid',$value);
                $detailViewUrl = $recordModel->getDetailViewUrl();
            }
            $displayvalue[] = "<a href=" .$detailViewUrl. ">" .getOwnerName($value). "</a>&nbsp";
        }
        $displayvalue = implode(',',$displayvalue);
        return $displayvalue;
	}
    
	/**
	 * Function to know owner is either User or Group
	 * @param <Integer> userId/GroupId
	 * @return <String> User/Group
	 */
	public static function getOwnerType($id) {
		$db = PearDatabase::getInstance();

		$result = $db->pquery('SELECT 1 FROM mycrm_users WHERE id = ?', array($id));
		if ($db->num_rows($result) > 0) {
			return 'User';
		}
		return 'Group';
	}
}