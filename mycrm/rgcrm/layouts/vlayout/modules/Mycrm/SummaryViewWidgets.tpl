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
{if !empty($MODULE_SUMMARY)}
	<div class="row-fluid">
		<div class="span7">
			<div class="summaryView row-fluid">
			{$MODULE_SUMMARY}
			</div>
{/if}
			{foreach item=DETAIL_VIEW_WIDGET from=$DETAILVIEW_LINKS['DETAILVIEWWIDGET'] name=count}
				{if $smarty.foreach.count.index % 2 == 0}
					<div class="summaryWidgetContainer">
						<div class="widgetContainer_{$smarty.foreach.count.index}" data-url="{$DETAIL_VIEW_WIDGET->getUrl()}" data-name="{$DETAIL_VIEW_WIDGET->getLabel()}">
							<div class="widget_header row-fluid">
								<span class="span8 margin0px"><h4>{vtranslate($DETAIL_VIEW_WIDGET->getLabel(),$MODULE_NAME)}</h4></span>
							</div>
							<div class="widget_contents">
							</div>
						</div>
					</div>
				{/if}
			{/foreach}
		</div>
		<div class="span5" style="overflow: hidden">
			<div id="relatedActivities">
				{$RELATED_ACTIVITIES}
			</div>
			{foreach item=DETAIL_VIEW_WIDGET from=$DETAILVIEW_LINKS['DETAILVIEWWIDGET'] name=count}
				{if $smarty.foreach.count.index % 2 != 0}
					<div class="summaryWidgetContainer">
						<div class="widgetContainer_{$smarty.foreach.count.index}" data-url="{$DETAIL_VIEW_WIDGET->getUrl()}" data-name="{$DETAIL_VIEW_WIDGET->getLabel()}">
							<div class="widget_header row-fluid">
								<span class="span8 margin0px"><h4>{vtranslate($DETAIL_VIEW_WIDGET->getLabel(),$MODULE_NAME)}</h4></span>
							</div>
							<div class="widget_contents">
							</div>
						</div>
					</div>
				{/if}
			{/foreach}
		</div>
	</div>
{/strip}