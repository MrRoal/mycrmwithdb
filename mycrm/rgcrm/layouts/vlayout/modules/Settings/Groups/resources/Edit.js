/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

Settings_Mycrm_Edit_Js('Settings_Groups_Edit_Js', {}, {
	memberSelectElement : false,
	
	getMemberSelectElement : function () {
		if(this.memberSelectElement == false) {
			this.memberSelectElement = jQuery('#memberList');
		}
		return this.memberSelectElement;
	},
	/**
	 * Function to register event for select2 element
	 */
	registerEventForSelect2Element : function(){
		var editViewForm = this.getForm();
		var selectElement = this.getMemberSelectElement();
		var params = {};
		params.dropdownCss = {'z-index' : 0};
		params.formatSelection = function(object,container){
			var selectedId = object.id;
			var selectedOptionTag = editViewForm.find('option[value="'+selectedId+'"]');
			var selectedMemberType = selectedOptionTag.data('memberType');
			container.addClass(selectedMemberType);
			var element = '<div>'+selectedOptionTag.text()+'</div>';
			return element;
		}
		app.changeSelectElementView(selectElement, 'select2',params);
	},
	
	/**
	 * Function to register form for validation
	 */
	registerFormForValidation : function(){
		var editViewForm = this.getForm();
		editViewForm.validationEngine(app.getvalidationEngineOptions(true));
	},
	
	/**
	 * Function to register the submit event of form
	 */
	registerSubmitEvent : function() {
		var thisInstance = this;
		var form = jQuery('#EditView');
		form.on('submit',function(e) {
			if(form.data('submit') == 'true' && form.data('performCheck') == 'true') {
				return true;
			} else {
				if(form.data('jqv').InvalidFields.length <= 0) {
					var formData = form.serializeFormData();
					thisInstance.checkDuplicateName({
						'groupname' : formData.groupname,
						'record' : formData.record
					}).then(
						function(data){
							form.data('submit', 'true');
							form.data('performCheck', 'true');
							form.submit();
						},
						function(data, err){
							var params = {};
							params['text'] = data['message'];
							params['type'] = 'error';
							Settings_Mycrm_Index_Js.showMessage(params);
							return false;
						}
					);
				} else {
					//If validation fails, form should submit again
					form.removeData('submit');
					// to avoid hiding of error message under the fixed nav bar
					app.formAlignmentAfterValidation(form);
				}
				e.preventDefault();
			}
		});
	},
	
	/*
	 * Function to check Duplication of Group Names
	 * returns boolean true or false
	 */
	checkDuplicateName : function(details) {
		var aDeferred = jQuery.Deferred();
		
		var params = {
		'module' : app.getModuleName(),
		'parent' : app.getParentModuleName(),
		'action' : 'EditAjax',
		'mode'   : 'checkDuplicate',
		'groupname' : details.groupname,
		'record' : details.record
		}
		
		AppConnector.request(params).then(
			function(data) {
				var response = data['result'];
				var result = response['success'];
				if(result == true) {
					aDeferred.reject(response);
				} else {
					aDeferred.resolve(response);
				}
			},
			function(error,err){
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},
	
	/**
	 * Function which will handle the registrations for the elements 
	 */
	registerEvents : function() {
		this._super();
		this.registerEventForSelect2Element();
		this.registerSubmitEvent();
	}
});