<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * CompanyDetails Record Model class
 */
class Mycrm_CompanyDetails_Model extends Mycrm_Base_Model {

	/**
	 * Function to get the Company Logo
	 * @return Mycrm_Image_Model instance
	 */
	public function getLogo(){
		$logoName = decode_html($this->get('logoname'));
		$logoModel = new Mycrm_Image_Model();
		if(!empty($logoName)) {
			$companyLogo = array();
			$companyLogo['imagepath'] = "test/logo/$logoName";
			$companyLogo['alt'] = $companyLogo['title'] = $companyLogo['imagename'] = $logoName;
			$logoModel->setData($companyLogo);
		}
		return $logoModel;
	}

    /**
     * Function to get the instance of the CompanyDetails model for a given organization id
     * @param <Number> $id
     * @return Mycrm_CompanyDetails_Model instance
     */
    public static function getInstanceById($id = 1) {
        $companyDetails = Mycrm_Cache::get('mycrm', 'organization');
        if (!$companyDetails) {
            $db = PearDatabase::getInstance();
            $sql = 'SELECT * FROM mycrm_organizationdetails WHERE organization_id=?';
            $params = array($id);
            $result = $db->pquery($sql, $params);
            $companyDetails = new self();
            if ($result && $db->num_rows($result) > 0) {
                $resultRow = $db->query_result_rowdata($result, 0);
                $companyDetails->setData($resultRow);
            }
            Mycrm_Cache::set('mycrm','organization',$companyDetails);
        }
        return $companyDetails;
    }

}