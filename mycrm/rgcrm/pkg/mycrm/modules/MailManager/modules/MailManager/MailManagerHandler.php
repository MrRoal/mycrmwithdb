<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/
class MailManagerHandler extends VTEventHandler {

	function handleEvent($eventName, $data) {

		if($eventName == 'mycrm.entity.beforesave') {
			// Entity is about to be saved, take required action
		}

		if($eventName == 'mycrm.entity.aftersave') {
			// Entity has been saved, take next action
		}
	}
}

?>
