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
	<div class="row-fluid">
		<div class="span2">{vtranslate('LBL_METHOD_NAME',$QUALIFIED_MODULE)} :</div>
		<div class="span8">
			{assign var=ENTITY_METHODS value=$WORKFLOW_MODEL->getEntityMethods()}
			{if empty($ENTITY_METHODS)} 
				<div class="alert alert-info">{vtranslate('LBL_NO_METHOD_IS_AVAILABLE_FOR_THIS_MODULE',$QUALIFIED_MODULE)}</div>
			{else}	
				<select name="methodName" class="chzn-select">
					{foreach from=$ENTITY_METHODS item=METHOD}
						<option {if $TASK_OBJECT->methodName eq $METHOD}selected="" {/if} value="{$METHOD}">{vtranslate($METHOD,$QUALIFIED_MODULE)}</option>
					{/foreach}
				</select>
			{/if}
		</div>
	</div>
{/strip}	