<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Contacts_SaveAjax_Action extends Mycrm_SaveAjax_Action {

    public function getRecordModelFromRequest(Mycrm_Request $request) {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');

        if (!empty($recordId)) {
            $recordModel = Mycrm_Record_Model::getInstanceById($recordId, $moduleName);
            $recordModel->set('id', $recordId);
            $recordModel->set('mode', 'edit');

            $fieldModelList = $recordModel->getModule()->getFields();
            foreach ($fieldModelList as $fieldName => $fieldModel) {
                //For not converting craetedtime and modified time to user format
                $uiType = $fieldModel->get('uitype');
                if ($uiType == 70) {
                    $fieldValue = $recordModel->get($fieldName);
                } else {
                    $fieldValue = $fieldModel->getUITypeModel()->getUserRequestValue($recordModel->get($fieldName));
                }

                if ($fieldName === $request->get('field')) {
                    $fieldValue = $request->get('value');
                }
                $fieldDataType = $fieldModel->getFieldDataType();
                if ($fieldDataType == 'time') {
                    $fieldValue = Mycrm_Time_UIType::getTimeValueWithSeconds($fieldValue);
                }
                if ($fieldValue !== null) {
                    if (!is_array($fieldValue)) {
                        $fieldValue = trim($fieldValue);
                    }
                    $recordModel->set($fieldName, $fieldValue);
                }
                $recordModel->set($fieldName, $fieldValue);
            }
        } else {
            $moduleModel = Mycrm_Module_Model::getInstance($moduleName);

            $recordModel = Mycrm_Record_Model::getCleanInstance($moduleName);
            $recordModel->set('mode', '');

            $fieldModelList = $moduleModel->getFields();

            foreach ($fieldModelList as $fieldName => $fieldModel) {
                if ($request->has($fieldName)) {
                    $fieldValue = $request->get($fieldName, null);
                } else {
                    $fieldValue = $fieldModel->getDefaultFieldValue();

                    // to update the  support_end_date and support_start_date in Quick create
                    if ($fieldName == 'support_end_date') {
                        $fieldValue = DateTimeField::convertToUserFormat(date('Y-m-d', strtotime("+1 year")));
                    } else if ($fieldName == 'support_start_date') {
                        $fieldValue = DateTimeField::convertToUserFormat(date('Y-m-d'));
                    }
                }
                $fieldDataType = $fieldModel->getFieldDataType();
                if ($fieldDataType == 'time') {
                    $fieldValue = Mycrm_Time_UIType::getTimeValueWithSeconds($fieldValue);
                }
                if ($fieldValue !== null) {
                    if (!is_array($fieldValue)) {
                        $fieldValue = trim($fieldValue);
                    }
                    $recordModel->set($fieldName, $fieldValue);
                }
            }
        }

        return $recordModel;
    }
}
?>
