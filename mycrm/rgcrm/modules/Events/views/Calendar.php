<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

// TODO This is a stop-gap measure to have the
// user continue working with Calendar when dropping from Event View.
class Events_Calendar_View extends Mycrm_Index_View {
	
	public function preProcess(Mycrm_Request $request, $display = true) {}
	public function postProcess(Mycrm_Request $request) {}
	
	public function process(Mycrm_Request $request) {
		header("Location: index.php?module=Calendar&view=Calendar");
	}
}
