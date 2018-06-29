<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: mycrm CRM Open source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class MailManager_Request extends Mycrm_Request {

	public function get($key, $defvalue = '') {
		return urldecode(parent::get($key, $defvalue));
	}

	public static function getInstance($request) {
		return new MailManager_Request($request->getAll(), $request->getAll());
	}
}