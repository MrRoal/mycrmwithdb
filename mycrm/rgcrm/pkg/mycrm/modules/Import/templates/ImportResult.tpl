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
<div id="toggleButton" class="toggleButton" title="{vtranslate('LBL_LEFT_PANEL_SHOW_HIDE', 'Mycrm')}">
	<i id="tButtonImage" class="{if $LEFTPANELHIDE neq '1'}icon-chevron-left{else}icon-chevron-right{/if}"></i>
</div>&nbsp
<div style="padding-left: 15px;">
    <input type="hidden" name="module" value="{$FOR_MODULE}" />
    <table style=" width:90%;margin-left: 5% " cellpadding="5" class="searchUIBasic well">
        <tr>
            <td class="font-x-large" align="left" colspan="2">
                <strong>{'LBL_IMPORT'|@vtranslate:$MODULE} {$FOR_MODULE|@vtranslate:$FOR_MODULE} - {'LBL_RESULT'|@vtranslate:$MODULE}</strong>
            </td>
        </tr>
        {if $ERROR_MESSAGE neq ''}
            <tr>
                <td class="style1" align="left" colspan="2">
                    {$ERROR_MESSAGE}
                </td>
            </tr>
        {/if}
        <tr>
            <td valign="top">
                {include file="Import_Result_Details.tpl"|@vtemplate_path:'Import'}
            </td>
        </tr>
        <tr>
            <td align="right" colspan="2">
                {include file='Import_Finish_Buttons.tpl'|@vtemplate_path:'Import'}
            </td>
        </tr>
    </table>
</div>
{/strip}