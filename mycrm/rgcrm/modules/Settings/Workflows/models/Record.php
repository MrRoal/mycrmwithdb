<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

/*
 * Workflow Record Model Class
 */
require_once 'modules/com_mycrm_workflow/include.inc';
require_once 'modules/com_mycrm_workflow/expression_engine/VTExpressionsManager.inc';

class Settings_Workflows_Record_Model extends Settings_Mycrm_Record_Model {

	public function getId() {
		return $this->get('workflow_id');
	}

	public function getName() {
		return $this->get('summary');
	}

	public function get($key) {
//		if($key == 'execution_condition') {
//			$executionCondition = parent::get($key);
//			$executionConditionAsLabel = Settings_Workflows_Module_Model::$triggerTypes[$executionCondition];
//			return Mycrm_Language_Handler::getTranslatedString($executionConditionAsLabel, 'Settings:Workflows');
//		}
//		if($key == 'module_name') {
//			$moduleName = parent::get($key);
//			return Mycrm_Language_Handler::getTranslatedString($moduleName, $moduleName);
//		}
		return parent::get($key);
	}

	public function getEditViewUrl() {
		return 'index.php?module=Workflows&parent=Settings&view=Edit&record='.$this->getId();
	}

	public function getTasksListUrl() {
		return 'index.php?module=Workflows&parent=Settings&view=TasksList&record='.$this->getId();
	}

	public function getAddTaskUrl() {
		return 'index.php?module=Workflows&parent=Settings&view=EditTask&for_workflow='.$this->getId();
	}

	protected function setWorkflowObject($wf) {
		$this->workflow_object = $wf;
		return $this;
	}

	public function getWorkflowObject() {
		return $this->workflow_object;
	}

	public function getModule() {
		return $this->module;
	}

	public function setModule($moduleName) {
		$this->module = Mycrm_Module_Model::getInstance($moduleName);
		return $this;
	}

	public function getTasks($active=false) {
		return Settings_Workflows_TaskRecord_Model::getAllForWorkflow($this, $active);
	}

	public function getTaskTypes() {
		return Settings_Workflows_TaskType_Model::getAllForModule($this->getModule());
	}

	public function isDefault() {
		$wf = $this->getWorkflowObject();
		if($wf->defaultworkflow == 1) {
			return true;
		}
		return false;
	}

	public function save() {
		$db = PearDatabase::getInstance();
		$wm = new VTWorkflowManager($db);

		$wf = $this->getWorkflowObject();
		$wf->description = $this->get('summary');
		$wf->test = Zend_Json::encode($this->get('conditions'));
		$wf->moduleName = $this->get('module_name');
		$wf->executionCondition = $this->get('execution_condition');
		$wf->filtersavedinnew = $this->get('filtersavedinnew');
		$wf->schtypeid = $this->get('schtypeid');
		$wf->schtime = $this->get('schtime');
		$wf->schdayofmonth = $this->get('schdayofmonth');
		$wf->schdayofweek = $this->get('schdayofweek');
		$wf->schmonth = $this->get('schmonth');
		$wf->schmonth = $this->get('schmonth');
		$wf->schannualdates = $this->get('schannualdates');
		$wf->nexttrigger_time = $this->get('nexttrigger_time');
		$wm->save($wf);

		$this->set('workflow_id', $wf->id);
	}

	public function delete() {
		$db = PearDatabase::getInstance();
		$wm = new VTWorkflowManager($db);
		$wm->delete($this->getId());
	}

	/**
	 * Functions returns the Custom Entity Methods that are supported for a module
	 * @return <Array>
	 */
	public function getEntityMethods() {
		$db = PearDatabase::getInstance();
		$emm = new VTEntityMethodManager($db);
		$methodNames = $emm->methodsForModule($this->get('module_name'));
		return $methodNames;
	}

