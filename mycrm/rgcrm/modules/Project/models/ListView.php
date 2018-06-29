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
 * ListView Model Class for Project module
 */
class Project_ListView_Model extends Mycrm_ListView_Model {

	/**
	 * Function to get the list of listview links
	 * @param <Array> $linkParams Parameters to be replaced in the link template
	 * @return <Array> - an array of Mycrm_Link_Model instances
	 */
	public function getListViewLinks($linkParams) {
		$links = parent::getListViewLinks($linkParams);

		$quickLinks = array(
			array(
				'linktype' => 'LISTVIEWQUICK',
				'linklabel' => 'Tasks List',
				'linkurl' => $this->getModule()->getDefaultUrl(),
				'linkicon' => ''
			),
		);
		foreach($quickLinks as $quickLink) {
			$links['LISTVIEWQUICK'][] = Mycrm_Link_Model::getInstanceFromValues($quickLink);
		}

		return $links;
	}

}