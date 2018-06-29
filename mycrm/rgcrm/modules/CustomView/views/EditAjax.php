<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

Class CustomView_EditAjax_View extends Mycrm_IndexAjax_View {

	public function process(Mycrm_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->get('source_module');
		$module = $request->getModule();
		$record = $request->get('record');
                
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);
		$recordStructureInstance = Mycrm_RecordStructure_Model::getInstanceForModule($moduleModel);

		if(!empty($record)) {
			$customViewModel = CustomView_Record_Model::getInstanceById($record);
			$viewer->assign('MODE', 'edit');
		} else {
			$customViewModel = new CustomView_Record_Model();
            $customViewModel->setModule($moduleName);
			$viewer->assign('MODE', '');
		}

		$viewer->assign('ADVANCE_CRITERIA', $customViewModel->transformToNewAdvancedFilter());
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('DATE_FILTERS', Mycrm_Field_Model::getDateFilterTypes());
		
		if($moduleName == 'Calendar'){
			$advanceFilterOpsByFieldType = Calendar_Field_Model::getAdvancedFilterOpsByFieldType();
		} else{
			$advanceFilterOpsByFieldType = Mycrm_Field_Model::getAdvancedFilterOpsByFieldType();
		}
		$viewer->assign('ADVANCED_FILTER_OPTIONS', Mycrm_Field_Model::getAdvancedFilterOptions());
		$viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', $advanceFilterOpsByFieldType);
        $dateFilters = Mycrm_Field_Model::getDateFilterTypes();
        foreach($dateFilters as $comparatorKey => $comparatorInfo) {
            $comparatorInfo['startdate'] = DateTimeField::convertToUserFormat($comparatorInfo['startdate']);
            $comparatorInfo['enddate'] = DateTimeField::convertToUserFormat($comparatorInfo['enddate']);
            $comparatorInfo['label'] = vtranslate($comparatorInfo['label'],$module);
            $dateFilters[$comparatorKey] = $comparatorInfo;
        }
        $viewer->assign('DATE_FILTERS', $dateFilters);
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
        $recordStructure = $recordStructureInstance->getStructure();
        // for Inventory module we should now allow item details block
        if(in_array($moduleName, getInventoryModules())){
            $itemsBlock = "LBL_ITEM_DETAILS";
            unset($recordStructure[$itemsBlock]);
        }
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		// Added to show event module custom fields
		if($moduleName == 'Calendar'){
        	$relatedModuleName = 'Events';
            $relatedModuleModel = Mycrm_Module_Model::getInstance($relatedModuleName);
            $relatedRecordStructureInstance = Mycrm_RecordStructure_Model::getInstanceForModule($relatedModuleModel);
            $eventBlocksFields = $relatedRecordStructureInstance->getStructure();
            $viewer->assign('EVENT_RECORD_STRUCTURE_MODEL', $relatedRecordStructureInstance);
            $viewer->assign('EVENT_RECORD_STRUCTURE', $eventBlocksFields);
        }
		$viewer->assign('CUSTOMVIEW_MODEL', $customViewModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $module);
		$viewer->assign('SOURCE_MODULE',$moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CV_PRIVATE_VALUE', CustomView_Record_Model::CV_STATUS_PRIVATE);
		$viewer->assign('CV_PENDING_VALUE', CustomView_Record_Model::CV_STATUS_PENDING);
        $viewer->assign('CV_PUBLIC_VALUE', CustomView_Record_Model::CV_STATUS_PUBLIC);
		$viewer->assign('MODULE_MODEL',$moduleModel);

		echo $viewer->view('EditView.tpl', $module, true);
	}
}