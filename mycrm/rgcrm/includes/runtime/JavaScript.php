<?php
/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

class Mycrm_JavaScript extends Mycrm_Viewer {

	/**
	 * Function to get the path of a given style sheet or default style sheet
	 * @param <String> $fileName
	 * @return <string / Boolean> - file path , false if not exists
	 */
	public static function getFilePath($fileName=''){
		if(empty($fileName)) {
			return false;
		}
		$filePath =  self::getBaseJavaScriptPath() . '/' . $fileName;
		$completeFilePath = Mycrm_Loader::resolveNameToPath('~'.$filePath);

		if(file_exists($completeFilePath)){
			return $filePath;
		}
		return false;
	}

	/**
	 * Function to get the Base Theme Path, until theme folder not selected theme folder
	 * @return <string> - theme folder
	 */
	public static function getBaseJavaScriptPath(){
		return 'layouts'. '/' . self::getLayoutName();
	}
}