$(document).ready(function(){

	// authorisation works:
	document.execCommand('ClearAuthenticationCache', 'false');
	var height = $(window).height();
	height = (height - 100)/2;
	height = height + 'px';
	//$('#loginwindow').css('top', height);
	// Create Base64 Object
	var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}

	function make_base_auth(user, password) {
	 	var tok = user + ':' + password;
	 	var hash = Base64.encode(tok);
		return "Basic " + hash;
	}

	function cloudScoreLogin(){

		var username = $("input#userlogin").val();
		var password = $("input#userpassword").val();
		if(username == ""){
			var errmsg = "Username can not be empty";
		}
		if(password == ""){
			var errmsg = "Password can not be empty";
		}
		if(username == "" && password == ""){
			var errmsg = "Username and Password can not be empty";
		}
		var full = location.protocol+'//'+location.hostname+(location.port ? ':'+location.port: '');
		var domainpart = location.hostname+(location.port ? ':'+location.port: '');
		var ajaxurl = full + "/cloud-fortis/user/";	

		console.log(domainpart);
		console.log(ajaxurl);
		console.log(username);
		console.log(password);

		$.ajax({
			type: "GET",
			url: ajaxurl,
			headers: {"Authorization": "Basic " + btoa(username + ":" + password)},
			beforeSend: function (xhr){xhr.setRequestHeader('Authorization', make_base_auth(username, password));},
			success: function(){
				var domain = domainpart;
				location.href = 'http://'+domain+'/cloud-fortis/user/';
				//location.href = 'http://'+username+':'+password+'@'+domain+'/cloud-fortis/user/';
				//setTimeout(function() { window.location = '/cloud-fortis/user/'; }, 2000);
			},
			error: function(){
				$("#tab_currenttab3").css("display","inline");  // show recover password link

				if(errmsg){
					alert(errmsg);
				} else {
					alert('Credentials provided is not valid');
				}
			}
		});
	}
	$('#btnlogin').click(function(){
		cloudScoreLogin();
	});
	$("#userpassword").keypress(function(e){
		if(e.which == 13){
			cloudScoreLogin();
		}
	});
	$("#userlogin").keypress(function(e){
		if(e.which == 13){
			cloudScoreLogin();
		}
	});
	// --- end authorisation works ---

	$("#tab_register_tab1 a").attr("href","#");
	$("#tab_register_tab2 a").attr("href","#");
	$("#tab_register_tab3 a").attr("href","#");

	$("#tab_register_tab1 a").click(function () {
		$("#accountModal").modal("show");
		return false;
	});

	$("#tab_register_tab2 a").click(function () {
		$("#activateModal").modal("show");
		return false;
	});

	$("#tab_register_tab3 a").click(function () {
		$("#recoverModal").modal("show");
		return false;
	});
});
