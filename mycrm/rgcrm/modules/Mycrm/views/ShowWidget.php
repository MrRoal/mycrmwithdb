<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_ShowWidget_View extends Mycrm_IndexAjax_View {

	function checkPermission(Mycrm_Request $request) {
		return true;
	}

	function process(Mycrm_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$moduleName = $request->getModule();
		$componentName = $request->get('name');
		$linkId = $request->get('linkid');
		if(!empty($componentName)) {
			$className = Mycrm_Loader::getComponentClassName('Dashboard', $componentName, $moduleName);
			if(!empty($className)) {
				$widget = NULL;
				if(!empty($linkId)) {
					$widget = new Mycrm_Widget_Model();
					$widget->set('linkid', $linkId);
					$widget->set('userid', $currentUser->getId());
					$widget->set('filterid', $request->get('filterid', NULL));
					if ($request->has('data')) {
						$widget->set('data', $request->get('data'));
					}
					$widget->add();
				}
				$classInstance = new $className();
				$classInstance->process($request, $widget);
				return;
			}
	}

		$response = new Mycrm_Response();
		$response->setResult(array('success'=>false,'message'=>  vtranslate('NO_DATA')));
		$response->emit();
	}
        
        public function validateRequest(Mycrm_Request $request) { 
            $request->validateWriteAccess(); 
        } 
}