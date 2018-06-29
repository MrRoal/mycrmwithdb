<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/
vimport('~~/libraries/freetag/freetag.class.php');

class Mycrm_Tag_Model extends Mycrm_Base_Model {

	private $_freetag = false;

	static $TAG_FETCH_LIMIT = 100;

	function __construct() {
		$this->_freetag = new freetag();
	}

	/**
	 * Function saves a tag to database
	 */
	public function save() {
		$this->_freetag->tag_object($this->get('userid'), $this->get('record'), $this->get('tagname'), $this->get('module'));
	}

	/**
	 * Function deletes a tag from database
	 */
	public function delete() {
		$db = PearDatabase::getInstance();
		$db->pquery('DELETE FROM mycrm_freetagged_objects WHERE tag_id = ? AND object_id = ?',
				array($this->get('tag_id'), $this->get('record')));
	}

	/**
	 * Function returns the tags
	 * @param type $userId
	 * @param type $module
	 * @param type $record
	 * @return type
	 */
	public static function getAll($userId = NULL, $module = "", $record = NULL) {
		$tag = new self();
		return $tag->_freetag->get_tag_cloud_tags(self::$TAG_FETCH_LIMIT, $userId, $module, $record);
	}
	
	public static function getTaggedRecords($tagId) {
		$recordModels = array();
		if(!empty($tagId)) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery("SELECT mycrm_crmentity.* FROM mycrm_freetags 
				INNER JOIN mycrm_freetagged_objects ON mycrm_freetags.id = mycrm_freetagged_objects.tag_id 
				INNER JOIN mycrm_crmentity ON mycrm_freetagged_objects.object_id=mycrm_crmentity.crmid 
					AND mycrm_crmentity.deleted=0 
				WHERE tag_id = ?", array($tagId));
			$rows = $db->num_rows($result);
			for($i=0; $i<$rows; $i++) {
				$row = $db->query_result_rowdata($result, $i);
				$recordModel = Mycrm_Record_Model::getCleanInstance($row['setype']);
				$recordModel->setData($row);
				$recordModel->setId($row['crmid']);
				$recordModels[$row['setype']][] = $recordModel;
			}
		}
		return $recordModels;
	}
}

?>
