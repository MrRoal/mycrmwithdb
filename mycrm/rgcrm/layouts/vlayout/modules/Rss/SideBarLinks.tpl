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
<div class="quickLinksDiv">
    {assign var=SIDEBARLINK value=$QUICK_LINKS['SIDEBARLINK'][0]}
    <div style="margin-bottom: 5px" class="btn-group row-fluid">
        <button id="rssAddButton" class="btn addButton span12 rssAddButton" data-href="{$SIDEBARLINK->getUrl()}">
            <img src="layouts/vlayout/skins/images/rss_add.png" />
            <strong>&nbsp;&nbsp; {vtranslate($SIDEBARLINK->getLabel(), $MODULE)}</strong>
        </button>
    </div>
    <div class="rssAddFormContainer hide">
    </div>
</div>
{/strip}