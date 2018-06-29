<?php
/*+*******************************************************************************
 *  The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *
 *********************************************************************************/

require_once 'include/Webservices/Retrieve.php';

/**
 * Retrieve inventory record with LineItems
 */
function vtws_retrieve_inventory($id){
	global $current_user;

	$record = vtws_retrieve($id, $current_user);

	$handler = vtws_getModuleHandlerFromName('LineItem', $user);
    $id = vtws_getIdComponents($id);
    $id = $id[1];
	$inventoryLineItems = $handler->getAllLineItemForParent($id);

	$record['LineItems'] = $inventoryLineItems;

	return $record;
}

?>
