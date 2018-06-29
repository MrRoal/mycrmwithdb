<?php
/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */

class Import_Config_Model extends Mycrm_Base_Model {

	function __construct() {
		$ImportConfig = array(
			'importTypes' => array(
								'csv' => array('reader' => 'Import_CSVReader_Reader', 'classpath' => 'modules/Import/readers/CSVReader.php'),
								'vcf' => array('reader' => 'Import_VCardReader_Reader', 'classpath' => 'modules/Import/readers/VCardReader.php'),
								'ics' => array('reader' => 'Import_ICSReader_Reader', 'classpath' => 'modules/Import/readers/ICSReader.php'),
								'default' => array('reader' => 'Import_FileReader_Reader', 'classpath' => 'modules/Import/readers/FileReader.php')
							),

			'userImportTablePrefix' => 'mycrm_import_',
			// Individual batch limit - Specified number of records will be imported at one shot and the cycle will repeat till all records are imported
			'importBatchLimit' => '250',
			// Threshold record limit for immediate import. If record count is more than this, then the import is scheduled through cron job
			'immediateImportLimit' => '1000',
		);

		$this->setData($ImportConfig);
	}
}
