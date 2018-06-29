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
<div id="listPriceUpdateContainer">
	<div class="modal-header">
		<button data-dismiss="modal" class="pull-right"><i class="icon-remove alignMiddle"></i></button>
		<h3>{vtranslate('LBL_EDIT_LIST_PRICE', $MODULE)}</h3>
	</div>	
	<form class="form-horizontal" id="listPriceUpdate" method="post" action="index.php">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="action" value="RelationAjax" />
		<input type="hidden" name="src_record" value="{$PRICEBOOK_ID}" />
		<input type="hidden" name="relid" value="{$REL_ID}" />
		<div class="modal-body">
			<div>
				<span><strong>{vtranslate('LBL_EDIT_LIST_PRICE',$MODULE)}</strong></span>
				&nbsp;:&nbsp;
				<input type="text" name="currentPrice" value="{$CURRENT_PRICE}" data-validation-engine="validate[required,funcCall[Mycrm_Currency_Validator_Js.invokeValidation]]" 
						   data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}' />
			</div>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
	</form>	
</div>
{/strip}	