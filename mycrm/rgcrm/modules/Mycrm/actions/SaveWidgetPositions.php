<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_SaveWidgetPositions_Action extends Mycrm_IndexAjax_View {

	public function process(Mycrm_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		
		$positionsMap = $request->get('positionsmap');
		
		if ($positionsMap) {
			foreach ($positionsMap as $id => $position) {
				list ($linkid, $widgetid) = explode('-', $id);
				if ($widgetid) {
					Mycrm_Widget_Model::updateWidgetPosition($position, NULL, $widgetid, $currentUser->getId());
				} else {
					Mycrm_Widget_Model::updateWidgetPosition($position, $linkid, NULL, $currentUser->getId());
				}
			}
		}
		
		$response = new Mycrm_Response();
		$response->setResult(array('Save' => 'OK'));
		$response->emit();
	}
}
