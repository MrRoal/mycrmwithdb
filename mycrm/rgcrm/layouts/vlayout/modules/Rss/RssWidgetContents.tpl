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
<div class="recordNamesList">
    <div class="row-fluid">
        <div class="span10">
            <ul class="nav nav-list">
                {foreach item=recordsModel from=$RSS_SOURCES}
                <li>
                    <a href="#" class="rssLink" data-id={$recordsModel->getId()} data-url="{$recordsModel->get('rssurl')}" title="{decode_html($recordsModel->getName())}">{decode_html($recordsModel->getName())}</a>
                </li>
                {foreachelse}
                    <li style="text-align:center">{vtranslate('LBL_NO_RECORDS', $MODULE)}
                    </li>
                {/foreach}

            </ul>
        </div>
    </div>
</div>
{/strip}