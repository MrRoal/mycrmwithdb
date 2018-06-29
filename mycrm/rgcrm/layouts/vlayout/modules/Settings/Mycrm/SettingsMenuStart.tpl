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
{include file="Header.tpl"|vtemplate_path:$MODULE}
{include file="BasicHeader.tpl"|vtemplate_path:$MODULE}

{strip}
{if $LOAD_OLD}
    <div class="bodyContents">
        <div class="mainContainer row-fluid">
            <div class="span12">
                <iframe id="ui5frame" src="{$UI5_URL}" width="100%" height="650px" style="border:0;"></iframe>
            </div>

{else}
    <div class="bodyContents">
        <div class="mainContainer row-fluid">
            <div class="span2 row-fluid">
				<div>{include file='Sidebar.tpl'|@vtemplate_path:$QUALIFIED_MODULE}</div>
            </div>
    		<div class="contentsDiv span10 marginLeftZero">
{/if}
{/strip}