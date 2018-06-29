<?php

/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */
include_once 'include/Webservices/Revise.php';
include_once 'include/Webservices/Retrieve.php';

class PurchaseOrderHandler extends VTEventHandler {

    function handleEvent($eventName, $entityData) {

        $moduleName = $entityData->getModuleName();


        // Validate the event target
        if ($moduleName != 'PurchaseOrder') {
            return;
        }

        //Get Current User Information
        global $current_user, $currentModule;

        /**
         * Adjust the balance amount against total & paid amount
         * NOTE: beforesave the total amount will not be populated in event data.
         */
        if ($eventName == 'mycrm.entity.aftersave') {
            if ($currentModule != 'PurchaseOrder')
                return;
            $entityDelta = new VTEntityDelta();
            $oldCurrency = $entityDelta->getOldValue($entityData->getModuleName(), $entityData->getId(), 'currency_id');
            $oldConversionRate = $entityDelta->getOldValue($entityData->getModuleName(), $entityData->getId(), 'conversion_rate');
            $newCurrency = $entityDelta->getCurrentValue($entityData->getModuleName(), $entityData->getId(), 'currency_id');
            $db = PearDatabase::getInstance();
            $wsid = vtws_getWebserviceEntityId('PurchaseOrder', $entityData->getId());
            $wsrecord = vtws_retrieve($wsid,$current_user);
            if ($oldCurrency != $newCurrency && $oldCurrency != '') {
                if($oldConversionRate != ''){
                    $wsrecord['paid'] = floatval(($wsrecord['paid']/$oldConversionRate) * $wsrecord['conversion_rate']);
                } 
            }
            $wsrecord['balance'] = floatval($wsrecord['hdnGrandTotal'] - $wsrecord['paid']);
            if ($wsrecord['balance'] == 0)
                $wsrecord['postatus'] = 'Received Shipment';
            $query = "UPDATE mycrm_purchaseorder SET balance=?,paid=? WHERE purchaseorderid=?";
            $db->pquery($query, array($wsrecord['balance'], $wsrecord['paid'], $entityData->getId()));
            // TODO Make it available for other event handlers
        }
    }

}

?>
