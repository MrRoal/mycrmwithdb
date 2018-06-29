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
<div id="popupPageContainer" class="popupBackgroundColor">
	<div class="emailTemplatesContainer">
		<h3>{vtranslate($MODULE,$QUALIFIED_MODULE)}</h3>
		<hr>
		<div style="padding:0 10px">
			<table class="table table-bordered table-condensed">
				<thead>
					<tr class="listViewHeaders">
						<th>
							<a>{vtranslate('LBL_TEMPLATE_NAME',$QUALIFIED_MODULE)}</a>
						</th>
						<th>
							<a>{vtranslate('LBL_SUBJECT',$QUALIFIED_MODULE)}</a>
						</th>
						<th>
							<a>{vtranslate('LBL_DESCRIPTION',$QUALIFIED_MODULE)}</a>
						</th>
					</tr>
				</thead>
				{foreach item=EMAIL_TEMPLATE from=$EMAIL_TEMPLATES}
				<tr class="listViewEntries" data-id="{$EMAIL_TEMPLATE->get('templateid')}" data-name="{$EMAIL_TEMPLATE->get('subject')}" data-info="{$EMAIL_TEMPLATE->get('body')}">
					<td><a class="cursorPointer">{vtranslate($EMAIL_TEMPLATE->get('templatename',$QUALIFIED_MODULE))}</a></td>
					<td><a class="cursorPointer">{vtranslate($EMAIL_TEMPLATE->get('subject',$QUALIFIED_MODULE))}</a></td>
					<td>{vtranslate($EMAIL_TEMPLATE->get('description',$QUALIFIED_MODULE))}</td>
				</tr>
				{/foreach}
			</table>
		</div>
	</div>
		<input type="hidden" class="triggerEventName" value="{getPurifiedSmartyParameters('triggerEventName')}"/>
</div>
{/strip}