/*+***********************************************************************************
 * The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
 *************************************************************************************/

(function( $ ) {

  var vtImageLoader = function(element, options) {
	var thisInstance = this;
	this.defaults = {
		'position' : 'append',
		'mode' : 'show'
	}

	if(typeof options == 'undefined'){
		options = {};
	}

	this.options = $.extend(this.defaults, options);
	this.container = element;
	this.position = options.position

	this.show = function(){
		var imagePath = 'themes/vlayout/images/loading.gif';
		var imageHtml = '<span class="imageHolder"><img class="loadinImg" src="'+imagePath+'" /></span>';

		switch(thisInstance.position) {
				case "prepend":
						thisInstance.container.prepend(imageHtml);
						break;
				case "html":
						thisInstance.container.html(imageHtml);
						break;
				case "replace":
						thisInstance.container.replaceWith(imageHtml);
						break;
				default:
					thisInstance.container.append(imageHtml);
		}
	}

	this.hide = function() {
		$('.imageHolder',this.container).remove();
	}


	if(this.options.mode == 'show'){
		this.show();
	}else if(this.options.mode == 'hide') {
		this.hide();
	}

  }

  $.fn.vtLoadImage = function(options) {
	return this.each(function(index, element){
		var jQueryObject = $(element);
		var imageLoader = new vtImageLoader(jQueryObject, options);
		jQueryObject.data('imageLoader',imageLoader);
	});

  };
})( jQuery );

