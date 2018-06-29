<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class ModComments_Delete_Action extends Mycrm_Delete_Action {

	function checkPermission(Mycrm_Request $request) {
		throw new AppException('LBL_PERMISSION_DENIED');
	}
}
