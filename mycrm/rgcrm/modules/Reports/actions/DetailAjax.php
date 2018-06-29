<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_DetailAjax_Action extends Mycrm_BasicAjax_Action{
    
    public function __construct() {
        parent::__construct();
		$this->exposeMethod('getRecordsCount');
	}
    
    public function process(Mycrm_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
    
    /**
	 * Function to get related Records count from this relation
	 * @param <Mycrm_Request> $request
	 * @return <Number> Number of record from this relation
	 */
	public function getRecordsCount(Mycrm_Request $request) {
		$record = $request->get('record');
		$reportModel = Reports_Record_Model::getInstanceById($record);
		$reportModel->setModule('Reports');
		$reportModel->set('advancedFilter', $request->get('advanced_filter'));
        
        $advFilterSql = $reportModel->getAdvancedFilterSQL();
        $query = $reportModel->getReportSQL($advFilterSql, 'PDF');
        $countQuery = $reportModel->generateCountQuery($query);

        $count = $reportModel->getReportsCount($countQuery);
        $response = new Mycrm_Response();
        $response->setResult($count);
        $response->emit();
    }
    
}