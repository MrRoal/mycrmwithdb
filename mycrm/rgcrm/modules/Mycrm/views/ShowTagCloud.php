<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_ShowTagCloud_View extends Mycrm_IndexAjax_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('showTags');
	}

	function showTags(Mycrm_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$record = $request->get('record');
		if($record) {
			$module = $request->getModule();
			if($module == 'Events'){
				$module = 'Calendar';
			}
			
			vimport('~~/libraries/freetag/freetag.class.php');
			$freeTagInstance = new freetag();
			$maxTagLength = $freeTagInstance->_MAX_TAG_LENGTH;

			$tags = Mycrm_Tag_Model::getAll($currentUser->id, $module, $record);
			$viewer = $this->getViewer($request);
			
			$viewer->assign('MAX_TAG_LENGTH', $maxTagLength);
			$viewer->assign('TAGS', $tags);
			$viewer->assign('MODULE',$module);
			echo $viewer->view('ShowTagCloud.tpl', $module, true);
		}
	}
}

?>
