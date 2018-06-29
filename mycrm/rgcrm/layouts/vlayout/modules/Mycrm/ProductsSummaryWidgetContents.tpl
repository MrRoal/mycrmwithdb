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
	{foreach item=HEADER from=$RELATED_HEADERS}
		{if $HEADER->get('label') eq "Product Name"}
			{assign var=PRODUCT_NAME_HEADER value={vtranslate($HEADER->get('label'),$MODULE)}}
		{elseif $HEADER->get('label') eq "Unit Price"}
			{assign var=PRODUCT_UNITPRICE_HEADER value={vtranslate($HEADER->get('label'),$MODULE)}}
		{/if}
	{/foreach}
	<div class="row-fluid">		
		<span class="span7">
			<strong>{$PRODUCT_NAME_HEADER}</strong>
		</span>
		<span class="span4">
			<span class="pull-right">
				<strong>{$PRODUCT_UNITPRICE_HEADER}</strong>
			</span>
		</span>
	</div>
	{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
		<div class="recentActivitiesContainer">
			<ul class="unstyled">
				<li>
					<div class="row-fluid">
						<span class="span7 textOverflowEllipsis">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('productname')}">
								{$RELATED_RECORD->getDisplayValue('productname')}
							</a>
						</span>
						<span class="span4">
							<span class="pull-right">{$RELATED_RECORD->getDisplayValue('unit_price')}</span>
						</span>
					</div>
				</li>
			</ul>
		</div>
	{/foreach}
	{assign var=NUMBER_OF_RECORDS value=count($RELATED_RECORDS)}
	{if $NUMBER_OF_RECORDS eq 5}
		<div class="row-fluid">
			<div class="pull-right">
				<a class="moreRecentProducts cursorPointer">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
			</div>
		</div>
	{/if}
{/strip}