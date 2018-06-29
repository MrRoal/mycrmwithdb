<?php

/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Users_ChangePassword_View extends Mycrm_Basic_View {
    
    public function preProcess (Mycrm_Request $request, $display=true) {
		parent::preProcess($request, false);
        
		$viewer = $this->getViewer($request);

		$moduleName = $request->getModule();
		if(!empty($moduleName)) {
			$moduleModel = Mycrm_Module_Model::getInstance($moduleName);
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$userPrivilegesModel = Users_Privileges_Model::getInstanceById($currentUser->getId());
			$viewer->assign('MODULE', $moduleName);

			//Dont check for module permissions since for non admin users module permission will not be there 

			$linkParams = array('MODULE'=>$moduleName, 'ACTION'=>$request->get('view'));
			$linkModels = $moduleModel->getSideBarLinks($linkParams);

			$viewer->assign('QUICK_LINKS', $linkModels);
		}
		
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('CURRENT_VIEW', $request->get('view'));
		if($display) {
			$this->preProcessDisplay($request);
		}
	}
    
    
    protected function preProcessDisplay(Mycrm_Request $request) {}
	
	public function process(Mycrm_Request $request) {
		$viewer = $this->getViewer($request);
		$viewer->assign('UI5_URL', $this->getUI5EmbedURL($request));
		$viewer->view('UI5EmbedView.tpl');
	}
    
}