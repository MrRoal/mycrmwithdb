<?php

/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */

class Leads_Save_Action extends Mycrm_Save_Action {

	public function process(Mycrm_Request $request) {

		//To stop saveing the value of salutation as '--None--'
		$salutationType = $request->get('salutationtype');
		if ($salutationType === '--None--') {
			$request->set('salutationtype', '');
		}
		parent::process($request);
	}
}
