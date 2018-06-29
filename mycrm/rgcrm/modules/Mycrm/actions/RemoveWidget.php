<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_RemoveWidget_Action extends Mycrm_IndexAjax_View {

	public function process(Mycrm_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$linkId = $request->get('linkid');
		$response = new Mycrm_Response();
		
		if ($request->has('widgetid')) {
			$widget = Mycrm_Widget_Model::getInstanceWithWidgetId($request->get('widgetid'), $currentUser->getId());
		} else {
			$widget = Mycrm_Widget_Model::getInstance($linkId, $currentUser->getId());
		}

		if (!$widget->isDefault()) {
			$widget->remove();
			$response->setResult(array('linkid' => $linkId, 'name' => $widget->getName(), 'url' => $widget->getUrl(), 'title' => vtranslate($widget->getTitle(), $request->getModule())));
		} else {
			$response->setError(vtranslate('LBL_CAN_NOT_REMOVE_DEFAULT_WIDGET', $moduleName));
		}
		$response->emit();
	}
        
        public function validateRequest(Mycrm_Request $request) { 
            $request->validateWriteAccess(); 
        } 
}
