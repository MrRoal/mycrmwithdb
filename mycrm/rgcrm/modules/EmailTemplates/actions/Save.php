<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class EmailTemplates_Save_Action extends Mycrm_Save_Action {
	
	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$recordModel = new EmailTemplates_Record_Model();
		$recordModel->setModule($moduleName);
		
		if(!empty($record)) {
			$recordModel->setId($record);
		}

		$recordModel->set('templatename', $request->get('templatename'));
		$recordModel->set('description', $request->get('description'));
		$recordModel->set('subject', $request->get('subject'));
		$recordModel->set('body', $request->get('templatecontent'));
		
		$recordModel->save();

		$loadUrl = $recordModel->getDetailViewUrl();
		header("Location: $loadUrl");
	}
    
}