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
<div class="modelContainer">
    <div class="modal-header contentsBackground">
        <button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">&times;</button>
        <h3>{vtranslate('LBL_CREATE_FOLLOWUP_EVENT', "Events")}</h3>
    </div>
    {assign var=RECORD_ID value="{$RECORD_MODEL->get('id')}"}
    {assign var="dateFormat" value=$USER_MODEL->get('date_format')}
    {assign var="timeformat" value=$USER_MODEL->get('hour_format')}
    {assign var="currentDate" value=Mycrm_Date_UIType::getDisplayDateValue('')}
    {assign var="time" value=Mycrm_Time_UIType::getDisplayTimeValue(null)}
    {assign var="currentTimeInMycrmFormat" value=Mycrm_Time_UIType::getDisplayValue($time)}
    {assign var=FOLLOW_UP_LABEL value={vtranslate('LBL_HOLD_FOLLOWUP_ON',"Events")}}
    
    <form class="form-horizontal followupCreateView" id="followupQuickCreate" name="followupQuickCreate" method="post" action="index.php">
        <input type="hidden" name="module" value="{$MODULE}">
        <input type="hidden" name="action" value="SaveFollowupAjax" />
        <input type="hidden" name="mode" value="createFollowupEvent">
        <input type="hidden" name="record" value="{$RECORD_ID}" />
        <input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}" />
        <input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}" />
        <input class="dateField" type="hidden" name="date_start" value="{$STARTDATE}" data-date-format="{$dateFormat}" data-fieldinfo="{Mycrm_Util_Helper::toSafeHTML(ZEND_JSON::encode($STARTDATEFIELDMODEL))}"/>
        <div class="modal-body" style="padding:0px">
            {$FIELD_INFO['label'] = {$FOLLOW_UP_LABEL}}
            <br />
            <div class="control-group">
                <div class="control-label">
                    <label class="muted">
                        {$FOLLOW_UP_LABEL}
                    </label>
                </div>
                <div class="controls">
                        <div class="input-append row-fluid">
                            <div class="span10 row-fluid date">
                                <input name="followup_date_start" type="text" class="span9 dateField" data-date-format="{$dateFormat}" type="text"  data-fieldinfo= '{Mycrm_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_INFO))}'
                                       value="{$currentDate}" data-validation-engine="validate[ required,funcCall[Mycrm_Base_Validator_Js.invokeValidation]] validate[funcCall[Mycrm_greaterThanDependentField_Validator_Js.invokeValidation,]]" />
                                <span class="add-on"><i class="icon-calendar"></i></span>
                            </div>	
                        </div>		
                </div>
                <div class="controls">
                    <div class="input-append time">
                        <input type="text" name="followup_time_start" class="timepicker-default input-small" 
                               value="{$currentTimeInMycrmFormat}" data-format="{$timeformat}" data-validation-engine="validate[ required,funcCall[Mycrm_Base_Validator_Js.invokeValidation]]" />
                        <span class="add-on cursorPointer">
                            <i class="icon-time"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="tab-content overflowVisible">
                <div class="modal-footer quickCreateActions">
                        <a class="cancelLink cancelLinkContainer pull-right" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                        <button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_CREATE', $MODULE)}</strong></button>
                </div>
            </div>
        </div>
    </form>
</div>  
{/strip}

