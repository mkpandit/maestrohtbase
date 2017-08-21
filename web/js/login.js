$(document).ready(function(){

// authorisation works:

document.execCommand('ClearAuthenticationCache', 'false');

//var height = $(window).height();
//height = (height - 100)/2;
//height = height + 'px';
//console.log(height);
//$('#loginwindow').css('top', height);
	

// Create Base64 Object

	$('#btnlogin').click(function(){
		var username = $("#userlogin").val();
		var password = $("#userpassword").val();  

		if ( username == "" ) {
			username = 'bad';
			password = 'bad';
		}
		console.log(username);
		console.log(password);
		$.ajax
		({
		  type: "GET",
		  url: "/htvcenter/base/",
		  username: username,
  		  password: password,
		 

	      success: function(){
	      	console.log('ok');
	      	var domain = document.domain;
			location.href = 'http://'+username+':'+password+'@'+domain+'/htvcenter/base/';
	        //setTimeout(function() { window.location = '/cloud-fortis/user/'; }, 2000);
	      },

	      error: function(){
	      	alert('Invalid Credentials');
	      }
		});
	});
	
	$('#userlogin, #userpassword').keypress(function (e) {
	  if (e.which == 13) {
	    //$('form#login').submit();
		var username = $("#userlogin").val();
		var password = $("#userpassword").val();  

		if ( username == "" ) {
			username = 'bad';
			password = 'bad';
		}
		console.log(username);
		console.log(password);
		$.ajax
		({
		  type: "GET",
		  url: "/htvcenter/base/",
		  username: username,
  		  password: password,
		 

	      success: function(){
	      	console.log('ok');
	      	var domain = document.domain;
			location.href = 'http://'+username+':'+password+'@'+domain+'/htvcenter/base/';
	        //setTimeout(function() { window.location = '/cloud-fortis/user/'; }, 2000);
	      },

	      error: function(){
	      	alert('Invalid Credentials');
	      }
		});
	    return false;    //<---- Add this line
	  }
	});
		
	// --- end authorisation works ---

	});