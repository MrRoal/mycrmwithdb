/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class("Settings_Mycrm_Detail_Js",{},{
	detailViewForm : false,

	/**
	 * Function which will give the detail view form
	 * @return : jQuery element
	 */
	getForm : function() {
		if(this.detailViewForm == false) {
			this.detailViewForm = jQuery('#detailView');
		}
		return this.detailViewForm;
	},

	/**
	 * Function to register form for validation
	 */
	registerFormForValidation : function(){
		var detailViewForm = this.getForm();
		detailViewForm.validationEngine(app.validationEngineOptions);
	},

	/**
	 * Function which will handle the registrations for the elements
	 */
	registerEvents : function() {
		this.registerFormForValidation();
	}
});