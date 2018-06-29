<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Mycrm_IndexAjax_View extends Settings_Mycrm_Index_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('getSettingsShortCutBlock');
                $this->exposeMethod('realignSettingsShortCutBlock');
	}
	
	public function preProcess (Mycrm_Request $request) {
		return;
	}

	public function postProcess (Mycrm_Request $request) {
		return;
	}
	
	public function process (Mycrm_Request $request) {
		$mode = $request->getMode();

		if($mode){
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}
	
	public function getSettingsShortCutBlock(Mycrm_Request $request) {
		$fieldid = $request->get('fieldid');
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$pinnedSettingsShortcuts = Settings_Mycrm_MenuItem_Model::getPinnedItems();
		$viewer->assign('SETTINGS_SHORTCUT',$pinnedSettingsShortcuts[$fieldid]);
		$viewer->assign('MODULE',$qualifiedModuleName);
		$viewer->view('SettingsShortCut.tpl', $qualifiedModuleName);
	}
        
        public function realignSettingsShortCutBlock(Mycrm_Request $request){
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$pinnedSettingsShortcuts = Settings_Mycrm_MenuItem_Model::getPinnedItems();
		$viewer->assign('SETTINGS_SHORTCUT',$pinnedSettingsShortcuts);
		$viewer->assign('MODULE',$qualifiedModuleName);
		$viewer->view('ReAlignSettingsShortCut.tpl', $qualifiedModuleName);
	}
}