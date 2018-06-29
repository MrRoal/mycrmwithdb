<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/
require_once('vtlib/Mycrm/Layout.php'); 

class Settings_ModuleManager_Basic_Action extends Settings_Mycrm_IndexAjax_View {
    function __construct() {
		parent::__construct();
		$this->exposeMethod('updateModuleStatus');
                $this->exposeMethod('importUserModuleStep3');
                $this->exposeMethod('updateUserModuleStep3');
	}
    
    function process(Mycrm_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}
    
    public function updateModuleStatus(Mycrm_Request $request) {
        $moduleName = $request->get('forModule');
        $updateStatus = $request->get('updateStatus');
        
        $moduleManagerModel = new Settings_ModuleManager_Module_Model();
        
        if($updateStatus == 'true') {
            $moduleManagerModel->enableModule($moduleName);
        }else{
            $moduleManagerModel->disableModule($moduleName);
        }
        
        $response = new Mycrm_Response();
		$response->emit();
    }
    
    public function importUserModuleStep3(Mycrm_Request $request) {
        $importModuleName = $request->get('module_import_name');
        $uploadFile = $request->get('module_import_file');
        $uploadDir = Settings_ModuleManager_Extension_Model::getUploadDirectory();
        $uploadFileName = "$uploadDir/$uploadFile";
        checkFileAccess($uploadFileName);

        $importType = $request->get('module_import_type');
        if(strtolower($importType) == 'language') {
                $package = new Mycrm_Language();
        }else  if(strtolower($importType) == 'layout') {
                 $package = new Mycrm_Layout();
                 }
        else {
                $package = new Mycrm_Package();
        }

        $package->import($uploadFileName);
        checkFileAccessForDeletion($uploadFileName);
        unlink($uploadFileName);
        
        $result = array('success'=>true, 'importModuleName'=> $importModuleName);
        $response = new Mycrm_Response();
        $response->setResult($result);
        $response->emit();
    }
    
    public function updateUserModuleStep3(Mycrm_Request $request){
        $importModuleName = $request->get('module_import_name');
        $uploadFile = $request->get('module_import_file');
        $uploadDir = Settings_ModuleManager_Extension_Model::getUploadDirectory();
        $uploadFileName = "$uploadDir/$uploadFile";
        checkFileAccess($uploadFileName);

        $importType = $request->get('module_import_type');
        if(strtolower($importType) == 'language') {
                $package = new Mycrm_Language();
        } else if(strtolower($importType) == 'layout') { 
            $package = new Mycrm_Layout(); 
        } else { 
                $package = new Mycrm_Package();
        }

        if (strtolower($importType) == 'language' || strtolower($importType) == 'layout' ) {
                $package->import($uploadFileName);
        } else {
                $package->update(Mycrm_Module::getInstance($importModuleName), $uploadFileName);
        }

        checkFileAccessForDeletion($uploadFileName);
        unlink($uploadFileName);
        
        $result = array('success'=>true, 'importModuleName'=> $importModuleName);
        $response = new Mycrm_Response();
        $response->setResult($result);
        $response->emit();
    }

	 public function validateRequest(Mycrm_Request $request) { 
        $request->validateWriteAccess(); 
    } 
}
