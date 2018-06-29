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
	{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
		<div class="recentActivitiesContainer">
			<ul class="unstyled">
				<li>
					<div>
						<div class="textOverflowEllipsis width27em">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" title="{$RELATED_RECORD->getDisplayValue('ticket_title')}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}">
								{$RELATED_RECORD->getDisplayValue('ticket_title')}
							</a>
						</div>
						<div>{vtranslate('LBL_TICKET_PRIORITY',$MODULE)} : <strong>{$RELATED_RECORD->getDisplayValue('ticketpriorities')}</strong></div>
						{assign var=DESCRIPTION value="{$RELATED_RECORD->getDescriptionValue()}"}
						{if !empty($DESCRIPTION)}
							<div class="row-fluid">
								<span class="span8 textOverflowEllipsis width27em">{$DESCRIPTION}</span>
								<span class="span3"><a href="{$RELATED_RECORD->getDetailViewUrl()}">{vtranslate('LBL_MORE',$MODULE)}</a></span>
							</div>
						{/if}
					</div>
				</li>
			</ul>
		</div>
	{/foreach}
	{assign var=NUMBER_OF_RECORDS value=count($RELATED_RECORDS)}
	{if $NUMBER_OF_RECORDS eq 5}
		<div class="row-fluid">
			<div class="pull-right">
				<a class="moreRecentTickets cursorPointer">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
			</div>
		</div>
	{/if}
{/strip}