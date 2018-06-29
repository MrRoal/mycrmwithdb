<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_Relation_Model extends Mycrm_Base_Model{

	protected $parentModule = false;
	protected $relatedModule = false;

	protected $relationType = false;

	//one to many
	const RELATION_DIRECT = 1;

	//Many to many and many to one
	const RELATION_INDIRECT = 2;
	
	/**
	 * Function returns the relation id
	 * @return <Integer>
	 */
	public function getId(){
		return $this->get('relation_id');
	}

	/**
	 * Function sets the relation's parent module model
	 * @param <Mycrm_Module_Model> $moduleModel
	 * @return Mycrm_Relation_Model
	 */
	public function setParentModuleModel($moduleModel){
		$this->parentModule = $moduleModel;
		return $this;
	}

	/**
	 * Function that returns the relation's parent module model
	 * @return <Mycrm_Module_Model>
	 */
	public function getParentModuleModel(){
		if(empty($this->parentModule)){
			$this->parentModule = Mycrm_Module_Model::getInstance($this->get('tabid'));
		}
		return $this->parentModule;
	}

	public function getRelationModuleModel(){
		if(empty($this->relatedModule)){
			$this->relatedModule = Mycrm_Module_Model::getInstance($this->get('related_tabid'));
		}
		return $this->relatedModule;
	}
    
    public function getRelationModuleName() {
        $relationModuleName = $this->get('relatedModuleName');
        if(!empty($relationModuleName)) {
            return $relationModuleName;
        }
        return $this->getRelationModuleModel()->getName();
    }

	public function getListUrl($parentRecordModel) {
		return 'module='.$this->getParentModuleModel()->get('name').'&relatedModule='.$this->get('modulename').
				'&view=Detail&record='.$parentRecordModel->getId().'&mode=showRelatedList';
	}

	public function setRelationModuleModel($relationModel){
		$this->relatedModule = $relationModel;
		return $this;
	}

	public function isActionSupported($actionName){
		$actionName = strtolower($actionName);
		$actions = $this->getActions();
		foreach($actions as $action) {
			if(strcmp(strtolower($action), $actionName)== 0){
				return true;
			}
		}
		return false;
	}

	public function isSelectActionSupported() {
		return $this->isActionSupported('select');
	}

	public function isAddActionSupported() {
		return $this->isActionSupported('add');
	}

	public function getActions(){
		$actionString = $this->get('actions');

		$label = $this->get('label');
		// No actions for Activity history
		if($label == 'Activity History') {
			return array();
		}

		return explode(',', $actionString);
	}

	public function getQuery($parentRecord, $actions=false){
		$parentModuleModel = $this->getParentModuleModel();
		$relatedModuleModel = $this->getRelationModuleModel();
		$parentModuleName = $parentModuleModel->getName();
		$relatedModuleName = $relatedModuleModel->getName();
		$functionName = $this->get('name');
		$query = $parentModuleModel->getRelationQuery($parentRecord->getId(), $functionName, $relatedModuleModel);

		return $query;
	}

	public function addRelation($sourcerecordId, $destinationRecordId) {
		$sourceModule = $this->getParentModuleModel();
		$sourceModuleName = $sourceModule->get('name');
		$sourceModuleFocus = CRMEntity::getInstance($sourceModuleName);
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		relateEntities($sourceModuleFocus, $sourceModuleName, $sourcerecordId, $destinationModuleName, $destinationRecordId);
	}

	public function deleteRelation($sourceRecordId, $relatedRecordId){
		$sourceModule = $this->getParentModuleModel();
		$sourceModuleName = $sourceModule->get('name');
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		$destinationModuleFocus = CRMEntity::getInstance($destinationModuleName);
		DeleteEntity($destinationModuleName, $sourceModuleName, $destinationModuleFocus, $relatedRecordId, $sourceRecordId);
		return true;
	}

	public function isDirectRelation() {
		return ($this->getRelationType() == self::RELATION_DIRECT);
	}

	public function getRelationType(){
		if(empty($this->relationType)){
			$this->relationType = self::RELATION_INDIRECT;
			if ($this->getRelationField()) {
				$this->relationType = self::RELATION_DIRECT;
			}
		}
		return $this->relationType;
	}
    
    /**
     * Function which will specify whether the relation is editable
     * @return <Boolean>
     */
    public function isEditable() {
        return $this->getRelationModuleModel()->isPermitted('EditView');
    }
    
    /**
     * Function which will specify whether the relation is deletable
     * @return <Boolean>
     */
    public function isDeletable() {
        return $this->getRelationModuleModel()->isPermitted('Delete');
    }

	public static function getInstance($parentModuleModel, $relatedModuleModel, $label=false) {
		$db = PearDatabase::getInstance();

		$query = 'SELECT mycrm_relatedlists.*,mycrm_tab.name as modulename FROM mycrm_relatedlists
					INNER JOIN mycrm_tab on mycrm_tab.tabid = mycrm_relatedlists.related_tabid AND mycrm_tab.presence != 1
					WHERE mycrm_relatedlists.tabid = ? AND related_tabid = ?';
		$params = array($parentModuleModel->getId(), $relatedModuleModel->getId());

		if(!empty($label)) {
			$query .= ' AND label = ?';
			$params[] = $label;
		}
		
		$result = $db->pquery($query, $params);
		if($db->num_rows($result)) {
			$row = $db->query_result_rowdata($result, 0);
			$relationModelClassName = Mycrm_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->get('name'));
			$relationModel = new $relationModelClassName();
			$relationModel->setData($row)->setParentModuleModel($parentModuleModel)->setRelationModuleModel($relatedModuleModel);
			return $relationModel;
		}
		return false;
	}

	public static function getAllRelations($parentModuleModel, $selected = true, $onlyActive = true) {
		$db = PearDatabase::getInstance();

		$skipReltionsList = array('get_history');
        $query = 'SELECT mycrm_relatedlists.*,mycrm_tab.name as modulename FROM mycrm_relatedlists 
                    INNER JOIN mycrm_tab on mycrm_relatedlists.related_tabid = mycrm_tab.tabid
                    WHERE mycrm_relatedlists.tabid = ? AND related_tabid != 0';

		if ($selected) {
			$query .= ' AND mycrm_relatedlists.presence <> 1';
		}
        if($onlyActive){
            $query .= ' AND mycrm_tab.presence <> 1 ';
        }
        $query .= ' AND mycrm_relatedlists.name NOT IN ('.generateQuestionMarks($skipReltionsList).') ORDER BY sequence'; // TODO: Need to handle entries that has related_tabid 0

        $result = $db->pquery($query, array($parentModuleModel->getId(), $skipReltionsList));

		$relationModels = array();
		$relationModelClassName = Mycrm_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->get('name'));
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			//$relationModuleModel = Mycrm_Module_Model::getCleanInstance($moduleName);
			// Skip relation where target module does not exits or is no permitted for view.
			if (!Users_Privileges_Model::isPermitted($row['modulename'],'DetailView')) {
				continue;
			}
			$relationModel = new $relationModelClassName();
			$relationModel->setData($row)->setParentModuleModel($parentModuleModel)->set('relatedModuleName',$row['modulename']);
			$relationModels[] = $relationModel;
		}
		return $relationModels;
	}

	/**
	 * Function to get relation field for relation module and parent module
	 * @return Mycrm_Field_Model
	 */
	public function getRelationField() {
		$relationField = $this->get('relationField');
		if (!$relationField) {
			$relationField = false;
			$relatedModel = $this->getRelationModuleModel();
			$parentModule = $this->getParentModuleModel();
			$relatedModelFields = $relatedModel->getFields();

			foreach($relatedModelFields as $fieldName => $fieldModel) {
				if($fieldModel->getFieldDataType() == Mycrm_Field_Model::REFERENCE_TYPE) {
					$referenceList = $fieldModel->getReferenceList();
					if(in_array($parentModule->getName(), $referenceList)) {
						$this->set('relationField', $fieldModel);
						$relationField = $fieldModel;
						break;
					}
				}
			}
		}
		return $relationField;
	}
    
    public static  function updateRelationSequenceAndPresence($relatedInfoList, $sourceModuleTabId) {
        $db = PearDatabase::getInstance();
        $query = 'UPDATE mycrm_relatedlists SET sequence=CASE ';
        $relation_ids = array();
        foreach($relatedInfoList as $relatedInfo){
            $relation_id = $relatedInfo['relation_id'];
            $relation_ids[] = $relation_id;
            $sequence = $relatedInfo['sequence'];
            $presence = $relatedInfo['presence'];
            $query .= ' WHEN relation_id='.$relation_id.' THEN '.$sequence;
        }
        $query.= ' END , ';
        $query.= ' presence = CASE ';
        foreach($relatedInfoList as $relatedInfo){
            $relation_id = $relatedInfo['relation_id'];
            $relation_ids[] = $relation_id;
            $sequence = $relatedInfo['sequence'];
            $presence = $relatedInfo['presence'];
            $query .= ' WHEN relation_id='.$relation_id.' THEN '.$presence;
        }
        $query .= ' END WHERE tabid=? AND relation_id IN ('.  generateQuestionMarks($relation_ids).')';
        $result = $db->pquery($query, array($sourceModuleTabId,$relation_ids));
    }
	
	public function isActive() {
		return $this->get('presence') == 0 ? true : false;
	}
}
