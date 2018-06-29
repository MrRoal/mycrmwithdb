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
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<table class="table table-bordered detailview-table">
		<thead>
			<tr>
				<th class="blockHeader" colspan="4">{vtranslate('Email Template - Properties of ', $MODULE_NAME)} " {decode_html($RECORD->get('templatename'))} "</th>
			</tr>
		</thead>
		<tbody 
			<tr>
				<td class="fieldLabel {$WIDTHTYPE}"><label class="muted marginRight10px">{vtranslate('Templatename', $MODULE_NAME)}</label></td>
				<td class="fieldValue {$WIDTHTYPE}">{decode_html($RECORD->get('templatename'))}</td>
			</tr>
			<tr>
				<td class="fieldLabel {$WIDTHTYPE}"><label class="muted marginRight10px">{vtranslate('Description', $MODULE_NAME)}</label></td>
				<td class="fieldValue {$WIDTHTYPE}">{decode_html($RECORD->get('description'))}</td>
			</tr>
			<tr>
				<td class="fieldLabel {$WIDTHTYPE}"><label class="muted marginRight10px">{vtranslate('Subject',$MODULE_NAME)}</label></td>
				<td class="fieldValue {$WIDTHTYPE}">{decode_html($RECORD->get('subject'))}</td>
			</tr>
			<tr>
				<td class="fieldLabel {$WIDTHTYPE}"><label class="muted marginRight10px">{vtranslate('Message',$MODULE_NAME)}</label></td>
				<td class="fieldValue {$WIDTHTYPE}">{decode_html($RECORD->get('body'))}</td>
			</tr>
		</tbody>
	</table>
{/strip}