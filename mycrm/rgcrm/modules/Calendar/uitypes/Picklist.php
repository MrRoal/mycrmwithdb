<?php

/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Calendar_Picklist_UIType extends Mycrm_Picklist_UIType {
    
    
    public function getListSearchTemplateName() {
        
        $fieldName = $this->get('field')->get('name');
        
        if($fieldName == 'taskstatus') {
            return 'uitypes/StatusPickListFieldSearchView.tpl';
        }
        else if ($fieldName == 'activitytype') {
            return 'uitypes/ActivityPicklistFieldSearchView.tpl';
        }
            return parent::getListSearchTemplateName();
    }
}