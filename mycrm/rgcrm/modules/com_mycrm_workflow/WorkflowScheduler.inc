<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

require_once ('include/events/VTEntityData.inc');
require_once ('include/QueryGenerator/QueryGenerator.php');
require_once ('include/Zend/Json.php');
require_once ('include/utils/utils.php');
require_once ('modules/com_mycrm_workflow/VTWorkflowUtils.php');
require_once ('modules/com_mycrm_workflow/VTWorkflowManager.inc');
require_once ('modules/com_mycrm_workflow/VTTaskManager.inc');
require_once ('modules/com_mycrm_workflow/VTTaskQueue.inc');
require_once ('modules/com_mycrm_workflow/VTEntityCache.inc');
require_once ('include/Webservices/WebserviceField.php');
require_once ('include/Webservices/EntityMeta.php');
require_once ('include/Webservices/MycrmWebserviceObject.php');
require_once ('include/Webservices/MycrmCRMObject.php');
require_once ('include/Webservices/MycrmCRMObjectMeta.php');
require_once ('include/Webservices/DataTransform.php');
require_once ('include/Webservices/Retrieve.php');