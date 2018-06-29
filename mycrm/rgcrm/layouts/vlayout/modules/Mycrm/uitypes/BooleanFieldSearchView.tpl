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
    {assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
    {assign var=SEARCH_VALUES value=$SEARCH_INFO['searchValue']}
    <div class="row-fluid">
    <select class="select2 listSearchContributor" name="{$FIELD_MODEL->get('name')}" style="width:90px;" data-fieldinfo='{$FIELD_INFO|escape}'>
        <option value="">{vtranslate('LBL_SELECT_OPTION','Mycrm')}</option>
        <option value="1" {if $SEARCH_VALUES eq 1} selected{/if}>{vtranslate('LBL_YES',$MODULE)}</option>
        <option value="0" {if $SEARCH_VALUES eq '0'} selected{/if}>{vtranslate('LBL_NO',$MODULE)}</option>
    </select>
    </div>
{/strip}