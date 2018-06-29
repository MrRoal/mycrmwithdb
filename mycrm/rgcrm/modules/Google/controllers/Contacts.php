<?php
/* +***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * *********************************************************************************** */
vimport('~~/modules/WSAPP/synclib/controllers/SynchronizeController.php');

class Google_Contacts_Controller extends WSAPP_SynchronizeController {

    /**
     * Returns the connector of the google contacts
     * @return Google_Contacts_Connector
     */
    public function getTargetConnector() {
        $oauth2Connector = new Google_Oauth2_Connector($this->getSourceType(),$this->user->id); 
        $oauth2Connection = $oauth2Connector->authorize(); 
        $connector = new Google_Contacts_Connector($oauth2Connection); 
        $connector->setSynchronizeController($this);
        return $connector;
    }
    
    public function getSourceConnector() { 
        $connector = new Google_Mycrm_Connector(); 
        $connector->setSynchronizeController($this); 
        $targetName = $this->targetConnector->getName(); 
               if(empty ($targetName)){ 
                       throw new Exception('Target Name cannot be empty'); 
               } 
        return $connector->setName('Mycrm_'.$targetName); 
    } 

    /**
     * Return the types of snyc 
     * @return type
     */
    public function getSyncType() {
        return WSAPP_SynchronizeController::WSAPP_SYNCHRONIZECONTROLLER_USER_SYNCTYPE;
    }

    /**
     * Returns source type of Controller
     * @return string
     */
    public function getSourceType() {
        return 'Contacts';
    }

}
