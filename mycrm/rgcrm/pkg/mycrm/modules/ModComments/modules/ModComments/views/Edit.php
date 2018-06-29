<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

Class ModComments_Edit_View extends Mycrm_Edit_View {

	public function checkPermission(Mycrm_Request $request) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$moduleName = $request->getModule();
		$record = $request->get('record');
		if (!empty($record) || !Users_Privileges_Model::isPermitted($moduleName, 'EditView')) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}
}