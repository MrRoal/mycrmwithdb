<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_ModuleManager_ModuleImport_View extends Settings_Mycrm_Index_View {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('importUserModuleStep1');
		$this->exposeMethod('importUserModuleStep2');
		$this->exposeMethod('importUserModuleStep3');
		$this->exposeMethod('updateUserModuleStep3');
	}

	public function process(Mycrm_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		
		$EXTENSIONS = Settings_ModuleManager_Extension_Model::getAll();
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('EXTENSIONS', $EXTENSIONS);
		$viewer->assign('EXTENSIONS_AVAILABLE', (count($EXTENSIONS) > 0)? true :false);
		$viewer->view('Step1.tpl', $qualifiedModuleName);
	}


    /**
	 * Function to get the list of Script models to be included
	 * @param Mycrm_Request $request
	 * @return <Array> - List of Mycrm_JsScript_Model instances
	 */
	function getHeaderScripts(Mycrm_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.ModuleImport"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	function importUserModuleStep1(Mycrm_Request $request){
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('ImportUserModuleStep1.tpl', $qualifiedModuleName);
	}
	
	public function importUserModuleStep2(Mycrm_Request $request){
		$viewer = $this->getViewer($request);
		$uploadDir = Settings_ModuleManager_Extension_Model::getUploadDirectory();
		$qualifiedModuleName = $request->getModule(false);

		$uploadFile = 'usermodule_'. time() . '.zip';
		$uploadFileName = "$uploadDir/$uploadFile";
		checkFileAccess($uploadDir);
		if(!move_uploaded_file($_FILES['moduleZip']['tmp_name'], $uploadFileName)) {
			$viewer->assign('MODULEIMPORT_FAILED', true);
		}else{
			$package = new Mycrm_Package();
			$importModuleName = $package->getModuleNameFromZip($uploadFileName);
			$importModuleDepVtVersion = $package->getDependentMycrmVersion();
			
			if($importModuleName == null ) {
				$viewer->assign('MODULEIMPORT_FAILED', true);
				$viewer->assign("MODULEIMPORT_FILE_INVALID", true);
				checkFileAccessForDeletion($uploadFileName);
				unlink($uploadFileName);
			} else {
				
				// We need these information to push for Update if module is detected to be present.
				$moduleLicence = vtlib_purify($package->getLicense());
				
				$viewer->assign("MODULEIMPORT_FILE", $uploadFile);
				$viewer->assign("MODULEIMPORT_TYPE", $package->type());
				$viewer->assign("MODULEIMPORT_NAME", $importModuleName);
				$viewer->assign("MODULEIMPORT_DEP_VTVERSION", $importModuleDepVtVersion);
				$viewer->assign("MODULEIMPORT_LICENSE", $moduleLicence);
				
				if(!$package->isLanguageType() && !$package->isModuleBundle()) {
					$moduleInstance = Mycrm_Module::getInstance($importModuleName);
					$moduleimport_exists = ($moduleInstance)? "true" : "false";
					$moduleimport_dir_name = "modules/$importModuleName";
					$moduleimport_dir_exists = (is_dir($moduleimport_dir_name)? "true" : "false");
					$viewer->assign("MODULEIMPORT_EXISTS", $moduleimport_exists);
					$viewer->assign("MODULEIMPORT_DIR", $moduleimport_dir_name);
					$viewer->assign("MODULEIMPORT_DIR_EXISTS", $moduleimport_dir_exists);
				}
			}
		}
		$viewer->view('ImportUserModuleStep2.tpl', $qualifiedModuleName);
	}

	public function validateRequest(Mycrm_Request $request) { 
            $request->validateReadAccess(); 
        }
}
