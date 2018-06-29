<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class SMSNotifier_Provider_Model extends Mycrm_Base_Model {

	/**
	 * Function to get instance of provider model
	 * @param <String> $providerName
	 * @return <SMSNotifier_Provider_Model> provider object
	 */
	public static function getInstance($providerName) {
		if(!empty($providerName)) {
			$providerName = trim($providerName);
			$className = Mycrm_Loader::getComponentClassName('Provider', $providerName, 'SMSNotifier');
			return new $className();
		}
		return false;
	}

	/**
	 * Function to get All providers
	 * @return <Array> list of all providers <SMSNotifier_Provider_Model>
	 */
	public static function getAll() {
		$providers = array();
		if ($handle = opendir( dirname(__FILE__) . '/../providers')) {
			while ($file = readdir($handle)) {
				if (!in_array($file, array('.', '..', 'MyProvider.php', 'CVS'))) {
					if(preg_match("/(.*)\.php$/", $file, $matches)) {
						$providers[] = self::getInstance($matches[1]);
					}
				}
			}
		}
		return $providers;
	}
}
?>