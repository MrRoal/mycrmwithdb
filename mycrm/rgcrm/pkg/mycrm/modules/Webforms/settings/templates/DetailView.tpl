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
{include file="DetailViewHeader.tpl"|vtemplate_path:Mycrm MODULE_NAME=$MODULE_NAME}
{include file='DetailViewBlockView.tpl'|@vtemplate_path:Mycrm RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
{include file='FieldsDetailView.tpl'|@vtemplate_path:$MODULE_NAME RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
</div></div>
{/strip}