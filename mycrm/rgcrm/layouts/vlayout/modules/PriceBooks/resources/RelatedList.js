/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

Mycrm_RelatedList_Js("PriceBooks_RelatedList_Js",{},{
	
	/**
	 * Function to handle the popup show
	 */
	showSelectRelationPopup : function(){
		var thisInstance = this;
		var popupInstance = Mycrm_Popup_Js.getInstance();
		popupInstance.show(this.getPopupParams(), function(responseString){
				var responseData = JSON.parse(responseString);
				thisInstance.addRelations(responseData).then(
					function(data){
						var relatedCurrentPage = thisInstance.getCurrentPageNum();
						var params = {'page':relatedCurrentPage};
						thisInstance.loadRelatedList(params);
					}
				);
			}
		);
	},
	/**
	 * Function to get params for show event invocation
	 */
	getPopupParams : function(){
		var parameters = {
			'module' : this.relatedModulename,
			'src_module' :this.parentModuleName ,
			'src_record' : this.parentRecordId,
			 'view' : "PriceBookProductPopup",
			 'src_field' : 'priceBookRelatedList',
			'multi_select' : true
		}
		return parameters;
	},
	/**
	 * Function to handle the adding relations between parent and child window
	 */
	addRelations : function(idList){
		var aDeferred = jQuery.Deferred();
		var sourceRecordId = this.parentRecordId;
		var sourceModuleName = this.parentModuleName;
		var relatedModuleName = this.relatedModulename;

		var params = {};
		params['mode'] = "addListPrice";
		params['module'] = sourceModuleName;
		params['action'] = 'RelationAjax';
		
		params['related_module'] = relatedModuleName;
		params['src_record'] = sourceRecordId;
		params['relinfo'] = JSON.stringify(idList);
		AppConnector.request(params).then(
			function(responseData){
				aDeferred.resolve(responseData);
			}
			);
		return aDeferred.promise();
	}
})