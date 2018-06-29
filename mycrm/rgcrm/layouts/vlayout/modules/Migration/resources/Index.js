/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class("Migration_Index_Js",{
	
	startMigrationEvent : function(){
		
		var migrateUrl = 'index.php?module=Migration&view=Index&mode=applyDBChanges';
			AppConnector.request(migrateUrl).then(
			function(data) {
				jQuery("#running").hide();
				jQuery("#success").show();
				jQuery("#nextButton").show();
				jQuery("#showDetails").show().html(data);
			})
	},
	
	registerEvents : function(){
		this.startMigrationEvent();
	}
	
});
