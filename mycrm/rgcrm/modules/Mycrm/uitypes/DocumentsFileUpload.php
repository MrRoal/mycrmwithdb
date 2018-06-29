<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/
class Mycrm_DocumentsFileUpload_UIType extends Mycrm_Base_UIType {
	/**
	 * Function to get the Template name for the current UI Type Object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/DocumentsFileUpload.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <String> $value
	 * @param <Integer> $recordId
	 * @param <Mycrm_Record_Model>
	 * @return <String>
	 */
	public function getDisplayValue($value, $recordId=false, $recordModel=false) {
		if($recordModel) {
			$fileLocationType = $recordModel->get('filelocationtype');
			$fileStatus = $recordModel->get('filestatus');
			if(!empty($value) && $fileStatus) {
				if($fileLocationType == 'I') {
					$db = PearDatabase::getInstance();
					$fileIdRes = $db->pquery('SELECT attachmentsid FROM mycrm_seattachmentsrel WHERE crmid = ?', array($recordId));
					$fileId = $db->query_result($fileIdRes, 0, 'attachmentsid');
					if($fileId){
						$value = '<a href="index.php?module=Documents&action=DownloadFile&record='.$recordId.'&fileid='.$fileId.'"'.
									' title="'.	vtranslate('LBL_DOWNLOAD_FILE', 'Documents').'" >'.$value.'</a>';
					}
				} else {
					$value = '<a href="'.$value.'" target="_blank" title="'. vtranslate('LBL_DOWNLOAD_FILE', 'Documents').'" >'.$value.'</a>';
				}
			}
		}
		return $value;
	}
}