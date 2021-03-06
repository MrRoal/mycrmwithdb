{*<!--/************************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 ************************************************************************************/-->*}

{strip}
{if $FOLDERS}
    <div id="foldersList" class="row-fluid">
        <div class="span10">
            <ul class="nav nav-list">
                {foreach item=FOLDER from=$FOLDERS}
                    <li>
                        <a class="mm_folder" id='_mailfolder_{$FOLDER->name()}' href='#{$FOLDER->name()}' onclick="MailManager.clearSearchString(); MailManager.folder_open('{$FOLDER->name()}'); ">{if $FOLDER->unreadCount()}<b>{$FOLDER->name()} ({$FOLDER->unreadCount()})</b>{else}{$FOLDER->name()}{/if}</a>
                    </li>
                {/foreach}
            </ul>
        </div>
    </div>
{/if}
{/strip}