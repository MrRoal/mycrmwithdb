<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_Notebook_Model extends Mycrm_Widget_Model {
	
	public function getContent() {
		$data = Zend_Json::decode(decode_html($this->get('data')));
		return $data['contents'];
		
	}
	
	public function getLastSavedDate() {
		$data = Zend_Json::decode(decode_html($this->get('data')));
		return $data['lastSavedOn'];
		
	}
	
	public function save($request) {
		$db = PearDatabase::getInstance();
		$content = $request->get('contents');
		$noteBookId = $request->get('widgetid');
		$date_var = date("Y-m-d H:i:s");
		$date = $db->formatDate($date_var, true);
		
		$dataValue = array();
		$dataValue['contents'] = $content;
		$dataValue['lastSavedOn'] = $date;
		
		$data = Zend_Json::encode((object) $dataValue);
		$this->set('data', $data);
		
		
		$db->pquery('UPDATE mycrm_module_dashboard_widgets SET data=? WHERE id=?', array($data, $noteBookId));
	}

	public static function getUserInstance($widgetId) {
			$currentUser = Users_Record_Model::getCurrentUserModel();

			$db = PearDatabase::getInstance();
			
			$result = $db->pquery('SELECT mycrm_module_dashboard_widgets.* FROM mycrm_module_dashboard_widgets 
			INNER JOIN mycrm_links ON mycrm_links.linkid = mycrm_module_dashboard_widgets.linkid 
			WHERE linktype = ? AND mycrm_module_dashboard_widgets.id = ? AND mycrm_module_dashboard_widgets.userid = ?', array('DASHBOARDWIDGET', $widgetId, $currentUser->getId()));
			
			$self = new self();
			if($db->num_rows($result)) {
				$row = $db->query_result_rowdata($result, 0);
				$self->setData($row);
			}
		return $self;
		
	}
	
}