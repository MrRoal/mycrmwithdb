/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

Mycrm_Popup_Js("PriceBooks_Popup_Js",{},{
	
	/**
	 * Function to pass params for request
	 */
	getCompleteParams : function(){
		var params = this._super();
		params['currency_id'] = jQuery('#currencyId').val();
		return params;
	},
	
	registerEvents: function(){
		this._super();
	}
});

