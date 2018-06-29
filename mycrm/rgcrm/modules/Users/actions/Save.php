<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Users_Save_Action extends Mycrm_Save_Action {
	
	public function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$recordModel = Mycrm_Record_Model::getInstanceById($record, $moduleName);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		// Check for operation access.
		$allowed = Users_Privileges_Model::isPermitted($moduleName, 'Save', $record);
		
		if ($allowed) {
			// Deny access if not administrator or account-owner or self
			if(!$currentUserModel->isAdminUser()) {
				if (empty($record)) {
					$allowed = false;
				} else if ($currentUserModel->get('id') != $recordModel->getId()) {
					$allowed = false;
				}
			}
		}

		if(!$allowed) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Mycrm_Request $request
	 * @return Mycrm_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
                $currentUserModel = Users_Record_Model::getCurrentUserModel();
		if(!empty($recordId)) {
			$recordModel = Mycrm_Record_Model::getInstanceById($recordId, $moduleName);
			$modelData = $recordModel->getData();
			$recordModel->set('id', $recordId);
			$sharedType = $request->get('sharedtype');
			if(!empty($sharedType))
				$recordModel->set('calendarsharedtype', $request->get('sharedtype'));
			$recordModel->set('mode', 'edit');
		} else {
			$recordModel = Mycrm_Record_Model::getCleanInstance($moduleName);
			$modelData = $recordModel->getData();
			$recordModel->set('mode', '');
		}

		foreach ($modelData as $fieldName => $value) {
			$requestFieldExists = $request->has($fieldName);
			if(!$requestFieldExists){
				continue;
			}
			$fieldValue = $request->get($fieldName, null);

			if ($fieldName === 'is_admin') {
                            if (!$currentUserModel->isAdminUser() && (!$fieldValue)) {
				$fieldValue = 'off';
                            } else if ($currentUserModel->isAdminUser() && ($fieldValue || $fieldValue === 'on')) {
                                $fieldValue = 'on';
                                $recordModel->set('is_owner', 1);
                            } else {
                                $fieldValue = 'off';
                                $recordModel->set('is_owner', 0);
                            }
                        }
			if($fieldValue !== null) {
				if(!is_array($fieldValue)) {
					$fieldValue = trim($fieldValue);
				}
				$recordModel->set($fieldName, $fieldValue);
			}
		}
		$homePageComponents = $recordModel->getHomePageComponents();
		$selectedHomePageComponents = $request->get('homepage_components', array());
		foreach ($homePageComponents as $key => $value) {
			if(in_array($key, $selectedHomePageComponents)) {
				$request->setGlobal($key, $key);
			} else {
				$request->setGlobal($key, '');
			}
		}

		// Tag cloud save
		$tagCloud = $request->get('tagcloudview');
		if($tagCloud == "on") {
			$recordModel->set('tagcloud', 0);
		} else {
			$recordModel->set('tagcloud', 1);
		}
		return $recordModel;
	}

	public function process(Mycrm_Request $request) {
		$result = Mycrm_Util_Helper::transformUploadedFiles($_FILES, true);
		$_FILES = $result['imagename'];

		$recordModel = $this->saveRecord($request);

		if ($request->get('relationOperation')) {
			$parentRecordModel = Mycrm_Record_Model::getInstanceById($request->get('sourceRecord'), $request->get('sourceModule'));
			$loadUrl = $parentRecordModel->getDetailViewUrl();
		} else if ($request->get('isPreference')) {
			$loadUrl =  $recordModel->getPreferenceDetailViewUrl();
		} else {
			$loadUrl = $recordModel->getDetailViewUrl();
		}

		header("Location: $loadUrl");
	}
}
