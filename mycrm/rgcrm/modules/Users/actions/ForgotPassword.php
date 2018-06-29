<?php

/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */
chdir(dirname(__FILE__) . "/../../../");
include_once "include/utils/VtlibUtils.php";
include_once "include/utils/CommonUtils.php";
include_once "includes/Loader.php";
include_once 'includes/runtime/BaseModel.php';
include_once 'includes/runtime/Viewer.php';
include_once "includes/http/Request.php";
include_once "include/Webservices/Custom/ChangePassword.php";
include_once "include/Webservices/Utils.php";
include_once "includes/runtime/EntryPoint.php";

class Users_ForgotPassword_Action {

    public function changePassword($request) {

        $request = new Mycrm_Request($request);
        $viewer = Mycrm_Viewer::getInstance();
        $userName = $request->get('username');
        $newPassword = $request->get('password');
        $confirmPassword = $request->get('confirmPassword');
        $shortURLID = $request->get('shorturl_id');
        $secretHash = $request->get('secret_hash');
        $shortURLModel = Mycrm_ShortURL_Helper::getInstance($shortURLID);
        $secretToken = $shortURLModel->handler_data['secret_token'];

        $validateData = array('username' => $userName,
            'secret_token' => $secretToken,
            'secret_hash' => $secretHash
        );
        $valid = $shortURLModel->compareEquals($validateData);
        if ($valid) {
            $userId = getUserId_Ol($userName);
            $user = Users::getActiveAdminUser();
            $wsUserId = vtws_getWebserviceEntityId('Users', $userId);
            vtws_changePassword($wsUserId, '', $newPassword, $confirmPassword, $user);
        } else {
            $viewer->assign('ERROR', true);
        }
        $shortURLModel->delete();
        $viewer->assign('USERNAME', $userName);
        $viewer->assign('PASSWORD', $newPassword);
        $viewer->view('FPLogin.tpl', 'Users');
    }

    public static function run($request) {
        $instance = new self();
        $instance->changePassword($request);
    }

}

Users_ForgotPassword_Action::run($_REQUEST);
