{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the mycrm CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  mycrm CRM Open Source
   * The Initial Developer of the Original Code is mycrm.
   * Portions created by mycrm are Copyright (C) mycrm.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
{if count($DATA) gt 0 }
	<input class="widgetData" type=hidden value='{Mycrm_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATA))}' />
	<div class="widgetChartContainer" style="height:250px;width:85%"></div>
{else}
	<span class="noDataMsg">
		{vtranslate('LBL_EQ_ZERO')} {vtranslate($MODULE_NAME, $MODULE_NAME)} {vtranslate('LBL_MATCHED_THIS_CRITERIA')}
	</span>
{/if}
{/strip}