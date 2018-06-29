<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Mycrm_UI5Embed_View extends Mycrm_Index_View {
	
	protected function preProcessDisplay(Mycrm_Request $request) {}
	
	protected function getUI5EmbedURL(Mycrm_Request $request) {
		return '../index.php?action=index&module=' . $request->getModule();
	}
	
	public function process(Mycrm_Request $request) {
		$viewer = $this->getViewer($request);
		$viewer->assign('UI5_URL', $this->getUI5EmbedURL($request));
		$viewer->view('UI5EmbedView.tpl');
	}
	
}