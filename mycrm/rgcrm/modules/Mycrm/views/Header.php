<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 * ************************************************************************************/

abstract class Mycrm_Header_View extends Mycrm_View_Controller {

	function __construct() {
		parent::__construct();
	}

	//Note : To get the right hook for immediate parent in PHP,
	// specially in case of deep hierarchy
	/*function preProcessParentTplName(Mycrm_Request $request) {
		return parent::preProcessTplName($request);
	}*/

	/**
	 * Function to determine file existence in relocated module folder (under mycrm6)
	 * @param String $fileuri
	 * @return Boolean
	 *
	 * Utility function to manage the backward compatible file load
	 * which are registered for 5.x modules (and now provided for 6.x as well).
	 */
	protected function checkFileUriInRelocatedMouldesFolder($fileuri) {
		list ($filename, $query) = array_pad(explode('?', $fileuri, 2), 2, null);

		// prefix the base lookup folder (relocated file).
		if (strpos($filename, 'modules') === 0) {
			$filename = $filename;
		}

        return file_exists($filename);
	}

	/**
	 * Function to get the list of Header Links
	 * @return <Array> - List of Mycrm_Link_Model instances
	 */
	function getHeaderLinks() {
		$appUniqueKey = vglobal('application_unique_key');
		$mycrmCurrentVersion = vglobal('mycrm_current_version');
		$site_URL = vglobal('site_URL');

		$userModel = Users_Record_Model::getCurrentUserModel();
		$userEmail = $userModel->get('email1');

		$headerLinks = array(
				// Note: This structure is expected to generate side-bar feedback button.
			array (
				'linktype' => 'HEADERLINK',
				'linklabel' => 'LBL_FEEDBACK',
				'linkurl' => "javascript:window.open('http://mycrm.com/products/crm/od-feedback/index.php?version=".$mycrmCurrentVersion.
					"&email=".$userEmail."&uid=".$appUniqueKey.
					"&ui=6','feedbackwin','height=400,width=550,top=200,left=300')",
				'linkicon' => 'info.png',
				'childlinks' => array(
					array (
						'linktype' => 'HEADERLINK',
						'linklabel' => 'LBL_DOCUMENTATION',
						'linkurl' => 'https://wiki.mycrm.com/mycrm6/index.php/Main_Page',
						'linkicon' => '',
						'target' => '_blank'
					),
					array (
						'linktype' => 'HEADERLINK',
						'linklabel' => 'LBL_VIDEO_TUTORIAL',
						'linkurl' => 'https://www.mycrm.com/crm/videos',
						'linkicon' => '',
						'target' => '_blank'
					),
					// Note: This structure is expected to generate side-bar feedback button.
					array (
						'linktype' => 'HEADERLINK',
						'linklabel' => 'LBL_FEEDBACK',
						'linkurl' => "javascript:window.open('http://mycrm.com/products/crm/od-feedback/index.php?version=".$mycrmCurrentVersion.
							"&email=".$userEmail."&uid=".$appUniqueKey.
							"&ui=6','feedbackwin','height=400,width=550,top=200,left=300')",
						'linkicon' => '',
					)
				)
			)
		);
		
		if($userModel->isAdminUser()) {
			$crmSettingsLink = array(
				'linktype' => 'HEADERLINK',
				'linklabel' => 'LBL_CRM_SETTINGS',
				'linkurl' => '',
				'linkicon' => 'setting.png',
				'childlinks' => array(
					array (
						'linktype' => 'HEADERLINK',
						'linklabel' => 'LBL_CRM_SETTINGS',
						'linkurl' => '?module=Mycrm&parent=Settings&view=Index',
						'linkicon' => '',
					),
                                        array(), // separator 
                                        array ( 
                                                'linktype' => 'HEADERLINK', 
                                                'linklabel' => 'LBL_MANAGE_USERS', 
                                                'linkurl' => '?module=Users&parent=Settings&view=List', 
                                                'linkicon' => '', 
                                       ),
				)
			);
			array_push($headerLinks, $crmSettingsLink);
		}
		$userPersonalSettingsLinks = array(
				'linktype' => 'HEADERLINK',
				'linklabel' => $userModel->getDisplayName(),
				'linkurl' => '',
				'linkicon' => '',
				'childlinks' => array(
					array (
						'linktype' => 'HEADERLINK',
						'linklabel' => 'LBL_MY_PREFERENCES',
						'linkurl' => $userModel->getPreferenceDetailViewUrl(),
						'linkicon' => '',
					),
					array(), // separator
					array (
						'linktype' => 'HEADERLINK',
						'linklabel' => 'LBL_SIGN_OUT',
						'linkurl' => '?module=Users&parent=Settings&action=Logout',
						'linkicon' => '',
					)
				)
			);
		array_push($headerLinks, $userPersonalSettingsLinks);
		$headerLinkInstances = array();

		$index = 0;
		foreach($headerLinks as  $headerLink) {
			$headerLinkInstance = Mycrm_Link_Model::getInstanceFromValues($headerLink);
			foreach($headerLink['childlinks'] as $childLink) {
				$headerLinkInstance->addChildLink(Mycrm_Link_Model::getInstanceFromValues($childLink));
			}
			$headerLinkInstances[$index++] = $headerLinkInstance;
		}

		// Fix for http://code.mycrm.com/mycrm/mycrmcrm/issues/49
		// Push HEADERLINKS to drop-down menu shown with username (last-one) as structured above.
		$lastindex = count($headerLinkInstances)-1;
		$headerLinks = Mycrm_Link_Model::getAllByType(Mycrm_Link::IGNORE_MODULE, array('HEADERLINK'));
		if ($headerLinks) $headerLinkInstances[$lastindex]->addChildLink(Mycrm_Link_Model::getInstanceFromValues(array())); // Separator
		foreach($headerLinks as $headerType => $headerLinks) {
			foreach($headerLinks as $headerLink) {
				$headerLinkInstances[$lastindex]->addChildLink(Mycrm_Link_Model::getInstanceFromLinkObject($headerLink));
			}
		}
		return $headerLinkInstances;
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Mycrm_Request $request
	 * @return <Array> - List of Mycrm_JsScript_Model instances
	 */
	function getHeaderScripts(Mycrm_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$headerScripts = Mycrm_Link_Model::getAllByType(Mycrm_Link::IGNORE_MODULE, array('HEADERSCRIPT'));
		foreach($headerScripts as $headerType => $headerScripts) {
			foreach($headerScripts as $headerScript) {
				if ($this->checkFileUriInRelocatedMouldesFolder($headerScript->linkurl)) {
					$headerScriptInstances[] = Mycrm_JsScript_Model::getInstanceFromLinkObject($headerScript);
				}
			}
		}
		return $headerScriptInstances;
	}

	/**
	 * Function to get the list of Css models to be included
	 * @param Mycrm_Request $request
	 * @return <Array> - List of Mycrm_CssScript_Model instances
	 */
	function getHeaderCss(Mycrm_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request);
		$headerCss = Mycrm_Link_Model::getAllByType(Mycrm_Link::IGNORE_MODULE, array('HEADERCSS'));
		$selectedThemeCssPath = Mycrm_Theme::getStylePath();
		//TODO : check the filename whether it is less or css and add relative less
		$isLessType = (strpos($selectedThemeCssPath, ".less") !== false)? true:false;
		$cssScriptModel = new Mycrm_CssScript_Model();
		$headerCssInstances[] = $cssScriptModel->set('href', $selectedThemeCssPath)
									->set('rel',
											$isLessType?
											Mycrm_CssScript_Model::LESS_REL :
											Mycrm_CssScript_Model::DEFAULT_REL);

		foreach($headerCss as $headerType => $cssLinks) {
			foreach($cssLinks as $cssLink) {
				if ($this->checkFileUriInRelocatedMouldesFolder($cssLink->linkurl)) {
					$headerCssInstances[] = Mycrm_CssScript_Model::getInstanceFromLinkObject($cssLink);
				}
			}
		}
		return $headerCssInstances;
	}

	/**
	 * Function to get the Announcement
	 * @return Mycrm_Base_Model - Announcement
	 */
	function getAnnouncement() {
		//$announcement = Mycrm_Cache::get('announcement', 'value');
		$model = new Mycrm_Base_Model();
		//if(!$announcement) {
			$announcement = get_announcements();
				//Mycrm_Cache::set('announcement', 'value', $announcement);
		//}
		return $model->set('announcement', $announcement);
	}

}
