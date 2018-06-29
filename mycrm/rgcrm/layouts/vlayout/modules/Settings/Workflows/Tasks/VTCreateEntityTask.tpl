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
		<div class="span3" style="position:relative;top:4px;">
			<strong>{vtranslate('LBL_MODULES_TO_CREATE_RECORD',$QUALIFIED_MODULE)}
				<span class="redColor">*</span>
			</strong>
		</div>
		<div class="span6">
			{assign var=RELATED_MODULES_INFO value=$WORKFLOW_MODEL->getDependentModules()}
			{assign var=RELATED_MODULES value=$RELATED_MODULES_INFO|array_keys}
			{assign var=RELATED_MODULE_MODEL_NAME value=$TASK_OBJECT->entity_type}
			
			<select class="chzn-select" id="createEntityModule" name="entity_type" data-validation-engine='validate[required]'>
				<option value="">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
				{foreach from=$RELATED_MODULES item=MODULE}
					<option {if $TASK_OBJECT->entity_type eq $MODULE} selected="" {/if} value="{$MODULE}">{vtranslate($MODULE,$MODULE)}</option>
				{/foreach}	
			</select>
		</div>
	</div><br>
	<div id="addCreateEntityContainer">
		{include file="CreateEntity.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
	</div>
{/strip}