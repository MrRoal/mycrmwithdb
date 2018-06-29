<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

include_once 'vtlib/Mycrm/Link.php';

/**
 * Mycrm Link Model Class
 */
class Mycrm_Link_Model extends Mycrm_Link {

	// Class variable to store the child links
	protected $childlinks = array();


	/**
	 * Function to get the value of a given property
	 * @param <String> $propertyName
	 * @return <Object>
	 * @throws Exception
	 */
	public function get($propertyName) {
		if(property_exists($this,$propertyName)){
			return $this->$propertyName;
		}
	}

	/**
	 * Function to set the value of a given property
	 * @param <String> $propertyName
	 * @param <Object> $propertyValue
	 * @return Mycrm_Link_Model instance
	 */
	public function set($propertyName, $propertyValue) {
		$this->$propertyName = $propertyValue;
		return $this;
	}

	/**
	 * Function to get the link url
	 * @return <String>
	 */
	public function getUrl() {
		return $this->convertToNativeLink();
	}

	/**
	 * Function to get the link label
	 * @return <String>
	 */
	public function getLabel() {
		return $this->linklabel;
	}

	/**
	 * Function to get the link type
	 * @return <String>
	 */
	public function getType() {
		return $this->linktype;
	}

	/**
	 * Function to get the link icon name
	 * @return <String>
	 */
	public function getIcon() {
		return $this->linkicon;
	}

	/**
	 * Function to check whether link has icon or not
	 * @return <Boolean> true/false
	 */
	public function isIconExists() {
		$linkIcon = $this->getIcon();
		if(empty($linkIcon)) {
			return false;
		}
		return true;
	}

	/**
	 * Function to retrieve the icon path for the link icon
	 * @return <String/Boolean> - returns image path if icon exits
	 *                              else returns false;
	 */
	public function getIconPath() {
		if(!$this->isIconExists()) {
			return false;
		}
		return Mycrm_Theme::getImagePath($this->getIcon());
	}

	/**
	 * Function to get the link id
	 * @return <Number>
	 */
	public function getId() {
		return $this->linkid;
	}

	/**
	 * Function to Add link to the child link list
	 * @param Mycrm_Link_Model $link - link model
	 * @result Mycrm_Link_Model - current Instance;
	 */
	public function addChildLink(Mycrm_Link_Model $link) {
		$this->childlinks[] = $link;
		return $this;
	}

	/**
	 * Function to get all the child links
	 * @result <array> - list of Mycrm_Link_Model instances
	 */
	public function getChildLinks() {
		//See if indexing is need depending only user selection
		return $this->childlinks;
	}

	/**
	 * Function to check whether the link model has any child links
	 * @return <Boolean> true/false
	 */
	public function hasChild() {
		(count($this->childlinks) > 0)? true : false;
	}

	public function isPageLoadLink() {
		$url = $this->get('linkurl');
		if(strpos($url, 'index') === 0){
			return true;
		}
		return false;
	}

