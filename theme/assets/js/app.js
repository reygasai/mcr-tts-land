'use strict';

let app = {
  toast: function(title, message, color = 1) {
    switch (color) {
      case 1: color = '#e46259'; break;
      case 2: color = '#5f943f'; break;
      case 3: color = '#466280'; break;
      default: color = '';
    }

    iziToast.show({
    	theme: 'dark',
      title: title,
      message: message,
      color: color,
      position: 'topRight',
    });
  },
  getUrlParam: function(name){
		name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
		  results = regex.exec(location.search);
		return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
  },

	getUrlParams: function(){
    let params = decodeURIComponent(location.search.split('?')[1]),
        expl = [],
        result = {};
    if (params === undefined) return result;
		$.each(params.split('&'), function(key, value) {
			expl = value.split('=');
			result[expl[0]] = expl[1];
		});
		return result;
  },

	changeUrlParam: function(array) {
    let url_params = app.getUrlParams(),
        url = '?';
		$.each(array, function(key, value) {
			if(url_params[key] === undefined || value !== false) url_params[key] = value;
			if(value === false && url_params[key] !== undefined) delete url_params[key];
		});
		if(Object.keys(url_params).length <= 0) {
      location.search = '';
      return false;
    }
		$.each(url_params, function(key, value){
			url = url+key+'='+value+'&';
		});
		url = url.substring(0, url.length - 1);
		location.search = url;
		return true;
	},
}

$(document).ready(() => {
  $('form').prepend('<input type="hidden" name="csrf_secure_key" value="'+csrf_key+'">');
  if($('.alert').length) {
    let timeout = setTimeout(()=> {
      $('.alert.fadeIn').removeClass('fadeIn').fadeOut();
    }, 5000);
    $('.alert.fadeIn').hover(()=>{
      clearTimeout(timeout);
    }, function(){
      timeout = setTimeout(()=> {
        $(this).removeClass('fadeIn').fadeOut();
      }, 5000);
    });
  }
});
