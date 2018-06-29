<?php
/*+*******************************************************************************
 *  The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *
 *********************************************************************************/
require_once 'include/Webservices/MycrmActorOperation.php';
/**
 * Description of MycrmCompanyDetails
 *
 * @author MAK
 */
class MycrmCompanyDetails extends MycrmActorOperation {
	public function create($elementType, $element) {
		$db = PearDatabase::getInstance();
		$sql = 'select * from mycrm_organizationdetails';
		$result = $db->pquery($sql,$params);
		$rowCount = $db->num_rows($result);
		if($rowCount > 0) {
			$id = $db->query_result($result,0,'organization_id');
			$meta = $this->getMeta();
			$element['id'] = vtws_getId($meta->getEntityId(), $id);
			return $this->revise($element);
		}else{
			$element = $this->handleFileUpload($element);
			return parent::create($elementType, $element);
		}
	}

	function handleFileUpload($element) {
		$fileFieldList = $this->meta->getFieldListByType('file');
		foreach ($fileFieldList as $field) {
			$fieldname = $field->getFieldName();
			if(is_array($_FILES[$fieldname])) {
				$element[$fieldname] = vtws_CreateCompanyLogoFile($fieldname);
			}
		}
		return $element;
	}

	public function update($element) {
		$element = $this->handleFileUpload($element);
		return parent::update($element);
	}

	public function revise($element) {
		$element = $this->handleFileUpload($element);
		return parent::revise($element);
	}

}
?>