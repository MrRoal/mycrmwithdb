/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/
Mycrm_BaseValidator_Js("Mycrm_PhoneValidator_Js",{},{
	error: "",
	validate: function(){
		var field = this.fieldInfo;
		var fieldValue = field.val();
		var strippedValue = fieldValue.replace(/[\(\)\.\-\ ]/g, '');

	   if (fieldValue == "") {

			this.getEmptyPhoneNumberError();

		} else if (isNaN(parseInt(strippedValue))) {

			this.getPhoneNumberIllegalCharacterError();

		} else if (!(strippedValue.length == 10)) {
			
			this.getPhoneNumberWrongLengthError();

		}
	},

	getEmptyPhoneNumberError: function(){
		this.error = "You didn't enter a phone number.\n";
		return this.error;
	},

	getPhoneNumberIllegalCharacterError: function(){
		this.error = "The phone number contains illegal characters.\n";
		return this.error;
	},

	getPhoneNumberWrongLengthError: function(){
		this.error = "The phone number is the wrong length. Make sure you included an area code.\n";
		return this.error;
	}
})