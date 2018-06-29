<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_MailConverter_CheckMailBoxMaxLimit_Action extends Settings_Mycrm_Index_Action {
	
	public function process(Mycrm_Request $request) {
		$recordsCount = Settings_MailConverter_Record_Model::getCount();
		$qualifiedModuleName = $request->getModule(false);
		$response = new Mycrm_Response();
        global $max_mailboxes;
        if ($recordsCount < $max_mailboxes) {
			$result = array(true);
			$response->setResult($result);
		} else {
			$response->setError(vtranslate('LBL_MAX_LIMIT_EXCEEDED', $qualifiedModuleName));
		}
		$response->emit();
	}
}
?>
