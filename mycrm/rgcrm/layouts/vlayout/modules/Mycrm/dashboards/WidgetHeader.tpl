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

{foreach key=index item=cssModel from=$STYLES}
	<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
{/foreach}
{foreach key=index item=jsModel from=$SCRIPTS}
	<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}

<table width="100%" cellspacing="0" cellpadding="0">
	<tbody>
		<tr>
			<td class="span5">
				<div class="dashboardTitle textOverflowEllipsis" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}" style="width: 15em;"><b>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</b></div>
			</td>
			<td class="refresh span2" align="right">
				<span style="position:relative;">&nbsp;</span>
			</td>
			<td class="widgeticons span5" align="right">
				<div class="box pull-right">
					{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
				</div>
			</td>
		</tr>
	</tbody>
</table>
