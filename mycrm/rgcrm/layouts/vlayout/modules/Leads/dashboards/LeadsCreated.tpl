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

<div class="dashboardWidgetHeader">
	{include file="dashboards/WidgetHeader.tpl"|@vtemplate_path:$MODULE_NAME SETTING_EXIST=true}
	<div class="row-fluid filterContainer hide" style="position:absolute;z-index:100001">
		<div class="row-fluid">
			<span class="span5">
				<span class="pull-right">
					{vtranslate('Created Time', $MODULE_NAME)} &nbsp; {vtranslate('LBL_BETWEEN', $MODULE_NAME)}
				</span>
			</span>
			<span class="span4">
				<input type="text" name="createdtime" class="dateRange widgetFilter" />
			</span>
		</div>
		<div class="row-fluid">
			<span class="span5">
				<span class="pull-right">
					{vtranslate('Assigned To', $MODULE_NAME)}
				</span>
			</span>
			<span class="span4">
				{assign var=CURRENT_USER_ID value=$CURRENTUSER->getId()}
				<select class="widgetFilter" name="owner">
					<option value="">{vtranslate('LBL_ALL', $MODULE_NAME)}</option>
					{foreach key=USER_ID item=USER_NAME from=$ACCESSIBLE_USERS}
					<option value="{$USER_ID}">
						{if $USER_ID eq $CURRENTUSER->getId()}
							{vtranslate('LBL_MINE',$MODULE_NAME)}
						{else}
							{$USER_NAME}
						{/if}
					</option>
					{/foreach}
				</select>
			</span>
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file="dashboards/DashBoardWidgetContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>