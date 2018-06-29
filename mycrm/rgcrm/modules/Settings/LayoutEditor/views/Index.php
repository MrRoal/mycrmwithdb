<?php

/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_LayoutEditor_Index_View extends Settings_Mycrm_Index_View {

	function __construct() {
		$this->exposeMethod('showFieldLayout');
		$this->exposeMethod('showRelatedListLayout');
	}

	public function process(Mycrm_Request $request) {
		$mode = $request->getMode();
		if($this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
		}else {
			//by default show field layout
			$this->showFieldLayout($request);
		}
	}

	public function showFieldLayout(Mycrm_Request $request) {
		$sourceModule = $request->get('sourceModule');
		$supportedModulesList = Settings_LayoutEditor_Module_Model::getSupportedModules();

		if(empty($sourceModule)) {
			//To get the first element
			$sourceModule = reset($supportedModulesList);
		}
		$moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($sourceModule);
		$fieldModels = $moduleModel->getFields();
		$blockModels = $moduleModel->getBlocks();


		$blockIdFieldMap = array();
		$inactiveFields = array();
		foreach($fieldModels as $fieldModel) {
			$blockIdFieldMap[$fieldModel->getBlockId()][$fieldModel->getName()] = $fieldModel;
			if(!$fieldModel->isActiveField()) {
				$inactiveFields[$fieldModel->getBlockId()][$fieldModel->getId()] = vtranslate($fieldModel->get('label'), $sourceModule);
			}
		}

		foreach($blockModels as $blockLabel => $blockModel) {
			$fieldModelList = $blockIdFieldMap[$blockModel->get('id')];
			$blockModel->setFields($fieldModelList);
		}

		$qualifiedModule = $request->getModule(false);

		$viewer = $this->getViewer($request);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('SUPPORTED_MODULES',$supportedModulesList);
		$viewer->assign('SELECTED_MODULE_MODEL', $moduleModel);
		$viewer->assign('BLOCKS',$blockModels);
		$viewer->assign('ADD_SUPPORTED_FIELD_TYPES', $moduleModel->getAddSupportedFieldTypes());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->assign('IN_ACTIVE_FIELDS', $inactiveFields);
		$viewer->view('Index.tpl',$qualifiedModule);
	}

	public function showRelatedListLayout(Mycrm_Request $request) {
		$sourceModule = $request->get('sourceModule');
		$supportedModulesList = Settings_LayoutEditor_Module_Model::getSupportedModules();

		if(empty($sourceModule)) {
			//To get the first element
			$moduleInstance = reset($supportedModulesList);
			$sourceModule = $moduleInstance->getName();
		}
		$moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($sourceModule);
		$relatedModuleModels = $moduleModel->getRelations();

		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);

		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('RELATED_MODULES',$relatedModuleModels);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->view('RelatedList.tpl',$qualifiedModule);
	}

}