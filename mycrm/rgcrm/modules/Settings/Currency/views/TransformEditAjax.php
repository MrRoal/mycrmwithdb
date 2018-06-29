<?php

/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Currency_TransformEditAjax_View extends Settings_Mycrm_IndexAjax_View {
    
    public function process(Mycrm_Request $request) {
        $record = $request->get('record');
        
        $currencyList = Settings_Currency_Record_Model::getAll($record);
        
        $qualifiedName = $request->getModule(false);
        $viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE',$qualifiedName);
        $viewer->assign('CURRENCY_LIST',$currencyList);
        $viewer->assign('RECORD_MODEL',  Settings_Currency_Record_Model::getInstance($record));
        echo $viewer->view('TransformEdit.tpl', $qualifiedName, true);
    }
}