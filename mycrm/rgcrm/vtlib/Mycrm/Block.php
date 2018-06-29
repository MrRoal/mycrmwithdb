<?php
/*+*******************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ******************************************************************************/
include_once('vtlib/Mycrm/Utils.php');
require_once 'includes/runtime/Cache.php';

/**
 * Provides API to work with mycrm CRM Module Blocks
 * @package vtlib
 */
class Mycrm_Block {
	/** ID of this block instance */
	var $id;
	/** Label for this block instance */
	var $label;

	var $sequence;
	var $showtitle = 0;
	var $visible = 0;
	var $increateview = 0;
	var $ineditview = 0;
	var $indetailview = 0;

    var $display_status=1;
	var $iscustom=0;

	var $module;

	/**
	 * Constructor
	 */
	function __construct() {
	}

	/**
	 * Get unquie id for this instance
	 * @access private
	 */
	function __getUniqueId() {
		global $adb;

		/** Sequence table was added from 5.1.0 */
		$maxblockid = $adb->getUniqueID('mycrm_blocks');
		return $maxblockid;
	}

	/**
	 * Get next sequence value to use for this block instance
	 * @access private
	 */
	function __getNextSequence() {
		global $adb;
		$result = $adb->pquery("SELECT MAX(sequence) as max_sequence from mycrm_blocks where tabid = ?", Array($this->module->id));
		$maxseq = 0;
		if($adb->num_rows($result)) {
			$maxseq = $adb->query_result($result, 0, 'max_sequence');
		}
		return ++$maxseq;
	}

	/**
	 * Initialize this block instance
	 * @param Array Map of column name and value
	 * @param Mycrm_Module Instance of module to which this block is associated
	 * @access private
	 */
	function initialize($valuemap, $moduleInstance=false) {
		$this->id = isset($valuemap['blockid']) ? $valuemap['blockid'] : null;
		$this->label= isset($valuemap['blocklabel']) ? $valuemap['blocklabel'] : null;
        $this->display_status = isset($valuemap['display_status']) ? $valuemap['display_status'] : null;
		$this->sequence = isset($valuemap['sequence']) ? $valuemap['sequence'] : null;
        $this->iscustom = isset($valuemap['iscustom']) ? $valuemap['iscustom'] : null;
        $tabid = isset($valuemap['tabid']) ? $valuemap['tabid'] : null;
		$this->module= $moduleInstance ? $moduleInstance : Mycrm_Module::getInstance($tabid);
	}

	/**
	 * Create mycrm CRM block
	 * @access private
	 */
	function __create($moduleInstance) {
		global $adb;

		$this->module = $moduleInstance;

		$this->id = $this->__getUniqueId();
		if(!$this->sequence) $this->sequence = $this->__getNextSequence();

		$adb->pquery("INSERT INTO mycrm_blocks(blockid,tabid,blocklabel,sequence,show_title,visible,create_view,edit_view,detail_view,iscustom)
			VALUES(?,?,?,?,?,?,?,?,?,?)", Array($this->id, $this->module->id, $this->label,$this->sequence,
			$this->showtitle, $this->visible,$this->increateview, $this->ineditview, $this->indetailview, $this->iscustom));
		self::log("Creating Block $this->label ... DONE");
		self::log("Module language entry for $this->label ... CHECK");
	}

	/**
	 * Update mycrm CRM block
	 * @access private
	 * @internal TODO
	 */
	function __update() {
		self::log("Updating Block $this->label ... DONE");
	}

	/**
	 * Delete this instance
	 * @access private
	 */
	function __delete() {
		global $adb;
		self::log("Deleting Block $this->label ... ", false);
		$adb->pquery("DELETE FROM mycrm_blocks WHERE blockid=?", Array($this->id));
		self::log("DONE");
	}

	/**
	 * Save this block instance
	 * @param Mycrm_Module Instance of the module to which this block is associated
	 */
	function save($moduleInstance=false) {
		if($this->id) $this->__update();
		else $this->__create($moduleInstance);
		return $this->id;
	}

	/**
	 * Delete block instance
	 * @param Boolean True to delete associated fields, False to avoid it
	 */
	function delete($recursive=true) {
		if($recursive) {
			$fields = Mycrm_Field::getAllForBlock($this);
			foreach($fields as $fieldInstance) $fieldInstance->delete($recursive);
		}
		$this->__delete();
	}

	/**
	 * Add field to this block
	 * @param Mycrm_Field Instance of field to add to this block.
	 * @return Reference to this block instance
	 */
	function addField($fieldInstance) {
		$fieldInstance->save($this);
		return $this;
	}

	/**
	 * Helper function to log messages
	 * @param String Message to log
	 * @param Boolean true appends linebreak, false to avoid it
	 * @access private
	 */
	static function log($message, $delim=true) {
		Mycrm_Utils::Log($message, $delim);
	}

	/**
	 * Get instance of block
	 * @param mixed block id or block label
	 * @param Mycrm_Module Instance of the module if block label is passed
	 */
	static function getInstance($value, $moduleInstance=false) {
		global $adb;
		$cache = Mycrm_Cache::getInstance();
		if($moduleInstance && $cache->getBlockInstance($value, $moduleInstance->id)){
			return $cache->getBlockInstance($value, $moduleInstance->id);
		} else {
			$instance = false;
			$query = false;
			$queryParams = false;
			if(Mycrm_Utils::isNumber($value)) {
				$query = "SELECT * FROM mycrm_blocks WHERE blockid=?";
				$queryParams = Array($value);
			} else {
				$query = "SELECT * FROM mycrm_blocks WHERE blocklabel=? AND tabid=?";
				$queryParams = Array($value, $moduleInstance->id);
			}
			$result = $adb->pquery($query, $queryParams);
			if($adb->num_rows($result)) {
				$instance = new self();
				$instance->initialize($adb->fetch_array($result), $moduleInstance);
			}
			$cache->setBlockInstance($value,$instance->module->id, $instance);
			return $instance;
		}
	}

	/**
	 * Get all block instances associated with the module
	 * @param Mycrm_Module Instance of the module
	 */
	static function getAllForModule($moduleInstance) {
		global $adb;
		$instances = false;

		$query = "SELECT * FROM mycrm_blocks WHERE tabid=? ORDER BY sequence";
		$queryParams = Array($moduleInstance->id);

		$result = $adb->pquery($query, $queryParams);
		for($index = 0; $index < $adb->num_rows($result); ++$index) {
			$instance = new self();
			$instance->initialize($adb->fetch_array($result), $moduleInstance);
			$instances[] = $instance;
		}
		return $instances;
	}

	/**
	 * Delete all blocks associated with module
	 * @param Mycrm_Module Instnace of module to use
	 * @param Boolean true to delete associated fields, false otherwise
	 * @access private
	 */
	static function deleteForModule($moduleInstance, $recursive=true) {
		global $adb;
		if($recursive) Mycrm_Field::deleteForModule($moduleInstance);
		$adb->pquery("DELETE FROM mycrm_blocks WHERE tabid=?", Array($moduleInstance->id));
		self::log("Deleting blocks for module ... DONE");
	}
}
?>
