/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

Mycrm_Popup_Js("Inventory_Popup_Js",{},{
	
	subProductsClickEvent : function() {
		var thisInstance = this;
		var popupPageContentsContainer = this.getPopupPageContainer();
		popupPageContentsContainer.on('click','.subproducts',function(e){
			var rowElement = jQuery(e.currentTarget).closest('tr');
			e.stopPropagation();
			var params = {};
			params.view = 'SubProductsPopup';
			params.module = app.getModuleName();
			params.multi_select = true;
			params.subProductsPopup = true;
			params.productid = rowElement.data('id');
			jQuery('#recordsCount').val('');
			jQuery('#pageNumber').val("1");
			jQuery('#pageToJump').val('1');
			jQuery('#orderBy').val('');
			jQuery("#sortOrder").val('');
			AppConnector.request(params).then(function(data) {
				jQuery('#popupContentsDiv').html(data);
				jQuery('#totalPageCount').text('');
				thisInstance.registerEventForBackToProductsButtonClick();
				thisInstance.calculatePages().then(function(){
					thisInstance.updatePagination();					
				});
			});
		});
	},
	
	/**
	 * Function to register event for back to products button click
	 */
	registerEventForBackToProductsButtonClick : function(){
		jQuery('#backToProducts').on('click',function(){
			window.location.reload();
		})
	},
	
	/**
	 * Function to pass params for request
	 */
	getCompleteParams : function(){
		var params = this._super();
		var subProductsPopup = jQuery('#subProductsPopup').val();
		var parentProductId = jQuery('#parentProductId').val();
		if((typeof(subProductsPopup) != "undefined") && (typeof(parentProductId) != "undefined")){
			params['subProductsPopup'] = subProductsPopup;
			params['productid'] = parentProductId;
			params['view'] = 'SubProductsPopupAjax';
		}
		return params;
	},
	
	registerEvents: function(){
		this._super();
		this.subProductsClickEvent();
	}
});

