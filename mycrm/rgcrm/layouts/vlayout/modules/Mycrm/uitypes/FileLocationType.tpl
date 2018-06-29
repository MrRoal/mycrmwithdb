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
{assign var="FIELD_INFO" value=Mycrm_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var=FIELD_VALUES value=$FIELD_MODEL->getFileLocationType()}
<select class="chzn-select" name="{$FIELD_MODEL->getFieldName()}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Mycrm_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO}' >
{foreach item=TYPE key=KEY from=$FIELD_VALUES}
	<option value="{$KEY}" {if $FIELD_MODEL->get('fieldvalue') eq $KEY} selected {/if}>{vtranslate($TYPE, $MODULE)}</option>
{/foreach}
</select>
{/strip}