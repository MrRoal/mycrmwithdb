<?php
/*********************************************************************************
** The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
*
 ********************************************************************************/

require_once('config.php');
require_once('vtlib/Mycrm/Cron.php');
require_once('config.inc.php');
global $mod_strings, $app_strings, $current_language;
global $theme;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$smarty = new mycrmCRM_Smarty;
if(isset($_REQUEST['record']) && $_REQUEST['record']!='') {
    $id = $_REQUEST['record'];
    $cronTask = Mycrm_cron::getInstanceById($id);
    $label = getTranslatedString($cronTask->getName(),$cronTask->getModule());
    $cron_status = $cronTask->getStatus();
    $cron_freq =  $cronTask->getFrequency();
    $cron_desc = $cronTask->getDescription();
    $cron = Array();
    $cron['label'] = $label;
    if($cron_freq/(60*60)>1 && is_int($cron_freq/(60*60))){
        $cron['frequency']=(int)($cron_freq/(60*60));
        $cron['time'] = 'hour';
    }
    else{
        $cron['frequency']=(int)($cron_freq/60);
        $cron['time'] = 'min';
    }
    $cron['status'] = $cron_status;
    $cron['description'] = $cron_desc;
    $cron['id']=$id;


    $smarty->assign("CRON_DETAILS",$cron);
    $smarty->assign("MOD", return_module_language($current_language,'CronTasks'));
    $smarty->assign("THEME", $theme);
    $smarty->assign("IMAGE_PATH",$image_path);
    $smarty->assign("APP", $app_strings);
    $smarty->assign("CMOD", $mod_strings);
    $smarty->assign("MIN_CRON_FREQUENCY", getMinimumCronFrequency());
    $smarty->display("modules/CronTasks/EditCron.tpl");
}
else {
    header("Location:index.php?module=CronTasks&action=ListCronJobs&directmode=ajax");
}
?>
