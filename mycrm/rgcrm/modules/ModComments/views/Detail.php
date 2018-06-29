<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class ModComments_Detail_View extends Mycrm_Detail_View {

	/**
	 * Function to get Ajax is enabled or not
	 * @param Mycrm_Record_Model record model
	 * @return <boolean> true/false
	 */
	public function isAjaxEnabled($recordModel) {
		return false;
	}

}