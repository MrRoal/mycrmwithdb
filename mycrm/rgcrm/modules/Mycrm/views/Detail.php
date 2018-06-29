<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_Detail_View extends Mycrm_Index_View {
	protected $record = false;

	function __construct() {
		parent::__construct();
		$this->exposeMethod('showDetailViewByMode');
		$this->exposeMethod('showModuleDetailView');
		$this->exposeMethod('showModuleSummaryView');
		$this->exposeMethod('showModuleBasicView');
		$this->exposeMethod('showRecentActivities');
		$this->exposeMethod('showRecentComments');
		$this->exposeMethod('showRelatedList');
		$this->exposeMethod('showChildComments');
		$this->exposeMethod('showAllComments');
		$this->exposeMethod('getActivities');
	}

	function checkPermission(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordId);
		if(!$recordPermission) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
		return true;
	}

	function preProcess(Mycrm_Request $request, $display=true) {
		parent::preProcess($request, false);

		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		if(!$this->record){
			$this->record = Mycrm_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();
		$recordStrucure = Mycrm_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Mycrm_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		$summaryInfo = array();
		// Take first block information as summary information
		$stucturedValues = $recordStrucure->getStructure();
		foreach($stucturedValues as $blockLabel=>$fieldList) {
			$summaryInfo[$blockLabel] = $fieldList;
			break;
		}

		$detailViewLinkParams = array('MODULE'=>$moduleName,'RECORD'=>$recordId);

		$detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);
		$navigationInfo = ListViewSession::getListViewNavigation($recordId);

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('NAVIGATION', $navigationInfo);

		//Intially make the prev and next records as null
		$prevRecordId = null;
		$nextRecordId = null;
		$found = false;
		if ($navigationInfo) {
			foreach($navigationInfo as $page=>$pageInfo) {
				foreach($pageInfo as $index=>$record) {
					//If record found then next record in the interation
					//will be next record
					if($found) {
						$nextRecordId = $record;
						break;
					}
					if($record == $recordId) {
						$found = true;
					}
					//If record not found then we are assiging previousRecordId
					//assuming next record will get matched
					if(!$found) {
						$prevRecordId = $record;
					}
				}
				//if record is found and next record is not calculated we need to perform iteration
				if($found && !empty($nextRecordId)) {
					break;
				}
			}
		}

		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);
		if(!empty($prevRecordId)) {
			$viewer->assign('PREVIOUS_RECORD_URL', $moduleModel->getDetailViewUrl($prevRecordId));
		}
		if(!empty($nextRecordId)) {
			$viewer->assign('NEXT_RECORD_URL', $moduleModel->getDetailViewUrl($nextRecordId));
		}

		$viewer->assign('MODULE_MODEL', $this->record->getModule());
		$viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);

		$viewer->assign('IS_EDITABLE', $this->record->getRecord()->isEditable($moduleName));
		$viewer->assign('IS_DELETABLE', $this->record->getRecord()->isDeletable($moduleName));

		$linkParams = array('MODULE'=>$moduleName, 'ACTION'=>$request->get('view'));
		$linkModels = $this->record->getSideBarLinks($linkParams);
		$viewer->assign('QUICK_LINKS', $linkModels);
        $viewer->assign('MODULE_NAME', $moduleName);

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$viewer->assign('DEFAULT_RECORD_VIEW', $currentUserModel->get('default_record_view'));

                $picklistDependencyDatasource=  Mycrm_DependencyPicklist::getPicklistDependencyDatasource($moduleName); 
                $viewer->assign('PICKLIST_DEPENDENCY_DATASOURCE',Zend_Json::encode($picklistDependencyDatasource));
		if($display) {
			$this->preProcessDisplay($request);
		}
	}

	function preProcessTplName(Mycrm_Request $request) {
		return 'DetailViewPreProcess.tpl';
	}

	function process(Mycrm_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}

		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		if ($currentUserModel->get('default_record_view') === 'Summary') {
			echo $this->showModuleBasicView($request);
		} else {
			echo $this->showModuleDetailView($request);
		}
	}

	public function postProcess(Mycrm_Request $request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$moduleModel = Mycrm_Module_Model::getInstance($moduleName);
		if(!$this->record){
			$this->record = Mycrm_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$detailViewLinkParams = array('MODULE'=>$moduleName,'RECORD'=>$recordId);
		$detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);

		$selectedTabLabel = $request->get('tab_label');

		if(empty($selectedTabLabel)) {
            if($currentUserModel->get('default_record_view') === 'Detail') {
                $selectedTabLabel = vtranslate('SINGLE_'.$moduleName, $moduleName).' '. vtranslate('LBL_DETAILS', $moduleName);
            } else{
                if($moduleModel->isSummaryViewSupported()) {
                    $selectedTabLabel = vtranslate('SINGLE_'.$moduleName, $moduleName).' '. vtranslate('LBL_SUMMARY', $moduleName);
                } else {
                    $selectedTabLabel = vtranslate('SINGLE_'.$moduleName, $moduleName).' '. vtranslate('LBL_DETAILS', $moduleName);
                }
            }
        }

		$viewer = $this->getViewer($request);

		$viewer->assign('SELECTED_TAB_LABEL', $selectedTabLabel);
		$viewer->assign('MODULE_MODEL', $this->record->getModule());
		$viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);

		$viewer->view('DetailViewPostProcess.tpl', $moduleName);

		parent::postProcess($request);
	}


	public function getHeaderScripts(Mycrm_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'modules.Mycrm.resources.Detail',
			"modules.$moduleName.resources.Detail",
			'modules.Mycrm.resources.RelatedList',
			"modules.$moduleName.resources.RelatedList",
			'libraries.jquery.jquery_windowmsg',
            "libraries.jquery.ckeditor.ckeditor",
			"libraries.jquery.ckeditor.adapters.jquery",
			"modules.Emails.resources.MassEdit",
			"modules.Mycrm.resources.CkEditor",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	function showDetailViewByMode($request) {
		$requestMode = $request->get('requestMode');
		if($requestMode == 'full') {
			return $this->showModuleDetailView($request);
		}
		return $this->showModuleBasicView($request);
	}

	/**
	 * Function shows the entire detail for the record
	 * @param Mycrm_Request $request
	 * @return <type>
	 */
	function showModuleDetailView(Mycrm_Request $request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		if(!$this->record){
		$this->record = Mycrm_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();
		$recordStrucure = Mycrm_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Mycrm_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		$structuredValues = $recordStrucure->getStructure();

        $moduleModel = $recordModel->getModule();

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('RECORD_STRUCTURE', $structuredValues);
        $viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));

		return $viewer->view('DetailViewFullContents.tpl',$moduleName,true);
	}

	function showModuleSummaryView($request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		if(!$this->record){
			$this->record = Mycrm_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();
		$recordStrucure = Mycrm_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Mycrm_RecordStructure_Model::RECORD_STRUCTURE_MODE_SUMMARY);

        $moduleModel = $recordModel->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
        $viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		$viewer->assign('SUMMARY_RECORD_STRUCTURE', $recordStrucure->getStructure());
		$viewer->assign('RELATED_ACTIVITIES', $this->getActivities($request));

		return $viewer->view('ModuleSummaryView.tpl', $moduleName, true);
	}

	/**
	 * Function shows basic detail for the record
	 * @param <type> $request
	 */
	function showModuleBasicView($request) {

		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		if(!$this->record){
			$this->record = Mycrm_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();

		$detailViewLinkParams = array('MODULE'=>$moduleName,'RECORD'=>$recordId);
		$detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('MODULE_SUMMARY', $this->showModuleSummaryView($request));

		$viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		$viewer->assign('MODULE_NAME', $moduleName);

		$recordStrucure = Mycrm_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Mycrm_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		$structuredValues = $recordStrucure->getStructure();

        $moduleModel = $recordModel->getModule();

		$viewer->assign('RECORD_STRUCTURE', $structuredValues);
        $viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());

		echo $viewer->view('DetailViewSummaryContents.tpl', $moduleName, true);
	}

	/**
	 * Function returns recent changes made on the record
	 * @param Mycrm_Request $request
	 */
	function showRecentActivities (Mycrm_Request $request) {
		$parentRecordId = $request->get('record');
		$pageNumber = $request->get('page');
		$limit = $request->get('limit');
		$moduleName = $request->getModule();

		if(empty($pageNumber)) {
			$pageNumber = 1;
		}

		$pagingModel = new Mycrm_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if(!empty($limit)) {
			$pagingModel->set('limit', $limit);
		}

		$recentActivities = ModTracker_Record_Model::getUpdates($parentRecordId, $pagingModel);
		$pagingModel->calculatePageRange($recentActivities);

		if($pagingModel->getCurrentPage() == ModTracker_Record_Model::getTotalRecordCount($parentRecordId)/$pagingModel->getPageLimit()) {
        	$pagingModel->set('nextPageExists', false);
        }

		$viewer = $this->getViewer($request);
		$viewer->assign('RECENT_ACTIVITIES', $recentActivities);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('PAGING_MODEL', $pagingModel);

		echo $viewer->view('RecentActivities.tpl', $moduleName, 'true');
	}

	/**
	 * Function returns latest comments
	 * @param Mycrm_Request $request
	 * @return <type>
	 */
	function showRecentComments(Mycrm_Request $request) {
		$parentId = $request->get('record');
		$pageNumber = $request->get('page');
		$limit = $request->get('limit');
		$moduleName = $request->getModule();

		if(empty($pageNumber)) {
			$pageNumber = 1;
		}

		$pagingModel = new Mycrm_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if(!empty($limit)) {
			$pagingModel->set('limit', $limit);
		}

		$recentComments = ModComments_Record_Model::getRecentComments($parentId, $pagingModel);
		$pagingModel->calculatePageRange($recentComments);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$modCommentsModel = Mycrm_Module_Model::getInstance('ModComments');

		$viewer = $this->getViewer($request);
		$viewer->assign('COMMENTS', $recentComments);
		$viewer->assign('CURRENTUSER', $currentUserModel);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel);

		return $viewer->view('RecentComments.tpl', $moduleName, 'true');
	}

	/**
	 * Function returns related records
	 * @param Mycrm_Request $request
	 * @return <type>
	 */
	function showRelatedList(Mycrm_Request $request) {
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$targetControllerClass = null;

		// Added to support related list view from the related module, rather than the base module.
		try {
			$targetControllerClass = Mycrm_Loader::getComponentClassName('View', 'In'.$moduleName.'Relation', $relatedModuleName);
		}catch(AppException $e) {
			try {
				// If any module wants to have same view for all the relation, then invoke this.
				$targetControllerClass = Mycrm_Loader::getComponentClassName('View', 'InRelation', $relatedModuleName);
			}catch(AppException $e) {
				// Default related list
				$targetControllerClass = Mycrm_Loader::getComponentClassName('View', 'RelatedList', $moduleName);
			}
		}
		if($targetControllerClass) {
			$targetController = new $targetControllerClass();
			return $targetController->process($request);
		}
	}

	/**
	 * Function sends the child comments for a comment
	 * @param Mycrm_Request $request
	 * @return <type>
	 */
	function showChildComments(Mycrm_Request $request) {
		$parentCommentId = $request->get('commentid');
		$parentCommentModel = ModComments_Record_Model::getInstanceById($parentCommentId);
		$childComments = $parentCommentModel->getChildComments();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$modCommentsModel = Mycrm_Module_Model::getInstance('ModComments');

		$viewer = $this->getViewer($request);
		$viewer->assign('PARENT_COMMENTS', $childComments);
		$viewer->assign('CURRENTUSER', $currentUserModel);
		$viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel);

		return $viewer->view('CommentsList.tpl', $moduleName, 'true');
	}

	/**
	 * Function sends all the comments for a parent(Accounts, Contacts etc)
	 * @param Mycrm_Request $request
	 * @return <type>
	 */
	function showAllComments(Mycrm_Request $request) {
		$parentRecordId = $request->get('record');
		$commentRecordId = $request->get('commentid');
		$moduleName = $request->getModule();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$modCommentsModel = Mycrm_Module_Model::getInstance('ModComments');

		$parentCommentModels = ModComments_Record_Model::getAllParentComments($parentRecordId);

		if(!empty($commentRecordId)) {
			$currentCommentModel = ModComments_Record_Model::getInstanceById($commentRecordId);
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('CURRENTUSER', $currentUserModel);
		$viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel);
		$viewer->assign('PARENT_COMMENTS', $parentCommentModels);
		$viewer->assign('CURRENT_COMMENT', $currentCommentModel);

		return $viewer->view('ShowAllComments.tpl', $moduleName, 'true');
	}
	/**
	 * Function to get Ajax is enabled or not
	 * @param Mycrm_Record_Model record model
	 * @return <boolean> true/false
	 */
	function isAjaxEnabled($recordModel) {
		return $recordModel->isEditable();
	}

	/**
	 * Function to get activities
	 * @param Mycrm_Request $request
	 * @return <List of activity models>
	 */
	public function getActivities(Mycrm_Request $request) {
		return '';
	}


	/**
	 * Function returns related records based on related moduleName
	 * @param Mycrm_Request $request
	 * @return <type>
	 */
	function showRelatedRecords(Mycrm_Request $request) {
		$parentId = $request->get('record');
		$pageNumber = $request->get('page');
		$limit = $request->get('limit');
		$relatedModuleName = $request->get('relatedModule');
		$moduleName = $request->getModule();

		if(empty($pageNumber)) {
			$pageNumber = 1;
		}

		$pagingModel = new Mycrm_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if(!empty($limit)) {
			$pagingModel->set('limit', $limit);
		}

		$parentRecordModel = Mycrm_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Mycrm_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);
		$models = $relationListView->getEntries($pagingModel);
		$header = $relationListView->getHeaders();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE' , $moduleName);
		$viewer->assign('RELATED_RECORDS' , $models);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE' , $relatedModuleName);
		$viewer->assign('PAGING_MODEL', $pagingModel);

		return $viewer->view('SummaryWidgets.tpl', $moduleName, 'true');
	}
}
