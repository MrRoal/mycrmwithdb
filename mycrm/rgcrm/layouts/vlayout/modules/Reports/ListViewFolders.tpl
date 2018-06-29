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
<span class="customFilterMainSpan btn-group">
	<select id="customFilter"  style="width:350px;">
		<optgroup id="foldersBlock" label="{vtranslate('LBL_FOLDERS', $MODULE)}">
			<option value="All" data-id="All">{vtranslate('LBL_ALL_REPORTS', $MODULE)}</option>
			{foreach item=FOLDER from=$FOLDERS}
				<option  data-editurl="{$FOLDER->getEditUrl()}" id="filterOptionId_{$FOLDER->getId()}" class="filterOptionId_{$FOLDER->getId()}" data-deletable="{$FOLDER->isDeletable()}" data-editable="{$FOLDER->isEditable()}" data-deleteurl="{$FOLDER->getDeleteUrl()}" value="{$FOLDER->getId()}" data-id="{$FOLDER->getId()}" {if $VIEWNAME eq $FOLDER->getId()}selected=""{/if}>{vtranslate($FOLDER->getName(), $MODULE)}</option>
			{/foreach}
		</optgroup>
	</select>
</span>
<span class="hide filterActionImages pull-right">
	<i title="{vtranslate('LBL_DELETE', $MODULE)}" data-value="delete" class="icon-trash alignMiddle deleteFilter filterActionImage pull-right"></i>
	<i title="{vtranslate('LBL_EDIT', $MODULE)}" data-value="edit" class="icon-pencil alignMiddle editFilter filterActionImage pull-right"></i>
</span>
{/strip}