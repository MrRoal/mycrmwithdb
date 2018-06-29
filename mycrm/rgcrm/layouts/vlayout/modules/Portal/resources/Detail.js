/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/
Mycrm_Detail_Js("Portal_Detail_Js",{},{
    
    registerAddBookmark : function() {
        jQuery('#addBookmark').click(function() {
            var params = {
                'module' : app.getModuleName(),
                'parent' : app.getParentModuleName(),
                'view' : 'EditAjax'
            };
            Portal_List_Js.editBookmark(params);
        });
    },
    
    registerDetailViewChangeEvent : function() {
        jQuery('#bookmarksDropdown').change(function() {
            var selectedBookmark = jQuery('#bookmarksDropdown').val();
            var progressIndicatorElement = jQuery.progressIndicator({
                'position' : 'html',
                'blockInfo' : {
                    'enabled' : true
                }
            });
            var url = 'index.php?module='+app.getModuleName()+'&view=Detail&record='+selectedBookmark;
            window.location.href = url;
        });
    },
    
    registerEvents : function(){
        this._super();
        this.registerAddBookmark();
        this.registerDetailViewChangeEvent();
    }
});