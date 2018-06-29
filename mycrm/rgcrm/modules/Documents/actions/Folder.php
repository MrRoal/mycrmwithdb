<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Documents_Folder_Action extends Mycrm_Action_Controller {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('delete');
	}

	public function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();

		if(!Users_Privileges_Model::isPermitted($moduleName, 'DetailView')) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $moduleName));
		}
	}

	public function process(Mycrm_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
		}
	}

	public function save($request) {
		$moduleName = $request->getModule();
		$folderName = $request->get('foldername');
		$folderDesc = $request->get('folderdesc');
		$result = array();

		if (!empty ($folderName)) {
			$folderModel = Documents_Folder_Model::getInstance();
			$folderModel->set('foldername', $folderName);
			$folderModel->set('description', $folderDesc);

			if ($folderModel->checkDuplicate()) {
				throw new AppException(vtranslate('LBL_FOLDER_EXISTS', $moduleName));
				exit;
			}

			$folderModel->save();
			$result = array('success'=>true, 'message'=>vtranslate('LBL_FOLDER_SAVED', $moduleName), 'info'=>$folderModel->getInfoArray());

			$response = new Mycrm_Response();
			$response->setResult($result);
			$response->emit();
		}
	}


	public function delete($request) {
		$moduleName = $request->getModule();
		$folderId = $request->get('folderid');
		$result = array();

		if (!empty ($folderId)) {
			$folderModel = Documents_Folder_Model::getInstanceById($folderId);
			if (!($folderModel->hasDocuments())) {
				$folderModel->delete();
				$result = array('success'=>true, 'message'=>vtranslate('LBL_FOLDER_DELETED', $moduleName));
			} else {
				$result = array('success'=>false, 'message'=>vtranslate('LBL_FOLDER_HAS_DOCUMENTS', $moduleName));
			}
		}

		$response = new Mycrm_Response();
		$response->setResult($result);
		$response->emit();
	}
        
        public function validateRequest(Mycrm_Request $request) { 
            $request->validateWriteAccess(); 
        } 
}
