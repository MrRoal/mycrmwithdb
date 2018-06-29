{*<!--
/*********************************************************************************
** The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
{strip}
<div class="container-fluid" id="TermsAndConditionsContainer">
	<div class="widget_header row-fluid">
		<div class="row-fluid"><h3>{vtranslate('LBL_TERMS_AND_CONDITIONS', $QUALIFIED_MODULE)}</h3></div>
	</div>
	<hr>

    <div class="contents row-fluid">
		<br>
        <textarea class="input-xxlarge TCContent textarea-autosize" rows="3" placeholder="{vtranslate('LBL_SPECIFY_TERMS_AND_CONDITIONS', $QUALIFIED_MODULE)}" style="width:100%;" >{$CONDITION_TEXT}</textarea>
        <div class="row-fluid textAlignCenter">
            <br>
			<button class="btn btn-success saveTC hide"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
        </div>
    </div>
</div>
{/strip}