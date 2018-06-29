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
 * Mycrm Action Model Class
 */
class Mycrm_Action_Model extends Mycrm_Base_Model {

	static $standardActions = array('0' => 'Save','1' => 'EditView','2' => 'Delete','3' => 'index','4' => 'DetailView');
	static $nonConfigurableActions = array('Save', 'index', 'SavePriceBook', 'SaveVendor',
											'DetailViewAjax', 'PriceBookEditView', 'QuickCreate', 'VendorEditView',
											'DeletePriceBook', 'DeleteVendor', 'Popup', 'PriceBookDetailView',
											'TagCloud', 'VendorDetailView','Merge');
	static $utilityActions = array('5' => 'Import', '6' => 'Export', '8' => 'Merge', '9' => 'ConvertLead', '10' => 'DuplicatesHandling');

	public function getId() {
		return $this->get('actionid');
	}

	public function getName() {
		return $this->get('actionname');
	}

	public function isUtilityTool() {
		return false;
	}

	public function isModuleEnabled($module) {
		$db = PearDatabase::getInstance();
		if(!$module->isEntityModule()) {
			return false;
		}
		if(in_array($this->getName(), self::$standardActions)) {
			return true;
		}
		$tabId = $module->getId();
		$sql = 'SELECT 1 FROM mycrm_profile2standardpermissions WHERE tabid = ? AND operation = ? LIMIT 1';
		$params = array($tabId, $this->getId());
		$result = $db->pquery($sql, $params);
		if($result && $db->num_rows($result) > 0) {
			return true;
		}
		return false;
	}

	public static function getInstanceFromQResult($result, $rowNo=0) {
		$db = PearDatabase::getInstance();
		$row = $db->query_result_rowdata($result, $rowNo);
		$className = 'Mycrm_Action_Model';
		$actionName = $row['actionname'];
		if(!in_array($actionName, self::$standardActions)) {
			$className = 'Mycrm_Utility_Model';
		}
		$actionModel = new $className();
		return $actionModel->setData($row);
	}

	protected static $cachedInstances = NULL;
	public static function getInstance($value, $force=false) {
		if (!self::$cachedInstances || $force) {
			self::$cachedInstances = self::getAll();
		}
		if (self::$cachedInstances) {
			$actionid = Mycrm_Utils::isNumber($value) ? $value : false;
			foreach (self::$cachedInstances as $instance) {
				if($actionid !== false) {
					if ($instance->get('actionid') == $actionid) {
						return $instance;
					}
				} else {
					if ($instance->get('actionname') == $value) {
						return $instance;
					}
				}
			}
		}
		return null;
	}
	
	public static function getInstanceWithIdOrName($value) {
		$db = PearDatabase::getInstance();

		if(Mycrm_Utils::isNumber($value)) {
			$sql = 'SELECT * FROM mycrm_actionmapping WHERE actionid=? LIMIT 1';
		} else {
			$sql = 'SELECT * FROM mycrm_actionmapping WHERE actionname=?';
		}
		$params = array($value);
		$result = $db->pquery($sql, $params);
		if($db->num_rows($result) > 0) {
			return self::getInstanceFromQResult($result);
		}
		return null;
	}

	public static function getAll($configurable=false) {
		$actionModels = Mycrm_Cache::get('mycrm', 'actions');
        if(!$actionModels){
            $db = PearDatabase::getInstance();

            $sql = 'SELECT * FROM mycrm_actionmapping';
            $params = array();
            if($configurable) {
                $sql .= ' WHERE actionname NOT IN ('. generateQuestionMarks(self::$nonConfigurableActions) .')';
                array_push($params, self::$nonConfigurableActions);
            }
            $result = $db->pquery($sql, $params);
            $noOfRows = $db->num_rows($result);
            $actionModels = array();
            for($i=0; $i<$noOfRows; ++$i) {
                $actionModels[] = self::getInstanceFromQResult($result, $i);
            }
            Mycrm_Cache::set('mycrm','actions', $actionModels);
        }
		return $actionModels;
	}

	public static function getAllBasic($configurable=false) {
		$db = PearDatabase::getInstance();

		$basicActionIds = array_keys(self::$standardActions);
		$sql = 'SELECT * FROM mycrm_actionmapping WHERE actionid IN ('. generateQuestionMarks($basicActionIds) .')';
		$params = $basicActionIds;
		if($configurable) {
			$sql .= ' AND actionname NOT IN ('. generateQuestionMarks(self::$nonConfigurableActions) .')';
			$params = array_merge($params, self::$nonConfigurableActions);
		}
		$result = $db->pquery($sql, $params);
		$noOfRows = $db->num_rows($result);
		$actionModels = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$actionModels[] = self::getInstanceFromQResult($result, $i);
		}
		return $actionModels;
	}

	public static function getAllUtility($configurable=false) {
		$db = PearDatabase::getInstance();

		$basicActionIds = array_keys(self::$standardActions);
		$sql = 'SELECT * FROM mycrm_actionmapping WHERE actionid NOT IN ('. generateQuestionMarks($basicActionIds) .')';
		$params = $basicActionIds;
		if($configurable) {
			$sql .= ' AND actionname NOT IN ('. generateQuestionMarks(self::$nonConfigurableActions) .')';
			$params = array_merge($params, self::$nonConfigurableActions);
		}
		$result = $db->pquery($sql, $params);
		$noOfRows = $db->num_rows($result);
		$actionModels = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$actionModels[] = self::getInstanceFromQResult($result, $i);
		}
		return $actionModels;
	}

}