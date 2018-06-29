<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Portal_Module_Model extends Mycrm_Module_Model {
    
    public function getSideBarLinks($linkParams) {
		$quickLink = array(
            'linktype' => 'SIDEBARLINK',
            'linklabel' => 'LBL_OUR_SITES_LIST',
            'linkurl' => $this->getListViewUrl(),
            'linkicon' => '',
		);
        $links['SIDEBARLINK'][] = Mycrm_Link_Model::getInstanceFromValues($quickLink);
		return $links;
	}
    
        public function savePortalRecord($recordId, $bookmarkName, $bookmarkUrl) {
        $db = PearDatabase::getInstance();
        if(empty($recordId)) {
            $portalId = $db->getUniqueID('mycrm_portal');
            $query = "INSERT INTO mycrm_portal VALUES(?,?,?,?,?,?)";
            $params = array($portalId, $bookmarkName, $bookmarkUrl, 0, 0, date('Y-m-d H:i:s'));
        }
        else {
            $query = "UPDATE mycrm_portal SET portalname=?, portalurl=? WHERE portalid=?";
            $params = array($bookmarkName, $bookmarkUrl, $recordId);
        }
        
        $db->pquery($query, $params);
        return true;
    }
    
    public function getRecord($recordId) {
        $db = PearDatabase::getInstance();
        
        $result = $db->pquery('SELECT portalname, portalurl FROM mycrm_portal WHERE portalid = ?', array($recordId));
        
        $data['bookmarkName'] = $db->query_result($result, 0, 'portalname');
        $data['bookmarkUrl'] = $db->query_result($result, 0, 'portalurl');
        
        return $data;
    }
    
    public function deleteRecord($recordId) {
        $db = PearDatabase::getInstance();
        $db->pquery('DELETE FROM mycrm_portal WHERE portalid = ?', array($recordId));
    }
    
    public function getWebsiteUrl($recordId) {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT portalurl FROM mycrm_portal WHERE portalid=?', array($recordId));
        
        return $db->query_result($result, 0, 'portalurl');
    }
    
    public function getAllRecords() {
        $db = PearDatabase::getInstance();
        $record = array();
        
        $result = $db->pquery('SELECT portalid, portalname FROM mycrm_portal', array());
        
        for($i = 0; $i < $db->num_rows($result); $i++) {
            $row = $db->fetch_row($result, $i);
            $record[$i]['id'] = $row['portalid'];
            $record[$i]['portalname'] = $row['portalname'];
        }
        
        return $record;
    }
    
    public function deleteRecords(Mycrm_Request $request) {
        $searchValue = $request->get('search_value');
        $selectedIds = $request->get('selected_ids');
        $excludedIds = $request->get('excluded_ids');
        
        $db = PearDatabase::getInstance();
        
        $query = 'DELETE FROM mycrm_portal';
        $params = array();
        
        if(!empty($selectedIds) && $selectedIds != 'all' && count($selectedIds) > 0) {
            $query .= " WHERE portalid IN (".generateQuestionMarks($selectedIds).")";
            $params = $selectedIds;
        } else if($selectedIds == 'all') {
            if(empty($searchValue) && count($excludedIds) > 0) {
                $query .= " WHERE portalid NOT IN (".generateQuestionMarks($excludedIds).")";
                $params = $excludedIds;
            } else if(!empty($searchValue) && count($excludedIds) < 1) {
                $query .= " WHERE portalname LIKE '%".$searchValue."%'";
            } else if(!empty($searchValue) && count($excludedIds) > 0) {
                $query .= " WHERE portalname LIKE '%".$searchValue."%' AND portalid NOT IN (".generateQuestionMarks($excludedIds).")";
                $params = $excludedIds;
            }
        }
        $db->pquery($query, $params);
    }
}
