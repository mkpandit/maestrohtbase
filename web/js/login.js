$(document).ready(function(){
	
document.execCommand('ClearAuthenticationCache', 'false');

$('#btnlogin-arrow').click(function(){
	var username = $("#userlogin").val();
	var password = $("#userpassword").val();  

	if ( username == "" ) {
		username = 'Provide a valid username';
		password = 'Provide a valid password';
	}
	console.log(username);
	console.log(password);
	$.ajax({
		type: "GET",
		url: "/htvcenter/base/",
		username: username,
		password: password,
		success: function(){
			var domain = document.domain;
			location.href = 'http://'+domain+'/htvcenter/base/';
		},
		error: function(){
			alert('Invalid Credentials');
		}
	});
});
	
$('#userlogin, #userpassword').keypress(function (e) {
	if (e.which == 13) {
		var username = $("#userlogin").val();
		var password = $("#userpassword").val();  
		if ( username == "" ) {
			username = 'Provide a valid username';
			password = 'Provide a valid password';
		}
		console.log(username);
		console.log(password);
		$.ajax({
			type: "GET",
			url: "/htvcenter/base/",
			username: username,
			password: password,
			success: function(){
				var domain = document.domain;
				location.href = 'http://'+domain+'/htvcenter/base/';
			},
			error: function(){
				alert('Invalid Credentials');
			}
		});
		return false;
	}
});
	// --- end authorisation works ---
});