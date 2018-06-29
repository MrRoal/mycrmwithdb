<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

include_once dirname(__FILE__) . '/Block.php';

class Mobile_UI_ModuleRecordModel {
	private $_id;
	private $_blocks = array();
	
	function initData($recordData) {
		$this->data = $recordData;
		if (isset($recordData['blocks'])) {
			$blocks = Mobile_UI_BlockModel::buildModelsFromResponse($recordData['blocks']);
			foreach($blocks as $block) {
				$this->_blocks[$block->label()] = $block;
			}
		}
	}
	
	function setId($newId) {
		$this->_id = $newId;
	}
	
	function id() {
		return $this->data['id'];
	}
	
	function label() {
		return $this->data['label'];
	}
	
	function blocks() {
		return $this->_blocks;
	}
	
	static function buildModelFromResponse($recordData) {
		$instance = new self();
		$instance->initData($recordData);
		return $instance;
	}
	
	static function buildModelsFromResponse($records) {
		$instances = array();
		foreach($records as $recordData) {
			$instance = new self();
			$instance->initData($recordData);
			$instances[] = $instance;
		}
		return $instances;
	}
	
}