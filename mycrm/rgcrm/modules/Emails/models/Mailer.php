<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/
include_once 'vtlib/Mycrm/Mailer.php';

class Emails_Mailer_Model extends Mycrm_Mailer {

	public static function getInstance() {
		return new self();
	}

	/**
	 * Function returns error from phpmailer
	 * @return <String>
	 */
	function getError() {
		return $this->ErrorInfo;
	}
}
