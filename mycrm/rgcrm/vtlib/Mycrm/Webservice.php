<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/
include_once('vtlib/Mycrm/Utils.php');

/**
 * Provides API to work with mycrm CRM Webservice (available from mycrm 5.1)
 * @package vtlib
 */
class Mycrm_Webservice {
	
	/**
	 * Helper function to log messages
	 * @param String Message to log
	 * @param Boolean true appends linebreak, false to avoid it
	 * @access private
	 */
	static function log($message, $delim=true) {
		Mycrm_Utils::Log($message, $delim);
	}

	/**
	 * Initialize webservice for the given module
	 * @param Mycrm_Module Instance of the module.
	 */
	static function initialize($moduleInstance) {
		if($moduleInstance->isentitytype) {
			// TODO: Enable support when webservice API support is added.
			if(function_exists('vtws_addDefaultModuleTypeEntity')) { 
				vtws_addDefaultModuleTypeEntity($moduleInstance->name);
				self::log("Initializing webservices support ...DONE");
			}
		}
	}

	/**
	 * Initialize webservice for the given module
	 * @param Mycrm_Module Instance of the module.
	 */
	static function uninitialize($moduleInstance) {
		if($moduleInstance->isentitytype) {
			// TODO: Enable support when webservice API support is added.
			if(function_exists('vtws_deleteWebserviceEntity')) { 
				vtws_deleteWebserviceEntity($moduleInstance->name);
				self::log("De-Initializing webservices support ...DONE");
			}
		}
	}
}
?>
