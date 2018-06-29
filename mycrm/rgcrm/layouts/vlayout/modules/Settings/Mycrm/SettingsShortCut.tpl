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
	<span id="shortcut_{$SETTINGS_SHORTCUT->getId()}" data-actionurl="{$SETTINGS_SHORTCUT->getPinUnpinActionUrl()}" class="span3 contentsBackground well cursorPointer moduleBlock" data-url="{$SETTINGS_SHORTCUT->getUrl()}">
		<button data-id="{$SETTINGS_SHORTCUT->getId()}" title="{vtranslate('LBL_REMOVE',$MODULE)}" style="margin-right: -2%;margin-top: -5%;" title="Close" type="button" class="unpin close hide">x</button>
		<h5 class="themeTextColor">{vtranslate($SETTINGS_SHORTCUT->get('name'),$SETTINGS_SHORTCUT->getModuleNameFromUrl($SETTINGS_SHORTCUT->get('linkto')))}</h5>
        <div>{vtranslate($SETTINGS_SHORTCUT->get('description'),$SETTINGS_SHORTCUT->getModuleNameFromUrl($SETTINGS_SHORTCUT->get('linkto')))}</div>
	</span>
{/strip}	