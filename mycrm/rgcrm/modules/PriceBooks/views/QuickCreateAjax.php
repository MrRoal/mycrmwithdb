<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class PriceBooks_QuickCreateAjax_View extends Mycrm_QuickCreateAjax_View {

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);
		$viewer->assign('IS_RELATION', $request->get('relationOperation'));

		parent::process($request);
	}
}