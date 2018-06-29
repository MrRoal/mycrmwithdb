<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Import_Lock_Action extends Mycrm_Action_Controller {

	public function  __construct() {
	}

	public function process(Mycrm_Request $request) {
		return false;
	}

	public static function lock($importId, $module, $user) {
		$adb = PearDatabase::getInstance();

		if(!Mycrm_Utils::CheckTable('mycrm_import_locks')) {
			Mycrm_Utils::CreateTable(
				'mycrm_import_locks',
				"(mycrm_import_lock_id INT NOT NULL PRIMARY KEY,
				userid INT NOT NULL,
				tabid INT NOT NULL,
				importid INT NOT NULL,
				locked_since DATETIME)",
				true);
		}

		$adb->pquery('INSERT INTO mycrm_import_locks VALUES(?,?,?,?,?)',
						array($adb->getUniqueID('mycrm_import_locks'), $user->id, getTabid($module), $importId, date('Y-m-d H:i:s')));
	}

	public static function unLock($user, $module=false) {
		$adb = PearDatabase::getInstance();
		if(Mycrm_Utils::CheckTable('mycrm_import_locks')) {
			$query = 'DELETE FROM mycrm_import_locks WHERE userid=?';
			$params = array(method_exists($user, 'get')?$user->get('id'):$user->id);
			if($module != false) {
				$query .= ' AND tabid=?';
				array_push($params, getTabid($module));
			}
			$adb->pquery($query, $params);
		}
	}

	public static function isLockedForModule($module) {
		$adb = PearDatabase::getInstance();

		if(Mycrm_Utils::CheckTable('mycrm_import_locks')) {
			$lockResult = $adb->pquery('SELECT * FROM mycrm_import_locks WHERE tabid=?',array(getTabid($module)));

			if($lockResult && $adb->num_rows($lockResult) > 0) {
				$lockInfo = $adb->query_result_rowdata($lockResult, 0);
				return $lockInfo;
			}
		}

		return null;
	}
}