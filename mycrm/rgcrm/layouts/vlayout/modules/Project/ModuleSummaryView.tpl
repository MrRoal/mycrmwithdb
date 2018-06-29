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
	<div class="recordDetails">
		<div>
			<h4> {vtranslate('LBL_RECORD_SUMMARY',$MODULE_NAME)}	</h4>
			<hr>
		</div>
        {foreach item=SUMMARY_CATEGORY from=$SUMMARY_INFORMATION}
            <div class="row-fluid textAlignCenter roundedCorners">
                {foreach key=FIELD_NAME item=FIELD_VALUE from=$SUMMARY_CATEGORY}
                    <span class="well squeezedWell span3">
                        <div>
                            <label class="font-x-small">
                                {vtranslate($FIELD_NAME,$MODULE_NAME)}
                            </label>
                        </div>
                        <div>
                            <label class="font-x-x-large">
                                {if !empty($FIELD_VALUE)}{$FIELD_VALUE}{else}0{/if}
                            </label>
                        </div>
                    </span>
                {/foreach}
            </div>
        {/foreach}
		{include file='SummaryViewContents.tpl'|@vtemplate_path}
	</div>
{/strip}