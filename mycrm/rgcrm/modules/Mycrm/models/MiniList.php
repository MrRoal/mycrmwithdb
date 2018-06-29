<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_MiniList_Model extends Mycrm_Widget_Model {

	protected $widgetModel;
	protected $extraData;

	protected $listviewController;
	protected $queryGenerator;
	protected $listviewHeaders;
	protected $listviewRecords;
	protected $targetModuleModel;

	public function setWidgetModel($widgetModel) {
		$this->widgetModel = $widgetModel;
		$this->extraData = $this->widgetModel->get('data');

		// Decode data if not done already.
		if (is_string($this->extraData)) {
			$this->extraData = Zend_Json::decode(decode_html($this->extraData));
		}
		if ($this->extraData == NULL) {
			throw new Exception("Invalid data");
		}
	}

	public function getTargetModule() {
		return $this->extraData['module'];
	}

	public function getTargetFields() {
		$fields = $this->extraData['fields'];
		if (!in_array("id", $fields)) $fields[] = "id";
		return $fields;
	}

	public function getTargetModuleModel() {
		if (!$this->targetModuleModel) {
			$this->targetModuleModel = Mycrm_Module_Model::getInstance($this->getTargetModule());
		}
		return $this->targetModuleModel;
	}

	protected function initListViewController() {
		if (!$this->listviewController) {
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$db = PearDatabase::getInstance();

			$filterid = $this->widgetModel->get('filterid');
			$this->queryGenerator = new QueryGenerator($this->getTargetModule(), $currentUserModel);
			$this->queryGenerator->initForCustomViewById($filterid);
			$this->queryGenerator->setFields( $this->getTargetFields() );

			if (!$this->listviewController) {
				$this->listviewController = new ListViewController($db, $currentUserModel, $this->queryGenerator);
			}

			$this->listviewHeaders = $this->listviewRecords = NULL;
		}
	}

	public function getTitle($prefix='') {
		$this->initListViewController();

		$db = PearDatabase::getInstance();

		$suffix = '';
		$customviewrs = $db->pquery('SELECT viewname FROM mycrm_customview WHERE cvid=?', array($this->widgetModel->get('filterid')));
		if ($db->num_rows($customviewrs)) {
			$customview = $db->fetch_array($customviewrs);
			$suffix = ' - ' . $customview['viewname'];
		}
		return $prefix . vtranslate($this->getTargetModuleModel()->label, $this->getTargetModule()). $suffix;
	}

	public function getHeaders() {
		$this->initListViewController();

		if (!$this->listviewHeaders) {
			$headerFieldModels = array();
			foreach ($this->listviewController->getListViewHeaderFields() as $fieldName => $webserviceField) {
				$fieldObj = Mycrm_Field::getInstance($webserviceField->getFieldId());
				$headerFieldModels[$fieldName] = Mycrm_Field_Model::getInstanceFromFieldObject($fieldObj);
			}
			$this->listviewHeaders = $headerFieldModels;
		}

		return $this->listviewHeaders;
	}

	public function getHeaderCount() {
		return count($this->getHeaders());
	}

	public function getRecordLimit() {
		return 10;
	}

	public function getRecords() {

		$this->initListViewController();

		if (!$this->listviewRecords) {
			$db = PearDatabase::getInstance();

			$query = $this->queryGenerator->getQuery();
			$query .= ' ORDER BY mycrm_crmentity.modifiedtime DESC ';
			$query .= ' LIMIT 0,' . $this->getRecordLimit();
			$query = str_replace(" FROM ", ",mycrm_crmentity.crmid as id FROM ", $query);

			$result = $db->pquery($query, array());

			$targetModuleName = $this->getTargetModule();
			$targetModuleFocus= CRMEntity::getInstance($targetModuleName);

			$entries = $this->listviewController->getListViewRecords($targetModuleFocus,$targetModuleName,$result);

			$this->listviewRecords = array();
			$index = 0;
			foreach ($entries as $id => $record) {
				$rawData = $db->query_result_rowdata($result, $index++);
				$record['id'] = $id;
				$this->listviewRecords[$id] = $this->getTargetModuleModel()->getRecordFromArray($record, $rawData);
			}
		}

		return $this->listviewRecords;
	}
}