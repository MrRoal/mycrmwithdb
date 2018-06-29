<?php

/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */

abstract class Mycrm_Footer_View extends Mycrm_Header_View {

	function __construct() {
		parent::__construct();
	}

	//Note: To get the right hook for immediate parent in PHP,
	// specially in case of deep hierarchy
	/*function preProcessParentTplName(Mycrm_Request $request) {
		return parent::preProcessTplName($request);
	}*/

	/*function postProcess(Mycrm_Request $request) {
		parent::postProcess($request);
	}*/
}
