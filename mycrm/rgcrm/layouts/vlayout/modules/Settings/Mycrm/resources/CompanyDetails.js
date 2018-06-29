/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class("Settings_Mycrm_CompanyDetails_Js",{},{
	
	registerUpdateDetailsClickEvent : function() {
		jQuery('#updateCompanyDetails').on('click',function(e){
			jQuery('#CompanyDetailsContainer').addClass('hide');
			jQuery('#updateCompanyDetailsForm').removeClass('hide');
            jQuery('#updateCompanyDetails').addClass('hide');
		});
	},
	
	registerSaveCompanyDetailsEvent : function() {
		var thisInstance = this;
		jQuery('#updateCompanyDetailsForm').on('submit',function(e) {
			var result = thisInstance.checkValidation();
			if(result == false){
				return result;
				e.preventDefault();
			}
		});
	},
	
	registerCancelClickEvent : function () {
		jQuery('.cancelLink').on('click',function() {
			jQuery('#CompanyDetailsContainer').removeClass('hide');
			jQuery('#updateCompanyDetailsForm').addClass('hide');
            jQuery('#updateCompanyDetails').removeClass('hide');
		});
	},
	
	checkValidation : function() {
		var imageObj = jQuery('#logoFile');
		var imageName = imageObj.val();
		if(imageName != '') {
			var image_arr = new Array();
			image_arr = imageName.split(".");
			var image_arr_last_index = image_arr.length - 1;
			if(image_arr_last_index < 0) {
				imageObj.validationEngine('showPrompt', app.vtranslate('LBL_WRONG_IMAGE_TYPE') , 'error','topLeft',true);
				imageObj.val('');
				return false;
			}
			var image_extensions = JSON.parse(jQuery('#supportedImageFormats').val());
			var image_ext = image_arr[image_arr_last_index].toLowerCase();
			if(image_extensions.indexOf(image_ext) != '-1') {
				var size = imageObj[0].files[0].size;
				if (size < 1024000) {
					return true;
				} else {
					imageObj.validationEngine('showPrompt', app.vtranslate('LBL_MAXIMUM_SIZE_EXCEEDS') , 'error','topLeft',true);
					return false;
				}
			} else {
				imageObj.validationEngine('showPrompt', app.vtranslate('LBL_WRONG_IMAGE_TYPE') , 'error','topLeft',true);
				imageObj.val('');
				return false;
			}
	
		}
	},
	
	registerEvents: function() {
		this.registerUpdateDetailsClickEvent();
		this.registerSaveCompanyDetailsEvent();
		this.registerCancelClickEvent();
		jQuery('#updateCompanyDetailsForm').validationEngine(app.validationEngineOptions);
	}

});

jQuery(document).ready(function(e){
	var instance = new Settings_Mycrm_CompanyDetails_Js();
	instance.registerEvents();
})