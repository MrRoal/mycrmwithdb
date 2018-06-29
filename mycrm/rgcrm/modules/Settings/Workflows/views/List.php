<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Workflows_List_View extends Settings_Mycrm_List_View {

	public function preProcess(Mycrm_Request $request, $display=true) {
		$viewer = $this->getViewer($request);
		$viewer->assign('SUPPORTED_MODULE_MODELS', Settings_Workflows_Module_Model::getSupportedModules());
        $viewer->assign('CRON_RECORD_MODEL', Settings_CronTasks_Record_Model::getInstanceByName('Workflow'));
		parent::preProcess($request, $display);
	}
}
