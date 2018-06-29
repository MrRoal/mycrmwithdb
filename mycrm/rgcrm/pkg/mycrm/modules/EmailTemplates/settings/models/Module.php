<?php

/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */
/**
 * Email Template Model Class
 */
class Settings_EmailTemplates_Module_Model extends Settings_Mycrm_Module_Model {

	/**
	 * Function retruns List of Email Templates
	 * @return string
	 */
	function getListViewUrl() {
		return 'module=EmailTemplates&parent=Settings&view=List';
	}

	/**
	 * Function returns all the Email Template Models
	 * @return <Array of EmailTemplates_Record_Model>
	 */
	function getAll() {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM mycrm_emailtemplates WHERE deleted = 0', array());

		$emailTemplateModels = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$emailTemplateModel = Settings_EmailTemplates_Record_Model::getInstance();
			$emailTemplateModel->setData($db->query_result_rowdata($result, $i));
			$emailTemplateModels[] = $emailTemplateModel;
		}

		return $emailTemplateModels;
	}
}
