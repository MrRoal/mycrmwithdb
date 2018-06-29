<?php

/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_LayoutEditor_Field_Action extends Settings_Mycrm_Index_Action {

    function __construct() {
        $this->exposeMethod('add');
        $this->exposeMethod('save');
        $this->exposeMethod('delete');
        $this->exposeMethod('move');
        $this->exposeMethod('unHide');
    }

    public function add(Mycrm_Request $request) {
        $type = $request->get('fieldType');
        $moduleName = $request->get('sourceModule');
        $blockId = $request->get('blockid');
        $moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($moduleName);
        $response = new Mycrm_Response();
        try{

            $fieldModel = $moduleModel->addField($type,$blockId,$request->getAll());
            $fieldInfo = $fieldModel->getFieldInfo();
            $responseData = array_merge(array('id'=>$fieldModel->getId(), 'blockid'=>$blockId, 'customField'=>$fieldModel->isCustomField()),$fieldInfo);
            $response->setResult($responseData);
        }catch(Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    public function save(Mycrm_Request $request) {
        $fieldId = $request->get('fieldid');
        $fieldInstance = Mycrm_Field_Model::getInstance($fieldId);
        $fieldInstance->updateTypeofDataFromMandatory($request->get('mandatory'))
					  ->set('presence', $request->get('presence'))
                      ->set('quickcreate', $request->get('quickcreate'))
					  ->set('summaryfield', $request->get('summaryfield'))
                      ->set('masseditable', $request->get('masseditable'));
		$defaultValue = $request->get('fieldDefaultValue');
		if($fieldInstance->getFieldDataType() == 'date') {
			$dateInstance = new Mycrm_Date_UIType();
			$defaultValue = $dateInstance->getDBInsertedValue($defaultValue);
		}

		if(is_array($defaultValue)) {
			$defaultValue = implode(' |##| ',$defaultValue);
		}
        $fieldInstance->set('defaultvalue', $defaultValue);
        $response = new Mycrm_Response();
        try{
            $fieldInstance->save();
            $response->setResult(array('success'=>true, 'presence'=>$request->get('presence'), 'mandatory'=>$fieldInstance->isMandatory(),
									'label'=>vtranslate($fieldInstance->get('label'), $request->get('sourceModule'))));
        }catch(Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    public function delete(Mycrm_Request $request) {
        $fieldId = $request->get('fieldid');
        $fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($fieldId);
        $response = new Mycrm_Response();

        if(!$fieldInstance->isCustomField()) {
            $response->setError('122', 'Cannot delete Non custom field');
            $response->emit();
            return;
        }

        try{
            $fieldInstance->delete();
            $response->setResult(array('success'=>true));
        }catch(Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    public function move(Mycrm_Request $request) {
        $updatedFieldsList = $request->get('updatedFields');

		//This will update the fields sequence for the updated blocks
        Settings_LayoutEditor_Block_Model::updateFieldSequenceNumber($updatedFieldsList);

        $response = new Mycrm_Response();
		$response->setResult(array('success'=>true));
        $response->emit();
    }

    public function unHide(Mycrm_Request $request) {
        $response = new Mycrm_Response();
        try{
			$fieldIds = $request->get('fieldIdList');
            Settings_LayoutEditor_Field_Model::makeFieldActive($fieldIds, $request->get('blockId'));
			$responseData = array();
			foreach($fieldIds as $fieldId) {
				$fieldModel = Settings_LayoutEditor_Field_Model::getInstance($fieldId);
				$fieldInfo = $fieldModel->getFieldInfo();
				$responseData[] = array_merge(array('id'=>$fieldModel->getId(), 'blockid'=>$fieldModel->get('block')->id, 'customField'=>$fieldModel->isCustomField()),$fieldInfo);
			}
            $response->setResult($responseData);
        }catch(Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();

    }
    
    public function validateRequest(Mycrm_Request $request) { 
        $request->validateWriteAccess(); 
    } 
}