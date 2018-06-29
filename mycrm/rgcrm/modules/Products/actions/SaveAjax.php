<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Products_SaveAjax_Action extends Mycrm_SaveAjax_Action {

	public function process(Mycrm_Request $request) {
		//the new values are added to $_REQUEST for Ajax Save, are removing the Tax details depend on the 'ajxaction' value
		$_REQUEST['ajxaction'] = 'DETAILVIEW';
		$request->set('ajaxaction', 'DETAILVIEW');
		parent::process($request);
	}
}
