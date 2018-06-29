<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: mycrm CRM Open source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class MailManager_Settings_View extends MailManager_MainUI_View {

	/**
	 * Process the request for Settings Operations
	 * @param Mycrm_Request $request
	 * @return MailManager_Response
	 */
	public function process(Mycrm_Request $request) {
		$response = new Mycrm_Response();
		$module = $request->getModule();
		if ('edit' == $this->getOperationArg($request)) {

			$model = $this->getMailBoxModel();
            $connector = $this->getConnector();
			$serverName = $model->serverName();

            if ($connector->isConnected()) {
                $folders = $connector->folders();
            }
			$viewer = $this->getViewer($request);
			$viewer->assign('MODULE', $module);
			$viewer->assign('MAILBOX', $model);
			$viewer->assign('SERVERNAME', $serverName);
            $viewer->assign('FOLDERS', $folders);
			$response->setResult($viewer->view('SettingsEdit.tpl', $module, true));

		} else if ('save' == $this->getOperationArg($request)) {

			$model = $this->getMailBoxModel();
			$model->setServer($request->get('_mbox_server'));
			$model->setUsername($request->get('_mbox_user'));
			$model->setPassword($request->getRaw('_mbox_pwd'));
			$model->setProtocol($request->get('_mbox_protocol', 'IMAP4'));
			$model->setSSLType($request->get('_mbox_ssltype', 'ssl'));
			$model->setCertValidate($request->get('_mbox_certvalidate', 'novalidate-cert'));
			$model->setRefreshTimeOut($request->get('_mbox_refresh_timeout'));
			$connector = $this->getConnector();
            $sentFolder = $request->get('_mbox_sent_folder');
            if($connector->isConnected() && empty($sentFolder)) {
                $folderInstaces = $connector->folders();
                foreach($folderInstaces as $folder) {
                    if (strpos(strtolower($folder->name()), 'sent') !== false) {
                        $sentFolder = $folder->name();
                    }
                }
            }
            $model->setFolder($sentFolder);
			if ($connector->isConnected()) {
				$model->save();

				$request->set('_operation', 'mainui');
				return parent::process($request);
			} else if($connector->hasError()) {
                $error = $connector->lastError();
                		$response->isJSON(true);
				$response->setError(101, $error);
			}
		} else if ('remove' == $this->getOperationArg($request)) {

			$model = $this->getMailBoxModel();
			$model->delete();

			$response->isJSON(true);
			$response->setResult(array('status' => true));

		} else if ('detail' == $this->getOperationArg($request)) {

			$model = $this->getMailBoxModel();
			$serverName = $model->serverName();

			$viewer = $this->getViewer($request);
			$viewer->assign('MODULE', $module);
			$viewer->assign('MAILBOX', $model);
			$viewer->assign('SERVERNAME', $serverName);
			$response->setResult($viewer->view('SettingsDetail.tpl', $module, true));
		}

		return $response;
	}
        
        public function validateRequest(Mycrm_Request $request) { 
            return $request->validateWriteAccess(); 
        } 
}
?>