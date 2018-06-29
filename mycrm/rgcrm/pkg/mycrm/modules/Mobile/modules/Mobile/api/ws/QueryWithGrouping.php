<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/Query.php';

include_once 'include/Webservices/Query.php';

class Mobile_WS_QueryWithGrouping extends Mobile_WS_Query {
	
	private $queryModule;
	
	function processQueryResultRecord($record, $user) {
		parent::processQueryResultRecord($record, $user);

		if ($this->cachedDescribeInfo() === false) {
			$describeInfo = vtws_describe($this->queryModule, $user);
			$this->cacheDescribeInfo($describeInfo);
		}
		$transformedRecord = $this->transformRecordWithGrouping($record, $this->queryModule);
		// Update entity fieldnames
		$transformedRecord['labelFields'] = $this->cachedEntityFieldnames($this->queryModule);
		return $transformedRecord;
	}
	
	function process(Mobile_API_Request $request) {
		$this->queryModule = $request->get('module');
		return parent::process($request);
	}
}