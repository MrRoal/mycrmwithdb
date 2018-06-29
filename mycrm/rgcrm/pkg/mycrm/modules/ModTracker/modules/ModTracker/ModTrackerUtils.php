<?php
require_once('include/utils/utils.php');
require_once 'vtlib/Mycrm/Module.php';
require_once dirname(__FILE__) .'/ModTracker.php';
class ModTrackerUtils
{
	static function modTrac_changeModuleVisibility($tabid,$status) {
		if($status == 'module_disable'){
			ModTracker::disableTrackingForModule($tabid);
		} else {
			ModTracker::enableTrackingForModule($tabid);
		}
	}
	function modTrac_getModuleinfo(){
		global $adb;
		$query = $adb->pquery("SELECT mycrm_modtracker_tabs.visible,mycrm_tab.name,mycrm_tab.tabid
								FROM mycrm_tab
								LEFT JOIN mycrm_modtracker_tabs ON mycrm_modtracker_tabs.tabid = mycrm_tab.tabid
								WHERE mycrm_tab.isentitytype = 1 AND mycrm_tab.name NOT IN('Emails', 'Webmails')",array());
		$rows = $adb->num_rows($query);

        for($i = 0;$i < $rows; $i++){
			$infomodules[$i]['tabid']  = $adb->query_result($query,$i,'tabid');
			$infomodules[$i]['visible']  = $adb->query_result($query,$i,'visible');
			$infomodules[$i]['name'] = $adb->query_result($query,$i,'name');
		}

		return $infomodules;
	}
}
?>
