<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_TagCloud_Action extends Mycrm_Action_Controller {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('delete');
	}

	function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);

		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if(!$permission) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
		return true;
	}

	public function process(Mycrm_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Function saves a tag for a record
	 * @param Mycrm_Request $request
	 */
	public function save(Mycrm_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$tagModel = new Mycrm_Tag_Model();
		$tagModel->set('userid', $currentUser->id);
		$tagModel->set('record', $request->get('record'));
		$tagModel->set('tagname', decode_html($request->get('tagname')));
		$tagModel->set('module', $request->getModule());
		$tagModel->save();

		$taggedInfo = Mycrm_Tag_Model::getAll($currentUser->id, $request->getModule(), $request->get('record'));
		$response = new Mycrm_Response();
		$response->setResult($taggedInfo);
		$response->emit($taggedInfo);
	}

	/**
	 * Function deleted a tag
	 * @param Mycrm_Request $request
	 */
	public function delete(Mycrm_Request $request) {
		$tagModel = new Mycrm_Tag_Model();
		$tagModel->set('record', $request->get('record'));
		$tagModel->set('tag_id', $request->get('tag_id'));
		$tagModel->delete();
	}

	/**
	 * Function returns list of tage for the record
	 * @param Mycrm_Request $request
	 */
	public function getTags(Mycrm_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$record = $request->get('record');
		$module = $request->getModule();
		$tags = Mycrm_Tag_Model::getAll($currentUser->id, $module, $record);

		$response = new Mycrm_Response();
		$response->emit($tags);
	}
}
