<?php
/*+**********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Picklist_Module_Model extends Mycrm_Module_Model {

    public function getPickListTableName($fieldName) {
        return 'mycrm_'.$fieldName;
    }

    public function getFieldsByType($type) {
		$presence = array('0','2');

        $fieldModels = parent::getFieldsByType($type);
        $fields = array();
        foreach($fieldModels as $fieldName=>$fieldModel) {
            if(($fieldModel->get('displaytype') != '1' && $fieldName != 'salutationtype') || !in_array($fieldModel->get('presence'),$presence)) {
                continue;
            }
            $fields[$fieldName] = Settings_Picklist_Field_Model::getInstanceFromFieldObject($fieldModel);
        }
        return $fields;
    }

    public function addPickListValues($fieldModel, $newValue, $rolesSelected = array()) {
        $db = PearDatabase::getInstance();
        $pickListFieldName = $fieldModel->getName();
        $id = $db->getUniqueID("mycrm_$pickListFieldName");
        vimport('~~/include/ComboUtil.php');
        $picklist_valueid = getUniquePicklistID();
		$tableName = 'mycrm_'.$pickListFieldName;
		$maxSeqQuery = 'SELECT max(sortorderid) as maxsequence FROM '.$tableName;
		$result = $db->pquery($maxSeqQuery, array());
		$sequence = $db->query_result($result,0,'maxsequence');

        if($fieldModel->isRoleBased()) {
            $sql = 'INSERT INTO '.$tableName.' VALUES (?,?,?,?,?)';
            $db->pquery($sql, array($id, $newValue, 1, $picklist_valueid,++$sequence));
        }else{
            $sql = 'INSERT INTO '.$tableName.' VALUES (?,?,?,?)';
            $db->pquery($sql, array($id, $newValue, ++$sequence, 1));
        }

        if($fieldModel->isRoleBased() && !empty($rolesSelected)) {
            $sql = "select picklistid from mycrm_picklist where name=?";
            $result = $db->pquery($sql, array($pickListFieldName));
            $picklistid = $db->query_result($result,0,"picklistid");
            //add the picklist values to the selected roles
            for($j=0;$j<count($rolesSelected);$j++){
                $roleid = $rolesSelected[$j];

                $sql ="SELECT max(sortid)+1 as sortid
                       FROM mycrm_role2picklist left join mycrm_$pickListFieldName
                           on mycrm_$pickListFieldName.picklist_valueid=mycrm_role2picklist.picklistvalueid
                       WHERE roleid=? and picklistid=?";
                $sortid = $db->query_result($db->pquery($sql, array($roleid, $picklistid)),0,'sortid');

                $sql = "insert into mycrm_role2picklist values(?,?,?,?)";
                $db->pquery($sql, array($roleid, $picklist_valueid, $picklistid, $sortid));
            }

        }
        return array('picklistValueId' => $picklist_valueid,
					'id' => $id);
    }

    public function renamePickListValues($pickListFieldName, $oldValue, $newValue, $moduleName, $id) {
		$db = PearDatabase::getInstance();

		$query = 'SELECT tablename,columnname FROM mycrm_field WHERE fieldname=? and presence IN (0,2)';
		$result = $db->pquery($query, array($pickListFieldName));
		$num_rows = $db->num_rows($result);

		//As older look utf8 characters are pushed as html-entities,and in new utf8 characters are pushed to database
		//so we are checking for both the values
		$primaryKey = Mycrm_Util_Helper::getPickListId($pickListFieldName);
		
		$query = 'UPDATE ' . $this->getPickListTableName($pickListFieldName) . ' SET ' . $pickListFieldName . '=? WHERE '.$primaryKey.' = ?';
		$db->pquery($query, array($newValue, $id));

		for ($i = 0; $i < $num_rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$tableName = $row['tablename'];
			$columnName = $row['columnname'];
			$query = 'UPDATE ' . $tableName . ' SET ' . $columnName . '=? WHERE ' . $columnName . '=?';
			$db->pquery($query, array($newValue, $oldValue));
		}

		$query = "UPDATE mycrm_field SET defaultvalue=? WHERE defaultvalue=? AND columnname=?";
		$db->pquery($query, array($newValue, $oldValue, $columnName));

		vimport('~~/include/utils/CommonUtils.php');

		$query = "UPDATE mycrm_picklist_dependency SET sourcevalue=? WHERE sourcevalue=? AND sourcefield=?";
		$db->pquery($query, array($newValue, $oldValue, $pickListFieldName));
				
		$em = new VTEventsManager($db);
		$data = array();
		$data['fieldname'] = $pickListFieldName;
		$data['oldvalue'] = $oldValue;
		$data['newvalue'] = $newValue;
		$data['module'] = $moduleName;
		$em->triggerEvent('mycrm.picklist.afterrename', $data);
		
		return true;
	}

    public function remove($pickListFieldName , $valueToDeleteId, $replaceValueId , $moduleName) {
        $db = PearDatabase::getInstance();
		if(!is_array($valueToDeleteId)) {
			$valueToDeleteId = array($valueToDeleteId);
		}
		$primaryKey = Mycrm_Util_Helper::getPickListId($pickListFieldName);
		
		$pickListValues = array();
		$valuesOfDeleteIds = "SELECT $pickListFieldName FROM ".$this->getPickListTableName($pickListFieldName)." WHERE $primaryKey IN (".generateQuestionMarks($valueToDeleteId).")";
		$pickListValuesResult = $db->pquery($valuesOfDeleteIds,array($valueToDeleteId));
		$num_rows = $db->num_rows($pickListValuesResult);
		for($i=0;$i<$num_rows;$i++) {
			$pickListValues[] = decode_html($db->query_result($pickListValuesResult,$i,$pickListFieldName)); 
		}
		
		$replaceValueQuery = $db->pquery("SELECT $pickListFieldName FROM ".$this->getPickListTableName($pickListFieldName)." WHERE $primaryKey IN (".generateQuestionMarks($replaceValueId).")",array($replaceValueId));
		$replaceValue = decode_html($db->query_result($replaceValueQuery,0,$pickListFieldName));
		
		//As older look utf8 characters are pushed as html-entities,and in new utf8 characters are pushed to database
		//so we are checking for both the values
                $encodedValueToDelete = array(); 
		foreach ($pickListValues as $key => $value) {
			$encodedValueToDelete[$key]  = Mycrm_Util_Helper::toSafeHTML($value);
		}
		$mergedValuesToDelete = array_merge($pickListValues, $encodedValueToDelete);
		
        $fieldModel = Settings_Picklist_Field_Model::getInstance($pickListFieldName,$this);
        //if role based then we need to delete all the values in role based picklist
        if($fieldModel->isRoleBased()) {
            $picklistValueIdToDelete = array();
            $query = 'SELECT picklist_valueid FROM '.$this->getPickListTableName($pickListFieldName).
                     ' WHERE '.$primaryKey.' IN ('.generateQuestionMarks($valueToDeleteId).')';
            $result = $db->pquery($query,$valueToDeleteId);
            $num_rows = $db->num_rows($result);
            for($i=0;$i<$num_rows;$i++) {
                $picklistValueIdToDelete[] = $db->query_result($result,$i,'picklist_valueid');
            }
            $query = 'DELETE FROM mycrm_role2picklist WHERE picklistvalueid IN ('.generateQuestionMarks($picklistValueIdToDelete).')';
            $db->pquery($query,$picklistValueIdToDelete);
        }

        $query = 'DELETE FROM '. $this->getPickListTableName($pickListFieldName).
					' WHERE '.$primaryKey.' IN ('.  generateQuestionMarks($valueToDeleteId).')';
        $db->pquery($query,$valueToDeleteId);

        vimport('~~/include/utils/CommonUtils.php');
        $tabId = getTabId($moduleName);
        $query = 'DELETE FROM mycrm_picklist_dependency WHERE sourcevalue IN ('. generateQuestionMarks($pickListValues) .')'.
				' AND sourcefield=?';
		$params = array();
		array_push($params, $pickListValues);
		array_push($params, $pickListFieldName);
        $db->pquery($query, $params);

        $query='SELECT tablename,columnname FROM mycrm_field WHERE fieldname=? AND presence in (0,2)';
        $result = $db->pquery($query, array($pickListFieldName));
        $num_row = $db->num_rows($result);

        for($i=0; $i<$num_row; $i++) {
            $row = $db->query_result_rowdata($result, $i);
            $tableName = $row['tablename'];
            $columnName = $row['columnname'];

            $query = 'UPDATE '.$tableName.' SET '.$columnName.'=? WHERE '.$columnName.' IN ('.  generateQuestionMarks($pickListValues).')';
			$params = array($replaceValue);
			array_push($params, $pickListValues);
            $db->pquery($query, $params);
        }
		
		$query = 'UPDATE mycrm_field SET defaultvalue=? WHERE defaultvalue IN ('. generateQuestionMarks($pickListValues) .') AND columnname=?';
		$params = array($replaceValue);
		array_push($params, $pickListValues);
		array_push($params, $columnName);
		$db->pquery($query, $params);
		
		$em = new VTEventsManager($db);
		$data = array();
		$data['fieldname'] = $pickListFieldName;
		$data['valuetodelete'] = $pickListValues;
		$data['replacevalue'] = $replaceValue;
		$data['module'] = $moduleName;
		$em->triggerEvent('mycrm.picklist.afterdelete', $data);

        return true;
    }

    public function enableOrDisableValuesForRole($picklistFieldName, $valuesToEnables, $valuesToDisable, $roleIdList) {
        $db = PearDatabase::getInstance();
        //To disable die On error since we will be doing insert without chekcing
        $dieOnErrorOldValue = $db->dieOnError;
        $db->dieOnError = false;

		$sql = "select picklistid from mycrm_picklist where name=?";
		$result = $db->pquery($sql, array($picklistFieldName));
		$picklistid = $db->query_result($result,0,"picklistid");
		
		$primaryKey = Mycrm_Util_Helper::getPickListId($picklistFieldName);

        $pickListValueList = array_merge($valuesToEnables,$valuesToDisable);
        $pickListValueDetails = array();
        $query = 'SELECT picklist_valueid,'. $picklistFieldName.', '.$primaryKey.
                 ' FROM '.$this->getPickListTableName($picklistFieldName).
                 ' WHERE '.$primaryKey .' IN ('.  generateQuestionMarks($pickListValueList).')';
		$params = array();
		array_push($params, $pickListValueList);

		$result = $db->pquery($query, $params);
		$num_rows = $db->num_rows($result);

        for($i=0; $i<$num_rows; $i++) {
            $row = $db->query_result_rowdata($result,$i);

            $pickListValueDetails[decode_html($row[$primaryKey])] =array('picklistvalueid'=>$row['picklist_valueid'],
																	'picklistid'=>$picklistid);
        }
		$insertValueList = array();
        $deleteValueList = array();
        foreach($roleIdList as $roleId) {
            foreach($valuesToEnables  as $picklistValue) {
		        $valueDetail = $pickListValueDetails[$picklistValue];
				if(empty($valueDetail)){
					 $valueDetail = $pickListValueDetails[Mycrm_Util_Helper::toSafeHTML($picklistValue)];
				}
                $pickListValueId = $valueDetail['picklistvalueid'];
                $picklistId = $valueDetail['picklistid'];
                $insertValueList[] = '("'.$roleId.'","'.$pickListValueId.'","'.$picklistId.'")';
            }

            foreach($valuesToDisable as $picklistValue) {
                $valueDetail = $pickListValueDetails[$picklistValue];
				if(empty($valueDetail)){
					 $valueDetail = $pickListValueDetails[Mycrm_Util_Helper::toSafeHTML($picklistValue)];
				}
                $pickListValueId = $valueDetail['picklistvalueid'];
                $picklistId = $valueDetail['picklistid'];
                $deleteValueList[] = ' ( roleid = "'.$roleId.'" AND '.'picklistvalueid = "'.$pickListValueId.'") ';
            }
        }
		$query = 'INSERT IGNORE INTO mycrm_role2picklist (roleid,picklistvalueid,picklistid) VALUES '.implode(',',$insertValueList);
        $result = $db->pquery($query,array());

		$deleteQuery = 'DELETE FROM mycrm_role2picklist WHERE '.implode(' OR ',$deleteValueList);

		$result = $db->pquery($deleteQuery,array());

        //retaining to older value
        $db->dieOnError = $dieOnErrorOldValue;

    }

    public function updateSequence($pickListFieldName , $picklistValues) {
        $db = PearDatabase::getInstance();

		$primaryKey = Mycrm_Util_Helper::getPickListId($pickListFieldName);
		
        $query = 'UPDATE '.$this->getPickListTableName($pickListFieldName).' SET sortorderid = CASE ';
        foreach($picklistValues as $values => $sequence) {
            $query .= ' WHEN '.$primaryKey.'="'.$values.'" THEN "'.$sequence.'"';
        }
		$query .= ' END';
        $db->pquery($query, array());
    }


    public static function getPicklistSupportedModules() {
         $db = PearDatabase::getInstance();

        // vtlib customization: Ignore disabled modules.
        $query = 'SELECT distinct mycrm_tab.tablabel, mycrm_tab.name as tabname
                  FROM mycrm_tab
                        inner join mycrm_field on mycrm_tab.tabid=mycrm_field.tabid
                  WHERE uitype IN (15,33,16) and mycrm_field.tabid NOT IN (29,10)  and mycrm_tab.presence != 1 and mycrm_field.presence in (0,2)
                  ORDER BY mycrm_tab.tabid ASC';
        // END
        $result = $db->pquery($query, array());

        $modulesModelsList = array();
        while($row = $db->fetch_array($result)){
            $moduleLabel = $row['tablabel'];
            $moduleName  = $row['tabname'];
            $instance = new self();
            $instance->name = $moduleName;
            $instance->label = $moduleLabel;
            $modulesModelsList[] = $instance;
        }
        return $modulesModelsList;
    }


    /**
	 * Static Function to get the instance of Mycrm Module Model for the given id or name
	 * @param mixed id or name of the module
	 */
	public static function getInstance($value) {
		//TODO : add caching
		$instance = false;
		    $moduleObject = parent::getInstance($value);
		    if($moduleObject) {
			$instance = self::getInstanceFromModuleObject($moduleObject);
		    }
		return $instance;
	}

	/**
	 * Function to get the instance of Mycrm Module Model from a given Mycrm_Module object
	 * @param Mycrm_Module $moduleObj
	 * @return Mycrm_Module_Model instance
	 */
	public static function getInstanceFromModuleObject(Mycrm_Module $moduleObj){
		$objectProperties = get_object_vars($moduleObj);
		$moduleModel = new self();
		foreach($objectProperties as $properName=>$propertyValue){
			$moduleModel->$properName = $propertyValue;
		}
		return $moduleModel;
	}
}