	/**
	 * Function to get the list view actions for the record
	 * @return <Array> - Associate array of Mycrm_Link_Model instances
	 */
	public function getRecordLinks() {

		$links = array();

		$recordLinks = array(
			array(
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'icon-pencil'
			),
			array(
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:Mycrm_List_Js.deleteRecord('.$this->getId().');',
				'linkicon' => 'icon-trash'
			)
		);
		foreach($recordLinks as $recordLink) {
			$links[] = Mycrm_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}

	public static function getInstance($workflowId) {
		$db = PearDatabase::getInstance();
		$wm = new VTWorkflowManager($db);
		$wf = $wm->retrieve($workflowId);
		return self::getInstanceFromWorkflowObject($wf);
	}

	public static function getCleanInstance($moduleName) {
		$db = PearDatabase::getInstance();
		$wm = new VTWorkflowManager($db);
		$wf = $wm->newWorkflow($moduleName);
		$wf->filtersavedinnew = 6;
		return self::getInstanceFromWorkflowObject($wf);
	}

	public static function getInstanceFromWorkflowObject($wf) {
		$workflowModel = new self();

		$workflowModel->set('summary', $wf->description);
		$workflowModel->set('conditions', Zend_Json::decode($wf->test));
		$workflowModel->set('execution_condition', $wf->executionCondition);
		$workflowModel->set('module_name', $wf->moduleName);
		$workflowModel->set('workflow_id', $wf->id);
		$workflowModel->set('filtersavedinnew', $wf->filtersavedinnew);
		$workflowModel->setWorkflowObject($wf);
		$workflowModel->setModule($wf->moduleName);
		return $workflowModel;
	}

	function executionConditionAsLabel($executionCondition=null){
		if($executionCondition == null) {
			$executionCondition = $this->get('execution_condition');
		}
		$arr = array('ON_FIRST_SAVE', 'ONCE', 'ON_EVERY_SAVE', 'ON_MODIFY', '', 'ON_SCHEDULE', 'MANUAL');
		return $arr[$executionCondition-1];
	}

	function isFilterSavedInNew() {
		$wf = $this->getWorkflowObject();
		if($wf->filtersavedinnew == '6') {
			return true;
		}
		return false;
	}
	/**
	 * Functions transforms workflow filter to advanced filter
	 * @return <Array>
	 */
	function transformToAdvancedFilterCondition() {
		$conditions = $this->get('conditions');
		$transformedConditions = array();

		if(!empty($conditions)) {
			foreach($conditions as $index => $info) {
				if(!($info['groupid'])) {
					$firstGroup[] = array('columnname' => $info['fieldname'], 'comparator' => $info['operation'], 'value' => $info['value'],
						'column_condition' => $info['joincondition'], 'valuetype' => $info['valuetype'], 'groupid' => $info['groupid']);
				} else {
					$secondGroup[] = array('columnname' => $info['fieldname'], 'comparator' => $info['operation'], 'value' => $info['value'],
						'column_condition' => $info['joincondition'], 'valuetype' => $info['valuetype'], 'groupid' => $info['groupid']);
				}
			}
		}
		$transformedConditions[1] = array('columns'=>$firstGroup);
		$transformedConditions[2] = array('columns'=>$secondGroup);
		return $transformedConditions;
	}

	/**
	 * Function returns valuetype of the field filter
	 * @return <String>
	 */
	function getFieldFilterValueType($fieldname) {
		$conditions = $this->get('conditions');
		if(!empty($conditions) && is_array($conditions)) {
			foreach($conditions as $filter) {
				if($fieldname == $filter['fieldname']) {
					return $filter['valuetype'];
				}
			}
		}
		return false;
	}

	/**
	 * Function transforms Advance filter to workflow conditions
	 */
	function transformAdvanceFilterToWorkFlowFilter() {
		$conditions = $this->get('conditions');
		$wfCondition = array();

		if(!empty($conditions)) {
			foreach($conditions as $index => $condition) {
				$columns = $condition['columns'];
				if($index == '1' && empty($columns)) {
					$wfCondition[] = array('fieldname'=>'', 'operation'=>'', 'value'=>'', 'valuetype'=>'',
						'joincondition'=>'', 'groupid'=>'0');
				}
				if(!empty($columns) && is_array($columns)) {
					foreach($columns as $column) {
						$wfCondition[] = array('fieldname'=>$column['columnname'], 'operation'=>$column['comparator'],
							'value'=>$column['value'], 'valuetype'=>$column['valuetype'], 'joincondition'=>$column['column_condition'],
							'groupjoin'=>$condition['condition'], 'groupid'=>$column['groupid']);
					}
				}
			}
		}
		$this->set('conditions', $wfCondition);
	}


	/**
	 * Function returns all the related modules for workflows create entity task
	 * @return <JSON>
	 */
	public function getDependentModules() {
		$db = PearDatabase::getInstance();
		$moduleName = $this->getModule()->getName();

		$result = $db->pquery("SELECT fieldname, tabid, typeofdata, mycrm_ws_referencetype.type as reference_module FROM mycrm_field
								INNER JOIN mycrm_ws_fieldtype ON mycrm_field.uitype = mycrm_ws_fieldtype.uitype
								INNER JOIN mycrm_ws_referencetype ON mycrm_ws_fieldtype.fieldtypeid = mycrm_ws_referencetype.fieldtypeid
							UNION
							SELECT fieldname, tabid, typeofdata, relmodule as reference_module FROM mycrm_field
								INNER JOIN mycrm_fieldmodulerel ON mycrm_field.fieldid = mycrm_fieldmodulerel.fieldid", array());

		$noOfFields = $db->num_rows($result);

		$dependentFields = array();
		// List of modules which will not be supported by 'Create Entity' workflow task
		$filterModules = array('Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder', 'Emails', 'Calendar', 'Events', 'Accounts');
		$skipFieldsList = array();
		for ($i = 0; $i < $noOfFields; ++$i) {
			$tabId = $db->query_result($result, $i, 'tabid');
			$fieldName = $db->query_result($result, $i, 'fieldname');
			$typeOfData = $db->query_result($result, $i, 'typeofdata');
			$referenceModule = $db->query_result($result, $i, 'reference_module');
			$tabModuleName = getTabModuleName($tabId);
			if (in_array($tabModuleName, $filterModules))
				continue;
			if ($referenceModule == $moduleName && $tabModuleName != $moduleName) {
				if(!vtlib_isModuleActive($tabModuleName))continue;
				$dependentFields[$tabModuleName] = array('fieldname' => $fieldName, 'modulelabel' => getTranslatedString($tabModuleName, $tabModuleName));
			} else {
				$dataTypeInfo = explode('~', $typeOfData);
				if ($dataTypeInfo[1] == 'M') { // If the current reference field is mandatory
					$skipFieldsList[$tabModuleName] = array('fieldname' => $fieldName);
				}
			}
		}
		foreach ($skipFieldsList as $tabModuleName => $fieldInfo) {
			$dependentFieldInfo = $dependentFields[$tabModuleName];
			if ($dependentFieldInfo['fieldname'] != $fieldInfo['fieldname']) {
				unset($dependentFields[$tabModuleName]);
			}
		}

		return $dependentFields;
	}

	/**
	 * Function to get reference field name
	 * @param <String> $relatedModule
	 * @return <String> fieldname
	 */
	public function getReferenceFieldName($relatedModule) {
		if ($relatedModule) {
			$db = PearDatabase::getInstance();

			$relatedModuleModel = Mycrm_Module_Model::getInstance($relatedModule);
			$referenceFieldsList = $relatedModuleModel->getFieldsByType('reference');

			foreach ($referenceFieldsList as $fieldName => $fieldModel) {
				if (in_array($this->getModule()->getName(), $fieldModel->getReferenceList())) {
					return $fieldName;
				}
			}
		}
		return false;
	}
	public function updateNextTriggerTime() {
		$db = PearDatabase::getInstance();
		$wm = new VTWorkflowManager($db);
		$wf = $this->getWorkflowObject();
		$wm->updateNexTriggerTime($wf);
	}
}