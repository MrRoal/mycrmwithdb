<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/
require_once 'vtlib/Mycrm/Block.php';

class Mycrm_Block_Model extends Mycrm_Block {

	public $fields = false;

	public function getFields() {
		if(empty($this->fields)) {
			$moduleFields = Mycrm_Field_Model::getAllForModule($this->module);
            $this->fields = array();
            
            // if block does not contains any fields 
            if(!is_array($moduleFields[$this->id])){
                $moduleFields[$this->id] = array();
            }
            
			foreach($moduleFields[$this->id] as $field){
                    $this->fields[$field->get('name')] = $field;
			}
		}
		return $this->fields;
	}
    
    public function setFields($fieldModelList) {
        $this->fields = $fieldModelList;
        return $this;
    }

	/**
	 * Function to get the value of a given property
	 * @param <String> $propertyName
	 * @return <Object>
	 */
	public function get($propertyName) {
		if(property_exists($this,$propertyName)){
			return $this->$propertyName;
		}
	}
    
    public function set($propertyName, $value) {
        if(property_exists($this,$propertyName)){
            $this->$propertyName = $value;
        }
        return $this;
    }
    
    public function isCustomized() {
        return ($this->iscustom != 0) ? true : false;
    }
    
    public function __update() {
        $db = PearDatabase::getInstance();
        
        $query = 'UPDATE mycrm_blocks SET blocklabel=?,display_status=? WHERE blockid=?';
        $params = array($this->label, $this->display_status, $this->id);
        $db->pquery($query, $params);
    }

	/**
     * Function which indicates whether the block is shown or hidden
     * @return : <boolean>
     */
    public function isHidden(){
        if($this->get('display_status') == '0') {
            return true;
        }
        return false;
    }
    
    /**
     * Function to get the in active fields for the block
     * @param type $raw - true to send field in model format or false to send in array format
     * @return type - arrays
     */
    public function getInActiveFields($raw=true) {
        $inActiveFields = array();
        $fields = $this->getFields();
        foreach($fields as $fieldName => $fieldModel) {
            if(!$fieldModel->isActiveField()) {
                if($raw){
                    $inActiveFields[$fieldName] = $fieldModel;
                }else{
                    $fieldDetails = $fieldModel->getFieldInfo();
                    $fieldDetails['fieldid'] = $fieldModel->getId();
                    $inActiveFields[$fieldName] = $fieldDetails;
                }
            }
        }
        return $inActiveFields;
    }

	/**
	 * Function to retrieve block instances for a module
	 * @param <type> $moduleModel - module instance
	 * @return <array> - list of Mycrm_Block_Model
	 */
	public static function getAllForModule($moduleModel) {
		$blockObjects = Mycrm_Cache::get('ModuleBlock',$moduleModel->getName());
        
        if(!$blockObjects){
            $blockObjects = parent::getAllForModule($moduleModel);
            Mycrm_Cache::set('ModuleBlock',$moduleModel->getName(),$blockObjects);
        }
        $blockModelList = array();

		if($blockObjects) {
			foreach($blockObjects as $blockObject) {
				$blockModelList[] = self::getInstanceFromBlockObject($blockObject);
			}
		}
		return $blockModelList;
	}
	
	public static function getInstance($value, $moduleInstance = false) {
		$blockInstance = parent::getInstance($value, $moduleInstance);
		$blockModel = self::getInstanceFromBlockObject($blockInstance);
		return $blockModel;
	}

	/**
	 * Function to retrieve block instance from Mycrm_Block object
	 * @param Mycrm_Block $blockObject - vtlib block object
	 * @return Mycrm_Block_Model
	 */
	public static function getInstanceFromBlockObject(Mycrm_Block $blockObject) {
		$objectProperties = get_object_vars($blockObject);
		$blockClassName = Mycrm_Loader::getComponentClassName('Model', 'Block', $blockObject->module->name);
		$blockModel = new $blockClassName();
		foreach($objectProperties as $properName=>$propertyValue) {
			$blockModel->$properName = $propertyValue;
		}
		return $blockModel;
	}
    
    public static function updateSequenceNumber($sequenceList) {
        $db = PearDatabase::getInstance();
        $query = 'UPDATE mycrm_blocks SET sequence = CASE blockid ';
        foreach ($sequenceList as $blockId => $sequence){
            $query .=' WHEN '.$blockId.' THEN '.$sequence;
        }
        $query .=' END ';
        $query .= ' WHERE blockid IN ('.generateQuestionMarks($sequenceList).')';
        $db->pquery($query, array_keys($sequenceList));
    }
    
    public static function checkFieldsExists($blockId) {
        $db = PearDatabase::getInstance();
        $query = 'SELECT 1 FROM mycrm_field WHERE block=?';
        $result = $db->pquery($query, array($blockId));
        return ($db->num_rows($result) > 0) ? true : false;
    }
	
	/**
	 * Function to push all blocks down after sequence number
	 * @param type $fromSequence 
	 */
	public static function pushDown($fromSequence, $sourceModuleTabId) {
		$db = PearDatabase::getInstance();
		$query = 'UPDATE mycrm_blocks SET sequence=sequence+1 WHERE sequence > ? and tabid=?';
		$result = $db->pquery($query, array($fromSequence,$sourceModuleTabId));
	}
    
    public static function getAllBlockSequenceList($moduleTabId) {
        $db = PearDatabase::getInstance();
        $query = 'SELECT blockid,sequence FROM mycrm_blocks where tabid=?';
        $result = $db->pquery($query, array($moduleTabId));
        $response = array();
        $num_rows = $db->num_rows($result);
        for($i=0; $i<$num_rows; $i++) {
            $row = $db->query_result_rowdata($result, $i);
            $response[$row['blockid']] = $row['sequence'];
        }
        return $response;
    }

	/**
	 * Function to check whether duplicate exist or not
	 * @param <String> $blockLabel
	 * @param <Number> ModuleId
	 * @return <Boolean> true/false
	 */
	public static function checkDuplicate($blockLabel, $tabId) {
		$db = PearDatabase::getInstance();

		$result = $db->pquery('SELECT 1 FROM mycrm_blocks WHERE blocklabel = ? AND tabid = ?', array($blockLabel, $tabId));
		if ($db->num_rows($result)) {
			return true;
		}
		return false;
	}
	
}
