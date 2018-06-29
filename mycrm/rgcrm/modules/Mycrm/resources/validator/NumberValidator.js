/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/
Mycrm_BaseValidator_Js("Mycrm_NumberValidator_Js",{},{
	error: "",
	validate: function(){
		var field = this.fieldInfo;
		if(jQuery(field).attr('id') == "probability"){
			if (isNaN(field.val())) {
				// this allows the use of i18 for the error msgs
				this.getOnlyNumbersError;
			}else if(field.val() > 100){
				this.getProbabilityNumberError;
			}
		}
		if (isNaN(field.val())) {
			 // this allows the use of i18 for the error msgs
			this.getOnlyNumbersError;
		}
	},

	getOnlyNumbersError: function(){
		this.error = "please enter only numbers";
		return this.error;
	},

	getProbabilityNumberError: function(){
		this.error = "please enter only numbers less than 100 as field value is in percentage";
		return this.error;
	}
})