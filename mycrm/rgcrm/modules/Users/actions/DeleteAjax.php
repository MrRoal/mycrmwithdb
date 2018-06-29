<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/
vimport('~~/include/Webservices/Custom/DeleteUser.php');

class Users_DeleteAjax_Action extends Mycrm_Delete_Action {

	public function process(Mycrm_Request $request) {
		$moduleName = $request->getModule();
        $ownerId = $request->get('userid');
        $newOwnerId = $request->get('transfer_user_id');

        if($request->get('mode') == 'permanent') {
            Users_Record_Model::deleteUserPermanently($ownerId, $newOwnerId);
        } else {
            $userId = vtws_getWebserviceEntityId($moduleName, $ownerId);
            $transformUserId = vtws_getWebserviceEntityId($moduleName, $newOwnerId);

            $userModel = Users_Record_Model::getCurrentUserModel();

            vtws_deleteUser($userId, $transformUserId, $userModel);

            if($request->get('permanent') == '1')
                Users_Record_Model::deleteUserPermanently($ownerId, $newOwnerId);
        }
		
		$response = new Mycrm_Response();
		$response->setResult(array('message'=>vtranslate('LBL_USER_DELETED_SUCCESSFULLY', $moduleName)));
		$response->emit();
	}
}
