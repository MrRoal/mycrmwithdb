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
<span class="pull-right listViewActions">
	<select class="chzn-select span3 pickListSupportedModules">
		<option value="">{vtranslate('LBL_ALL', $QUALIFIED_MODULE)}</option>
		{foreach item=MODULE_MODEL from=$PICKLIST_MODULES_LIST}
			{assign var=MODULE_NAME value=$MODULE_MODEL->get('name')}
			<option value="{$MODULE_NAME}" {if $MODULE_NAME eq $FOR_MODULE} selected {/if}>
				{if $MODULE_MODEL->get('label') eq 'Calendar'}
					{vtranslate('LBL_TASK', $MODULE_MODEL->get('label'))}
				{else}
					{vtranslate($MODULE_MODEL->get('label'), $MODULE_MODEL->get('label'))}
				{/if}
			</option>
		{/foreach}
	</select>
</span>
{/strip}