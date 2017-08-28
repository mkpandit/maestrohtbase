<!DOCTYPE html>
<html>
<head>
<title>Maestro Enterprise Login</title>

<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/login.js"></script>

<link rel="stylesheet" type="text/css" href="css/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="css/bootstrap/js/bootstrap.min.js">
<link rel="stylesheet" type="text/css" href="css/fontawesome/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="css/login.css">
<link href="http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&amp;subset=latin" rel="stylesheet">

</head>
<body>

<div id="fullbg">
	
	<div id="loginside">
		
		<img src="logo.png" alt="Maestro by HTBase" title="Maestro by HTBase" />
		<h1>MAESTRO</h1>
		<div id="loginwindow">

			<div class="form-inline">
				<!-- <label class="sr-onlys" for="exampleInputAmount">Username:</label> -->
		  	  	
				<div class="form-group">
		    		<div class="input-group">
		      		  <!-- <div class="input-group-addon"><i class="fa fa-user"></i></div> -->
		      			<input type="text" class="form-control" id="userlogin" placeholder="Username">
		      
		    		</div>
		  	  	</div>
		  	  
			  	
		 	 	<!-- <label class="sr-onlys" for="exampleInputAmount">Password:</label> -->
		  	  	
				<div class="form-group">
		    		<div class="input-append input-group">
		      	  	  <!-- <div class="input-group-addon"><i class="fa fa-lock"></i></div> -->
					  <input type="password" class="form-control" id="userpassword" placeholder="Password">
					  <span id="btnlogin-arrow" class="fa fa-arrow-right login-arrow"></span>
		    		</div>
		  		</div>
		  
		  	  	<!-- <div class="form-group">
					<div class="input-group">
		  	  			<button type="submit" class="btn btn-primary" id="btnlogin">Log In to Maestro</button>
					</div>
				</div> -->
		  	  	<!-- <div class="row addlinks"></div> -->
			</div>

		</div>
	</div>
	<div class="maestr-footer">&copy; <?php echo date("Y"); ?> HTBASE</div>
</div>
</body>
</html>

<!-- /*
	HTVCenter Enterprise developed by HTBase Corp. All source code and content (c) Copyright 2015, htvcenter Corp unless specifically noted otherwise. This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp. By using this software, you acknowledge having read the license and agree to be bound thereby.

	http://www.htbase.com, Copyright 2015, HTBase Corp <contact@htbase.com>
*/ -->