	public function convertToNativeLink() {
		$url = $this->get('linkurl');
		if(empty($url)){
			return $url;
		}
		//Check if the link is not javascript
		if(!$this->isPageLoadLink()){
           //To convert single quotes and double quotes
           $url = Mycrm_Util_Helper::toSafeHTML($url);
           return $url;
		}

		$module = false;
		$sourceModule = false;
		$sourceRecord = false;
		$parametersParts = explode('&',$url);
		foreach($parametersParts as $index => $keyValue){
			$urlParts = explode('=', $keyValue);
			$key = $urlParts[0];
			$value = $urlParts[1];
			if(strcmp($key, 'module')== 0){
				$module = $value;
			}

			if(strcmp($key,'action')== 0) {
				if(strpos($value,'View')) {
					$value = str_replace('View', '', $value);
					$key = 'view';
				}
			}
			if(strcmp($key, 'return_module')== 0) {
				$key = 'sourceModule';
				//Indicating that it is an relation operation
				$parametersParts[] = 'relationOperation=true';
			}
			if(strcmp($key, 'return_id')== 0) {
				$key = 'sourceRecord';
			}

			if(strcmp($key, 'sourceRecord') == 0) {
				$sourceRecord = $value;
			}

			if(strcmp($key, 'sourceModule') == 0) {
				$sourceModule = $value;

			}
			$newUrlParts = array();
			array_push($newUrlParts, $key);
			array_push($newUrlParts, $value);
			$parametersParts[$index] = implode('=', $newUrlParts);
		}

		//to append the reference field in one to many relation
		if(!empty($module) && !empty ($sourceModule) && !empty($sourceRecord)) {
			$sourceModuleModel = Mycrm_Module_Model::getInstance($sourceModule);
			$relatedModuleModel = Mycrm_Module_Model::getInstance($module);
			$relationModel = Mycrm_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
			if($relationModel->isDirectRelation()){
				$fieldList = $relatedModuleModel->getFields();
				foreach($fieldList as $fieldName=>$fieldModel) {
					if($fieldModel->getFieldDataType() == Mycrm_Field_Model::REFERENCE_TYPE) {
						$referenceList = $fieldModel->getReferenceList();
						if(in_array($sourceModuleModel->get('name'), $referenceList)) {
							$parametersParts[] = $fieldModel->get('name').'='.$sourceRecord;
						}
					}
				}
			}
		}

        $url = implode('&', $parametersParts);
       //To convert single quotes and double quotes
        $url = Mycrm_Util_Helper::toSafeHTML($url);
		return  $url;
	}

	/**
	 * Function to get the instance of Mycrm Link Model from the given array of key-value mapping
	 * @param <Array> $valueMap
	 * @return Mycrm_Link_Model instance
	 */
	public static function getInstanceFromValues($valueMap) {
		$linkModel = new self();
		$linkModel->initialize($valueMap);

		// To set other properties for Link Model
		foreach($valueMap as $property => $value) {
			if(!isset($linkModel->$property)) {
				$linkModel->$property = $value;
			}
		}

		return $linkModel;
	}

	/**
	 * Function to get the instance of Mycrm Link Model from a given Mycrm_Link object
	 * @param Mycrm_Link $linkObj
	 * @return Mycrm_Link_Model instance
	 */
	public static function getInstanceFromLinkObject (Mycrm_Link $linkObj) {
		$objectProperties = get_object_vars($linkObj);
		$linkModel = new self();
		foreach($objectProperties as $properName=>$propertyValue) {
			$linkModel->$properName = $propertyValue;
		}
		return $linkModel;
	}

	/**
	 * Function to get all the Mycrm Link Models for a module of the given list of link types
	 * @param <Number> $tabid
	 * @param <Array> $type
	 * @param <Array> $parameters
	 * @return <Array> - List of Mycrm_Link_Model instances
	 */
	public static function getAllByType($tabid, $type = false, $parameters = false) {
		$links = Mycrm_Cache::get('links-'.$tabid, $type);
		if(!$links) {
			$links = parent::getAllByType($tabid, $type, $parameters);
			Mycrm_Cache::set('links-'.$tabid, $type, $links);
		}

		$linkModels = array();
		foreach($links as $linkType => $linkObjects) {
			foreach($linkObjects as $linkObject) {
				$linkModels[$linkType][] = self::getInstanceFromLinkObject($linkObject);
			}
		}

        if (!is_array($type)) {
			$type = array($type);
		}

		$diffTypes = array_diff($type, array_keys($linkModels));
		foreach ($diffTypes as $linkType) {
			$linkModels[$linkType] = array();
		}

		return $linkModels;
	}

	/**
	* Function to get the relatedModuleName
	* @return <String>
	*/
	public function getRelatedModuleName() {
		return $this->relatedModuleName;
	}
}