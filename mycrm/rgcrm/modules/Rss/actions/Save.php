<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/
require_once('libraries/magpierss/rss_fetch.inc');

class Rss_Save_Action extends Mycrm_Save_Action {

	public function process(Mycrm_Request $request) {
        $response = new Mycrm_Response();
		$moduleName = $request->getModule();
        $url = $request->get('feedurl');
        $recordModel = Rss_Record_Model::getCleanInstance($moduleName);
        $result = $recordModel->validateRssUrl($url);
        
        if($result) {
            $recordModel->save($url);
            $response->setResult(array('success' => true, 'message' => vtranslate('JS_RSS_SUCCESSFULLY_SAVED', $moduleName), 'id' => $recordModel->getId()));
        } else {
            $response->setResult(array('success' => false, 'message' => vtranslate('JS_INVALID_RSS_URL', $moduleName)));   
        }
        
        $response->emit();
	}
}
